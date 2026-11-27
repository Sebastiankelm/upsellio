# Production CI/CD — upsellio.pl

The default production transport is the installed WordPress Upsellio DevOps Bridge 2.0.0. GitHub Actions validates code only; a successful validation is not evidence of a production deployment. No SSH is needed. Do not run the legacy transport concurrently.

## Chat-driven release (no SSH)

1. Make a focused PR; wait for required checks and merge the reviewed revision to main.
2. Read WordPress page 947 in edit context with meta. Use only meta.upsellio_devops_status. Require configured, auto, file_mod_allowed, php_lint_available, and no recovery_required. For backend paths require the corresponding auto_crm, auto_offers and auto_seo settings (all applicable scopes for shared paths). Require offer_compatibility_ready for offer paths.
3. Fetch .upsellio-deploy/request.json from branch upsellio-deploy. Do not replace a pending request. The idle document is exactly {"schema":1,"action":"idle"}.
4. Select only reviewed, existing, allowed theme files. Fetch exact bytes from the pinned main commit and the known production baseline from GitHub. Compare production SHA-256 against the baseline byte hash; uniform LF/CRLF differences may be reconciled only by establishing the exact matching byte representation. If production is neither the expected reviewed baseline nor already the candidate, stop and reconcile; never use an unexplained live hash as authorization to overwrite. Skip unchanged files. For scope and size limits see the README on upsellio-deploy. Do not add paths by guessing; use the live files_sha256 and backend_allowlist.
5. Publish the exact schema-1 manifest on the request branch with a unique request_id, commit, and files entries (path, before, after). Use the current blob SHA for optimistic concurrency. WordPress polls and applies the manifest automatically, subject to its own safety checks and schedule.
6. Read the live status again: verify the exact request ID, commit, success state, all resulting hashes, no recovery flag, and functional checks for the affected public, CRM, offer or SEO scope. Do not claim completion until verified. Only then reset the exact consumed manifest to idle. Never alter offer terms or customer data in a deployment smoke test.

WordPress bridge retains file backup, lint, rollback, cache clearing, explicit allowlists and offer compatibility checks. No database migrations, new theme files, deletions, plugin self-update or arbitrary shell commands are covered by this protocol. Cron is traffic/schedule dependent; do not promise immediate installation.

## Legacy SSH fallback

Disabled by default, including pushes to main. It runs only on a manual workflow dispatch on main with repository variable UPSELLIO_ENABLE_LEGACY_SSH explicitly true. Use only if an administrator deliberately switches transport and has verified the independent production baseline and stopped concurrent bridge deployments. Never commit SSH credentials; the legacy workflow requires UPSELLIO_SSH_HOST, UPSELLIO_SSH_USER, UPSELLIO_SSH_PRIVATE_KEY, UPSELLIO_SSH_KNOWN_HOSTS and optionally UPSELLIO_SSH_PORT. Keep host-key verification enabled. The old SSH tools are retained for compatibility, not the normal release path.

## Bridge source

The installed bridge source must be recovered and reviewed before changing or replacing the plugin itself. This configuration change does not replace it or expand its permissions.
