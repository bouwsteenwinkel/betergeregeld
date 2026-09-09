#!/usr/bin/env python3
"""Beter Geregeld — VPS monitoring collector voor Linux.

De tegenhanger van collect.ps1. Zelfde endpoint, zelfde token-koptekst, zelfde
JSON-velden -- alleen de manier waarop de cijfers van de machine komen verschilt,
want /proc bestaat niet op Windows en WMI niet op Linux.

WAAROM PYTHON EN GEEN BASH. De payload heeft een geneste lijst (de zwaarste
processen). Die met de hand in shell-JSON plakken gaat één keer goed en daarna
stuk op een procesnaam met een aanhalingsteken erin. Python 3 staat op deze
machine al voor de telefonie zelf.

BUFFEREN. Een meting die niet verstuurd kan worden was anders gewoon weg, en dat
is precies de meting die je wilt hebben: als de machine het te druk heeft om te
antwoorden, mist de grafiek juist dán zijn punten. Wat niet weg kon gaan we de
volgende ronde alsnog na-leveren.

WAT DIT NIET OPLOST: start de timer zelf niet meer (machine te vol om een proces
te starten), dan is er niets om te bufferen. Het verschil tussen "agent stil" en
"machine stil" zie je aan agent_last_seen_at in de monitor.

Draait elke minuut via bsw-monitor.timer. Leest endpoint en token uit
/etc/bsw-monitor.env (of uit de omgeving).
"""

import json
import os
import subprocess
import time
import urllib.error
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

AGENT_VERSIE = "py-1.0"
ENV_BESTAND = Path("/etc/bsw-monitor.env")
BUFFER = Path("/var/lib/bsw-monitor/buffer.jsonl")
BUFFER_MAX = 720  # ~12 uur aan minuutmetingen; daarna de oudste laten vallen


def env_inlezen() -> dict:
    """Waarden uit /etc/bsw-monitor.env, met de echte omgeving als voorrang."""
    waarden = {}
    if ENV_BESTAND.is_file():
        for regel in ENV_BESTAND.read_text(encoding="utf-8").splitlines():
            regel = regel.strip()
            if not regel or regel.startswith("#") or "=" not in regel:
                continue
            sleutel, _, waarde = regel.partition("=")
            waarden[sleutel.strip()] = waarde.strip().strip('"').strip("'")
    for sleutel in ("MONITOR_ENDPOINT", "MONITOR_TOKEN"):
        if os.environ.get(sleutel):
            waarden[sleutel] = os.environ[sleutel]
    return waarden


def cpu_percent() -> float:
    """Gemiddelde bezetting over één seconde, uit /proc/stat.

    Eén momentopname zegt niets: /proc/stat telt tikken sinds het opstarten. Het
    verschil tussen twee metingen is de bezetting in dat venster.
    """

    def tikken():
        with open("/proc/stat", encoding="utf-8") as f:
            velden = [float(x) for x in f.readline().split()[1:]]
        idle = velden[3] + (velden[4] if len(velden) > 4 else 0.0)  # idle + iowait
        return sum(velden), idle

    totaal1, idle1 = tikken()
    time.sleep(1.0)
    totaal2, idle2 = tikken()

    d_totaal = totaal2 - totaal1
    d_idle = idle2 - idle1
    if d_totaal <= 0:
        return 0.0
    return round(max(0.0, min(100.0, (1.0 - d_idle / d_totaal) * 100.0)), 1)


def geheugen_mb() -> tuple:
    """(gebruikt, totaal) in MB.

    Gebruikt = totaal - MemAvailable, niet totaal - MemFree. Linux vult vrij
    geheugen met cache; MemFree ziet er daardoor altijd alarmerend laag uit
    terwijl die cache zo teruggegeven wordt zodra een proces hem nodig heeft.
    """
    kb = {}
    with open("/proc/meminfo", encoding="utf-8") as f:
        for regel in f:
            sleutel, _, rest = regel.partition(":")
            kb[sleutel] = float(rest.split()[0])
    totaal = kb.get("MemTotal", 0.0)
    beschikbaar = kb.get("MemAvailable", kb.get("MemFree", 0.0))
    return int(round((totaal - beschikbaar) / 1024)), int(round(totaal / 1024))


def schijf_gb(pad: str = "/") -> tuple:
    """(gebruikt, totaal) in GB voor het bestandssysteem onder `pad`."""
    s = os.statvfs(pad)
    totaal = s.f_blocks * s.f_frsize
    vrij = s.f_bavail * s.f_frsize  # bavail: wat een gewone gebruiker echt mag
    gebruikt = totaal - (s.f_bfree * s.f_frsize)
    return round(gebruikt / 1024**3, 2), round(totaal / 1024**3, 2)


def uptime_seconden() -> int:
    with open("/proc/uptime", encoding="utf-8") as f:
        return int(float(f.readline().split()[0]))


def zwaarste_processen(aantal: int = 8) -> list:
    """Per PROCESNAAM optellen, niet per los proces.

    Dezelfde reden als in de Windows-agent: tientallen kleine workers vullen
    samen het geheugen terwijl geen van hen ooit in een top-8 van losse
    processen komt. Juist die groep is de verdachte.
    """
    try:
        uit = subprocess.run(
            ["ps", "-eo", "comm=,rss=,time="],
            capture_output=True, text=True, timeout=10, check=True,
        ).stdout
    except (subprocess.SubprocessError, OSError):
        return []

    per_naam = {}
    for regel in uit.splitlines():
        delen = regel.split()
        if len(delen) < 3:
            continue
        naam, rss, tijd = delen[0], delen[1], delen[2]
        try:
            rss_kb = int(rss)
        except ValueError:
            continue
        # ps geeft de tijd als [dd-]hh:mm:ss
        seconden = 0
        dagen, _, klok = tijd.rpartition("-")
        stukken = klok.split(":")
        try:
            for stuk in stukken:
                seconden = seconden * 60 + int(stuk)
            if dagen:
                seconden += int(dagen) * 86400
        except ValueError:
            seconden = 0
        rij = per_naam.setdefault(naam, {"naam": naam, "n": 0, "mb": 0.0, "cpu_s": 0})
        rij["n"] += 1
        rij["mb"] += rss_kb / 1024
        rij["cpu_s"] += seconden

    rijen = sorted(per_naam.values(), key=lambda r: r["mb"], reverse=True)[:aantal]
    for rij in rijen:
        rij["mb"] = int(round(rij["mb"]))
    return rijen


def loadavg() -> list:
    with open("/proc/loadavg", encoding="utf-8") as f:
        return [float(x) for x in f.readline().split()[:3]]


def versturen(endpoint: str, token: str, regels: list) -> int:
    """Stuurt elke gebufferde meting; geeft terug hoeveel er zijn aangekomen."""
    gelukt = 0
    for regel in regels:
        verzoek = urllib.request.Request(
            endpoint,
            data=regel.encode("utf-8"),
            headers={
                "Content-Type": "application/json",
                "Authorization": f"Bearer {token}",
                "User-Agent": f"bsw-monitor/{AGENT_VERSIE}",
            },
            method="POST",
        )
        try:
            with urllib.request.urlopen(verzoek, timeout=20) as antwoord:
                if antwoord.status < 300:
                    gelukt += 1
                    continue
                break
        except urllib.error.HTTPError as fout:
            # 401/403 is een tokenprobleem en lost zichzelf niet op door te
            # blijven proberen; 429 en 5xx wel. Bij een tokenprobleem gooien we
            # de meting weg in plaats van de buffer te laten vollopen.
            if fout.code in (401, 403):
                gelukt += 1
                continue
            break
        except (urllib.error.URLError, TimeoutError, OSError):
            break
    return gelukt


def main() -> int:
    env = env_inlezen()
    endpoint = env.get("MONITOR_ENDPOINT", "https://betergeregeld.com/monitor/ingest")
    token = env.get("MONITOR_TOKEN", "")
    if not token:
        print("MONITOR_TOKEN ontbreekt (zet hem in /etc/bsw-monitor.env)")
        return 1

    cpu = cpu_percent()
    mem_gebruikt, mem_totaal = geheugen_mb()
    schijf_gebruikt, schijf_totaal = schijf_gb("/")
    mem_pct = round(mem_gebruikt / mem_totaal * 100, 1) if mem_totaal else 0.0

    # De zware uitvraag alleen doen als er iets aan de hand is: ps over alle
    # processen kost meer dan de rest van de meting samen, en dit draait elke
    # minuut.
    load = None
    if cpu >= 50 or mem_pct >= 80:
        load = {
            "reden": "druk",
            "cpu": cpu,
            "mem_pct": mem_pct,
            "mem_used_mb": mem_gebruikt,
            "loadavg": loadavg(),
            "top": zwaarste_processen(),
        }

    meting = {
        "collected_at": datetime.now(timezone.utc).isoformat(),
        "cpu_percent": cpu,
        "mem_used_mb": mem_gebruikt,
        "mem_total_mb": mem_totaal,
        "disk_used_gb": schijf_gebruikt,
        "disk_total_gb": schijf_totaal,
        "uptime_seconds": uptime_seconden(),
        "load": load,
        "agent_version": AGENT_VERSIE,
    }

    BUFFER.parent.mkdir(parents=True, exist_ok=True)
    wachtrij = []
    if BUFFER.is_file():
        wachtrij = [r for r in BUFFER.read_text(encoding="utf-8").splitlines() if r.strip()]
    wachtrij.append(json.dumps(meting, ensure_ascii=False))
    wachtrij = wachtrij[-BUFFER_MAX:]

    gelukt = versturen(endpoint, token, wachtrij)
    rest = wachtrij[gelukt:]
    BUFFER.write_text("\n".join(rest) + ("\n" if rest else ""), encoding="utf-8")

    print(f"verstuurd: {gelukt}/{len(wachtrij)} | cpu {cpu}% mem {mem_pct}% "
          f"schijf {schijf_gebruikt}/{schijf_totaal} GB")
    return 0 if gelukt else 2


if __name__ == "__main__":
    raise SystemExit(main())
