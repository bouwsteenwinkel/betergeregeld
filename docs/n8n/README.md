# n8n self-host op een TransIP VPS

Volledige controle: n8n draait in Docker op je eigen Linux-VPS, data blijft in
eigen beheer (AVG ✅). Vier containers: **Caddy** (reverse proxy + auto-HTTPS) →
**n8n** + **Uptime Kuma** (site-monitoring) → **Postgres**.

- **Tier:** TransIP **VPS V3** (2 vCPU, 4 GB RAM, 100 GB NVMe — ~€20/mnd).
- **OS:** Ubuntu 24.04 LTS, methode **SSH-keys** (key vooraf in het paneel zetten + standaardselectie).
- **Resultaat:** `https://n8n.betergeregeld.com` (n8n) + `https://uptime.betergeregeld.com` (monitoring), beide met geldig certificaat.

> Niet de gewone TransIP-webhosting: die is managed zonder root/Docker. Je hebt
> een **VPS (root)** nodig. (Eerder Strato/IONOS bekeken; uiteindelijk TransIP V3
> i.v.m. NL-datacenter + Nederlandse support.)

---

## 0. Bestellen + DNS
1. Bestel de **VPS V3** met **Ubuntu 24.04 LTS** (SSH-key geselecteerd bij de installatie).
2. Zet **twee A-records** naar het VPS-IP:
   - `n8n.betergeregeld.com` → IP
   - `uptime.betergeregeld.com` → IP
   (Wacht tot ze propageren: `ping n8n.betergeregeld.com` toont het juiste IP.)

## 1. Eerste login + non-root gebruiker
```bash
ssh root@<server-ip>
adduser deploy
usermod -aG sudo deploy
# SSH-key van je werkstation toevoegen voor 'deploy':
rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy   # of handmatig ~/.ssh/authorized_keys vullen
```

## 2. Hardening (BELANGRIJK — verse internet-facing server)
```bash
# --- SSH dichtzetten: alleen keys, geen root-login ---
sudo sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/'        /etc/ssh/sshd_config
sudo sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sudo systemctl restart ssh
#   ↑ Test EERST in een 2e terminal dat 'ssh deploy@<ip>' met key werkt,
#     voordat je deze sessie sluit.

# --- Firewall: alleen SSH + web ---
sudo apt update && sudo apt install -y ufw fail2ban unattended-upgrades
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# --- Automatische security-updates + fail2ban ---
sudo dpkg-reconfigure -plow unattended-upgrades   # of: echo enable
sudo systemctl enable --now fail2ban
```

## 3. Docker installeren
```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker deploy
newgrp docker   # of opnieuw inloggen, zodat 'deploy' docker mag draaien
```

## 4. Stack neerzetten
```bash
mkdir -p ~/n8n && cd ~/n8n
# Kopieer hierheen: docker-compose.yml, Caddyfile, .env.example
cp .env.example .env

# Sleutels genereren en in .env zetten:
openssl rand -hex 32       # → N8N_ENCRYPTION_KEY   (BEWAAR OFFLINE!)
openssl rand -base64 24    # → POSTGRES_PASSWORD
nano .env                  # vul N8N_HOST, ACME_EMAIL, beide secrets in

docker compose up -d
docker compose logs -f caddy   # zie of het TLS-cert wordt opgehaald
```

## 5. Eerste keer openen
- Ga naar **https://n8n.betergeregeld.com**.
- Maak **direct** het owner-account aan met een sterk wachtwoord (dit is je login;
  zolang er geen account is, kan de eerste bezoeker 'm claimen — doe dit meteen).

## 6. Uptime Kuma — al je sites bewaken
- Ga naar **https://uptime.betergeregeld.com** en maak **meteen** het admin-account aan
  (ook hier geldt: eerste bezoeker claimt 'm anders).
- Voeg per site een **Monitor** toe (type HTTP(s), interval bv. 60s): bouwsteenwinkel.nl,
  betergeregeld.com, 24werk.com, fifty-cal.com, en `https://n8n.betergeregeld.com` zelf.
- Stel **notificaties** in (e-mail/Telegram/ntfy) zodat je een melding krijgt bij downtime
  of een gewijzigde status — extra waardevol als vroeg-waarschuwing na een hack/defacement.
- Optioneel: publiceer een **status-pagina** voor intern of klanten.

---

## Koppeling met Betergeregeld (later)
Zodra n8n draait, leveren we in de Laravel-app aan:
- **Fase 1 (admin → n8n):** `config/n8n.php` met `base_url=https://n8n.betergeregeld.com`
  + een gedeeld webhook-secret (HMAC). De admin triggert dan flows via een queued job.
- **Fase 2 (n8n → admin):** Sanctum API-tokens + `routes/api.php`; n8n roept met een
  Bearer-token jullie API aan.
- **Fase 3 (embed):** ontcommentarieer het iframe-blok in de `Caddyfile`.

## Onderhoud
- **Updaten:** `docker compose pull && docker compose up -d` (pin desgewenst de n8n-tag
  in `docker-compose.yml` op een vaste versie i.p.v. `:latest`).
- **Back-up (dagelijks, via cron op de server):**
  ```bash
  docker compose exec -T postgres pg_dump -U n8n n8n | gzip > ~/backups/n8n-$(date +\%F).sql.gz
  ```
  Back-up óók het volume `n8n_data` en bewaar de **`N8N_ENCRYPTION_KEY`** apart/offline.
- **Restore-eis:** een Postgres-dump zonder de juiste `N8N_ENCRYPTION_KEY` levert
  onbruikbare credentials op. Bewaar die key dus los van de server.

## Beveiligingsuitgangspunten
- n8n-poort 5678 is **niet** publiek — alleen Caddy luistert op 80/443.
- HTTPS afgedwongen (HSTS), `PasswordAuthentication` voor SSH uit, firewall dicht.
- Gedeeld geheim op beide koppelrichtingen met Betergeregeld; tokens roteerbaar.
