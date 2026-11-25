"""Report comparable lab measurements, not field CWV or Lighthouse scores."""
import json
import statistics
import sys
from collections import defaultdict
from pathlib import Path

FIELDS = ("ttfb", "fcp", "lcp", "cls", "blocking", "domNodes", "localBytes", "localRequests", "blockedRequests")

def load(path):
    groups = defaultdict(list)
    for item in json.loads(Path(path).read_text()):
        groups[(item["width"], item["path"])].append(item)
    return groups

old, new = map(load, sys.argv[1:3])
assert old.keys() == new.keys() and len(new) == 14, "Incomplete measurement matrix"
summary = []
for key in sorted(old):
    assert len(old[key]) == len(new[key]) == 3, key
    row = {"width": key[0], "path": key[1]}
    for field in FIELDS:
        values = [[x[field] for x in group if x[field] is not None] for group in (old[key], new[key])]
        a, b = [statistics.median(v) if len(v) == 3 else None for v in values]
        row[field] = {"baseline": a, "candidate": b, "delta": b - a if a is not None and b is not None else None}
    row["failed_local_requests"] = {"baseline": sorted({e for x in old[key] for e in x["failures"]}), "candidate": sorted({e for x in new[key] for e in x["failures"]})}
    summary.append(row)
    print(json.dumps(row, ensure_ascii=False))
Path(sys.argv[3]).write_text(json.dumps(summary, indent=2, ensure_ascii=False))
# A reproducible synthetic layout jump is an actionable regression even though
# this experiment is not a field CWV assessment. Do not gate noisy latency.
failed = [r for r in summary if r["path"] == "/" and
          (r["cls"]["candidate"] is None or r["cls"]["candidate"] > 0.1)]
print("Lab only: route interception, no external resources, fresh contexts, no device/network throttling. Not real-user CWV.")
if failed:
    print("Homepage synthetic CLS exceeds 0.1:", [(r["width"], r["cls"]["candidate"]) for r in failed], file=sys.stderr)
    sys.exit(1)
print("Homepage laboratory CLS gate passed.")
