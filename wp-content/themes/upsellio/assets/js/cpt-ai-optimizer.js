(function ($) {
  "use strict";

  function getPostId() {
    var v = parseInt($("#post_ID").val(), 10);
    return isNaN(v) ? 0 : v;
  }

  function setStatus(msg, isError) {
    var $st = $("#upsellio-cpt-ai-status");
    $st.show()
      .css("color", isError ? "#b32d2e" : "#1e7e34")
      .text(msg);
  }

  function setLoading(busy) {
    var $btn = $("#upsellio-cpt-ai-run");
    $btn.prop("disabled", !!busy);
    $btn.text(busy ? "⏳ Generuję… (może potrwać 60–90 s)" : $btn.data("orig"));
  }

  $(function () {
    var $btn = $("#upsellio-cpt-ai-run");
    if (!$btn.length || !window.upselloCptAi) {
      return;
    }

    $btn.data("orig", $btn.text());

    $btn.on("click", function () {
      var postId = getPostId();
      if (!postId) {
        alert("Najpierw zapisz szkic (WordPress musi nadać ID wpisu).");
        return;
      }

      var notes = ($("#upsellio-cpt-ai-notes").val() || "").trim();

      setLoading(true);
      setStatus("Łączę z API Claude… proszę czekać.", false);

      $.post(window.upselloCptAi.ajaxurl, {
        action: "upsellio_cpt_ai_optimize",
        nonce: window.upselloCptAi.nonce,
        post_id: postId,
        notes: notes,
      })
        .done(function (res) {
          if (!res || !res.success) {
            var msg = res && res.data ? res.data : "Błąd odpowiedzi serwera.";
            setLoading(false);
            setStatus("❌ " + msg, true);
            return;
          }
          setStatus("✅ " + (res.data.message || "Zapisano!"), false);
          setTimeout(function () {
            window.location.reload();
          }, 800);
        })
        .fail(function (xhr) {
          setLoading(false);
          setStatus("❌ Błąd sieci: " + xhr.status + " " + xhr.statusText, true);
        });
    });
  });
}(jQuery));
