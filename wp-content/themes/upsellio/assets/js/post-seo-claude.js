(function ($) {
  "use strict";

  function getPostId() {
    var v = parseInt($("#post_ID").val(), 10);
    return isNaN(v) ? 0 : v;
  }

  function setUi(busy, text) {
    var $btn = $("#upsellio-seo-claude-run");
    var $st = $("#upsellio-seo-claude-status-text");
    $btn.prop("disabled", !!busy);
    $st.text(text || "");
  }

  $(function () {
    var $btn = $("#upsellio-seo-claude-run");
    if (!$btn.length || !window.upsellioPostSeoClaude) {
      return;
    }

    $btn.on("click", function () {
      var postId = getPostId();
      if (!postId) {
        window.alert("Najpierw zapisz szkic wpisu (WordPress musi nadać ID).");
        return;
      }
      var notes = ($("#upsellio-seo-claude-notes").val() || "").trim();
      setUi(true, "Żądanie do Claude…");
      $("#upsellio-seo-claude-status-code").text("running");

      $.post(
        window.upsellioPostSeoClaude.ajaxurl,
        {
          action: "upsellio_post_seo_claude_fill",
          nonce: window.upsellioPostSeoClaude.nonce,
          post_id: postId,
          notes: notes,
        }
      )
        .done(function (res) {
          if (!res || !res.success) {
            setUi(false, "Błąd odpowiedzi serwera.");
            $("#upsellio-seo-claude-status-code").text("error");
            return;
          }
          var d = res.data || {};
          if (typeof d.log === "string") {
            $("#upsellio-seo-claude-log").val(d.log);
          }
          if (d.status) {
            $("#upsellio-seo-claude-status-code").text(d.status);
          }
          if (d.ok) {
            setUi(false, "Gotowe. Przeładowanie…");
            window.setTimeout(function () {
              window.location.reload();
            }, 600);
          } else {
            $("#upsellio-seo-claude-status-code").text(d.status || "error");
            setUi(false, d.message || "Błąd integracji.");
          }
        })
        .fail(function () {
          $("#upsellio-seo-claude-status-code").text("error");
          setUi(false, "Błąd sieci.");
        });
    });
  });
})(jQuery);
