# Beter Geregeld — AI-Assistent Platform

**Bouwplan v1.0 — als multi-tenant module bínnen Beter Geregeld**

> Dit document herschrijft het "Master Project Plan v0.1" en verankert het aan de
> échte codebase van `betergeregeldv2` (Laravel 13, Filament 5, PHP 8.3). De rode draad:
> **niets van de fundering opnieuw bouwen.** Tenants, gebruikers, abonnementen,
> feature-gating, facturatie (Mollie), afspraken, boekhouding en de channel-sites
> bestaan al. De AI-module haakt daarop in als een nieuwe **"tool" / productlijn**.
> Alleen de **AI-Core** en de **Voice-Gateway** zijn echt nieuw.

---

## 0. De vier belangrijkste beslissingen (lees dit eerst)

1. **Module, geen los product.** De AI-assistent wordt een nieuwe productlijn
   (`product = 'assistant'`) binnen het bestaande platform en hergebruikt de
   volledige fundering. Zie §2.

2. **Laravel = control plane, niet de realtime-audiolaag.** PHP is ongeschikt voor
   realtime RTP/audio. De AI-Core (tekst, functies, kennis, config, logging) draait
   in Laravel. De **realtime spraak** loopt via een aparte, dunne **Voice-Bridge**
   (of via OpenAI's SIP-connector). Laravel stuurt en logt; de bridge/OpenAI doet audio.
   Zie §6.

3. **"Universeel koppelen aan elke centrale" = generieke SIP-trunk.** 3CX is voor
   ons de eerste centrale, maar de koppeling gebeurt via een SIP-trunk-abstractie.
   Elke PBX (3CX, Asterisk/FreePBX, etc.) die uitgaand kan SIP-trunken, werkt zonder
   codewijziging. Per-tenant routering op basis van het **gebelde nummer (DID)**. Zie §6.

4. **Eén AI-Core, alle kanalen.** Telefoon, webchat, WhatsApp en e-mail zijn slechts
   *transports*. Ze leveren allemaal dezelfde `AiConversation` aan dezelfde Core.
   Dezelfde kennis, dezelfde functies, dezelfde per-tenant prompt. Zie §5.

---

## 1. Wat er AL is (en dus NIET gebouwd wordt)

| Uit het originele plan | Status in `betergeregeldv2` | Hergebruik |
|---|---|---|
| Authentication, MFA, wachtwoord-reset, API-tokens, OAuth | `User`, `UserTwofa*`, `TenantApiKey`, Google OAuth | **100% hergebruik** |
| Tenant management (tenants, users, rollen, domeinen, branding) | `Tenant`, `User`, `Agency`, `Channel\Site`, `CmpBranding` | **100% hergebruik** |
| Abonnementen & facturatie | `Plan` (per `product`), `PlanFeature`, `TenantSubscription`, Mollie | **Hergebruik + nieuw AI-product** (§4) |
| Feature-/limietgating | `FeatureResolver` → `FeatureBag`, `tool.limit:` middleware, `ToolUsageDaily` | **Hergebruik + uitbreiden** (§4) |
| Agenda | `Appointment`, `AvailabilityRule`, `AvailabilityException`, Google Calendar | **Hergebruik als functie** (§7) |
| CRM (contacten/bedrijven/leads) | `BookkeepingRelation`, `WebsiteLead`, `SupportCase`, `ContactMessage` | **Hergebruik als ruggengraat** (§7) |
| Offertes/facturen | `Bookkeeping*` module | **Hergebruik als functie** (§7) |
| E-mail verzenden | Socketlabs + `Mail/*` | **Hergebruik als functie** (§7) |
| Website/webshop/klantenportaal | Channel-sites + Groeidiamant-facetten | **Webchat-widget haakt hierin** (§8) |
| Cookie-/consent-beheer (belangrijk voor gespreksopnames!) | `Cmp*` module | **Hergebruik voor opname-consent** (§9) |
| Dashboard-/analytics-fundament | Filament 5 panels, `UserEvent`, `AuditLog` | **Hergebruik + AI-widgets** (§10) |

**Echt nieuw:** AI-Core (§5), Voice-Gateway/SIP (§6), Knowledge/RAG-engine (§8),
Function-registry (§7), de AI-specifieke tabellen (§3) en een Filament-sectie voor
agent-configuratie.

---

## 2. Integratiepatroon: de AI-module is een "tool"

Het platform kent al een beproefd modulepatroon: elke module is een *tool* achter
`Route::middleware('tool.limit:<slug>')`, met per-plan limieten via `FeatureBag`
(`tool.<slug>.daily.<scope>`) en verbruik in `ToolUsageDaily`. Zie
`app/Http/Middleware/EnforceToolRateLimit.php` en `app/Services/Features/`.

De AI-assistent volgt exact dit patroon met slug **`assistant`**:

- Routes onder `Route::prefix('assistant')->middleware('tool.limit:assistant')`.
- Een **nieuwe productlijn** `assistant` in `plans` (naast `tools`), zodat AI zijn
  eigen prijzen/limieten heeft: `new FeatureResolver('assistant')`.
- Verbruiksmetering breidt `ToolUsageTracker` uit met een **gemeten** increment
  (`record($request, 'assistant', qty: $seconds)`) voor spraakminuten, naast de
  bestaande dag-tellingen voor chats.

Zo erven we automatisch: soft-paywall, trial, upgrade-flow, per-tenant isolatie.

---

## 3. Nieuwe database-tabellen

Conventies volgen de codebase: **snake_case, meervoud, elke tabel `tenant_id`
(`belongsTo Tenant`), `ulid`/`id` zoals bestaande tabellen.** Migraties in
`database/migrations`, modellen in `app/Models/Assistant/`.

**AI-Core & configuratie**
- `ai_agents` — per tenant ≥1 agent (naam, persona, taal, stem, model, temperatuur, `is_active`).
- `ai_agent_channels` — koppelt een agent aan een kanaal-instance: `channel_type`
  (`voice|web|whatsapp|email`), `binding` (DID-nummer / widget-key / wa-nummer / mailbox), config-JSON.
- `ai_conversations` — kanaal-onafhankelijke gespreks-root (`channel_type`, `contact_id`,
  `status`, `started_at`, `ended_at`, kosten, sentiment, samenvatting).
- `ai_messages` — beurten binnen een gesprek (`role` user/assistant/system/tool, content, tokens, audio-ref).

**Spraak (Voice-Gateway)**
- `voice_calls` — 1-op-1 met een `ai_conversation` van type `voice` (from/to-nummer,
  PBX-bron, richting, duur-seconden, opname-ref, disposition).
- `call_transcripts` — losse transcriptregels met timing (voor zoeken/analyse).
- `call_recordings` — verwijzing naar audio in S3 + **consent-status** (zie §9).

**Kennis (RAG)**
- `knowledge_documents` — bron (PDF/Word/URL/FAQ/product), tenant, status, hash.
- `knowledge_chunks` — chunks + metadata; embedding-referentie (vector store extern, §8).

**Functies & workflows**
- `ai_functions` — per tenant in-/uitgeschakelde functies + parameter-overrides
  (registry is code; deze tabel is enable/config-laag).
- `ai_function_calls` — audit van elke functie-aanroep (functie, args, resultaat, succes) → onmisbaar voor debugging/vertrouwen.
- `ai_workflows` + `ai_workflow_steps` — no-code flows (fase 2; MVP mag dit overslaan).

**Verbruik/billing** → hergebruik `ToolUsageDaily` (uitgebreid met `qty`) i.p.v.
een aparte `usage`-tabel. Facturatie loopt via bestaande Mollie/`BillingIntent`.

> **CRM-tabellen** (`crm_contacts/companies/leads` uit het originele plan) bouwen we
> **niet** opnieuw: `BookkeepingRelation` = contacten/bedrijven, `WebsiteLead` = leads,
> `SupportCase` = tickets. `ai_conversations.contact_id` verwijst naar `BookkeepingRelation`.

---

## 4. Abonnementen & feature-keys (concreet)

Nieuwe plannen onder `product = 'assistant'` (`plan_key`, `name`):

| plan_key | Naam | Kern |
|---|---|---|
| `assistant_free` | Gratis/Trial | Alleen webchat + FAQ, hard geplafonneerd |
| `assistant_starter` | Starter | Webchat + FAQ, x chats/mnd |
| `assistant_growth` | Groei | + Telefonie (3CX), WhatsApp, agenda, CRM |
| `assistant_pro` | Pro | + Workflows, meerdere agents, API, functies |
| `assistant_enterprise` | Enterprise | Eigen model, SSO, on-prem, SLA |

`PlanFeature`-limietkeys (via `FeatureBag`):
- `assistant.agents.max` — aantal agents.
- `assistant.channels.voice` / `.web` / `.whatsapp` / `.email` — kanaal aan/uit (0/1).
- `assistant.voice_minutes.monthly` — gemeten spraakminuten.
- `assistant.chats.monthly` — chatgesprekken.
- `assistant.knowledge_mb.max` — kennisbank-grootte.
- `assistant.functions.<slug>` — per-functie toestemming (bv. `assistant.functions.maakOfferte`).

`ToolUsageTracker::check()` en `FeatureResolver` ondersteunen dit patroon al; alleen
maand-scope + gemeten `qty` toevoegen.

---

## 5. AI-Core (nieuw) — `app/Services/Assistant/`

Kanaal-onafhankelijk brein. Alle transports leveren hier binnen.

```
Transport (voice | web | whatsapp | email)
        │  normaliseert naar een AiTurn { conversation, text|audio, contact }
        ▼
  Orchestrator ──► PromptBuilder   (systeem + tenant-context + geschiedenis + functies)
        │      ├─► KnowledgeRetriever (RAG, §8)
        │      ├─► ProviderClient   (OpenAI: Chat/Responses of Realtime)
        │      └─► FunctionRunner   (voert toegestane functies uit, §7)
        ▼
  Antwoord (tekst → transport rendert; spraak → TTS via bridge/Realtime)
```

Voorgestelde klassen:
- `Assistant\Orchestrator` — regie per beurt (LLM ↔ functie-loop ↔ antwoord).
- `Assistant\PromptBuilder` — bouwt de dynamische prompt (systeemprompt + bedrijfsinfo
  uit `Tenant`/`ai_agents` + openingstijden uit `AvailabilityRule` + diensten/prijzen
  uit `services_catalog` config + kennis + actuele context + geschiedenis + functie-schema).
- `Assistant\KnowledgeRetriever` — embeddings + vector-search.
- `Assistant\FunctionRegistry` + `FunctionRunner` — §7.
- `Assistant\Providers\OpenAiClient` — wrapper (hergebruik `OPENAI_API_KEY`), later
  pluggable voor Enterprise "eigen model".

Alle post-processing (samenvatting, sentiment, transcript-opslag, embeddings-indexering)
loopt via **Laravel queues (Redis)** — asynchroon, buiten het gesprek om.

---

## 6. Voice-Gateway & universele telefonie (de echte crux)

**Probleem:** OpenAI Realtime praat WebSocket-audio, 3CX praat SIP/RTP. Iets moet
bruggen. Twee routes — beide houden Laravel als control plane:

### Route A (aanbevolen voor MVP): OpenAI Realtime **SIP-connector**
OpenAI's Realtime API accepteert inkomende SIP. Flow:
```
Klant belt → 3CX (inbound rule op DID) → SIP-trunk → OpenAI Realtime SIP-endpoint
   → OpenAI roept onze webhook  POST /api/assistant/voice/incoming  (Laravel)
   → Laravel zoekt tenant+agent op DID, bouwt prompt, opent Realtime-sessie,
     registreert function-tools (call → POST /api/assistant/function)
   → gesprek loopt; Laravel logt transcript/kosten async
```
Minste eigen realtime-code. 3CX heeft alleen een **generieke SIP-trunk + inbound
rule** nodig — exact de universele abstractie.

### Route B (fallback / volledige controle / on-prem): eigen **Voice-Bridge microservice**
Aparte Node.js-service (FreeSWITCH of `drachtio` als SIP-stack) die SIP/RTP termineert
en audio ↔ OpenAI Realtime WS pipet, met dezelfde webhooks naar Laravel. Nodig als een
klant on-prem wil, of voor een andere STT/TTS-mix. **Niet in Laravel** — dit is het
enige nieuwe niet-PHP-component.

### Universaliteit (elke centrale)
- Koppelvlak = **SIP-trunk** (RFC-standaard). 3CX, Asterisk/FreePBX, Vialer, etc.
  wijzen simpelweg een uitgaande route/trunk naar ons endpoint.
- Per-tenant routering op **DID (gebeld nummer)** → `ai_agent_channels.binding`.
- Optionele `PbxProvider`-adapters (3cx, asterisk, generic-sip) alleen voor
  *provisioning-gemak* (bv. 3CX-config-export), niet voor het gesprek zelf.

**3CX-configuratie voor ons voorbeeld:** SIP-trunk naar het gateway-endpoint +
inbound rule die ons demo-DID naar die trunk stuurt. (Uitwerken in
`docs/ai-platform/3cx-setup.md` zodra Route A/B gekozen is.)

---

## 7. Function-Engine — dun laagje over wat er al is

De AI mag alleen vooraf gedefinieerde functies aanroepen (`FunctionRegistry`). Elke
functie = een adapter over een **bestaande** service, per-tenant scoped en per plan gated
(`assistant.functions.<slug>`):

| AI-functie | Haakt in op |
|---|---|
| `maakAfspraak`, `verzetAfspraak`, `checkBeschikbaarheid` | `Appointment` + `AvailabilityRule` + Google Calendar |
| `zoekKlant`, `maakKlant` | `BookkeepingRelation` |
| `maakLead` | `WebsiteLead` |
| `maakOfferte`, `zoekFactuur`, `controleerFactuur` | `Bookkeeping*` |
| `verstuurMail` | Socketlabs / `Mail/*` |
| `maakTicket` | `SupportCase` |
| `verstuurWhatsapp` | WhatsApp-transport (§8) |
| `belMedewerker` / `verbindDoor` | PBX-adapter (3CX transfer) |

Elke aanroep → `ai_function_calls` (audit). Nieuwe functies = nieuwe adapterklasse +
registry-entry; geen wijziging aan de Core.

---

## 8. Kennis (RAG) & de overige kanalen

**Vector store — beslissing nodig.** Stack is MySQL 8 + Redis; `pgvector` vereist
Postgres. Opties:
- **Qdrant** als sidecar-container op de VPS (aanbevolen: schaalt, filtert op `tenant_id`).
- MySQL 9 vector-kolommen (indien te upgraden) — minder ecosysteem.
- MVP-noodoplossing: embeddings in een `LONGBLOB` + in-PHP cosine (alleen kleine kennisbanken).

Pipeline: upload → extract (PDF/Word/URL) → chunk → embed (OpenAI) → upsert in vector
store met `tenant_id`-filter. Retrieval in `KnowledgeRetriever`, async indexeren via queue.

**Webchat** — widget (Bootstrap/vanilla JS, past bij bestaande stack) die in de
channel-sites/Groeidiamant-AI-facet valt en tegen `POST /api/assistant/chat` praat.
Zelfde Core, zelfde functies.

**WhatsApp** — inkomende webhook (WhatsApp Cloud API) → `ai_conversations(whatsapp)` →
Core → antwoord. Fase 2.

**E-mail** — inbound parse (bestaande mail-infra) → Core → concept/auto-reply. Fase 2.

---

## 9. Beveiliging, AVG & multi-tenant isolatie

- **Harde tenant-scoping** op elke query (`tenant_id`), net als bestaande modellen;
  Filament-resources scoped per tenant.
- **Gespreksopnames = persoonsgegevens.** Hergebruik de bestaande **CMP/consent-module**
  voor opname-consent (aankondiging "dit gesprek kan worden opgenomen", opt-out,
  bewaartermijn op `call_recordings`). Dit is een echt juridisch vereiste in NL.
- **Data-residency**: OpenAI-verwerking benoemen in de verwerkersovereenkomst; voor
  Enterprise de on-prem/eigen-model-route (Route B) als antwoord.
- **Function-calling least-privilege**: alleen expliciet toegestane functies; alle
  aanroepen in `ai_function_calls`. Nooit vrije DB-toegang voor de AI.
- **Secrets/PBX-credentials** per tenant versleuteld (bestaand `TenantApiKey`-patroon).

---

## 10. Dashboard (Filament + widgets)

Per tenant: aantal gesprekken, gem. duur, AI-kosten, chats, leads, afspraken,
conversie, top-vragen, sentiment, gemiste oproepen. Fundament (Filament 5,
`UserEvent`, `AuditLog`) bestaat; toevoegen = AI-widgets bovenop `ai_conversations`
/ `voice_calls` / `ToolUsageDaily`.

---

## 11. Herziene ontwikkelvolgorde (ingekort dankzij hergebruik)

Sprint 1–2 uit het origineel (multi-tenant, login, abonnementen, AI-Core-fundament)
zijn grotendeels **al klaar**. Realistische volgorde:

| Sprint | Doel | Bouwt op |
|---|---|---|
| **0** | AI-product + plannen + feature-keys + `assistant`-tool + Filament-sectie | §2, §4 |
| **1** | AI-Core + PromptBuilder + OpenAI-provider + `ai_conversations/messages` | §5 |
| **2** | **Webchat-widget** (snelste zichtbare waarde, geen telefonie-risico) | §5, §8 |
| **3** | Function-Engine + eerste functies (`checkBeschikbaarheid`, `maakAfspraak`, `maakLead`) | §7 |
| **4** | Knowledge/RAG (Qdrant + upload-pipeline) | §8 |
| **5** | **Telefonie: 3CX-demo** via Route A (SIP-connector) + `voice_calls`/transcript/consent | §6, §9 |
| **6** | Dashboard/analytics-widgets | §10 |
| **7** | WhatsApp-kanaal | §8 |
| **8** | E-mail-kanaal | §8 |
| **9** | Workflow-engine (no-code) | orig. |
| **10** | Enterprise: Voice-Bridge (Route B), eigen model, SSO | §6 |

> **Waarom webchat vóór telefonie:** zelfde Core, zonder realtime-audio-risico. Zodra
> chat + functies + RAG staan, is telefonie "alleen" een nieuw transport op dezelfde Core.

---

## 12. Openstaande beslissingen (voor we sprint 0 starten)

1. **Voice-route A vs B** voor de eerste 3CX-demo (OpenAI SIP-connector vs eigen bridge).
   Aanbeveling: **A** voor de demo, B pas voor Enterprise/on-prem.
2. **Vector store**: Qdrant-sidecar op de VPS? (aanbevolen)
3. **Productnaam/slug**: `assistant` aanhouden, of marketingnaam (bv. "Beter Geregeld AI")?
4. **LLM-provider**: OpenAI (key aanwezig) als default; Claude als optie/Enterprise?
5. **3CX-hosting**: waar draait jullie 3CX (self-hosted/cloud, versie) en mogen we daar
   een SIP-trunk + inbound rule op configureren voor het demo-DID?

---

*Volgende stap: kies §12.1–§12.5, dan werk ik sprint 0 uit tot concrete migraties,
modellen, routes en de eerste Filament-resource.*
