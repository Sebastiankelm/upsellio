(function () {
  "use strict";

  window.UPS_AUDIT_VIEW_CONFIG = window.UPS_AUDIT_VIEW_CONFIG || {
    "ca-clients": {
      title: "Profile klientów",
      qa: [
        { l: "Sync wszystkie zasoby", i: "ti-refresh", f: function () { window.upsAuditSyncAllResources(); } },
      ],
    },
    "ca-dashboard": {
      title: "Dashboard klienta",
      qa: [
        { l: "Raport miesięczny AI", i: "ti-sparkles", f: function () { window.upsAuditGenReport(window.UPS_AUDIT_CLIENT_ID || 0, "monthly"); } },
        { l: "Plan działań AI", i: "ti-target-arrow", f: function () { window.upsAuditGenReport(window.UPS_AUDIT_CLIENT_ID || 0, "plan"); } },
        { l: "Sync danych", i: "ti-refresh", f: function () { window.upsAuditSyncClient(window.UPS_AUDIT_CLIENT_ID || 0); } },
      ],
    },
    "ca-accounts": {
      title: "Konta Google",
      qa: [
        { l: "Połącz nowe konto", i: "ti-plus", f: function () { window.upsAuditOpenConnectModal(); } },
        { l: "Odśwież wszystkie konta", i: "ti-refresh", f: function () { window.upsAuditRefreshAllAccounts(); } },
      ],
    },
    "ca-meta-accounts": {
      title: "Konta Meta Ads",
      qa: [
        { l: "Odśwież wszystkie konta", i: "ti-refresh", f: function () { window.upsAuditRefreshAllMetaAccounts(); } },
      ],
    },
  };

  window.UPS_AUDIT_MAP_ACCOUNT_ID = 0;
  window.UPS_AUDIT_MAP_CLIENT_ID = 0;

  function upsAuditSyncMapCheckboxes(clientId) {
    document.querySelectorAll(".ups-audit-map-cb").forEach(function (cb) {
      var tile = cb.closest(".ups-audit-map-tile");
      var mapped = tile ? Number(tile.getAttribute("data-mapped-client-id") || 0) : 0;
      cb.checked = clientId > 0 && mapped === clientId;
    });
  }

  function upsAuditMountMapModal() {
    var modal = document.getElementById("ups-audit-map-modal");
    if (modal && modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    return modal;
  }

  function upsAuditShowMapModal() {
    var modal = upsAuditMountMapModal();
    if (modal) {
      modal.style.display = "flex";
      modal.classList.add("is-open");
      modal.setAttribute("aria-hidden", "false");
    }
  }

  window.upsAuditOpenMapModal = function (clientId, clientName) {
    window.UPS_AUDIT_MAP_CLIENT_ID = Number(clientId || 0);
    window.UPS_AUDIT_MAP_ACCOUNT_ID = 0;
    var subtitle = document.getElementById("ups-audit-map-modal-subtitle");
    var sel = document.getElementById("ups-audit-map-client-select");
    var inlineCreate = document.getElementById("ups-audit-map-create-inline");
    if (subtitle) {
      subtitle.textContent = "Profil: " + String(clientName || "");
    }
    if (sel && clientId) {
      sel.value = String(clientId);
    }
    if (inlineCreate) {
      inlineCreate.style.display = "none";
    }
    document.querySelectorAll(".ups-audit-map-tile").forEach(function (tile) {
      tile.style.display = "";
    });
    upsAuditSyncMapCheckboxes(window.UPS_AUDIT_MAP_CLIENT_ID);
    upsAuditShowMapModal();
  };

  window.upsAuditOpenMapModalForAccount = function (accountId, accountName) {
    window.UPS_AUDIT_MAP_ACCOUNT_ID = Number(accountId || 0);
    window.UPS_AUDIT_MAP_CLIENT_ID = 0;
    var subtitle = document.getElementById("ups-audit-map-modal-subtitle");
    var sel = document.getElementById("ups-audit-map-client-select");
    var inlineCreate = document.getElementById("ups-audit-map-create-inline");
    if (subtitle) {
      subtitle.textContent = "Konto Google: " + String(accountName || "");
    }
    document.querySelectorAll(".ups-audit-map-tile").forEach(function (tile) {
      var tid = Number(tile.getAttribute("data-google-account-id") || 0);
      tile.style.display =
        !window.UPS_AUDIT_MAP_ACCOUNT_ID || tid === window.UPS_AUDIT_MAP_ACCOUNT_ID ? "" : "none";
    });
    if (sel) {
      sel.value = "";
    }
    if (inlineCreate) {
      inlineCreate.style.display = "";
    }
    document.querySelectorAll(".ups-audit-map-cb").forEach(function (cb) {
      cb.checked = false;
    });
    upsAuditShowMapModal();
  };

  window.upsAuditCloseMapModal = function () {
    var modal = document.getElementById("ups-audit-map-modal");
    if (modal) {
      modal.style.display = "none";
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
    }
    window.UPS_AUDIT_MAP_CLIENT_ID = 0;
    window.UPS_AUDIT_MAP_ACCOUNT_ID = 0;
  };

  window.upsAuditSaveMapping = async function () {
    var clientId = Number(window.UPS_AUDIT_MAP_CLIENT_ID || 0);
    var sel = document.getElementById("ups-audit-map-client-select");
    if (!clientId && sel) {
      clientId = Number(sel.value || 0);
    }
    if (!clientId) {
      alert("Wybierz profil klienta do mapowania.");
      return;
    }
    var selected = Array.prototype.slice
      .call(document.querySelectorAll(".ups-audit-map-tile"))
      .filter(function (tile) {
        return tile.style.display !== "none";
      })
      .map(function (tile) {
        var cb = tile.querySelector(".ups-audit-map-cb");
        return cb && cb.checked ? { resource_id: Number(cb.value || 0) } : null;
      })
      .filter(function (r) {
        return r && r.resource_id > 0;
      });
    if (!selected.length && !window.UPS_AUDIT_MAP_ACCOUNT_ID) {
      alert("Wybierz minimum jeden zasób.");
      return;
    }
    var params = {
      client_id: String(clientId),
      resources: JSON.stringify(selected),
    };
    if (window.UPS_AUDIT_MAP_ACCOUNT_ID) {
      params.google_account_id = String(window.UPS_AUDIT_MAP_ACCOUNT_ID);
    }
    var json = await auditAjax("ups_audit_map_to_client", params);
    if (json && json.success) {
      upsAuditToast("Mapowanie zapisane.", "ok");
      window.upsAuditCloseMapModal();
      window.setTimeout(function () {
        window.location.reload();
      }, 500);
    } else {
      var errMsg = (json && json.data && json.data.msg) || "Nie udało się zapisać mapowania.";
      upsAuditToast(errMsg, "err");
      alert(errMsg);
    }
  };

  document.addEventListener("click", function (ev) {
    var mapBtn = ev.target.closest("[data-ups-audit-open-map]");
    if (mapBtn) {
      ev.preventDefault();
      window.upsAuditOpenMapModal(
        Number(mapBtn.getAttribute("data-client-id") || 0),
        mapBtn.getAttribute("data-client-name") || ""
      );
      return;
    }
    var mapAccBtn = ev.target.closest("[data-ups-audit-open-map-account]");
    if (mapAccBtn) {
      ev.preventDefault();
      window.upsAuditOpenMapModalForAccount(
        Number(mapAccBtn.getAttribute("data-account-id") || 0),
        mapAccBtn.getAttribute("data-account-name") || ""
      );
      return;
    }
    var modal = document.getElementById("ups-audit-map-modal");
    if (modal && modal.classList.contains("is-open") && ev.target === modal) {
      window.upsAuditCloseMapModal();
    }
  });

  function auditAjax(action, params) {
    if (!window.CRM_AJAX_URL) {
      return Promise.resolve({ success: false, data: { msg: "Brak CRM_AJAX_URL" } });
    }
    var body = new URLSearchParams(Object.assign({ action: action, nonce: window.UPS_CRM_NONCE || "" }, params || {}));
    return fetch(window.CRM_AJAX_URL, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body.toString(),
    }).then(function (r) {
      return r.text().then(function (text) {
        if (!text || text.trim() === "") {
          return { success: false, data: { msg: "Pusta odpowiedź serwera (HTTP " + r.status + ")" } };
        }
        try {
          return JSON.parse(text);
        } catch (parseErr) {
          var snippet = text.replace(/\s+/g, " ").trim().slice(0, 200);
          return {
            success: false,
            data: {
              msg:
                "Nieprawidłowa odpowiedź serwera (HTTP " +
                r.status +
                "). " +
                (snippet.indexOf("Fatal error") !== -1 || snippet.indexOf("Parse error") !== -1
                  ? "Błąd PHP na serwerze — sprawdź logi lub Dompdf (composer install)."
                  : snippet),
            },
          };
        }
      });
    });
  }

  function upsAuditToast(message, kind) {
    var el = document.getElementById("ups-audit-toast");
    if (!el) {
      el = document.createElement("div");
      el.id = "ups-audit-toast";
      el.setAttribute("role", "status");
      el.style.cssText =
        "position:fixed;bottom:22px;right:22px;z-index:10050;padding:12px 18px;border-radius:12px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 12px 32px rgba(0,0,0,.18);max-width:min(360px,92vw);display:none;";
      document.body.appendChild(el);
    }
    el.textContent = String(message || "");
    el.style.background =
      kind === "ok" ? "#16a34a" : kind === "err" ? "#dc2626" : "#0f172a";
    el.style.display = "block";
    clearTimeout(upsAuditToast._t);
    upsAuditToast._t = setTimeout(function () {
      el.style.display = "none";
    }, kind === "info" ? 12000 : 4500);
  }

  window.upsAuditImportResource = async function (googleAccountId, type, externalId, displayName, parentAccountId, triggerBtn) {
    if (!window.UPS_CRM_NONCE) {
      alert("Sesja CRM wygasła — odśwież stronę (F5).");
      return;
    }
    var btn = triggerBtn || null;
    var prevLabel = btn ? btn.textContent : "";
    if (btn) {
      btn.disabled = true;
      btn.classList.add("is-loading");
      btn.textContent = "Importuję…";
    }
    upsAuditToast("Importuję: " + String(displayName || externalId || type).slice(0, 48) + "…", "info");

    try {
      var json = await auditAjax("ups_audit_resource_import", {
        google_account_id: String(googleAccountId || 0),
        type: String(type || ""),
        external_id: String(externalId || ""),
        display_name: String(displayName || ""),
        parent_account_id: String(parentAccountId || ""),
      });
      if (json && json.success) {
        var already = json.data && json.data.already_exists;
        upsAuditToast(already ? "Zasób był już w bazie — odświeżam listę." : "Zaimportowano — odświeżam listę.", "ok");
        window.setTimeout(function () {
          window.location.reload();
        }, already ? 400 : 700);
        return;
      }
      var errMsg =
        (json && json.data && (json.data.msg || json.data.message)) ||
        (json && json.message) ||
        "Import nie powiódł się.";
      upsAuditToast(errMsg, "err");
      if (btn) {
        btn.disabled = false;
        btn.classList.remove("is-loading");
        btn.textContent = prevLabel || "Import";
      }
      alert(errMsg);
    } catch (err) {
      upsAuditToast("Błąd sieci podczas importu.", "err");
      if (btn) {
        btn.disabled = false;
        btn.classList.remove("is-loading");
        btn.textContent = prevLabel || "Import";
      }
      alert("Błąd sieci. Sprawdź połączenie i spróbuj ponownie.");
    }
  };

  function parseJsonEl(id) {
    var el = document.getElementById(id);
    if (!el || !el.textContent) return {};
    try {
      return JSON.parse(el.textContent);
    } catch (e) {
      return {};
    }
  }

  function initIntelCharts() {
    var C = window.upsCrmChart;
    if (!C) return;
    var payload = parseJsonEl("ups-audit-intel-charts");
    if (!payload || typeof payload !== "object") return;

    var scores = payload.scores || {};
    if (document.getElementById("ups-intel-gauge-health")) {
      C.scoreGauge("ups-intel-gauge-health", scores.health || 0, 100, scores.health >= 75 ? "#16a34a" : scores.health >= 50 ? "#d97706" : "#dc2626");
    }
    if (document.getElementById("ups-intel-gauge-tracking")) {
      C.scoreGauge("ups-intel-gauge-tracking", scores.tracking || 0, 100, scores.tracking >= 75 ? "#16a34a" : scores.tracking >= 50 ? "#d97706" : "#dc2626");
    }
    if (document.getElementById("ups-intel-gauge-opportunity")) {
      C.scoreGauge("ups-intel-gauge-opportunity", scores.opportunity || 0, 100, scores.opportunity >= 81 ? "#16a34a" : scores.opportunity >= 61 ? "#2563eb" : "#d97706");
    }
    function confidenceGaugeColor(score) {
      var s = Number(score) || 0;
      if (s >= 80) return "#16a34a";
      if (s >= 60) return "#0d9488";
      if (s >= 40) return "#d97706";
      if (s >= 20) return "#ea580c";
      return "#dc2626";
    }
    if (document.getElementById("ups-intel-gauge-revenue-conf")) {
      var revC = scores.revenue_confidence || 0;
      C.scoreGauge("ups-intel-gauge-revenue-conf", revC, 100, confidenceGaugeColor(revC));
    }
    if (document.getElementById("ups-intel-gauge-attribution")) {
      var attr = scores.attribution || 0;
      C.scoreGauge("ups-intel-gauge-attribution", attr, 100, confidenceGaugeColor(attr));
    }
    if (document.getElementById("ups-intel-gauge-crm-quality")) {
      var crmQ = scores.crm_quality || 0;
      C.scoreGauge("ups-intel-gauge-crm-quality", crmQ, 100, crmQ >= 75 ? "#16a34a" : crmQ >= 50 ? "#2563eb" : "#d97706");
    }

    var cpa = payload.ads_cpa || {};
    if (cpa.has_data && document.getElementById("ups-intel-chart-cpa")) {
      C.bar(
        "ups-intel-chart-cpa",
        ["Search CPA", "PMax CPA"],
        [Number(cpa.search_cpa || 0), Number(cpa.pmax_cpa || 0)],
        "CPA (zł)",
        "#0d9488"
      );
    }

    var st = payload.search_terms || {};
    if (st.top_waste && st.top_waste.labels && document.getElementById("ups-intel-chart-st-waste")) {
      C.horizontalBar("ups-intel-chart-st-waste", st.top_waste.labels, st.top_waste.values, "Koszt PLN");
    }
    if (st.actions && document.getElementById("ups-intel-chart-st-actions")) {
      C.doughnut(
        "ups-intel-chart-st-actions",
        ["Skaluj", "Obserwuj", "Wyklucz", "Bez kosztu"],
        [
          Number(st.actions.scale || 0),
          Number(st.actions.watch || 0),
          Number(st.actions.exclude || 0),
          Number(st.actions.other || 0),
        ],
        ["#16a34a", "#2563eb", "#dc2626", "#e2e8f0"]
      );
    }

    var crm = payload.crm_revenue || {};
    if (crm.labels && crm.labels.length && document.getElementById("ups-intel-chart-crm")) {
      C.groupedBar("ups-intel-chart-crm", crm.labels, [
        { label: "Koszt", data: crm.cost || [], color: "#f59e0b99" },
        { label: "Przychód CRM", data: crm.revenue || [], color: "#0d948899" },
      ]);
    }

    var clusters = payload.seo_clusters || {};
    if (clusters.labels && clusters.labels.length && document.getElementById("ups-intel-chart-seo")) {
      C.horizontalBar("ups-intel-chart-seo", clusters.labels, clusters.values, "+Klik. potencjału", clusters.colors);
    }

    var bench = payload.benchmark || {};
    if (bench.labels && bench.labels.length && document.getElementById("ups-intel-chart-bench")) {
      C.groupedBar("ups-intel-chart-bench", bench.labels, [
        { label: "Klient", data: bench.client || [], color: "#0ABFA3" },
        { label: "Średnia", data: bench.avg || [], color: "#94a3b899" },
      ]);
    }

    var attr = payload.attribution || {};
    if (attr.values && attr.values.length && document.getElementById("ups-intel-chart-attribution")) {
      C.doughnut("ups-intel-chart-attribution", attr.labels || [], attr.values || [], attr.colors || []);
    }

    var content = payload.content_potential || {};
    if (content.labels && content.labels.length && document.getElementById("ups-intel-chart-content")) {
      C.horizontalBar("ups-intel-chart-content", content.labels, content.values, "+Klik. potencjału");
    }

    var hh = payload.health_history || {};
    if (hh.labels && hh.labels.length && document.getElementById("ups-intel-chart-health-history")) {
      C.lineSeriesFromPairs("ups-intel-chart-health-history", hh.labels, hh.values, "Health /100", "#6366f1");
    }
  }

  function initDashboardCharts() {
    var C = window.upsCrmChart;
    if (!C) return;

    var mode = parseJsonEl("ups-audit-dash-chart-mode");
    var gsc = parseJsonEl("ups-audit-dash-ts-gsc");
    var ga4 = parseJsonEl("ups-audit-dash-ts-ga4");
    var adsClicksTs = parseJsonEl("ups-audit-dash-ts-ads-clicks");
    C.lineSeries("ups-audit-chart-gsc", gsc, "Kliknięcia GSC", "#0ABFA3");
    if (mode && mode.has_ga4) {
      C.lineSeries("ups-audit-chart-ga4", ga4, "Sesje GA4", "#3b82f6");
    } else if (mode && mode.has_ads && adsClicksTs && Object.keys(adsClicksTs).length) {
      var clicksFlat = {};
      Object.keys(adsClicksTs).forEach(function (k) {
        var v = adsClicksTs[k];
        clicksFlat[k] = v && typeof v === "object" ? Number(v.clicks || 0) : Number(v || 0);
      });
      C.lineSeries("ups-audit-chart-ga4", clicksFlat, "Kliknięcia Ads", "#3b82f6");
    } else {
      C.lineSeries("ups-audit-chart-ga4", ga4, "Sesje GA4", "#3b82f6");
    }

    var funnel = parseJsonEl("ups-audit-dash-funnel");
    if (funnel && typeof funnel === "object") {
      if (funnel.gsc_clicks !== undefined || funnel.ga4_sessions !== undefined) {
        C.bar(
          "ups-audit-chart-funnel",
          ["GSC klik.", "GA4 sesje", "Konwersje"],
          [
            Number(funnel.gsc_clicks || 0),
            Number(funnel.ga4_sessions || 0),
            Number(funnel.ga4_conversions || 0),
          ],
          "Lejek",
          "#8b5cf6"
        );
      } else {
        C.bar(
          "ups-audit-chart-funnel",
          ["Klik. Ads", "Konw. Ads", "Sesje Clarity"],
          [
            Number(funnel.ads_clicks || 0),
            Number(funnel.ads_conversions || 0),
            Number(funnel.clarity_sessions || 0),
          ],
          "Lejek paid",
          "#8b5cf6"
        );
      }
    }

    var adsTs = parseJsonEl("ups-audit-dash-ts-ads");
    if (adsTs && typeof adsTs === "object" && Object.keys(adsTs).length) {
      var adsFlat = {};
      Object.keys(adsTs).forEach(function (k) {
        var v = adsTs[k];
        adsFlat[k] = v && typeof v === "object" ? Number(v.cost || 0) : Number(v || 0);
      });
      C.lineSeries("ups-audit-chart-ads", adsFlat, "Koszt Google Ads (PLN)", "#f59e0b");
    } else {
      var camps = parseJsonEl("ups-audit-dash-campaigns");
      if (Array.isArray(camps) && camps.length) {
        var cLabels = [];
        var cCosts = [];
        camps.forEach(function (c) {
          cLabels.push(String((c && c.name) || "—").slice(0, 28));
          cCosts.push(Number((c && c.cost) || 0));
        });
        C.bar("ups-audit-chart-ads", cLabels, cCosts, "Koszt PLN", "#f59e0b");
      }
    }

    var metaTs = parseJsonEl("ups-audit-dash-ts-meta");
    if (metaTs && typeof metaTs === "object" && Object.keys(metaTs).length) {
      var metaFlat = {};
      Object.keys(metaTs).forEach(function (k) {
        var v = metaTs[k];
        metaFlat[k] = v && typeof v === "object" ? Number(v.cost || 0) : Number(v || 0);
      });
      C.lineSeries("ups-audit-chart-meta", metaFlat, "Koszt Meta Ads (PLN)", "#3b82f6");
    }

    var channels = parseJsonEl("ups-audit-dash-channels");
    if (channels.labels && channels.labels.length && document.getElementById("ups-audit-chart-channels")) {
      C.doughnut("ups-audit-chart-channels", channels.labels, channels.sessions, channels.colors);
    }

    var keywords = parseJsonEl("ups-audit-dash-keywords");
    if (keywords.labels && keywords.labels.length && document.getElementById("ups-audit-chart-keywords")) {
      C.horizontalBar("ups-audit-chart-keywords", keywords.labels, keywords.clicks, "Kliknięcia GSC");
    }

    var campsCpa = parseJsonEl("ups-audit-dash-campaigns-cpa");
    if (campsCpa.labels && campsCpa.labels.length && document.getElementById("ups-audit-chart-campaigns")) {
      C.bar("ups-audit-chart-campaigns", campsCpa.labels, campsCpa.cost, "Koszt PLN", "#f59e0b");
    }

    var ltv = parseJsonEl("ups-audit-dash-ltv");
    if (ltv.labels && ltv.labels.length && document.getElementById("ups-audit-chart-ltv")) {
      C.horizontalBar("ups-audit-chart-ltv", ltv.labels, ltv.ltv, "LTV zł/sesja");
    }

    initIntelCharts();
  }

  window.upsAuditSyncAllResources = async function (clientId, googleAccountId) {
    var msg = "Synchronizować wszystkie zasoby z połączonych kont Google? Może potrwać kilka minut.";
    if (googleAccountId) {
      msg = "Synchronizować zasoby tego konta Google?";
    } else if (clientId) {
      msg = "Synchronizować zasoby tego klienta CRM?";
    }
    if (!window.confirm(msg)) {
      return;
    }
    var params = { days: String(document.getElementById("ups-audit-window-select")?.value || "") };
    if (googleAccountId) {
      params.google_account_id = String(googleAccountId);
    } else if (clientId) {
      params.client_id = String(clientId);
    }
    var json = await auditAjax("ups_audit_sync_all", params);
    if (json && json.success) {
      var d = json.data || {};
      alert("Sync zakończony: OK " + (d.ok || 0) + ", błędy " + (d.fail || 0));
      window.location.reload();
    } else {
      alert("Sync nie powiódł się.");
    }
  };

  window.upsAuditSyncClient = function (clientId) {
    return window.upsAuditSyncAllResources(clientId || window.UPS_AUDIT_CLIENT_ID || 0);
  };

  window.upsAuditRefreshAllAccounts = async function () {
    if (!window.confirm("Odświeżyć listy zasobów dla wszystkich kont Google?")) return;
    var json = await auditAjax("ups_audit_refresh_all_accounts", {});
    if (json && json.success) {
      alert("Odświeżono kont: " + ((json.data && json.data.refreshed) || 0));
      window.location.reload();
    } else {
      alert("Nie udało się odświeżyć kont.");
    }
  };

  window.upsAuditMetaImportResource = async function (metaAccountId, externalId, displayName, triggerBtn) {
    if (!window.UPS_CRM_NONCE) {
      alert("Sesja CRM wygasła — odśwież stronę (F5).");
      return;
    }
    var btn = triggerBtn || null;
    var prevLabel = btn ? btn.textContent : "";
    if (btn) {
      btn.disabled = true;
      btn.classList.add("is-loading");
      btn.textContent = "Importuję…";
    }
    upsAuditToast("Import Meta: " + String(displayName || externalId || "").slice(0, 48) + "…", "info");
    try {
      var json = await auditAjax("ups_audit_meta_resource_import", {
        meta_account_id: String(metaAccountId || 0),
        external_id: String(externalId || ""),
        display_name: String(displayName || ""),
      });
      if (json && json.success) {
        upsAuditToast(json.data && json.data.already_exists ? "Zasób Meta już w bazie." : "Zaimportowano konto Meta.", "ok");
        window.setTimeout(function () { window.location.reload(); }, 600);
        return;
      }
      var errMsg = (json && json.data && (json.data.msg || json.data.message)) || "Import Meta nie powiódł się.";
      upsAuditToast(errMsg, "err");
      if (btn) {
        btn.disabled = false;
        btn.classList.remove("is-loading");
        btn.textContent = prevLabel || "Import";
      }
    } catch (err) {
      upsAuditToast("Błąd sieci podczas importu Meta.", "err");
      if (btn) {
        btn.disabled = false;
        btn.classList.remove("is-loading");
        btn.textContent = prevLabel || "Import";
      }
    }
  };

  window.upsAuditRefreshMetaAccountResources = async function (accountId) {
    if (!accountId) return;
    var json = await auditAjax("ups_audit_meta_account_refresh_resources", { account_id: String(accountId) });
    if (json && json.success) {
      window.location.reload();
    } else {
      alert("Nie udało się odświeżyć kont Meta.");
    }
  };

  window.upsAuditDisconnectMetaAccount = async function (accountId) {
    if (!accountId || !window.confirm("Odłączyć konto Meta i usunąć zaimportowane zasoby?")) return;
    var json = await auditAjax("ups_audit_meta_account_disconnect", { account_id: String(accountId) });
    if (json && json.success) {
      window.location.reload();
    } else {
      alert("Nie udało się odłączyć konta Meta.");
    }
  };

  window.upsAuditRefreshAllMetaAccounts = async function () {
    if (!window.confirm("Odświeżyć listy kont reklamowych dla wszystkich połączeń Meta?")) return;
    var json = await auditAjax("ups_audit_refresh_all_meta_accounts", {});
    if (json && json.success) {
      alert("Odświeżono kont Meta: " + ((json.data && json.data.refreshed) || 0));
      window.location.reload();
    } else {
      alert("Nie udało się odświeżyć kont Meta.");
    }
  };

  window.upsAuditBulkGenerateReports = function () {
    alert("Masowe raporty AI — uruchom z poziomu każdego klienta (Dashboard → Raport AI).");
  };

  window.upsAuditResourceSync = async function (resourceId) {
    if (!resourceId) return;
    var json = await auditAjax("ups_audit_resource_sync", { resource_id: String(resourceId) });
    if (json && json.success) {
      window.location.reload();
    } else {
      alert("Sync zasobu nie powiódł się.");
    }
  };

  function upsAuditMountConnectModal() {
    var modal = document.getElementById("ups-audit-connect-modal");
    if (modal && modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    return modal;
  }

  function upsAuditCloseConnectModal() {
    var modal = document.getElementById("ups-audit-connect-modal");
    if (modal) {
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
    }
  }

  window.upsAuditOpenConnectModal = function (label) {
    var modal = upsAuditMountConnectModal();
    var labelIn = document.getElementById("ups-audit-connect-label");
    if (label && labelIn) {
      labelIn.value = String(label);
    }
    if (modal) {
      modal.classList.add("is-open");
      modal.setAttribute("aria-hidden", "false");
      if (labelIn) {
        labelIn.focus();
      }
      return;
    }
    window.upsAuditConnectAccountSubmit(label || "", true);
  };

  window.upsAuditConnectAccount = function (label) {
    window.upsAuditOpenConnectModal(label);
  };

  window.upsAuditConnectAccountSubmit = async function (label, includeAds) {
    var body = new URLSearchParams({
      action: "ups_audit_oauth_start",
      nonce: window.UPS_CRM_NONCE || "",
      label: String(label || ""),
      include_ads: includeAds ? "1" : "0",
    }).toString();
    var res = await fetch(window.CRM_AJAX_URL, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body,
    });
    var json = null;
    try {
      json = await res.json();
    } catch (e) {
      json = null;
    }
    if (json && json.success && json.data && json.data.redirect_url) {
      window.location.href = json.data.redirect_url;
      return;
    }
    var errMsg =
      (json && json.data && (json.data.msg || json.data.message)) ||
      (res.ok ? "Brak adresu przekierowania OAuth." : "Błąd serwera (" + res.status + ").");
    alert("Nie udało się rozpocząć logowania Google: " + errMsg);
  };

  function upsAuditBindConnectUi() {
    upsAuditMountConnectModal();
    var openBtn = document.getElementById("ups-audit-connect-open");
    var modal = document.getElementById("ups-audit-connect-modal");
    var cancelBtn = document.getElementById("ups-audit-connect-cancel");
    var goBtn = document.getElementById("ups-audit-connect-go");

    if (openBtn && !openBtn.dataset.upsAuditBound) {
      openBtn.dataset.upsAuditBound = "1";
      openBtn.addEventListener("click", function (ev) {
        ev.preventDefault();
        window.upsAuditOpenConnectModal();
      });
    }
    if (cancelBtn && modal && !cancelBtn.dataset.upsAuditBound) {
      cancelBtn.dataset.upsAuditBound = "1";
      cancelBtn.addEventListener("click", function () {
        upsAuditCloseConnectModal();
      });
    }
    if (goBtn && !goBtn.dataset.upsAuditBound) {
      goBtn.dataset.upsAuditBound = "1";
      goBtn.addEventListener("click", function () {
        var labelIn = document.getElementById("ups-audit-connect-label");
        var adsCb = document.getElementById("ups-audit-connect-ads");
        window.upsAuditConnectAccountSubmit(
          labelIn ? labelIn.value : "",
          adsCb ? adsCb.checked : true
        );
      });
    }
    if (modal && !modal.dataset.upsAuditBound) {
      modal.dataset.upsAuditBound = "1";
      modal.addEventListener("click", function (ev) {
        if (ev.target === modal) {
          upsAuditCloseConnectModal();
        }
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    upsAuditBindConnectUi();
    var copyBtn = document.getElementById("ups-audit-copy-redirect");
    document.addEventListener("click", function (ev) {
      var importBtn = ev.target.closest(".ups-audit-import-btn");
      if (importBtn && !importBtn.disabled) {
        ev.preventDefault();
        window.upsAuditImportResource(
          Number(importBtn.getAttribute("data-account-id") || 0),
          importBtn.getAttribute("data-type") || "",
          importBtn.getAttribute("data-external-id") || "",
          importBtn.getAttribute("data-display-name") || "",
          importBtn.getAttribute("data-parent-account-id") || "",
          importBtn
        );
        return;
      }
      var metaImportBtn = ev.target.closest(".ups-audit-meta-import-btn");
      if (metaImportBtn && !metaImportBtn.disabled) {
        ev.preventDefault();
        window.upsAuditMetaImportResource(
          Number(metaImportBtn.getAttribute("data-meta-account-id") || 0),
          metaImportBtn.getAttribute("data-external-id") || "",
          metaImportBtn.getAttribute("data-display-name") || "",
          metaImportBtn
        );
      }
    });

    if (copyBtn) {
      copyBtn.addEventListener("click", function () {
        var code = document.getElementById("ups-audit-redirect-uri");
        var text = code && code.textContent ? code.textContent.trim() : "";
        if (text && navigator.clipboard) {
          navigator.clipboard.writeText(text).then(function () {
            copyBtn.textContent = "Skopiowano";
            setTimeout(function () { copyBtn.textContent = "Kopiuj URI"; }, 2000);
          });
        }
      });
    }

    if (typeof window.upsCrmScheduleChartInit === "function") {
      window.upsCrmScheduleChartInit(initDashboardCharts);
    } else if (window.upsCrmChart) {
      window.upsCrmChart.whenReady(initDashboardCharts);
    } else {
      initDashboardCharts();
    }

    var syncAllBtn = document.getElementById("ups-audit-sync-all-btn");
    if (syncAllBtn) {
      syncAllBtn.addEventListener("click", function () {
        window.upsAuditSyncAllResources(0);
      });
    }

    var syncClientBtn = document.getElementById("ups-audit-sync-client-btn");
    if (syncClientBtn) {
      syncClientBtn.addEventListener("click", function () {
        var root = document.getElementById("ups-audit-dash-root");
        var cid = root ? Number(root.getAttribute("data-client-id") || 0) : 0;
        window.upsAuditSyncClient(cid);
      });
    }

    var refreshCwvBtn = document.getElementById("ups-audit-refresh-cwv-btn");
    if (refreshCwvBtn) {
      refreshCwvBtn.addEventListener("click", async function () {
        var cid = Number(refreshCwvBtn.getAttribute("data-client-id") || window.UPS_AUDIT_CLIENT_ID || 0);
        if (!cid) {
          return;
        }
        refreshCwvBtn.disabled = true;
        upsAuditToast("PageSpeed — pobieranie (może potrwać ~60s)…", "info");
        var json = await auditAjax("ups_audit_refresh_cwv", { client_id: cid });
        refreshCwvBtn.disabled = false;
        if (json && json.success && json.data && json.data.cwv) {
          upsAuditToast("CWV zaktualizowane.", "ok");
          window.location.reload();
          return;
        }
        var msg = (json && json.data && json.data.msg) || "PageSpeed niedostępny (limit API lub błąd).";
        upsAuditToast(msg, "err");
      });
    }

    document.querySelectorAll("[data-ups-audit-client-sync]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var cid = Number(btn.getAttribute("data-ups-audit-client-sync") || 0);
        window.upsAuditSyncAllResources(cid, 0);
      });
    });

    document.querySelectorAll("[data-ups-audit-account-sync]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var aid = Number(btn.getAttribute("data-ups-audit-account-sync") || 0);
        window.upsAuditSyncAllResources(0, aid);
      });
    });

    window.upsAuditCreateProfile = async function (title, website, openDashboard) {
      var json = await auditAjax("ups_audit_create_client_profile", {
        title: String(title || ""),
        website: String(website || ""),
      });
      if (json && json.success && json.data) {
        upsAuditToast("Profil utworzony.", "ok");
        if (openDashboard && json.data.dashboard_url) {
          window.location.href = json.data.dashboard_url;
          return json.data.client_id;
        }
        if (!openDashboard) {
          return json.data.client_id;
        }
        window.location.reload();
        return json.data.client_id;
      }
      var msg = (json && json.data && json.data.msg) || "Nie udało się utworzyć profilu.";
      upsAuditToast(msg, "err");
      alert(msg);
      return 0;
    };

    var createProfileBtn = document.getElementById("ups-audit-create-profile-btn");
    if (createProfileBtn) {
      createProfileBtn.addEventListener("click", function () {
        var nameIn = document.getElementById("ups-audit-new-profile-name");
        var webIn = document.getElementById("ups-audit-new-profile-website");
        var title = nameIn ? nameIn.value.trim() : "";
        if (!title) {
          alert("Podaj nazwę profilu.");
          return;
        }
        createProfileBtn.disabled = true;
        window
          .upsAuditCreateProfile(title, webIn ? webIn.value.trim() : "", true)
          .finally(function () {
            createProfileBtn.disabled = false;
          });
      });
    }

    var mapInlineCreate = document.getElementById("ups-audit-map-inline-create");
    if (mapInlineCreate) {
      mapInlineCreate.addEventListener("click", async function () {
        var nameIn = document.getElementById("ups-audit-map-inline-name");
        var title = nameIn ? nameIn.value.trim() : "";
        if (!title) {
          alert("Podaj nazwę profilu.");
          return;
        }
        var newId = await window.upsAuditCreateProfile(title, "", false);
        if (newId) {
          var sel = document.getElementById("ups-audit-map-client-select");
          if (sel) {
            var opt = document.createElement("option");
            opt.value = String(newId);
            opt.textContent = title;
            opt.selected = true;
            sel.appendChild(opt);
          }
          window.UPS_AUDIT_MAP_CLIENT_ID = Number(newId);
        }
      });
    }

    document.querySelectorAll("[data-ups-audit-res-sync]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        window.upsAuditResourceSync(Number(btn.getAttribute("data-ups-audit-res-sync") || 0));
      });
    });

    var windowSelect = document.getElementById("ups-audit-window-select");
    if (windowSelect) {
      windowSelect.addEventListener("change", function () {
        var u = new URL(window.location.href);
        u.searchParams.set("window", windowSelect.value);
        window.location.assign(u.toString());
      });
    }

    var clarityTestBtn = document.getElementById("ups-clarity-test-btn");
    var clarityImportBtn = document.getElementById("ups-clarity-import-btn");
    if (clarityTestBtn) {
      clarityTestBtn.addEventListener("click", async function () {
        var tokenEl = document.getElementById("ups-clarity-api-token");
        var token = tokenEl ? tokenEl.value.trim() : "";
        if (!token) {
          alert("Wklej token API z Clarity (Settings → Data Export).");
          return;
        }
        clarityTestBtn.disabled = true;
        var json = await auditAjax("ups_audit_clarity_test", { api_token: token });
        clarityTestBtn.disabled = false;
        if (json && json.success && json.data) {
          upsAuditToast(
            "OK: " +
              (json.data.sessions || 0) +
              " sesji, " +
              (json.data.dead_clicks || 0) +
              " dead clicks (1 dzień)",
            "ok"
          );
        } else {
          var err =
            (json && json.data && json.data.msg) || "Test Clarity nie powiódł się.";
          upsAuditToast(err, "err");
          alert(err);
        }
      });
    }
    if (clarityImportBtn) {
      clarityImportBtn.addEventListener("click", async function () {
        var nameEl = document.getElementById("ups-clarity-project-name");
        var slugEl = document.getElementById("ups-clarity-project-slug");
        var tokenEl = document.getElementById("ups-clarity-api-token");
        var name = nameEl ? nameEl.value.trim() : "";
        var slug = slugEl ? slugEl.value.trim() : "";
        var token = tokenEl ? tokenEl.value.trim() : "";
        if (!name || !token) {
          alert("Podaj nazwę projektu i token API.");
          return;
        }
        clarityImportBtn.disabled = true;
        var json = await auditAjax("ups_audit_clarity_import", {
          project_name: name,
          project_slug: slug,
          api_token: token,
        });
        clarityImportBtn.disabled = false;
        if (json && json.success) {
          upsAuditToast(json.data && json.data.message ? json.data.message : "Dodano Clarity.", "ok");
          window.setTimeout(function () {
            window.location.reload();
          }, 600);
        } else {
          var err2 =
            (json && json.data && json.data.msg) || "Import Clarity nie powiódł się.";
          upsAuditToast(err2, "err");
          alert(err2);
        }
      });
    }

    var pdfBtn = document.getElementById("ups-audit-export-dash-pdf");
    if (pdfBtn) {
      pdfBtn.addEventListener("click", function () {
        var root = document.getElementById("ups-audit-dash-root");
        var cid = root ? Number(root.getAttribute("data-client-id") || 0) : 0;
        var win = root ? Number(root.getAttribute("data-window") || 30) : 30;
        if (!cid) return;
        pdfBtn.disabled = true;
        var prevLabel = pdfBtn.textContent;
        pdfBtn.textContent = "Generuję PDF…";
        upsAuditToast("Generowanie PDF dashboardu…", "info");
        auditAjax("ups_audit_export_dashboard_pdf", { client_id: String(cid), window: String(win) })
          .then(function (d) {
            pdfBtn.disabled = false;
            pdfBtn.textContent = prevLabel;
            if (d && d.success && d.data && d.data.url) {
              upsAuditToast("PDF gotowy — otwieram plik.", "ok");
              window.open(d.data.url, "_blank");
            } else {
              var errMsg =
                (d && d.data && (d.data.msg || d.data.message)) ||
                "Nie udało się wygenerować PDF.";
              upsAuditToast(errMsg, "err");
            }
          })
          .catch(function () {
            pdfBtn.disabled = false;
            pdfBtn.textContent = prevLabel;
            upsAuditToast("Błąd sieci przy eksporcie PDF.", "err");
          });
      });
    }
  });

  if (document.readyState !== "loading") {
    upsAuditBindConnectUi();
  }
})();
