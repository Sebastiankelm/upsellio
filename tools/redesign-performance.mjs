import { chromium } from "playwright";
import { writeFileSync } from "node:fs";
import assert from "node:assert/strict";

const origin = "https://fixture.test";
const upstream = "http://fixture-wp";
const paths = ["/", "/marketing-google-ads/", "/marketing-meta-ads/", "/tworzenie-stron-internetowych/", "/kontakt/", "/blog/", "/fixture-seo-article/"];
const output = process.argv[2];
assert.ok(output, "Output JSON required");
const browser = await chromium.launch({ headless: true, args: ["--no-sandbox"] });
const samples = [];
try {
  for (const width of [390, 1440]) {
    for (const path of paths) {
      for (let attempt = 1; attempt <= 3; attempt++) {
        const context = await browser.newContext({
          viewport: { width, height: 900 }, reducedMotion: "no-preference", serviceWorkers: "block"
        });
        try {
          let localBytes = 0, localRequests = 0, blockedRequests = 0;
          const failures = [];
          await context.addInitScript(() => {
            window.fixturePerf = { lcp: null, shifts: [], longTasks: [] };
            const observe = (type, callback) => {
              if (!PerformanceObserver.supportedEntryTypes.includes(type)) return;
              new PerformanceObserver(list => callback(list.getEntries())).observe({ type, buffered: true });
            };
            observe("largest-contentful-paint", entries => {
              for (const e of entries) window.fixturePerf.lcp = e.startTime;
            });
            observe("layout-shift", entries => {
              for (const e of entries) if (!e.hadRecentInput) window.fixturePerf.shifts.push({ t: e.startTime, value: e.value });
            });
            observe("longtask", entries => {
              for (const e of entries) window.fixturePerf.longTasks.push({ t: e.startTime, duration: e.duration });
            });
          });
          await context.route("**/*", async route => {
            const url = new URL(route.request().url());
            if (url.origin !== origin) {
              blockedRequests++;
              return route.abort();
            }
            try {
              const response = await route.fetch({
                url: upstream + url.pathname + url.search,
                headers: { ...route.request().headers(), host: "fixture.test", "x-forwarded-proto": "https" },
                maxRedirects: 0, timeout: 15000
              });
              const body = await response.body();
              localBytes += body.length;
              localRequests++;
              if (response.status() >= 400) failures.push(url.pathname + ": " + response.status());
              await route.fulfill({ response, body });
            } catch (error) {
              failures.push(url.pathname + ": " + error.message);
              await route.abort();
            }
          });
          const page = await context.newPage();
          const response = await page.goto(origin + path, { waitUntil: "load", timeout: 30000 });
          assert.equal(response.status(), 200);
          await page.locator("main").first().waitFor({ state: "visible" });
          await page.evaluate(() => document.fonts.ready);
          await page.waitForTimeout(2000);
          const metrics = await page.evaluate(() => {
            const nav = performance.getEntriesByType("navigation")[0];
            const fcp = performance.getEntriesByName("first-contentful-paint")[0]?.startTime ?? null;
            const p = window.fixturePerf;
            // Maximum CLS session window: <=1 s between shifts, <=5 s total.
            let cls = 0, sum = 0, first = 0, previous = -Infinity;
            for (const e of p.shifts) {
              if (e.t - previous > 1000 || e.t - first > 5000) { first = e.t; sum = 0; }
              sum += e.value; previous = e.t; cls = Math.max(cls, sum);
            }
            // Fixed-window long-task blocking after FCP, not Lighthouse TBT or INP.
            const blocking = fcp === null ? null : p.longTasks.reduce((total, e) =>
              total + Math.max(0, e.t + e.duration - Math.max(e.t, fcp) - 50), 0);
            return {
              ttfb: nav.responseStart - nav.startTime, fcp, lcp: p.lcp, cls,
              blocking, domNodes: document.querySelectorAll("*").length
            };
          });
          samples.push({ width, path, attempt, ...metrics, localBytes, localRequests, blockedRequests, failures });
          console.log(JSON.stringify(samples.at(-1)));
        } finally { await context.close(); }
      }
    }
  }
} finally { await browser.close(); }
writeFileSync(output, JSON.stringify(samples, null, 2));
