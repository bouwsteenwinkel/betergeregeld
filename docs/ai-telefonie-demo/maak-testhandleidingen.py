# -*- coding: utf-8 -*-
"""Twee testhandleidingen voor de AI-telefoniedemo's van Betergeregeld, als HTML voor print."""
import io, os, datetime

UIT = os.path.dirname(os.path.abspath(__file__))
DATUM = datetime.date.today().strftime('%d-%m-%Y')

CSS = """
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
@page { size: A4; margin: 13mm 15mm 14mm 15mm; }
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body { font-family: 'Inter', 'Segoe UI', system-ui, Arial, sans-serif; color: #0A0F1F; font-size: 10pt; line-height: 1.45; background: #fff; }
.kop { display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid rgba(10,15,31,.10); margin-bottom: 14px; }
.merk { display: flex; align-items: center; gap: 10px; }
.merk .bg { width: 34px; height: 34px; border-radius: 8px; background: #0A0F1F; color: #fff; font-weight: 800; font-size: 12px; display: flex; align-items: center; justify-content: center; letter-spacing: -.02em; }
.merk .naam { font-weight: 700; font-size: 12.5pt; line-height: 1.15; }
.merk .sub { font-size: 8.5pt; color: rgba(10,15,31,.45); }
.kop .rechts { font-size: 8.5pt; color: rgba(10,15,31,.55); text-align: right; line-height: 1.4; }
.eyebrow { display: inline-block; font-size: 8pt; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #0D9488; background: rgba(20,184,166,.12); padding: 3px 9px; border-radius: 999px; margin-bottom: 10px; }
h1 { font-size: 22pt; line-height: 1.15; letter-spacing: -.02em; margin: 0 0 8px; font-weight: 800; }
.lead { font-size: 10.5pt; color: rgba(10,15,31,.7); margin: 0 0 12px; max-width: 160mm; }
h2 { font-size: 13pt; margin: 14px 0 6px; font-weight: 700; letter-spacing: -.01em; break-after: avoid; }
h2 .nr { display: inline-flex; width: 22px; height: 22px; border-radius: 6px; background: #14B8A6; color: #fff; font-size: 10pt; align-items: center; justify-content: center; margin-right: 8px; vertical-align: -3px; }
p { margin: 0 0 8px; }
.bel { display: flex; align-items: center; gap: 16px; background: #0A0F1F; color: #fff; border-radius: 12px; padding: 12px 16px; margin: 4px 0 6px; break-inside: avoid; }
.bel .nummer { font-size: 22pt; font-weight: 800; letter-spacing: .01em; white-space: nowrap; }
.bel .uitleg { font-size: 9.5pt; color: rgba(255,255,255,.72); line-height: 1.45; }
.bel .uitleg b { color: #fff; }
.feiten { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px 10px; margin: 8px 0 0; }
.feit { background: #F2F4F7; border-radius: 10px; padding: 9px 12px; break-inside: avoid; }
.feit .l { font-size: 8pt; color: rgba(10,15,31,.5); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }
.feit .w { font-weight: 600; font-size: 10.5pt; }
ul { margin: 0 0 8px; padding-left: 18px; }
li { margin: 0 0 4px; }
.tips li::marker { color: #14B8A6; }
.kaarten { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px; break-inside: avoid; }
.kaart { border: 1px solid rgba(10,15,31,.12); border-radius: 12px; padding: 10px 12px; break-inside: avoid; background: #fff; }
.kaart .id { font-family: 'Inter', monospace; font-weight: 800; font-size: 14pt; letter-spacing: .04em; }
.kaart .naam { font-weight: 600; color: rgba(10,15,31,.75); margin-bottom: 6px; }
.kaart .zeg { font-size: 9.5pt; color: rgba(10,15,31,.62); margin-bottom: 6px; }
.kaart .zeg b { color: #0A0F1F; }
.kaart .hoor { font-size: 9.5pt; border-top: 1px dashed rgba(10,15,31,.14); padding-top: 6px; margin-top: 6px; }
.kaart .hoor .l { font-size: 8pt; text-transform: uppercase; letter-spacing: .06em; color: #0D9488; font-weight: 700; }
.scen { border-left: 3px solid #14B8A6; padding: 0 0 0 12px; margin: 0 0 7px; break-inside: avoid; }
.scen .t { font-weight: 700; }
.scen .q { font-style: italic; font-size: 9.5pt; color: #131B34; background: #F2F4F7; border-radius: 8px; padding: 3px 10px; margin: 2px 0; display: inline-block; }
.scen .q::before { content: '\\201C'; color: #14B8A6; font-weight: 700; }
.scen .q::after { content: '\\201D'; color: #14B8A6; font-weight: 700; }
.scen .r { font-size: 9pt; color: rgba(10,15,31,.65); }
.tabel { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin: 6px 0 10px; }
.tabel th { text-align: left; font-size: 8pt; text-transform: uppercase; letter-spacing: .06em; color: rgba(10,15,31,.5); padding: 4px 8px; border-bottom: 1px solid rgba(10,15,31,.14); }
.tabel td { padding: 5px 8px; border-bottom: 1px solid rgba(10,15,31,.07); vertical-align: top; }
.twee { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.kader { background: #F2F4F7; border-radius: 12px; padding: 12px 14px; break-inside: avoid; }
.kader h3 { margin: 0 0 6px; font-size: 10.5pt; }
.kader ul { margin: 0; }
.niet li::marker { color: #b91c1c; }
.voet { margin-top: 22px; padding-top: 8px; border-top: 1px solid rgba(10,15,31,.10); font-size: 8pt; color: rgba(10,15,31,.5); display: flex; justify-content: space-between; gap: 16px; }
.pagina { break-before: page; }
"""


def kop(titel_klein):
    return f"""
<div class="kop">
  <div class="merk"><div class="bg">BG</div><div><div class="naam">Beter Geregeld ICT</div><div class="sub">AI-telefonie &middot; demonstratie</div></div></div>
  <div class="rechts">{titel_klein}<br>Versie {DATUM} &middot; betergeregeld.com</div>
</div>"""


def voet():
    return f"""
<div class="voet"><span>Beter Geregeld ICT &middot; betergeregeld.com &middot; 088 254 5101</span><span>De demo-bedrijven zijn verzonnen; de assistent, de gegevens en de gesprekken zijn echt.</span></div>"""


ALGEMEEN_TIPS = """
<ul class="tips">
  <li><b>Praat zoals u tegen een medewerker praat.</b> Hele zinnen, gewone woorden, in uw eigen tempo. U hoeft geen commando's te geven en geen menu te doorlopen.</li>
  <li><b>Onderbreken mag.</b> Weet u al genoeg, praat er gewoon doorheen; de assistent stopt en luistert.</li>
  <li><b>Letters: zeg er een woord bij.</b> "J van Jan" of "K van Karel" komt altijd goed door; losse letters soms niet. Cijfers mag u zeggen zoals u wilt: "veertien twaalf" of "een vier een twee".</li>
  <li><b>Bel gerust buiten openingstijden.</b> Dan hoort u hoe hij een verzoek vastlegt in plaats van door te verbinden.</li>
  <li><b>Vraag hem eens iets wat hij niet mag.</b> Dan hoort u hoe hij een grens bewaakt zonder onbeleefd te worden.</li>
</ul>"""


def document(bestand, titel_klein, eyebrow, h1, lead, feiten, kaarten_intro, kaarten, scenarios, kan, niet, wat_dan):
    kaart_html = "".join(f"""
    <div class="kaart"><div class="id">{k['id']}</div><div class="naam">{k['naam']}</div>
      <div class="zeg">Zeg: <b>{k['zeg']}</b></div>
      <div class="hoor"><span class="l">Wat u hoort</span><br>{k['hoor']}</div></div>""" for k in kaarten)
    scen_html = "".join(f"""
    <div class="scen"><div class="t">{s['t']}</div><div class="q">{s['q']}</div><div class="r">{s['r']}</div></div>""" for s in scenarios)
    kan_html = "".join(f"<li>{x}</li>" for x in kan)
    niet_html = "".join(f"<li>{x}</li>" for x in niet)
    feit_html = "".join(f'<div class="feit"><div class="l">{l}</div><div class="w">{w}</div></div>' for l, w in feiten[1:])

    html = f"""<!doctype html><html lang="nl"><head><meta charset="utf-8"><title>{h1}</title><style>{CSS}</style></head><body>
{kop(titel_klein)}
<span class="eyebrow">{eyebrow}</span>
<h1>{h1}</h1>
<p class="lead">{lead}</p>

<div class="bel"><div class="nummer">{feiten[0][1]}</div><div class="uitleg"><b>Bel dit nummer en stel de vragen die uw eigen klanten stellen.</b><br>De assistent neemt op met de naam van het demo-bedrijf en doet precies wat hij bij een echt bedrijf zou doen. Er wordt niets opgenomen; na afloop staat het gesprek als tekst in het logboek van Betergeregeld.</div></div>

<div class="feiten">{feit_html}</div>

<h2><span class="nr">1</span>De testkaartjes</h2>
<p>{kaarten_intro}</p>
<div class="kaarten">{kaart_html}</div>

<div class="pagina"></div>
{kop(titel_klein)}
<h2 style="margin-top:0"><span class="nr">2</span>Probeer dit &mdash; voorbeeldgesprekken</h2>
<p>Elke situatie laat iets anders horen. U hoeft de zinnen niet letterlijk te zeggen; het gaat om wat u vraagt.</p>
{scen_html}

<div class="pagina"></div>
{kop(titel_klein)}
<h2 style="margin-top:0"><span class="nr">3</span>Tips voor het bellen</h2>
{ALGEMEEN_TIPS}

<h2><span class="nr">4</span>Wat u wel en niet hoort</h2>
<div class="twee">
  <div class="kader"><h3>De assistent doet</h3><ul>{kan_html}</ul></div>
  <div class="kader"><h3>Bewust niet</h3><ul class="niet">{niet_html}</ul></div>
</div>

<h2><span class="nr">5</span>En daarna?</h2>
{wat_dan}
{voet()}
</body></html>"""
    pad = os.path.join(UIT, bestand)
    io.open(pad, 'w', encoding='utf-8').write(html)
    return pad


# ---------------------------------------------------------------- Bakkerij
bakkerij = document(
    'bakkerij.html',
    'Testhandleiding Bakkerij Kruimel',
    'Demo voor bakkerijen',
    'Zo test u de AI-telefonie van Bakkerij Kruimel',
    'Bakkerij Kruimel is een verzonnen bakkerij in Hilversum met een echte telefonische assistent. Hij neemt op, beantwoordt vragen over assortiment en openingstijden, neemt bestellingen aan en legt terugbelverzoeken vast &mdash; precies zoals hij dat voor uw bakkerij zou doen.',
    [('Demonummer', '088 254 5170'),
     ('Openingstijden van de demo', 'ma t/m za 8.00 &ndash; 17.00 uur; zondag gesloten'),
     ('Adres (verzonnen)', 'Kerkbrink 12, Hilversum'),
     ('Taal', 'Nederlands; Engels op verzoek')],
    'Vier bestellingen staan al in de administratie van de bakkerij. Bel, zeg dat u wilt weten of uw bestelling klaar is, en noem het bestelnummer en de achternaam van het kaartje. Elk kaartje laat een ander antwoord horen.',
    [
        {'id': 'K-1042', 'naam': 'De Wit', 'zeg': '"K, tien tweeënveertig, De Wit"', 'hoor': 'Twee appeltaarten staan klaar; vandaag ophalen om tien uur, al betaald.'},
        {'id': 'K-1057', 'naam': 'Jansen', 'zeg': '"K, tien zevenenvijftig, Jansen"', 'hoor': 'Slagroomtaart voor tien personen met de tekst "Sam 7", overmorgen om half twaalf ophalen.'},
        {'id': 'K-1063', 'naam': 'El Amrani', 'zeg': '"K, tien drieënzestig, El Amrani"', 'hoor': 'Veertig belegde broodjes voor Notariskantoor Van Dijk, morgen vóór twaalf uur bezorgd op het Stationsplein, op rekening.'},
        {'id': 'K-1071', 'naam': 'Bakker', 'zeg': '"K, tien eenenzeventig, Bakker"', 'hoor': 'Chocoladetaart over vijf dagen; de vraag of er een foto op kan staat nog open bij de bakkerij.'},
    ],
    [
        {'t': 'Een taart bestellen', 'q': 'Ik wil graag een slagroomtaart bestellen voor zaterdag, voor tien personen, met "Lisa 8" erop.', 'r': 'Hij zoekt de taart op, let op de besteltermijn (twee dagen vooruit), vraagt uw naam en hoe laat u hem ophaalt, leest de bestelling terug en geeft een bestelnummer dat met K begint. Betalen doet u bij het afhalen &mdash; nooit aan de telefoon.'},
        {'t': 'Te snel willen', 'q': 'Kan die chocoladetaart morgen al?', 'r': 'Nee: een chocoladetaart moet twee dagen vooruit. Hij zegt eerlijk wat de vroegste dag is en vraagt of dat ook goed is. Hij belooft niet dat de bakker "het vast wel redt".'},
        {'t': 'Een zakelijke bestelling met bezorging', 'q': 'Ik wil veertig belegde broodjes bestellen voor ons kantoor, morgen bezorgen op de Kerkstraat 5 in Hilversum.', 'r': 'Bezorgen kan alleen zakelijk, in Hilversum, vanaf twintig broodjes. Hij vraagt om adres en bedrijfsnaam, noteert de wens "op rekening" voor de bakkerij en geeft een bestelnummer.'},
        {'t': 'Is mijn bestelling klaar?', 'q': 'Staat mijn bestelling al klaar? Het nummer is K, tien tweeënveertig, op naam van De Wit.', 'r': 'Hij zoekt op bestelnummer én achternaam. Met een van de vier kaartjes hoort u de stand; met een verkeerde naam zegt hij dat hij niets kan vinden &mdash; en verklapt hij niets.'},
        {'t': 'Assortiment en prijzen', 'q': 'Hebben jullie glutenvrij brood? En wat kost een appeltaart?', 'r': 'Glutenvrij brood is er alleen op vrijdag, op bestelling. Een appeltaart kost zestien vijftig, voor acht personen, een dag vooruit bestellen. Prijzen komen uit het assortiment, nooit uit zijn hoofd.'},
        {'t': 'Allergenen: de grens', 'q': 'Zitten er noten in de amandelstaaf? Mijn dochter is allergisch.', 'r': 'Hier houdt hij zich bewust in: over allergenen zegt hij niets uit zichzelf. Binnen openingstijden biedt hij aan door te verbinden, daarbuiten noteert hij een terugbelverzoek.'},
        {'t': 'Voor zondag', 'q': 'Ik wil een taart voor zondagmiddag.', 'r': 'Op zondag is de bakkerij dicht en wordt er niet gebakken. Hij stelt zaterdag ophalen voor.'},
        {'t': 'Een klacht', 'q': 'De taart van gisteren was helemaal ingezakt, dit is echt niet goed.', 'r': 'Een klacht krijgt geen assistent: hij oordeelt niet, neemt geen verantwoordelijkheid en verbindt door met de winkel. Buiten openingstijden noteert hij naam en nummer voor een terugbelverzoek.'},
        {'t': 'Buiten openingstijden bellen', 'q': '(bel na 17.00 uur of op zondag) Ik wil voor donderdag twaalf petitfours bestellen.', 'r': 'Hij neemt de bestelling gewoon aan; die staat de volgende ochtend in de mail en het portaal van de bakkerij. Doorverbinden biedt hij dan niet aan.'},
        {'t': 'Engels', 'q': 'Sorry, do you speak English? I would like to order a birthday cake.', 'r': 'Hij schakelt over naar het Engels en helpt verder.'},
    ],
    ['Openingstijden, adres en parkeren beantwoorden', 'Assortiment, prijzen en besteltermijnen opzoeken', 'Bestellingen aannemen, teruglezen en een bestelnummer geven', '"Is mijn bestelling klaar?" op nummer + achternaam', 'Terugbelverzoek noteren met naam en nummer, herhaald ter controle', 'Binnen openingstijden doorverbinden (klacht, allergie, wijziging voor vandaag, "ik wil een mens")', 'Na elk gesprek een samenvatting per mail en een regel in het portaal'],
    ['Uitspraken over allergenen of ingrediënten', 'Betalingen aannemen of om rekeninggegevens vragen', 'Een bestelling voor vandaag wijzigen of annuleren', 'Gesprekken opnemen (alleen tekst)', 'Iets verzinnen: prijzen, producten of "dat redt de bakker wel"'],
    """<p>Na elk gesprek ontvangt Betergeregeld een samenvatting; in de proefopstelling voor uw bakkerij komt die bij ú terecht, samen met elke bestelling en elk terugbelverzoek in uw eigen portaal. Wilt u horen hoe dit klinkt met uw eigen naam, openingstijden en assortiment? Plan een gesprek van een half uur via betergeregeld.com of bel 088 254 5101.</p>
<p><b>Let op:</b> doet u tijdens de demo een "echte" bestelling, dan zegt de assistent er na het noteren bij dat Bakkerij Kruimel niet bestaat en er niets gebakken wordt. Dat is expres: niemand mag ophangen in de veronderstelling dat er zaterdag een taart klaarstaat.</p>""",
)

# ---------------------------------------------------------------- Autobedrijf
garage = document(
    'autobedrijf.html',
    'Testhandleiding Autobedrijf De Wissel',
    'Demo voor autobedrijven',
    'Zo test u de AI-telefonie van Autobedrijf De Wissel',
    'Autobedrijf De Wissel is een verzonnen garage in Hilversum met een echte telefonische assistent. Hij neemt op, weet of een auto klaar is, wanneer de APK verloopt, wat een keuring kost en noteert een afspraakverzoek &mdash; precies zoals hij dat voor uw garage zou doen.',
    [('Demonummer', '088 254 5160'),
     ('Openingstijden van de demo', 'ma t/m vr 8.00 &ndash; 17.30 uur; weekend gesloten'),
     ('Adres (verzonnen)', 'Zonnestraat 40, Hilversum'),
     ('Taal', 'Nederlands; Engels op verzoek')],
    "Vier auto's staan in de werkplaatsadministratie. Bel, vraag of uw auto klaar is, en noem het kenteken en de achternaam van het kaartje. De assistent vraagt altijd om allebei: een kenteken alleen kan iedereen op straat aflezen.",
    [
        {'id': '12-KLM-3', 'naam': 'De Wit', 'zeg': '"Twaalf, K L M, drie. De Wit."', 'hoor': 'De grijze Volkswagen Golf is klaar: grote beurt en APK, rekening € 386,40. Vandaag tot half zes ophalen.'},
        {'id': '7-XRP-88', 'naam': 'Jansen', 'zeg': '"Zeven, X R P, achtentachtig. Jansen."', 'hoor': 'De rode Toyota Yaris wacht op een onderdeel (de startmotor). Dat komt overmorgen binnen; naar verwachting een dag later klaar. Geen prijs: de monteur belt eerst.'},
        {'id': 'KP-482-T', 'naam': 'El Amrani', 'zeg': '"K P, vier acht twee, T. El Amrani."', 'hoor': 'De blauwe Renault Clio staat niet in de werkplaats; de APK verloopt over twaalf dagen. Hij biedt meteen aan een afspraak te noteren.'},
        {'id': '3-VBH-21', 'naam': 'Bakker', 'zeg': '"Drie, V B H, eenentwintig. Bakker."', 'hoor': 'De witte Skoda Octavia heeft morgen om acht uur een afspraak. Hij bevestigt dag en tijd en zegt hoe brengen werkt.'},
    ],
    [
        {'t': 'Is mijn auto klaar?', 'q': 'Ja hallo, ik wilde vragen of mijn auto al klaar is.', 'r': 'Hij vraagt om kenteken en achternaam, herhaalt het kenteken teken voor teken ter controle en zegt dan de stand: klaar, in behandeling, wacht op een onderdeel of gepland. Probeer alle vier de kaartjes.'},
        {'t': 'Wat kost het?', 'q': 'Wat staat er op de rekening? (met 12-KLM-3, De Wit)', 'r': 'Bij een auto die klaar is noemt hij het bedrag en de omschrijving. Bij een auto die nog niet klaar is noemt hij géén bedrag en geen schatting: de monteur belt met een prijs voordat er iets gedaan wordt.'},
        {'t': 'APK', 'q': 'Wanneer moet mijn auto APK? (met KP-482-T, El Amrani)', 'r': '"Over twaalf dagen." Omdat dat binnen een maand is en er nog geen afspraak staat, biedt hij in dezelfde adem aan om een afspraak te noteren. Een APK kost € 49,50 inclusief afmelden.'},
        {'t': 'Een afspraak maken', 'q': 'Ik wil graag een afspraak voor een onderhoudsbeurt, het liefst woensdagochtend.', 'r': 'Hij vraagt waarvoor en wanneer het schikt (een dagdeel is genoeg), noteert het verzoek en zegt erbij dat een collega de afspraak nog bevestigt. Hij zet zelf niets in de agenda en vraagt niet naar kilometerstand of e-mail.'},
        {'t': 'Een klacht aan de auto', 'q': 'Hij maakt een piepend geluid als ik rem. Wat zou dat kunnen zijn?', 'r': 'Hij stelt geen diagnose en noemt geen oorzaak: dat is werk voor de monteur. Wel noteert hij de klacht in uw eigen woorden bij een afspraakverzoek.'},
        {'t': 'Verkeerde naam', 'q': 'Twaalf, K L M, drie, op naam van Pietersen.', 'r': 'Naam en kenteken horen niet bij elkaar. Hij zegt dat hij de auto zo niet kan vinden en vraagt het nog een keer &mdash; hij verklapt niet welke naam er wél bij hoort.'},
        {'t': 'Praktische vragen zonder kenteken', 'q': 'Kan ik wachten op een bandenwissel? En hebben jullie een leenauto?', 'r': 'Openingstijden, wachten (een uur, koffie en wifi), leenauto (€ 10 per dag, op aanvraag), brengen voor achten via de sleutelkluis: daar vraagt hij geen kenteken voor.'},
        {'t': 'Een auto die hij niet kent', 'q': 'Mijn kenteken is (uw eigen kenteken), ik wil een afspraak voor de APK.', 'r': 'Een nieuwe klant is welkom: hij noteert het verzoek. Maar daarna zegt hij er eerlijk bij dat dit een demonstratie is en dat er niemand terugbelt &mdash; zodat niemand met een verzonnen garage blijft zitten.'},
        {'t': 'Buiten openingstijden', 'q': "(bel 's avonds of in het weekend) Ik wil mijn auto morgenochtend brengen voor een beurt.", 'r': 'Doorverbinden biedt hij dan niet aan. Hij noteert het afspraakverzoek en een terugbelmoment.'},
        {'t': 'Engels', 'q': 'Do you speak English? I need an appointment for my car.', 'r': 'Hij schakelt over naar het Engels en helpt verder.'},
    ],
    ['Kenteken + achternaam controleren vóór hij iets zegt', 'Stand van de auto: klaar, in behandeling, wacht op onderdeel, gepland', 'Bedrag en omschrijving noemen als de auto klaar is', 'APK-vervaldatum en zelf een afspraak aanbieden', 'Afspraakverzoek noteren (waarvoor + wanneer het schikt)', 'Vaste prijzen: APK € 49,50, bandenwissel € 35, opslag € 50, leenauto € 10/dag', 'Terugbelverzoek buiten openingstijden; samenvatting per mail'],
    ['Een diagnose stellen of zeggen wat er kapot is', 'Een prijs of schatting noemen voor werk dat nog loopt', 'Zelf een afspraak in de agenda zetten of een tijd bevestigen', 'Een onderdeel bestellen, een werkorder wijzigen of annuleren', 'Iets vertellen over een auto zonder de juiste achternaam'],
    """<p>Na elk gesprek ontvangt Betergeregeld een samenvatting; in de proefopstelling voor uw garage komt die bij ú terecht, samen met elk afspraak- en terugbelverzoek in uw eigen portaal. Wilt u horen hoe dit klinkt met uw eigen naam, openingstijden en werkplaatsregels? Plan een gesprek van een half uur via betergeregeld.com of bel 088 254 5101.</p>
<p><b>Let op:</b> de vier demo-auto's veranderen niet door uw gesprek. U kunt ze dus zo vaak bellen als u wilt, en collega's kunnen hetzelfde kaartje gebruiken.</p>""",
)
print(bakkerij)
print(garage)
