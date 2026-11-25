#!/usr/bin/env python3
"""Build an exact, non-destructive release from two Git commits."""
import hashlib
import json
import pathlib
import re
import subprocess
import sys

PREFIX = "wp-content/themes/upsellio/"
ALLOWED = {
    "assets/css/upsellio.css", "assets/js/upsellio-ui.js", "assets/js/upsellio.js",
    "front-page.php", "functions.php", "header.php", "home.php", "single.php",
    "inc/crm.php", "inc/form-component.php", "page-kontakt.php",
    "inc/technical-seo.php", "inc/seo-indexing-fixes.php", "inc/cities-seed.php", "inc/definitions-seed.php",
    "page-marketing-google-ads.php", "page-marketing-meta-ads.php",
    "page-tworzenie-stron-internetowych.php",
}

def git(*args):
    return subprocess.check_output(["git", *args])

def blob(commit, path):
    return git("show", f"{commit}:{PREFIX}{path}")

def digest(data):
    return hashlib.sha256(data).hexdigest()

def build(base, head, destination):
    if not all(re.fullmatch(r"[0-9a-f]{40}", value) for value in (base, head)):
        raise ValueError("Invalid commit identifier")
    subprocess.run(["git", "merge-base", "--is-ancestor", base, head], check=True)
    changed = git("diff", "--name-only", "--no-renames", "-z", base, head,
                  "--", PREFIX).decode().split("\0")
    changed = {path.removeprefix(PREFIX) for path in changed if path}
    if changed - ALLOWED:
        raise ValueError("Unreviewed deployment paths: " + ", ".join(sorted(changed - ALLOWED)))
    destination.mkdir(mode=0o700, parents=True, exist_ok=False)
    records = []
    for path in sorted(changed):
        before, after = blob(base, path), blob(head, path)
        file = destination / "files" / path
        file.parent.mkdir(parents=True, exist_ok=True)
        file.write_bytes(after)
        if path.endswith(".php"):
            subprocess.run(["php", "-n", "-l", str(file)], check=True)
        elif path.endswith(".js"):
            subprocess.run(["node", "--check", str(file)], check=True)
        records.append({"path": path, "before": digest(before), "after": digest(after)})
    (destination / "manifest.json").write_text(json.dumps({
        "base": base, "head": head, "files": records,
    }, indent=2), encoding="utf-8")
    print(f"Prepared {len(records)} explicitly allowed files")

if __name__ == "__main__":
    if len(sys.argv) != 4:
        sys.exit("Usage: build-release.py BASE HEAD OUTPUT")
    build(sys.argv[1], sys.argv[2], pathlib.Path(sys.argv[3]))
