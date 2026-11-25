import { chromium } from "playwright";
import assert from "node:assert/strict";

const origin = "https://fixture.test";
const upstream = "http://fixture-wp";
const pages = ["/", "/marketing-google-ads/", "/marketing-meta-ads/", "/tworzenie-stron-internetowych/", "/kontakt/", "/blog/", "/fixture-seo-article/"];
const browser = await chromium.launch({ headless: true, args: ["--no-sandbox"] });
const issues = [];
try {
  for (const width of [390, 768, 1440]) {
    const context = await browser.newContext({
      viewport: { width, height: 900 }, reducedMotion: "reduce", serviceWorkers: "block"
    });
    // The container is on the internal Docker network; allow only fixture traffic.
    // Preserve the browser's HTTPS origin while proxying HTTP to the local Apache.
    await context.route("**/*", async route => {
      const url = new URL(route.request().url());
      if (url.origin !== origin) return route.abort();
      try {
        const response = await route.fetch({
          url: upstream + url.pathname + url.search,
          headers: { ...route.request().headers(), host: "fixture.test", "x-forwarded-proto": "https" },
          maxRedirects: 0,
          timeout: 15000
        });
        await route.fulfill({ response });
      } catch (error) {
        issues.push("Fixture request failed: " + url.pathname + " " + error.message);
        await route.abort();
      }
    });
    const page = await context.newPage();
    page.on("pageerror", error => issues.push(width + " JS: " + error.message));
    for (const path of pages) {
      try {
        const response = await page.goto(origin + path, { waitUntil: "domcontentloaded", timeout: 30000 });
        assert.equal(response.status(), 200, "HTTP");
        await page.locator("main").first().waitFor({ state: "visible" });
        await page.evaluate(() => document.fonts.ready);
        const overflow = await page.evaluate(() => ({
          actual: document.documentElement.scrollWidth,
          expected: document.documentElement.clientWidth
        }));
        assert.ok(overflow.actual <= overflow.expected + 2, "Horizontal overflow: " + JSON.stringify(overflow));
        const forms = page.locator("form[data-upsellio-server-form='1']");
        for (const form of await forms.all()) {
          for (const name of ["lead_name", "lead_phone", "lead_email", "lead_consent"]) {
            assert.ok(await form.locator('[name="' + name + '"]').isVisible(), "Hidden field: " + name);
          }
        }
        if (path === "/" && width < 981) {
          const toggle = page.locator("#hamburger");
          await toggle.click();
          assert.equal(await toggle.getAttribute("aria-expanded"), "true");
          assert.ok(await page.locator("#mobile-menu").isVisible());
          await page.keyboard.press("Escape");
          assert.equal(await toggle.getAttribute("aria-expanded"), "false");
          await page.waitForFunction(() => document.activeElement?.id === "hamburger");
        }
        if (path === "/" && width >= 981) {
          assert.ok(!(await page.locator("#hamburger").isVisible()), "Desktop hamburger visible");
        }
        console.log("Browser " + width + " " + path + ": PASS");
      } catch (error) {
        issues.push(width + " " + path + ": " + error.message);
      }
    }
    if (width === 390) {
      try {
        await page.goto(origin + "/kontakt/?utm_source=fixture-browser", { waitUntil: "load" });
        const form = page.locator("#contact-page-form");
        await page.waitForFunction(() => document.querySelector("#contact-page-form")?.dataset.upsellioAjaxReady === "1");
        let postedEventId = "";
        let posts = 0;
        page.on("request", request => {
          if (request.method() !== "POST" || !request.url().includes("/wp-admin/admin-post.php")) return;
          posts++;
          const raw = request.postData() || "";
          const match = raw.match(/name="lead_event_id"\r?\n\r?\n([^\r\n]+)/);
          if (match) postedEventId = match[1];
        });
        const before = await page.evaluate(() => (window.dataLayer || []).filter(e => e.event === "generate_lead").length);
        await form.locator('[name="lead_name"]').fill("Fixture browser");
        await form.locator('[name="lead_phone"]').fill("000000000");
        await form.locator('[name="lead_email"]').fill("browser-contact@example.invalid");
        await form.locator('[name="lead_consent"]').check();
        await form.locator('button[type="submit"], input[type="submit"]').first().click();
        await form.locator('[data-form-feedback].is-success').waitFor({ state: "visible", timeout: 15000 });
        const events = await page.evaluate(() => (window.dataLayer || []).filter(e => e.event === "generate_lead"));
        assert.equal(posts, 1, "Exactly one browser POST");
        assert.ok(postedEventId, "Event ID allocated before POST");
        assert.equal(events.length, before + 1, "Exactly one confirmed generate_lead");
        assert.equal(events.at(-1).event_id, postedEventId, "Browser and POST event ID");
        assert.equal(events.at(-1).form_origin, "contact-page-form");
        assert.equal(new URL(page.url()).pathname, "/kontakt/", "AJAX stays on contact page");
        console.log("Browser contact AJAX conversion: PASS");
      } catch (error) { issues.push("Browser contact AJAX: " + error.message); }
    }
    await context.close();
  }
} finally {
  await browser.close();
}
if (issues.length) {
  console.error(issues.join("\n"));
  process.exitCode = 1;
}
