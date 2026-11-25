#!/usr/bin/env bash
set -euo pipefail
SCRIPT="$PWD/tools/deploy/apply-release.php"
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
export HOME="$TMP/home"
ROOT="$HOME/domains/upsellio.pl/public_html/wp-content/themes/upsellio"
STORE="$HOME/.upsellio-deploy"
BUNDLE="$STORE/incoming/test"
mkdir -p "$ROOT" "$BUNDLE/files" "$TMP/bin"
export PATH="$TMP/bin:$PATH"
cat > "$TMP/bin/wp" <<'EOF'
#!/usr/bin/env bash
exit 0
EOF
cat > "$TMP/bin/curl" <<'EOF'
#!/usr/bin/env bash
if [[ "$*" == *--version* ]]; then exit 0; fi
printf '%s' "$MOCK_HTTP_STATUS"
EOF
chmod +x "$TMP/bin/wp" "$TMP/bin/curl"
export MOCK_HTTP_STATUS=200
printf '<?php echo "previous";\n' > "$ROOT/front-page.php"
printf '<?php echo "candidate";\n' > "$BUNDLE/files/front-page.php"
export BASE=d8b359ae80d1f8021cbe904e0bf43fa6115ac056
export HEAD=1111111111111111111111111111111111111111
python3 - "$ROOT" "$BUNDLE" <<'PY'
import hashlib,json,os,pathlib,sys
root,bundle=map(pathlib.Path,sys.argv[1:])
path="front-page.php"
sha=lambda p:hashlib.sha256(p.read_bytes()).hexdigest()
(bundle/"manifest.json").write_text(json.dumps({"base":os.environ["BASE"],"head":os.environ["HEAD"],"files":[{"path":path,"before":sha(root/path),"after":sha(bundle/"files"/path)}]}))
PY
cp "$ROOT/front-page.php" "$TMP/previous"
php -n "$SCRIPT" "$BUNDLE" check
cmp "$ROOT/front-page.php" "$TMP/previous"
printf 'unreviewed change' > "$ROOT/front-page.php"
if php -n "$SCRIPT" "$BUNDLE" apply; then echo "FAIL: drift accepted"; exit 1; fi
grep -q 'unreviewed change' "$ROOT/front-page.php"
cp "$TMP/previous" "$ROOT/front-page.php"
printf 'tampered' > "$BUNDLE/files/front-page.php"
if php -n "$SCRIPT" "$BUNDLE" apply; then echo "FAIL: tampered candidate accepted"; exit 1; fi
cmp "$ROOT/front-page.php" "$TMP/previous"
printf '<?php echo "candidate";\n' > "$BUNDLE/files/front-page.php"
export MOCK_HTTP_STATUS=500
if php -n "$SCRIPT" "$BUNDLE" apply; then echo "FAIL: HTTP failure accepted"; exit 1; fi
cmp "$ROOT/front-page.php" "$TMP/previous"
test ! -e "$STORE/pending.json"
test ! -e "$STORE/deployed-sha"
export MOCK_HTTP_STATUS=200
php -n "$SCRIPT" "$BUNDLE" apply
cmp "$ROOT/front-page.php" "$BUNDLE/files/front-page.php"
test "$(cat "$STORE/deployed-sha")" = "$HEAD"
if php -n "$SCRIPT" "$BUNDLE" apply; then echo "FAIL: stale base accepted"; exit 1; fi
echo 'PASS: preflight, drift refusal, payload integrity, HTTP rollback, success and stale release'
