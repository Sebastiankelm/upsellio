"""Compare rendered synthetic WordPress pages, never production content."""
import collections
import json
import os
import re
import sys
from html.parser import HTMLParser
from pathlib import Path


class Page(HTMLParser):
    def __init__(self, html):
        super().__init__(convert_charrefs=True)
        self.metadata = collections.defaultdict(list)
        self.headings = collections.Counter()
        self.schema = []
        self.errors = []
        self.forms = collections.Counter()
        self.capture = None
        self.buffer = []
        self.in_form = False
        self.feed(html)
        self.close()

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == "title" or tag in ("h1", "h2", "h3") or (
            tag == "script" and a.get("type") == "application/ld+json"
        ):
            self.capture = tag
            self.buffer = []
        if tag == "meta":
            key = (a.get("name") or a.get("property") or "").lower()
            if key in ("description", "robots", "og:title", "og:description", "og:url"):
                self.metadata[key].append(a.get("content", ""))
        if tag == "link" and a.get("rel", "").lower() == "canonical":
            self.metadata["canonical"].append(a.get("href", ""))
        if tag == "link" and a.get("hreflang"):
            self.metadata["hreflang"].append((a["hreflang"], a.get("href", "")))
        if tag == "form":
            self.in_form = a.get("data-upsellio-server-form") == "1"
        if tag == "input" and self.in_form and a.get("name") == "lead_form_origin":
            self.forms[a.get("value", "")] += 1

    def handle_data(self, data):
        if self.capture:
            self.buffer.append(data)

    def handle_endtag(self, tag):
        if tag == "form":
            self.in_form = False
        if tag != self.capture:
            return
        value = "".join(self.buffer).strip()
        if tag == "script":
            try:
                self.schema.append(json.loads(value))
            except ValueError:
                self.errors.append("Invalid JSON-LD")
        else:
            value = re.sub(r"\s+", " ", value)
            if tag == "title":
                self.metadata["title"].append(value)
            else:
                self.headings[(tag, value)] += 1
        self.capture = None
        self.buffer = []


def read_page(root, key):
    html = (root / (key + ".html")).read_text(encoding="utf-8", errors="replace")
    status = (root / (key + ".status")).read_text().strip()
    page = Page(html)
    if status != "200":
        page.errors.append("Unexpected HTTP " + status)
    if re.search(r"(?:Fatal error|Parse error|Uncaught (?:Error|Exception)):", html, re.I):
        page.errors.append("PHP fatal error in response")
    return page


def main():
    base, candidate = map(Path, sys.argv[1:3])
    failures = []
    for name in ("home", "google", "meta", "web", "contact", "blog", "article", "blog-core"):
        old, new = read_page(base, name), read_page(candidate, name)
        issues = old.errors + new.errors
        for key in ("title", "description", "robots", "hreflang"):
            if sorted(old.metadata[key]) != sorted(new.metadata[key]):
                issues.append("Changed " + key)
        expected_paths = {
            "home": "/", "google": "/marketing-google-ads/",
            "meta": "/marketing-meta-ads/", "web": "/tworzenie-stron-internetowych/",
            "contact": "/kontakt/", "blog": "/blog/", "blog-core": "/blog/",
            "article": "/fixture-seo-article/",
        }
        expected = "https://fixture.test" + expected_paths[name]
        if new.metadata["canonical"] != [expected]:
            issues.append("Expected exactly one canonical " + expected + ", got " + repr(new.metadata["canonical"]))
        elif expected not in old.metadata["canonical"]:
            # The old homepage routing flags classify / as the posts page.
            # Permit only the observed baseline defect; candidate must still
            # emit exactly one canonical pointing at the actual homepage.
            if name == "home" and old.metadata["canonical"] == ["https://fixture.test/blog/"]:
                print("home: corrected baseline canonical /blog/ -> /")
            elif (name == "home" and os.environ.get("FIXTURE_RANK_MATH_VERSION")
                  and old.metadata["canonical"] == []):
                print("home: restored missing baseline canonical in Rank Math fixture")
            else:
                issues.append("Canonical target absent from baseline: " + repr(old.metadata["canonical"]))
        else:
            print(f"{name}: canonical {len(old.metadata['canonical'])} -> 1")
        if not new.metadata["title"]:
            issues.append("Missing title")
        # Keep the original content headings, not the headings of the shared footer.
        # Footer headings are newly semantic and may be added.
        lost = old.headings - new.headings
        if lost:
            issues.append("Lost headings: " + repr(list(lost.elements())[:8]))
        if old.forms != new.forms:
            issues.append("Changed CRM form origins")
        if len(old.schema) > len(new.schema):
            issues.append("Lost JSON-LD blocks")
        for key in ("title", "description", "canonical"):
            if len(new.metadata[key]) > 1:
                print(f"WARNING {name}: {len(new.metadata[key])} {key} tags (check existing SEO configuration)")
        print(f"{name}: {'; '.join(issues) if issues else 'PASS'}")
        failures.extend(f"{name}: {issue}" for issue in issues)
    search = read_page(candidate, "blog-search")
    if not any("noindex" in v.lower() for v in search.metadata["robots"]):
        search.errors.append("Filtered blog missing noindex")
    failures.extend(f"blog-search: {issue}" for issue in search.errors)
    if failures:
        print("\n".join(failures), file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main())
