// Isolated Research UI regression tests: no real WordPress or network.
const assert = require("node:assert/strict");
const fs = require("node:fs");
const vm = require("node:vm");
const source = fs.readFileSync("wp-content/themes/upsellio/inc/crm-app/research-view.php", "utf8");
const script = source.match(/<script>\s*([\s\S]*?)<\/script>/)[1]
  .replace(/<\?php[\s\S]*?\?>/g, (tag) => tag.includes("wp_json_encode") ? '"stub"' : "stub")
  .replace(/\}\)\(\);\s*$/, "globalThis.research = { escapeHtml, numeric, records, post, setLoading, renderKwTable }; })();");
let response;
const tbody = { innerHTML: "" };
const context = {
  document: {getElementById: (id) => id === "kw-tbody" ? tbody : null, querySelectorAll: () => []},
  FormData: class { append() {} },
  fetch: (...args) => response(...args),
};
vm.createContext(context);
vm.runInContext(script, context);
const h = context.research;
(async () => {
  assert.equal(h.escapeHtml('<img src=x onerror="x">&' + "'"), "&lt;img src=x onerror=&quot;x&quot;&gt;&amp;&#39;");
  assert.equal(h.numeric("50"), 50);
  assert.equal(h.numeric("NaN"), 0);
  assert.equal(h.numeric(Infinity), 0);
  assert.equal(h.records([null, "bad", [], {keyword:"ok"}]).length, 1);
  h.renderKwTable([{keyword:"<img src=x>", competition:"<svg onload=x>", opportunity:'" onmouseover="x', cpc_pln:'" onmouseover="x'}]);
  assert.ok(tbody.innerHTML.includes("&lt;img"));
  assert.ok(tbody.innerHTML.includes("&lt;svg"));
  assert.ok(!tbody.innerHTML.includes("<svg"));
  assert.ok(!tbody.innerHTML.includes('onmouseover="x'));
  response = async () => { throw new Error("offline"); };
  assert.equal((await h.post("test", {})).success, false);
  response = async () => ({ok:true, json: async () => {throw new Error("bad JSON");}});
  assert.equal((await h.post("test", {})).success, false);
  response = async () => ({ok:false, json: async () => ({success:true})});
  assert.equal((await h.post("test", {})).success, false);
  response = async () => ({ok:true, json: async () => ({success:true,data:{keywords:[]}})});
  assert.equal((await h.post("test", {})).success, true);
  console.log("Research regression checks passed.");
})().catch((error) => { console.error(error); process.exitCode = 1; });
