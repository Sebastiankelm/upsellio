# Production CI/CD — upsellio.pl

Workflow: `.github/workflows/production-release.yml`. Pull requests validate only; pushes to `main` and manual runs on `main` validate and then deploy. The production job cannot proceed without the configured SSH credentials. No web-accessible deployment endpoint is introduced.

## One-time setup (administrator)

Create a dedicated SSH key for the hosting account with the minimum practical filesystem permissions. Install its public key using the cyber_Folks SSH administration interface. Confirm the SSH hostname, account name, port, and server public host key from the hosting provider or a trusted existing connection. Do not accept an unverified host key.

In GitHub Settings → Environments, create or edit `production`, restrict deployment branches to `main`, and configure these environment secrets:

- `UPSELLIO_SSH_HOST`: actual SSH hostname (not necessarily upsellio.pl).
- `UPSELLIO_SSH_USER`: hosting SSH login.
- `UPSELLIO_SSH_PRIVATE_KEY`: private deployment key (never commit it or share it in chat).
- `UPSELLIO_SSH_KNOWN_HOSTS`: verified OpenSSH known_hosts entry for hostname/port.
- Optional environment variable `UPSELLIO_SSH_PORT`: defaults to 22.

The hosting account must support PHP CLI, curl, WP-CLI, SCP and writable theme files. The current target follows the repository README: `$HOME/domains/upsellio.pl/public_html/wp-content/themes/upsellio`. If hosting restrictions prevent a connection or the real path differs, stop and correct the configuration. Do not disable SSH host verification. Do not use two deployment mechanisms concurrently.

## Release process

The initial expected Git base is `d8b359ae80d1f8021cbe904e0bf43fa6115ac056` (pre-redesign). The server stores the last completed Git SHA in `$HOME/.upsellio-deploy/deployed-sha`. The release builder compares that revision with the exact main revision and packages only changed files on the explicit public-theme allowlist. New/deleted/unreviewed theme paths are refused; CRM changes outside the shared files are not included. No database migrations or direct database changes are attempted. Extend the allowlist in both scripts via a reviewed PR when adding paths.

Server-side preflight checks baseline and payload SHA-256 hashes, target paths, PHP syntax, and release state. A file already at the exact candidate hash is accepted; other drift (including different line endings) blocks publication. Do not manually edit the recorded base to bypass drift. Reconcile the actual production files in a reviewed release first.

Before changing files, the process creates a private backup outside the webroot and records a pending journal. Changed files are replaced individually with atomic renames, verified, and followed by WP object-cache flush and HTTP checks on five public pages. This is a file-level deployment, not an atomic whole-site switch; external page caches may need separate invalidation. Failed checks trigger restoration of files changed by that run. No lead or ad conversion is submitted by smoke tests.

Interrupted processes may leave `pending.json`; further deploys deliberately stop. An administrator must inspect the journal and backup, reconcile production hashes, restore only the affected files if needed, verify the site, and clear the journal after reconciliation. Preserve backups and periodically review retention/storage. Never remove a journal merely to force another deployment.

After setting up SSH, run Actions → Production release (Upsellio) → Run workflow → main. Verify the deployment job, the recorded revision, and visual/contact-form functionality. Subsequent merges trigger the same workflow automatically. GitHub repository test success does not on its own prove production deployment.

## Scope and limitations

The initial release covers the fourteen explicitly approved files in the public redesign, including the shared CRM callback code. It is not a full WordPress synchronization and does not deploy plugins, uploads, credentials, wp-config.php, or database content. The DevOps Bridge remains installed; this workflow uses the documented SSH/SCP route rather than changing its admin-only access model.
