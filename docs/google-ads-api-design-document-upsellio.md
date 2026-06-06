# Upsellio CRM — Google Ads API Integration Design Document

| Field | Value |
|-------|--------|
| **Document version** | 1.0 |
| **Date** | March 2026 |
| **Company** | Upsellio (upsellio.pl) |
| **Primary website** | https://upsellio.pl/ |
| **API contact** | seba.k434@gmail.com |
| **Google Ads MCC (manager account)** | 133-333-0388 (1333330388) |
| **Developer token** | Issued in Google Ads API Center under the above MCC |

---

## 1. Executive summary

Upsellio is a digital marketing agency in Poland. We operate paid search, display, and performance campaigns for our own clients under a Google Ads manager account (MCC).

We developed an **internal** web application — **Upsellio CRM** — hosted on our domain `https://upsellio.pl/`. It is used **only by Upsellio employees** (and contractors bound by the same policies). The application is **not** a public SaaS where arbitrary third parties connect their Google Ads accounts.

The Google Ads API is integrated for **read-only reporting**: listing accessible customer accounts, downloading campaign performance metrics via GAQL (`googleAds:searchStream`), and displaying aggregated KPIs next to Google Analytics 4 and Google Search Console data in per-client audit dashboards.

We **do not** use the API to create or modify accounts, campaigns, ad groups, ads, keywords, budgets, or bids. We **do not** resell API access. We **do not** expose a public API that returns Google Ads data to third parties.

---

## 2. Company and use case

### 2.1 Business model

- **Legal entity / brand:** Upsellio  
- **Website:** https://upsellio.pl/  
- **Activity:** Digital marketing agency (Google Ads, SEO, analytics, landing pages, CRM for leads).  
- **Google Ads usage (human):** Campaign management in the Google Ads UI by certified staff.  
- **Google Ads usage (API):** Automated **reporting and auditing** inside our CRM only.

### 2.2 Why we need API access

| Need | Without API | With API |
|------|-------------|----------|
| Unified client dashboard | Manual CSV exports | Automated sync of last 30–90 days |
| Multi-account under MCC | Switching accounts in UI | `listAccessibleCustomers` + per-client mapping |
| Consistency with GA4/GSC | Disparate tools | Single “Client audit” view in CRM |

### 2.3 What we do **not** do with the API

- Account or campaign creation / editing / removal  
- Bid or budget changes  
- Ad creative upload  
- Remarketing list upload  
- Offline conversion import (Conversion Upload API)  
- App Conversion Tracking API  
- Offering our developer token to external developers or clients  

---

## 3. Users and access control

### 3.1 Who uses the tool

| User type | Access | Notes |
|-----------|--------|-------|
| Upsellio employees | Yes | WordPress login required |
| Contractors | Yes | Same CRM accounts, NDA / employment terms |
| Clients (advertisers) | **No** direct API tool access | May receive PDF/email reports; they do not log into the API integration |
| General public | **No** | CRM routes are authenticated |

### 3.2 Authentication to Upsellio CRM

- WordPress user accounts with role `edit_posts` or higher for OAuth connect actions.  
- CRM app URL: `https://upsellio.pl/crm-app/` (requires login).  
- Session cookies + HTTPS (TLS).

### 3.3 Google OAuth (per connected Google account)

- **Flow:** OAuth 2.0 authorization code, offline access (refresh token).  
- **Redirect URI (production):** `https://upsellio.pl/crm-app/?view=ca-accounts`  
- **Optional alternate (registered):** `https://upsellio.pl/index.php?pagename=crm-app&view=ca-accounts`  
- **Scopes requested (typical):**
  - `https://www.googleapis.com/auth/adwords` (Google Ads API)
  - `https://www.googleapis.com/auth/analytics.readonly` (GA4)
  - `https://www.googleapis.com/auth/webmasters.readonly` (Search Console)

Refresh tokens and client secrets are stored **encrypted** in the WordPress database (per `crm_google_account` record). Only staff with CRM access can trigger reconnect.

---

## 4. System architecture

### 4.1 High-level diagram (textual)

```
[Upsellio employee browser]
        | HTTPS
        v
[upsellio.pl - WordPress]
   |-- Theme: Upsellio
   |-- CRM module: /crm-app/
   |-- Admin: Analityka SEO (OAuth client ID/secret, Ads developer token)
        |
        |-- OAuth 2.0 --> [Google Accounts / OAuth]
        |
        |-- REST GAQL  --> [Google Ads API v21]
        |                  login-customer-id: MCC 1333330388
        |                  customer_id: client account e.g. 5195787252
        |
        |-- REST       --> [Google Analytics Data API]
        |-- REST       --> [Google Search Console API]
        v
[MySQL - WordPress DB]
   post types: crm_client, crm_google_account, crm_audit_resource
   encrypted meta: refresh tokens
   cache meta: synced metrics (_ups_resource_data_cache)
```

### 4.2 Technology stack

| Layer | Technology |
|-------|------------|
| CMS | WordPress 6.x |
| Application code | Custom theme `upsellio` (PHP 8.x) |
| HTTP client | WordPress HTTP API (`wp_remote_post`) |
| Scheduler | WordPress cron (`wp_schedule_single_event` for background sync) |
| Ads API version | Google Ads API **v21** (REST) |

### 4.3 Hosting

- Production: `https://upsellio.pl/` (commercial hosting, EU).  
- No multi-tenant separation required: single agency instance.

---

## 5. Google Ads API integration (detailed)

### 5.1 Credentials

| Credential | Where stored | Purpose |
|------------|--------------|---------|
| **Developer token** | WordPress option `upsellio_google_ads_config` (admin-only UI: Analityka SEO) | Required header on every Ads API request |
| **OAuth client ID / secret** | WordPress options (Analityka SEO / GSC settings) | Token refresh |
| **Refresh token** | Per-account encrypted post meta on `crm_google_account` | Access token for specific connected Google user |
| **login-customer-id** | Config: MCC **1333330388** | Manager context for client customer queries |
| **customer_id** | Per `crm_audit_resource` (Ads type) | Client Ads account to query (e.g. **5195787252** for client “wtapes”) |

### 5.2 API methods invoked

#### A. List accessible customers (diagnostics + UI picker)

- **Purpose:** Show which customer IDs the connected Google user can access under the MCC.  
- **Usage frequency:** On “Refresh resources” in CRM and after OAuth connect (low volume).  
- **Implementation:** `CustomerService.ListAccessibleCustomers` (REST wrapper in application).

#### B. Campaign reporting (primary)

- **Transport:** `POST /v21/customers/{customer_id}/googleAds:searchStream`  
- **Purpose:** Aggregate KPIs and campaign list for dashboard.  
- **Usage frequency:** Manual sync per client or scheduled job; typically **≤ few runs per client per day**.

**Example GAQL query (read-only):**

```sql
SELECT
  campaign.id,
  campaign.name,
  campaign.status,
  campaign.advertising_channel_type,
  segments.date,
  metrics.cost_micros,
  metrics.clicks,
  metrics.impressions,
  metrics.conversions,
  metrics.ctr,
  metrics.average_cpc
FROM campaign
WHERE campaign.status = 'ENABLED'
  AND segments.date DURING LAST_30_DAYS
```

Date ranges also supported: `LAST_14_DAYS`, `LAST_60_DAYS`, `LAST_90_DAYS` (configuration in sync module).

#### C. Daily metrics (optional / extended sync)

Similar GAQL on `customer` or campaign with `segments.date` for time-series charts in audit dashboard.

#### D. Search terms (optional, internal research module)

GAQL on `search_term_view` for keyword research UI — still read-only, staff-only.

#### E. Keyword Planner (optional, separate from core audit)

Keyword Plan Idea service may be used in internal keyword research with user-provided seeds. This is **planning**, not campaign mutation. Primary API approval need is **Reporting**.

### 5.3 Campaign types supported

Reporting queries do not filter by channel type; we **read all enabled campaign types** returned by the API, including:

- Search  
- Performance Max  
- Display  
- Video  
- Shopping  
- Demand Gen  

The field `campaign.advertising_channel_type` is stored in aggregated results for display in CRM.

### 5.4 Mutations

**None.** The application does not call `Mutate` services.

---

## 6. Data flow (end-to-end)

### 6.1 Connect Google account

1. Employee opens `CRM → Konta Google` (`/crm-app/?view=ca-accounts`).  
2. Clicks “Connect Google account” / “Add Google Ads (same Gmail)” for an existing card.  
3. OAuth consent screen (Google) — user approves scopes.  
4. Redirect to `https://upsellio.pl/crm-app/?view=ca-accounts` with authorization code.  
5. Server exchanges code for refresh token (single use), upserts `crm_google_account` post.  
6. Background job may prefetch GA4/GSC/Ads resource lists.

### 6.2 Map Ads account to client profile

1. Employee opens `Profile klientów` (e.g. client **wtapes**, ID 965).  
2. Imports or selects Ads resource with `customer_id` **5195787252**.  
3. Resource stored as `crm_audit_resource` with type `ads`, linked to `crm_google_account` **963**.

### 6.3 Sync metrics

1. Employee clicks “Sync” on dashboard or automated cron runs `ups_audit_sync_ads_resource`.  
2. App loads OAuth for linked `crm_google_account`, sets `login-customer-id` and `customer_id`, runs GAQL `searchStream`.  
3. Parses streaming JSON response; aggregates cost, clicks, impressions, conversions.  
4. Writes cache to post meta `_ups_resource_data_cache` and timestamp `_ups_resource_last_data_sync`.  
5. Dashboard reads cache only (no Google call on page view).

### 6.4 Data retention

- Cached metrics: stored until next sync or resource unmapped.  
- Refresh tokens: until disconnect or revocation.  
- No sale or export of Google Ads data to third-party APIs.

---

## 7. Rate limits, errors, and monitoring

### 7.1 Expected volume

| Operation | Estimated daily volume (agency-wide) |
|-----------|--------------------------------------|
| `listAccessibleCustomers` | < 50 |
| `searchStream` (GAQL) | < 200 |
| OAuth token refresh | < 100 |

We are a small agency; traffic is far below Google Ads API developer token limits.

### 7.2 Error handling

| HTTP / error | Application behavior |
|--------------|---------------------|
| 401 / invalid_grant | Prompt user to reconnect OAuth; clear invalid refresh token |
| 403 PERMISSION_DENIED | Surface message in CRM (e.g. developer token test mode, or missing account access) |
| 429 RESOURCE_EXHAUSTED | Backoff and retry (transient); log error |
| 5xx | Retry with delay; log error |

Errors are logged to WordPress `debug.log` when enabled; user sees human-readable message in CRM (`ups_audit_error` / resource health label).

### 7.3 Developer token handling

- Entered only in admin settings (not shown to clients).  
- Not embedded in front-end JavaScript.  
- Transmitted only server-side in `developer-token` HTTP header.

---

## 8. Security and compliance

### 8.1 Transport and storage

- All browser and API traffic over **HTTPS**.  
- Refresh tokens encrypted at rest (application-level encryption before `update_post_meta`).  
- Least-privilege OAuth scopes (read-only analytics/search console + adwords for reporting).

### 8.2 Access boundaries

- CRM requires WordPress authentication.  
- Google Ads data visible only inside CRM screens for mapped clients.  
- No public REST endpoint exposes raw Google Ads API responses.

### 8.3 Policies

- We comply with [Google Ads API Terms and Conditions](https://developers.google.com/google-ads/api/terms).  
- Privacy policy for website users: **https://upsellio.pl/** (footer link “Polityka prywatności” — standard GDPR notice for Poland).  
- Google user data used solely to provide reporting services to clients we already manage contractually.

### 8.4 Third-party tools

- We **do not** use the developer token with software built by another vendor.  
- WordPress core and Rank Math SEO plugin do not receive Google Ads API data.

---

## 9. Capabilities matrix (form alignment)

| Google Ads API capability (form) | Supported |
|----------------------------------|-----------|
| Account Creation | No |
| Account Management | No |
| Campaign Creation | No |
| Campaign Management | No |
| **Reporting** | **Yes** |
| Keyword Planning Services | Optional (internal keyword research module) |
| App Conversion Tracking / Remarketing API | No |

---

## 10. Screenshots (appendix — insert images before PDF export)

*Replace placeholders with actual screenshots from production.*

**Figure 1 — CRM: Google Accounts (OAuth connect)**  
Path: `/crm-app/?view=ca-accounts`  
Shows: Connected Google accounts, “Add Google Ads (same Gmail)” button, Developer token configured in admin.

**Figure 2 — CRM: Client profile mapping**  
Path: `/crm-app/?view=ca-clients`  
Shows: Client profile (e.g. wtapes) with mapped GA4, GSC, and Ads resources.

**Figure 3 — CRM: Client audit dashboard**  
Path: `/crm-app/?view=ca-dashboard&cid={client_id}`  
Shows: KPI row including Google Ads cost/clicks/impressions (after token approval and sync).

**Figure 4 — WordPress admin: Google Ads API settings**  
Path: Analityka SEO → Google Ads API section  
Shows: Developer token, Login Customer ID (MCC), default customer ID (admin-only).

---

## 11. Glossary

| Term | Meaning in Upsellio CRM |
|------|-------------------------|
| MCC | Manager account **1333330388** |
| `crm_google_account` | Connected Google identity (OAuth refresh token) |
| `crm_audit_resource` | Imported property (GA4 property ID, GSC site URL, or Ads customer ID) |
| `crm_client` | Client profile (brand/project) |
| GAQL | Google Ads Query Language via `searchStream` |

---

## 12. Document history

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | March 2026 | Upsellio / Sebastian | Initial submission for Google Ads API token application (Basic/Standard access) |

---

## 13. Contact

For questions regarding this integration during API token review:

- **Email:** seba.k434@gmail.com  
- **Company website:** https://upsellio.pl/  
- **MCC ID:** 133-333-0388  

---

---

## 14. Appendix B — Meta Ads API (CRM audit stack)

Upsellio CRM includes a parallel **Meta Marketing API** integration (read-only reporting):

| Item | Value |
|------|--------|
| CRM view | `/crm-app/?view=ca-meta-accounts` |
| OAuth redirect | `https://upsellio.pl/crm-app/?view=ca-meta-accounts` |
| Scopes | `ads_read`, `business_management` |
| Resource type | `_ups_resource_type = meta` |
| External ID | `act_{ad_account_id}` |
| CPT | `crm_meta_account` (encrypted long-lived token) |
| Sync | Insights API: daily spend/clicks/conversions + campaign breakdown |
| Dashboard KPIs | `meta_cost`, `meta_cpc`, `meta_roas`, `paid_cost` (Google + Meta) |

Configuration: App ID + App Secret in CRM → **Połączenia Meta** (admin only).

*End of document*
