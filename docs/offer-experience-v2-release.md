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
