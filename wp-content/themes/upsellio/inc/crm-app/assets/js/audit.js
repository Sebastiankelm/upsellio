(function () {
  window.UPS_AUDIT_VIEW_CONFIG = window.UPS_AUDIT_VIEW_CONFIG || {
    "ca-clients": {
      title: "Lista klientów-Audyt",
      qa: [
        { l: "Wszystkie raporty miesięczne", i: "ti-sparkles", f: function () { if (window.upsAuditBulkGenerateReports) window.upsAuditBulkGenerateReports("monthly"); } },
        { l: "Sync wszystkie zasoby", i: "ti-refresh", f: function () { if (window.upsAuditSyncAllResources) window.upsAuditSyncAllResources(); } }
      ]
    },
    "ca-dashboard": {
      title: "Dashboard klienta",
      qa: [
        { l: "Raport miesięczny AI", i: "ti-sparkles", f: function () { if (window.upsAuditGenReport) window.upsAuditGenReport(window.UPS_AUDIT_CLIENT_ID || 0, "monthly"); } },
        { l: "Plan działań AI", i: "ti-target-arrow", f: function () { if (window.upsAuditGenReport) window.upsAuditGenReport(window.UPS_AUDIT_CLIENT_ID || 0, "plan"); } },
        { l: "Audyt punktowy", i: "ti-list-check", f: function () { if (window.upsAuditGenReport) window.upsAuditGenReport(window.UPS_AUDIT_CLIENT_ID || 0, "audit"); } }
      ]
    },
    "ca-accounts": {
      title: "Konta Google & zasoby",
      qa: [
        { l: "Połącz nowe konto", i: "ti-plus", f: function () { if (window.upsAuditConnectAccount) window.upsAuditConnectAccount(""); } },
        { l: "Odśwież wszystkie tokeny", i: "ti-refresh", f: function () { if (window.upsAuditRefreshAllAccounts) window.upsAuditRefreshAllAccounts(); } }
      ]
    }
  };
})();
