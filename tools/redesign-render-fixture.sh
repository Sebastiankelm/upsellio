#!/usr/bin/env bash
# Disposable CI fixture. No production database, credentials, exposed ports or external network.
set -Eeuo pipefail
base_ref=${1:?base revision required}
candidate_ref=${2:?candidate revision required}
out=${3:?output directory required}
mkdir -p "$out"
out=$(realpath "$out")
theme=wp-content/themes/upsellio
network="upsellio-fixture-${GITHUB_RUN_ID:-$$}"
db="$network-db"
wp="$network-wp"
volume="$network-volume"
wp_image=wordpress:php8.3-apache
cli_image=wordpress:cli-php8.3
db_image=mariadb:10.11
cleanup() {
  docker rm -f "$wp" "$db" >/dev/null 2>&1 || true
  docker volume rm "$volume" >/dev/null 2>&1 || true
  docker network rm "$network" >/dev/null 2>&1 || true
}
trap cleanup EXIT
# Pull public images before creating the isolated internal network.
docker pull "$wp_image"
docker pull "$cli_image"
docker pull "$db_image"
browser_image=mcr.microsoft.com/playwright:v1.51.1-noble
browser_dir=$(mktemp -d)
trap 'rm -rf "$browser_dir"; cleanup' EXIT
npm install --prefix "$browser_dir" --no-audit --no-fund --ignore-scripts playwright@1.51.1
cp tools/redesign-browser.mjs "$browser_dir/"
docker pull "$browser_image"
docker network create --internal "$network" >/dev/null
wpcli() {
  docker run --rm --network "$network" --volumes-from "$wp" --user 33:33 \
    -e WORDPRESS_DB_HOST=db -e WORDPRESS_DB_NAME=fixture -e WORDPRESS_DB_USER=fixture \
    -e WORDPRESS_DB_PASSWORD=fixture-only \
    -e WORDPRESS_CONFIG_EXTRA='define("DISABLE_WP_CRON", true); if (!defined("WP_DEBUG")) { define("WP_DEBUG", true); } define("WP_DEBUG_DISPLAY", false); define("WP_HTTP_BLOCK_EXTERNAL", true); define("WP_ENVIRONMENT_TYPE", "local");' \
    --workdir /var/www/html --entrypoint wp "$cli_image" --url=https://fixture.test "$@"
}
capture() {
  local key=$1 url=$2 dest=$3
  docker exec "$wp" curl --silent --show-error --max-time 45 \
    -H 'Host: fixture.test' -H 'X-Forwarded-Proto: https' \
    -o /tmp/fixture-page.html -w '%{http_code}' "http://127.0.0.1$url" > "$dest/$key.status"
  docker cp "$wp:/tmp/fixture-page.html" "$dest/$key.html"
}
# Exercise the real public handler using a nonce from the actual rendered form.
# All synthetic records, mail and requests stay in the disposable fixture.
test_leads() {
  local dest=$1 origin key nonce result mode email
  for origin in home-hero-form home-contact-form contact-page-form single-post-contact-form; do
    case "$origin" in
      home-*) key=home ;;
      contact-*) key=contact ;;
      single-*) key=article ;;
    esac
    nonce=$(python3 - "$dest/$key.html" "$origin" <<'PY'
from html.parser import HTMLParser
from pathlib import Path
import sys
class Forms(HTMLParser):
    def __init__(self):
        super().__init__()
        self.current = None
        self.forms = []
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == "form":
            self.current = {"action": a.get("action", "")}
        elif tag == "input" and self.current is not None:
            self.current[a.get("name", "")] = a.get("value", "")
    def handle_endtag(self, tag):
        if tag == "form" and self.current is not None:
            self.forms.append(self.current)
            self.current = None
p = Forms()
p.feed(Path(sys.argv[1]).read_text())
matches = [f for f in p.forms if f.get("lead_form_origin") == sys.argv[2]]
assert len(matches) == 1, (sys.argv[2], len(matches))
f = matches[0]
assert f.get("action") == "upsellio_submit_lead"
assert f.get("upsellio_lead_form_nonce")
print(f["upsellio_lead_form_nonce"])
PY
)
    email="$origin@example.invalid"
    result=$(docker exec "$wp" curl -sS --max-time 45 -o /dev/null -w '%{http_code} %{redirect_url}' \
      -H 'Host: fixture.test' -H 'X-Forwarded-Proto: https' \
      --data-urlencode 'action=upsellio_submit_lead' \
      --data-urlencode "upsellio_lead_form_nonce=$nonce" \
      --data-urlencode 'redirect_url=https://fixture.test/kontakt/' \
      --data-urlencode 'lead_name=Fixture synthetic' \
      --data-urlencode "lead_email=$email" \
      --data-urlencode 'lead_phone=000000000' \
      --data-urlencode 'lead_consent=1' \
      --data-urlencode "lead_form_origin=$origin" \
      --data-urlencode "lead_event_id=fixture-$origin" \
      --data-urlencode 'utm_source=fixture-source' \
      --data-urlencode 'utm_medium=fixture-medium' \
      --data-urlencode 'utm_campaign=fixture-campaign' \
      http://127.0.0.1/wp-admin/admin-post.php)
    [[ "$result" == 302*ups_lead_status=success* || "$result" == 303*ups_lead_status=success* ]] || { echo "Lead $origin failed: $result"; return 1; }
    echo "Lead POST $origin: PASS"
  done
  for mode in nonce consent phone honeypot; do
    local token="$nonce" consent=1 phone=000000000 trapfield='' expected=fields
    case "$mode" in
      nonce) token=invalid-fixture-nonce; expected=nonce ;;
      consent) consent=0 ;;
      phone) phone=123 ;;
      honeypot) trapfield=bot; expected=success ;;
    esac
    result=$(docker exec "$wp" curl -sS --max-time 45 -o /dev/null -w '%{http_code} %{redirect_url}' \
      -H 'Host: fixture.test' -H 'X-Forwarded-Proto: https' \
      --data-urlencode 'action=upsellio_submit_lead' \
      --data-urlencode "upsellio_lead_form_nonce=$token" \
      --data-urlencode 'redirect_url=https://fixture.test/kontakt/' \
      --data-urlencode 'lead_name=Fixture rejected' \
      --data-urlencode "lead_email=rejected-$mode@example.invalid" \
      --data-urlencode "lead_phone=$phone" \
      --data-urlencode "lead_consent=$consent" \
      --data-urlencode "lead_website=$trapfield" \
      http://127.0.0.1/wp-admin/admin-post.php)
    if [[ "$expected" == success ]]; then
      [[ "$result" == *ups_lead_status=success* ]]
    else
      [[ "$result" == *ups_lead_status=error* && "$result" == *ups_lead_reason="$expected"* ]]
    fi
    echo "Lead rejection $mode: PASS"
  done
  wpcli eval '
    $origins = ["home-hero-form", "home-contact-form", "contact-page-form", "single-post-contact-form"];
    foreach ($origins as $origin) {
        $ids = get_posts(["post_type"=>"lead", "post_status"=>"any", "numberposts"=>-1, "fields"=>"ids", "meta_key"=>"_upsellio_lead_email", "meta_value"=>$origin."@example.invalid"]);
        if (count($ids) !== 1) { WP_CLI::error("Expected one lead: ".$origin); }
        $id = $ids[0];
        $expected = ["form_origin"=>$origin, "event_id"=>"fixture-".$origin, "utm_source"=>"fixture-source", "utm_medium"=>"fixture-medium", "utm_campaign"=>"fixture-campaign", "consent_form"=>$origin];
        foreach ($expected as $key=>$value) {
            if (get_post_meta($id, "_upsellio_lead_".$key, true) !== $value) { WP_CLI::error("Lead meta mismatch ".$origin." ".$key); }
        }
        if (!get_post_meta($id, "_upsellio_lead_consent_at", true)) { WP_CLI::error("Missing consent timestamp"); }
        $mirror = (int) get_post_meta($id, "_upsellio_synced_crm_lead_id", true);
        if ($mirror <= 0 || get_post_type($mirror) !== "crm_lead") { WP_CLI::error("Missing CRM mirror: ".$origin); }
        WP_CLI::success("CRM stored and mirrored: ".$origin);
    }
    foreach (["nonce", "consent", "phone", "honeypot"] as $mode) {
        $ids = get_posts(["post_type"=>"lead","post_status"=>"any","numberposts"=>-1,"fields"=>"ids","meta_key"=>"_upsellio_lead_email","meta_value"=>"rejected-".$mode."@example.invalid"]);
        if ($ids) { WP_CLI::error("Rejected lead persisted: ".$mode); }
    }
    WP_CLI::success("No rejected leads stored");
  '
}
render() {
  local revision=$1 label=$2
  local dest="$out/$label"
  mkdir -p "$dest"
  docker volume create "$volume" >/dev/null
  docker run -d --name "$db" --network "$network" --network-alias db \
    -e MARIADB_DATABASE=fixture -e MARIADB_USER=fixture \
    -e MARIADB_PASSWORD=fixture-only -e MARIADB_ROOT_PASSWORD=fixture-root-only "$db_image" >/dev/null
  docker run -d --name "$wp" --network "$network" --network-alias fixture-wp -v "$volume:/var/www/html" \
    -e WORDPRESS_DB_HOST=db -e WORDPRESS_DB_NAME=fixture -e WORDPRESS_DB_USER=fixture \
    -e WORDPRESS_DB_PASSWORD=fixture-only \
    -e WORDPRESS_CONFIG_EXTRA='define("DISABLE_WP_CRON", true); if (!defined("WP_DEBUG")) { define("WP_DEBUG", true); } define("WP_DEBUG_DISPLAY", false); define("WP_HTTP_BLOCK_EXTERNAL", true); define("WP_ENVIRONMENT_TYPE", "local");' \
    "$wp_image" >/dev/null
  # Socket ping can succeed during MariaDB's temporary initialization daemon.
  # Require a successful TCP query using the real fixture account from WordPress.
  local ready=0
  for i in $(seq 1 90); do
    if docker exec "$wp" test -f /var/www/html/wp-config.php &&
       docker exec "$wp" php -r 'mysqli_report(MYSQLI_REPORT_OFF); $db = @new mysqli("db","fixture","fixture-only","fixture"); exit($db->connect_errno || !$db->query("SELECT 1") ? 1 : 0);' >/dev/null 2>&1 &&
       docker exec "$wp" curl -s --max-time 2 http://127.0.0.1/ >/dev/null; then
      ready=1
      break
    fi
    sleep 2
  done
  if [[ "$ready" != 1 ]]; then
    echo "Fixture database or HTTP service did not become ready" >&2
    docker logs --tail 30 "$db" >&2
    docker logs --tail 30 "$wp" >&2
    return 1
  fi
  docker exec "$wp" mkdir -p /var/www/html/wp-content/mu-plugins
  # Belt and suspenders: WordPress API and mail disabled, even on the isolated network.
  docker exec -i "$wp" sh -c 'cat > /var/www/html/wp-content/mu-plugins/fixture-isolation.php' <<'PHP'
<?php
add_filter("pre_http_request", static function () { return new WP_Error("fixture_isolated", "External requests disabled in fixture"); }, PHP_INT_MAX);
add_filter("pre_wp_mail", "__return_true", PHP_INT_MAX);
add_action("send_headers", static function () { header("X-Robots-Tag: noindex, nofollow"); });
PHP
  wpcli core install --url=https://fixture.test --title="Upsellio fixture" --admin_user=fixtureadmin \
    --admin_password=fixture-only-not-production --admin_email=fixture@example.invalid --skip-email
  git archive "$revision" "$theme" | docker exec -i "$wp" tar -xf - -C /tmp
  docker exec "$wp" sh -c 'cp -a /tmp/wp-content/themes/upsellio /var/www/html/wp-content/themes/ && chown -R www-data:www-data /var/www/html/wp-content/themes/upsellio'
  wpcli theme activate upsellio
  wpcli rewrite structure '/%postname%/' --hard
  docker exec -i "$wp" sh -c 'cat > /var/www/html/.htaccess' <<'HTACCESS'
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
HTACCESS
  wpcli post create --post_type=post --post_status=publish --post_title='Fixture SEO article' \
    --post_name=fixture-seo-article --post_content='<h2>Fixture section</h2><p>Synthetic content. Ads and marketing.</p><h3>Fixture detail</h3><p>Example only.</p>' --porcelain
  # Trigger the theme runtime bootstrap, then use a static front page and native posts page.
  wpcli eval 'upsellio_runtime_bootstrap_site_structure();'
  local front blog
  front=$(wpcli post create --post_type=page --post_status=publish --post_title='Fixture home' --post_name=fixture-home --porcelain | tail -n1)
  blog=$(wpcli eval 'echo (int) upsellio_get_blog_page_id();' | tail -n1)
  [[ "$front" =~ ^[1-9][0-9]*$ && "$blog" =~ ^[1-9][0-9]*$ ]]
  wpcli option update show_on_front page
  wpcli option update page_on_front "$front"
  wpcli option update page_for_posts "$blog"
  wpcli rewrite flush
  capture home / "$dest"
  capture google /marketing-google-ads/ "$dest"
  capture meta /marketing-meta-ads/ "$dest"
  capture web /tworzenie-stron-internetowych/ "$dest"
  capture contact /kontakt/ "$dest"
  capture blog /blog/ "$dest"
  capture article /fixture-seo-article/ "$dest"
  if [[ "$label" == candidate ]]; then
    test_leads "$dest"
    docker run --rm --network "$network" --ipc=host \
      -v "$browser_dir:/tests:ro" -w /tests "$browser_image" node redesign-browser.mjs
  fi
  # Explicitly exercise the selectable core template rather than only home.php.
  wpcli option update page_for_posts 0
  wpcli post meta update "$blog" _wp_page_template ups-blog-core.php
  capture blog-core /blog/ "$dest"
  capture blog-search '/blog/?blog_search=Ads' "$dest"
  docker rm -f "$wp" "$db" >/dev/null
  docker volume rm "$volume" >/dev/null
}
render "$base_ref" baseline
render "$candidate_ref" candidate
python3 tools/redesign-render-compare.py "$out/baseline" "$out/candidate"
