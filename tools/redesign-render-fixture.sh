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
docker network create --internal "$network" >/dev/null
wpcli() {
  docker run --rm --network "$network" --volumes-from "$wp" --user 33:33 \
    -e WORDPRESS_DB_HOST=db -e WORDPRESS_DB_NAME=fixture -e WORDPRESS_DB_USER=fixture \
    -e WORDPRESS_DB_PASSWORD=fixture-only \
    -e WORDPRESS_CONFIG_EXTRA='define("DISABLE_WP_CRON", true); define("WP_DEBUG", true); define("WP_DEBUG_DISPLAY", false); define("WP_HTTP_BLOCK_EXTERNAL", true); define("WP_ENVIRONMENT_TYPE", "local");' \
    --workdir /var/www/html --entrypoint wp "$cli_image" --url=https://fixture.test "$@"
}
capture() {
  local key=$1 url=$2 dest=$3
  docker exec "$wp" curl --silent --show-error --max-time 45 \
    -H 'Host: fixture.test' -H 'X-Forwarded-Proto: https' \
    -o /tmp/fixture-page.html -w '%{http_code}' "http://127.0.0.1$url" > "$dest/$key.status"
  docker cp "$wp:/tmp/fixture-page.html" "$dest/$key.html"
}
render() {
  local revision=$1 label=$2
  local dest="$out/$label"
  mkdir -p "$dest"
  docker volume create "$volume" >/dev/null
  docker run -d --name "$db" --network "$network" --network-alias db \
    -e MARIADB_DATABASE=fixture -e MARIADB_USER=fixture \
    -e MARIADB_PASSWORD=fixture-only -e MARIADB_ROOT_PASSWORD=fixture-root-only "$db_image" >/dev/null
  docker run -d --name "$wp" --network "$network" -v "$volume:/var/www/html" \
    -e WORDPRESS_DB_HOST=db -e WORDPRESS_DB_NAME=fixture -e WORDPRESS_DB_USER=fixture \
    -e WORDPRESS_DB_PASSWORD=fixture-only \
    -e WORDPRESS_CONFIG_EXTRA='define("DISABLE_WP_CRON", true); define("WP_DEBUG", true); define("WP_DEBUG_DISPLAY", false); define("WP_HTTP_BLOCK_EXTERNAL", true); define("WP_ENVIRONMENT_TYPE", "local");' \
    "$wp_image" >/dev/null
  for i in $(seq 1 60); do
    if docker exec "$db" mariadb-admin ping -uroot -pfixture-root-only --silent >/dev/null 2>&1 &&
       docker exec "$wp" test -f /var/www/html/wp-config.php &&
       docker exec "$wp" curl -s --max-time 2 http://127.0.0.1/ >/dev/null; then break; fi
    sleep 2
  done
  docker exec "$db" mariadb-admin ping -uroot -pfixture-root-only --silent >/dev/null
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
