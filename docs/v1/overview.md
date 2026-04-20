# V1 Schema Overview

Reference map of the V1 database, imported from production dump on 2026-04-20
(source MariaDB 10.6.22 on VPS 85.215.166.3, target local MariaDB 12.2.2).

**47 tables, all InnoDB.** Mixed charsets — see note at the bottom.

For full DDL see [`schema.sql`](schema.sql). Machine-readable metadata:
[`tables.tsv`](tables.tsv), [`columns.tsv`](columns.tsv), [`indexes.tsv`](indexes.tsv),
[`fks.tsv`](fks.tsv).

---

## Row counts at a glance

Sorted by row count, so you can see what's actively used vs. dormant.

| Rows | Table |
|---:|---|
| 103,541 | `lego_element_map` |
| 11,604 | `audit_log` |
| 954 | `contact_messages` |
| 620 | `user_events` |
| 538 | `user_sessions` |
| 530 | `tenants` |
| 520 | `users` |
| 504 | `email_verify_tokens` |
| 334 | `pdf_merge_job_files` |
| 275 | `lego_rb_colors` |
| 135 | `pdf_merge_events` |
| 116 | `shipping_rates` |
| 115 | `pdf_merge_sessions` |
| 102 | `cmp_texts` |
| 99 | `user_acquisition` |
| 87 | `lego_color_map` |
| 32 | `pdf_merge_jobs`, `shipping_surcharges` |
| 14 | `cmp_consent_logs` |
| 12 | `cmp_categories`, `cmp_scripts` |
| 11 | `cmp_domains` |
| 10 | `cmp_consents`, `user_twofa_backup_codes` |
| 9 | `plan_features` |
| 8 | `tool_events` |
| 4 | `cmp_policy`, `pdf_merge_dailystats` |
| 3 | `cmp_branding`, `orders`, `plans`, `tenant_subscriptions`, `tool_diff_saves` |
| 1 | `cases`, `returns`, `shipments`, `user_twofa` |
| 0 | `case_events`, `evidence`, `loss_cases`, `loss_orders`, `loss_returns`, `loss_shipments`, `loss_tenant_settings`, `payments`, `tenant_api_keys`, `user_twofa_trusted_devices` |

---

## Tables grouped by domain

### Auth & users (6 tables)
Core identity + session + 2FA.

| Table | Rows | Notes |
|---|---:|---|
| `users` | 520 | Central user table. `tenant_id` → tenants (CASCADE). |
| `user_sessions` | 538 | Active/past sessions. `user_id` → users. |
| `email_verify_tokens` | 504 | Email confirm tokens. |
| `user_twofa` | 1 | 2FA secret per user. `user_id` → users. |
| `user_twofa_backup_codes` | 10 | Backup codes. `user_id` → users. |
| `user_twofa_trusted_devices` | 0 | Trusted-device tokens (unused — feature maybe not live). |

### Multi-tenancy & plans (5 tables)

| Table | Rows | Notes |
|---|---:|---|
| `tenants` | 530 | Tenant accounts. |
| `tenant_subscriptions` | 3 | Active subs. `plan_id` → plans. |
| `tenant_api_keys` | 0 | API keys per tenant (unused). |
| `plans` | 3 | Plan definitions (Free/Pro/etc.?). |
| `plan_features` | 9 | Feature flags per plan. |

### CMP — Consent Management Platform (8 tables)
Big clean sub-system. All `cmp_consents` and `cmp_consent_logs` link to `cmp_domains`.

| Table | Rows | Notes |
|---|---:|---|
| `cmp_domains` | 11 | Domains registered for CMP. |
| `cmp_consents` | 10 | Consent flags per domain. → domains (RESTRICT). |
| `cmp_consent_logs` | 14 | Audit log of consent changes. → domains. |
| `cmp_categories` | 12 | Cookie categories. |
| `cmp_scripts` | 12 | Script registrations. |
| `cmp_texts` | 102 | i18n text strings. |
| `cmp_policy` | 4 | Policy texts. |
| `cmp_branding` | 3 | Visual branding per domain. |

### Business domain: orders / shipments / returns / cases (9 tables)
Mostly empty — looks like this was scaffolded but not fully operational yet.

| Table | Rows | Notes |
|---|---:|---|
| `orders` | 3 | Order records. → tenants. |
| `payments` | 0 | → orders, tenants. |
| `returns` | 1 | → orders, tenants. |
| `shipments` | 1 | → orders, tenants. |
| `shipping_rates` | 116 | Active rate table. |
| `shipping_surcharges` | 32 | Surcharge table. |
| `cases` | 1 | Case records. → orders, users, tenants. |
| `case_events` | 0 | Case event log. → cases, tenants. |
| `evidence` | 0 | Evidence attachments. → cases, tenants. |

### Loss-tool (5 tables)
**All empty.** Schema exists but the feature has no data — confirm if live or dormant.

| Table | Rows |
|---|---:|
| `loss_orders` | 0 |
| `loss_cases` | 0 |
| `loss_returns` | 0 |
| `loss_shipments` | 0 |
| `loss_tenant_settings` | 0 |

### PDF-merge tool (5 tables)
Live and used. Two-level hierarchy: sessions → jobs → job_files, plus events log.

| Table | Rows | Notes |
|---|---:|---|
| `pdf_merge_sessions` | 115 | User sessions. |
| `pdf_merge_jobs` | 32 | Merge jobs. → sessions. |
| `pdf_merge_job_files` | 334 | Individual input files per job. → jobs. |
| `pdf_merge_events` | 135 | Audit events. → sessions, jobs. |
| `pdf_merge_dailystats` | 4 | Daily aggregate stats. |

### LEGO tools — reference data (3 tables)

| Table | Rows | Notes |
|---|---:|---|
| `lego_element_map` | 103,541 | Largest table. Element→part mapping. |
| `lego_rb_colors` | 275 | Rebrickable color catalog. |
| `lego_color_map` | 87 | Color cross-reference. |

### Analytics / audit (5 tables)

| Table | Rows | Notes |
|---|---:|---|
| `audit_log` | 11,604 | Generic audit trail (largest non-lego table). |
| `user_events` | 620 | User activity events. |
| `user_acquisition` | 99 | Attribution/acquisition tracking. |
| `tool_events` | 8 | Per-tool usage events. |
| `tool_diff_saves` | 3 | Saved diff-tool outputs. |

### Misc (1 table)

| Table | Rows | Notes |
|---|---:|---|
| `contact_messages` | 954 | Contact-form submissions. → users (SET NULL). |

---

## Foreign key dependencies

Read as: **table → parent table** (with delete rule). 27 FKs total.

- `cases` → `users` (SET NULL), `orders` (CASCADE), `tenants` (CASCADE)
- `case_events` → `cases` (CASCADE), `tenants` (CASCADE)
- `cmp_consents` → `cmp_domains` (RESTRICT)
- `cmp_consent_logs` → `cmp_domains` (RESTRICT)
- `contact_messages` → `users` (SET NULL)
- `evidence` → `cases` (CASCADE), `tenants` (CASCADE)
- `loss_cases`, `loss_returns`, `loss_shipments` → `loss_orders` (CASCADE)
- `orders`, `payments`, `returns`, `shipments` → `tenants` (CASCADE)
- `payments`, `returns`, `shipments` → `orders` (CASCADE)
- `pdf_merge_jobs` → `pdf_merge_sessions` (CASCADE)
- `pdf_merge_job_files` → `pdf_merge_jobs` (CASCADE)
- `pdf_merge_events` → `pdf_merge_sessions` (SET NULL), `pdf_merge_jobs` (SET NULL)
- `plan_features`, `tenant_subscriptions` → `plans` (RESTRICT)
- `tenant_api_keys` → `tenants` (CASCADE)
- `users`, `user_sessions`, `user_twofa`, `user_twofa_backup_codes`, `user_twofa_trusted_devices` → `tenants`/`users` (CASCADE)

**No FKs found on:** `lego_*`, `audit_log`, `user_events`, `user_acquisition`, `tool_events`,
`tool_diff_saves`, `email_verify_tokens`, `cmp_categories`/`scripts`/`texts`/`branding`/`policy`,
`shipping_rates`, `shipping_surcharges`, `pdf_merge_dailystats`. A few of those probably
*should* have FKs (e.g. `audit_log.user_id` → users) — opportunity for V2 to tighten up.

---

## Charset note

Most tables use `utf8mb4_*` ✓. These tables still use legacy `utf8mb3`:

- `lego_color_map`, `lego_element_map`, `lego_rb_colors`
- `plans`, `plan_features`
- `loss_tenant_settings`

**V2 recommendation:** standardize on `utf8mb4_unicode_ci` across the board. Emoji and
non-BMP chars silently break in utf8mb3.

Within utf8mb4, two collations are mixed: `utf8mb4_general_ci` and `utf8mb4_unicode_ci`.
General is faster but inaccurate for sorting; unicode is correct. Pick one —
Laravel default is `utf8mb4_unicode_ci`.

---

## Porting priority for V2

V2's core product is a **freemium tools platform** + corporate site for Beter Geregeld ICT
as software partner. Access model: anonymous → basic tools free; registered → more;
paid plan → advanced/bulk. `plan_features` drives gating.

1. **Foundation (auth + tenancy):** `users`, `tenants`, `user_sessions`,
   `user_twofa`, `user_twofa_backup_codes`, `email_verify_tokens`
2. **Freemium plumbing:** `plans`, `plan_features`, `tenant_subscriptions` — tool-level
   gating depends on this being right from day 1
3. **Tools event + audit infra:** `tool_events`, `tool_diff_saves`, `user_events`,
   `user_acquisition`, `audit_log` — analytics and usage gating need this live early
4. **CMP (complete sub-system):** all 8 `cmp_*` tables — legal/GDPR requirement for a
   public site with third-party scripts
5. **Loss-tool (strategic, not yet launched):** all 5 `loss_*` tables — treat as
   first-class V2 feature. Schema exists, logic/UI still to build.
6. **Tool: PDF-merge:** 5 `pdf_merge_*` tables — flagship live tool, keep it
7. **Contact + shipping rates:** `contact_messages`, `shipping_rates`, `shipping_surcharges`
   (shipping-rates is itself one of the public tools)
8. **Business domain (sparse but live — needed for paid flow):**
   `orders`, `payments`, `returns`, `shipments`, `cases`, `case_events`, `evidence`
9. **Low priority / legacy:** `lego_*` (3 tables)
10. **Defer:** `tenant_api_keys`, `user_twofa_trusted_devices`

**Tools sidebar — full list from V1 `app/controllers/`** (to help V2 scope):
PDF merge/redact, favicon generator, IBAN check, VAT check, postcode check, IP lookup,
JSON formatter, internet speedtest, shipping rates, diff, silent loss, LEGO color/element
lookup/import. Many of these are stateless (no DB) — port is mostly UI + logic.
