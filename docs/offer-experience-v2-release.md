# Offer Experience v2 — release checklist

## Scope and measurement contract

Private offer landing pages are not public marketing pages. The default shared site GTM container is intentionally not loaded on private offers. The public site GTM helper remains unchanged. CRM telemetry is enabled only with Cookiebot statistics consent and outside preview/internal tracking. Marketing-consented GCLID is optional; no contact data, offer token, title, internal client/person ID or raw link is intended for external event parameters.

Do not enable a dedicated container through the `upsellio_private_offer_gtm_container_id` filter until its tags have been reviewed. A custom page_location parameter alone does not prevent GTM tags from reading the real browser URL. Disable automatic page views, enhanced measurement, third-party remarketing and automatic DOM/form capture on private pages; verify actual network payloads and referrers using a synthetic offer. Never include real customer data in UTM parameters or screenshots.

## Release gates

- GitHub Actions: PHP lint, inline JS syntax, isolated regression harness.
- WordPress staging with synthetic client/offer, controlled mail sink, no live automations.
- Desktop/mobile (390, 768, 1440 px), keyboard focus, close/back, sticky CTA and selected variant.
- Test blank optional fields, offers without variants, expired/draft/closed offers, no consent, statistics-only and marketing consent, preview and internal users.
- Open two tabs: repeated event IDs, overlapping events, lost network response, concurrent acceptance, edit price/scope in CRM while offer open; reject stale commercial terms.
- Verify that Q&A includes the original question, answer, version and timestamp in CRM; check mail failure without duplicate submissions.
- Verify that closed offers never receive queued follow-up messages, including cron overlap and queued mail already being sent.
- Verify site timezone and DST behavior, legacy event migration, and dashboard counts versus the actual retained event history.
- Record a staging screenshot and network trace for each consent state. Confirm no offer URL/token appears in GA4 or GTM requests.
- Review existing offer links and archived data; no destructive metadata migration. Back up database before deployment.
- Only merge after integration results are recorded and reviewer approves. Deployment is separate from merging.

## Known limits

- Retained event history is bounded (600 entries); dashboard statistics are not lifetime totals.
- Sessions are browser-tab session IDs, not people or devices. Legacy visits are estimates. Activity time is sampled, not proof of reading.
- Public link possession is not verified customer identity; acceptance is not an electronic signature or proof of an executed contract.
- MySQL/MariaDB advisory lock support and object-cache behavior must be verified on the actual host.
- The acceptance status compare-and-set reduces repeated transitions; full database transaction and reliable email outbox remain separate work.
- This branch does not configure external GA4/GTM properties or deploy production changes.

## Verified CI baseline — 2026-11-25

- Run 45587251220 (#49): isolated WordPress/MariaDB, Chromium desktop/mobile, real AJAX acceptance and analytics, Q&A, ten persistence assertions and ten follow-up policy/locking assertions passed.
- Subsequent privacy, display-default and integrity changes require a new green run; previous evidence does not certify later commits.
- The CI WordPress is a minimal theme fixture, not a clone of the production plugin stack.

## Preflight and rollout

1. Record the exact release SHA, current production SHA and versions of WordPress, PHP, MariaDB and object cache. Confirm that the deploy mechanism covers the offer PHP files, CRM render/actions, sales engine and follow-ups.
2. Back up the database and existing changed theme files to a secure location. Verify restoration procedure before any writes. Never transfer private offer metadata to public artifacts.
3. Audit legacy offers with absent duration/billing/price-note/scope flags. Confirm real commercial terms with the operator and explicitly save them. The public renderer no longer inserts unsaved default terms; do not mass-fill defaults or alter accepted offers.
4. Inspect existing `_ups_offer_accept_integrity_error` and `delivery_unknown` states before enabling delivery. Investigate these manually; do not reset or re-send uncertain messages blindly.
5. Run PHP lint on the deployment host and verify GET_LOCK availability using two separate DB connections. Confirm permissions and the exact production AJAX URL. Keep GTM disabled on private offers.
6. Install the reviewed commit as a coordinated release. Do not deploy just one of the cooperating follow-up files. Use a maintenance window for the offer and follow-up module if the host lacks atomic release switching.
7. Smoke test a synthetic offer (desktop, mobile, privacy consent, Q&A, stale revision, acceptance, follow-up cancellation) with mail redirected or disabled. Confirm CRM dashboard and no external analytics leakage. Never use live clients for tests.
8. Check PHP errors, AJAX responses, duplicate claims, acceptance integrity errors, and the queue status before re-enabling live follow-up delivery.

## Rollback

- Stop the follow-up worker and disable new offer acceptance during a rollback window. Restore the previous complete theme version, not individual interdependent files.
- Preserve all real accepted offers, customer replies and event history created after release. A full DB restore may destroy new business records; use only after impact review and explicit authorization.
- Do not restore a queue snapshot over newer `sent`, `sending` or `delivery_unknown` states; inspect SMTP/mail logs first.
- Retain separate event history and accepted terms snapshots. Old code may expect the former mixed timeline field; do not overwrite commercial timeline with events.
- Re-run the smoke test and document the cause, affected records and the final deployed SHA. Do not restart automations until the queue has been reviewed.
