# Upsellio

WordPress theme and internal CRM for [upsellio.pl](https://upsellio.pl/) — digital marketing agency (Google Ads, SEO, analytics, landing pages, lead management).

The repository contains the **Upsellio theme** (`wp-content/themes/upsellio/`), GTM configuration specs, operational CLI tools, and API design documentation.

---

## Stack

| Layer | Technology |
|-------|------------|
| CMS | WordPress (App Router-style CRM under `/crm-app/`) |
| Theme | Custom PHP theme `upsellio` |
| CRM UI | Server-rendered PHP views + vanilla JS (Chart.js) |
| Data | WordPress post meta, custom post types (`ups_client`, resources, leads) |
| Integrations | GA4 Data API, GSC API, Google Ads API (GAQL read-only), Microsoft Clarity, Meta Ads |
| OAuth | Google OAuth (managed accounts), Meta OAuth |
| PDF export | Dompdf (`composer.json` in theme) |
| AI | Anthropic Claude (briefs, reports, blog bot) |

---

## Repository structure

```
upsellio/
├── README.md                 # This file
├── docs/                     # API design docs (Google Ads, etc.)
├── gtm/                      # GTM Web container spec + README
├── tools/                    # Root-level OAuth / admin test scripts
└── wp-content/
    ├── mu-plugins/
    │   └── upsellio-crm-memory.php   # Memory limit for /crm-app/
    └── themes/upsellio/
        ├── inc/
        │   ├── client-audit*.php       # Client Audit engine (core product)
        │   ├── crm-app/                # CRM shell, views, AJAX, assets
        │   ├── google-oauth-*.php      # Google OAuth
        │   ├── meta-ads-api.php        # Meta Marketing API
        │   ├── site-analytics.php      # Internal site analytics
        │   └── …                       # CRM, AI, SEO automation
        └── tools/                      # WP-CLI eval-file scripts (sync, debug)
```

---

## Client Audit CRM

Internal decision layer for agency clients: unified dashboard combining **GA4**, **GSC**, **Google Ads**, **Clarity**, and **CRM revenue attribution**.

### URLs

| View | Path |
|------|------|
| Client dashboard | `/crm-app/?view=ca-dashboard&cid={client_id}&window=30` |
| Command Center | `/crm-app/?view=ca-command-center` |
| Accounts / OAuth | `/crm-app/?view=ca-accounts` |

### Architecture

```
Resources (GA4, GSC, Ads, Clarity)
        ↓ sync (cron / manual / wp eval-file)
   _ups_resource_data_cache (post meta)
        ↓ ups_audit_aggregate_client_data()
   Aggregated KPIs + timeseries (30/60/90 d)
        ↓ ups_audit_attach_intelligence()
   Intelligence layer (opportunity, search terms, SEO roadmap, alerts)
        ↓ trust scores
   Data Quality panel (Attribution / Revenue / Tracking / Clarity)
```

### Key modules

| File | Role |
|------|------|
| `inc/client-audit.php` | GA4/GSC fetch, KPI derivation, event diagnostics |
| `inc/client-audit-sync.php` | Resource sync, aggregation, health snapshots |
| `inc/client-audit-data.php` | Search term classification, SEO clustering |
| `inc/client-audit-intelligence.php` | Opportunity score, journey, alerts, Command Center |
| `inc/client-audit-trust-scores.php` | Attribution & Revenue Confidence, Data Quality |
| `inc/client-audit-crm-attribution.php` | CRM revenue by channel (UTM / lead source) |
| `inc/client-audit-clarity.php` | Microsoft Clarity API |
| `inc/client-audit-oauth.php` | Google OAuth for audit resources |
| `inc/crm-app/views/client-audit-dashboard.php` | Main dashboard |
| `inc/crm-app/partials/audit-intelligence.php` | Command Center + Data Quality UI |

### Trust scores & data quality

The dashboard **does not hide bad data**. When tracking is broken, scores reflect it:

| Score | Meaning |
|-------|---------|
| **Attribution Confidence** | GA4 channel attribution reliability (`(not set)`, Ads↔GA4 gap, CRM UTM) |
| **Revenue Confidence** | GA4 revenue reliability (`purchaseRevenue` vs inflated `eventValue` on WooCommerce/GTM) |
| **Tracking Health** | Composite GTM/consent/UTM health |
| **Clarity UX** | UX score with **Confidence: Low** when API data is suspicious |
| **Health trend** | Monthly snapshots (6 months) on sync |

**Confidence bands** (both Attribution and Revenue):

| Range | Label |
|-------|-------|
| 0–20% | krytyczne |
| 20–40% | bardzo niskie |
| 40–60% | średnie |
| 60–80% | dobre |
| 80–100% | wysokie |

Catastrophic raw scores are floored to **5–12%** when data exists (avoids misleading absolute 0% while keeping warnings active).

The **Data Quality** panel also shows per-source ratings (GSC, Ads, Search Terms, GA4, Clarity, CRM) on a **0–10** scale with an overall average.

When `revenue_trusted = NO`, ROAS and revenue KPIs are dimmed and AI reports are blocked from using inflated GA4 revenue.

### Revenue model

- **KPI revenue** = GA4 `purchaseRevenue` (session-scoped purchases), not `totalRevenue`
- **Event diagnostics** = raw `eventValue` per event — surfaces WooCommerce/GTM misconfiguration (`view_item`, `add_to_cart` with revenue)

### Search Terms Intelligence

Classification: **Skaluj** / **Obserwuj** / **Wyklucz**

Exclusion candidates require aging: **≥30 days**, **≥100 PLN** spend, **0 conversions** — no automatic reckless exclusions.

---

## Integrations

### Google (OAuth + APIs)

- Managed Google accounts per client resource
- GA4 Data API — sessions, conversions, `purchaseRevenue`, channel attribution
- Search Console API — keywords, impressions, indexation
- Google Ads API — **read-only** GAQL (`searchStream`), campaign CPA, search terms  
  See [docs/google-ads-api-design-document-upsellio.md](docs/google-ads-api-design-document-upsellio.md)

### Microsoft Clarity

- Session recordings metrics via Clarity API (rate-limited: ~10 requests/day per project)
- Max 3-day window per API call

### Meta Ads

- `inc/meta-ads-api.php` + `client-audit-meta*.php` for Meta account linking and reporting

### GTM / GA4 (public site)

See [gtm/README.md](gtm/README.md) for container `GTM-KM9J5XC2` and dataLayer events (`generate_lead`, `contact_click`, etc.).

---

## Development

### Requirements

- PHP ≥ 7.4
- WordPress with theme active
- WP-CLI on server (for `tools/` scripts)
- Composer in theme dir for PDF: `cd wp-content/themes/upsellio && composer install`

### Local theme path

```
wp-content/themes/upsellio/
```

### CRM memory

`wp-content/mu-plugins/upsellio-crm-memory.php` raises memory to 1024M for `/crm-app/` and OAuth callbacks.

---

## Operations (WP-CLI)

Run from WordPress root (`public_html`):

```bash
# Full client sync (example: WTapes #965)
wp eval-file wp-content/themes/upsellio/tools/audit-sync-wtapes-full-prod.php

# Quick score check after code changes
wp eval-file wp-content/themes/upsellio/tools/audit-check-scores.php 965 30

# Intelligence smoke test
wp eval-file wp-content/themes/upsellio/tools/audit-test-intel.php 965 30

# Health check all audit profiles
wp eval-file wp-content/themes/upsellio/tools/audit-health-check.php
```

More scripts in `wp-content/themes/upsellio/tools/` — sync, OAuth debug, GA4/GSC/Ads diagnostics, PDF test.

**Never commit** `*-credentials.json`, deploy `.tgz` archives, or `.env` files.

---

## Production deployment

Hosting: **cyber_Folks** — `upsellio.pl` → `~/domains/upsellio.pl/public_html/`

Deploy changed theme files via SCP/rsync to:

```
domains/upsellio.pl/public_html/wp-content/themes/upsellio/
```

After deploy, re-aggregate affected clients:

```bash
wp eval-file wp-content/themes/upsellio/tools/audit-sync-wtapes-full-prod.php
```

---

## Documentation

| Document | Description |
|----------|-------------|
| [docs/google-ads-api-design-document-upsellio.md](docs/google-ads-api-design-document-upsellio.md) | Google Ads API application — read-only CRM reporting |
| [gtm/README.md](gtm/README.md) | GTM Web + GA4 event setup |

---

## Security notes

- OAuth tokens stored in WordPress post meta (managed accounts)
- Google Ads API: read-only, internal staff only, no public API exposure
- Credentials and sync scripts with secrets are gitignored (`*-credentials.json`, `gsc-sync.py`, etc.)

---

## License

Proprietary — Upsellio internal use.
