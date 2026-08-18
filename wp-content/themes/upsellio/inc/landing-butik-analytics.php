<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_btk_landing_key()
{
    return "butik";
}

function upsellio_btk_section_labels()
{
    return [
        "hero" => "Hero",
        "pain" => "Znasz to?",
        "process" => "Jak działam?",
        "results" => "Wątpliwości",
        "pricing" => "Pakiety",
        "contact" => "Kontakt",
    ];
}

function upsellio_btk_section_order()
{
    return array_keys(upsellio_btk_section_labels());
}

function upsellio_btk_allowed_events()
{
    return [
        "page_view",
        "section_view",
        "scroll_depth",
        "cta_click",
        "tel_click",
        "mail_click",
        "form_start",
        "form_submit",
        "faq_open",
        "plan_click",
        "nav_click",
        "engage",
    ];
}

function upsellio_btk_events_table()
{
    global $wpdb;
    return $wpdb->prefix . "ups_landing_events";
}

function upsellio_btk_sessions_table()
{
    global $wpdb;
    return $wpdb->prefix . "ups_landing_sessions";
}

function upsellio_btk_register_admin_page()
{
    add_submenu_page(
        upsellio_admin_hub_slug(),
        "Analityka landingu butiku",
        "Landing butik",
        "edit_posts",
        "upsellio-butik-landing-analytics",
        "upsellio_btk_render_admin_page"
    );
}
add_action("admin_menu", "upsellio_btk_register_admin_page", 21);

function upsellio_btk_print_tracker()
{
    if (is_admin() || !function_exists("is_page_template") || !is_page_template("page-marketing-butiku.php")) {
        return;
    }
    if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
        return;
    }

    $cfg = [
        "ajax" => admin_url("admin-ajax.php"),
        "nonce" => wp_create_nonce("ups_btk_track"),
        "landing" => upsellio_btk_landing_key(),
    ];
    ?>
<script>
(function () {
  var cfg = <?php echo wp_json_encode($cfg); ?>;
  if (!cfg || !cfg.ajax) return;
  if (document.documentElement.classList.contains("upsellio-internal-user")) return;

  function hasStatsConsent() {
    try {
      if (window.Cookiebot && Cookiebot.consent && Cookiebot.consent.statistics) return true;
    } catch (e) {}
    try {
      if (window.upsellioCookie && window.upsellioCookie.statistics) return true;
    } catch (e2) {}
    return false;
  }
  function startWhenAllowed(startFn) {
    if (hasStatsConsent()) { startFn(); return; }
    var started = false;
    function tryStart() {
      if (started || !hasStatsConsent()) return;
      started = true;
      startFn();
    }
    document.addEventListener("upsellio-cookie-consent", tryStart);
    window.addEventListener("CookiebotOnAccept", tryStart);
    window.addEventListener("CookiebotOnLoad", tryStart);
  }

  startWhenAllowed(function () {
  function uid() {
    var s = "";
    try {
      var a = new Uint8Array(16);
      crypto.getRandomValues(a);
      for (var i = 0; i < a.length; i++) s += ("0" + a[i].toString(16)).slice(-2);
    } catch (e) {
      s = String(Date.now()) + Math.random().toString(16).slice(2);
    }
    return s.slice(0, 32);
  }
  function store(key, fallback) {
    try {
      var v = localStorage.getItem(key);
      if (v && v.length >= 16) return v.slice(0, 32);
      localStorage.setItem(key, fallback);
      return fallback;
    } catch (e) {
      return fallback;
    }
  }
  function sess(key, fallback) {
    try {
      var v = sessionStorage.getItem(key);
      if (v && v.length >= 16) return v.slice(0, 32);
      sessionStorage.setItem(key, fallback);
      return fallback;
    } catch (e) {
      return fallback;
    }
  }

  var visitor = store("ups_btk_vid", uid());
  var session = sess("ups_btk_sid", uid());
  var params = new URLSearchParams(window.location.search);
  var device = window.matchMedia("(max-width: 767px)").matches ? "mobile" : (window.matchMedia("(max-width: 1024px)").matches ? "tablet" : "desktop");
  var queue = [];
  var seen = {};
  var flushing = false;

  function gaEvent(name, paramsObj) {
    var payload = Object.assign({
      event_category: "butik_landing",
      landing_id: "marketing_butik"
    }, paramsObj || {});
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(Object.assign({ event: "btk_" + name }, payload));
    } catch (e) {}
    try {
      if (typeof window.gtag === "function") window.gtag("event", "btk_" + name, payload);
    } catch (e2) {}
  }

  function track(name, section, extra) {
    var key = name + "|" + (section || "") + "|" + (extra || "");
    if (name === "section_view" || name === "scroll_depth" || name === "form_start" || name === "engage" || name === "page_view") {
      if (seen[key]) return;
      seen[key] = 1;
    }
    queue.push({
      name: name,
      section: section || "",
      extra: String(extra || "").slice(0, 80)
    });
    gaEvent(name, {
      section_id: section || "",
      extra: extra || "",
      engagement_time_msec: Math.round(performance.now())
    });
    if (queue.length >= 6) flush(false);
  }

  function flush(keepalive) {
    if (!queue.length || flushing) return;
    flushing = true;
    var batch = queue.splice(0, 12);
    var body = new URLSearchParams();
    body.set("action", "upsellio_btk_track");
    body.set("nonce", cfg.nonce);
    body.set("landing", cfg.landing);
    body.set("session", session);
    body.set("visitor", visitor);
    body.set("device", device);
    body.set("utm_source", params.get("utm_source") || "");
    body.set("utm_medium", params.get("utm_medium") || "");
    body.set("utm_campaign", params.get("utm_campaign") || "");
    body.set("events", JSON.stringify(batch));
    var blob = body.toString();
    try {
      if (keepalive && navigator.sendBeacon) {
        navigator.sendBeacon(cfg.ajax, new Blob([blob], { type: "application/x-www-form-urlencoded" }));
        flushing = false;
        return;
      }
    } catch (e) {}
    fetch(cfg.ajax, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: blob,
      credentials: "same-origin",
      keepalive: !!keepalive
    }).catch(function () {}).finally(function () { flushing = false; });
  }

  track("page_view", "hero", "");

  var sections = document.querySelectorAll("[data-btk-section]");
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.35) return;
        var id = entry.target.getAttribute("data-btk-section") || "";
        if (id) track("section_view", id, "");
      });
    }, { threshold: [0.35, 0.6] });
    sections.forEach(function (el) { io.observe(el); });
  }

  var depths = [25, 50, 75, 90];
  function onScroll() {
    var h = document.documentElement;
    var max = Math.max(1, h.scrollHeight - window.innerHeight);
    var pct = Math.round((window.scrollY / max) * 100);
    depths.forEach(function (d) {
      if (pct >= d) track("scroll_depth", "", String(d));
    });
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  document.addEventListener("click", function (e) {
    var t = e.target.closest("a,button,summary");
    if (!t) return;
    if (!t.closest(".btk") && !t.closest(".btk-dock")) return;
    var href = (t.getAttribute("href") || "").trim();
    var cta = t.getAttribute("data-btk-cta") || "";
    var nav = t.getAttribute("data-btk-nav") || "";
    var plan = t.getAttribute("data-btk-plan") || "";
    var faq = t.getAttribute("data-btk-faq") || (t.closest("[data-btk-faq]") ? t.closest("[data-btk-faq]").getAttribute("data-btk-faq") : "");
    var sectionEl = t.closest("[data-btk-section]");
    var section = sectionEl ? (sectionEl.getAttribute("data-btk-section") || "") : "";
    if (cta) track("cta_click", section, cta);
    if (nav) track("nav_click", "", nav);
    if (plan) track("plan_click", "pricing", plan);
    if (t.tagName === "SUMMARY" && faq) track("faq_open", "faq", faq);
    if (href.indexOf("tel:") === 0) track("tel_click", section || "nav", cta || href.replace("tel:", "").slice(0, 20));
    if (href.indexOf("mailto:") === 0) track("mail_click", section || "nav", cta || "mail");
  }, true);

  var form = document.getElementById("landing-butik-form") || document.querySelector("form.btk-form, .btk-form form");
  if (form) {
    form.addEventListener("focusin", function () { track("form_start", "contact", "landing-butik-form"); }, { once: true });
    form.addEventListener("submit", function () { track("form_submit", "contact", "attempt"); });
  }

  [15, 30, 60, 120].forEach(function (sec) {
    setTimeout(function () { track("engage", "", String(sec)); }, sec * 1000);
  });

  setInterval(function () { flush(false); }, 4000);
  document.addEventListener("visibilitychange", function () { if (document.visibilityState === "hidden") flush(true); });
  window.addEventListener("pagehide", function () { flush(true); });
  });
})();
</script>
    <?php
}
add_action("wp_footer", "upsellio_btk_print_tracker", 40);

function upsellio_btk_is_bot($ua)
{
    $ua = strtolower((string) $ua);
    if ($ua === "") {
        return true;
    }
    return (bool) preg_match("/bot|crawl|spider|slurp|facebookexternalhit|preview|lighthouse|headless|wget|curl/i", $ua);
}

function upsellio_btk_bump_event($landing, $name, $section, $extra)
{
    global $wpdb;
    $table = upsellio_btk_events_table();
    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO {$table} (landing_key, event_date, event_name, section_id, extra, hits)
             VALUES (%s, %s, %s, %s, %s, 1)
             ON DUPLICATE KEY UPDATE hits = hits + 1",
            $landing,
            current_time("Y-m-d"),
            $name,
            $section,
            $extra
        )
    );
}

function upsellio_btk_upsert_session($landing, $session, $visitor, $device, $utm, $events)
{
    global $wpdb;
    $table = upsellio_btk_sessions_table();
    $now = current_time("mysql");
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE landing_key = %s AND session_key = %s LIMIT 1",
            $landing,
            $session
        ),
        ARRAY_A
    );

    $sections = [];
    if (is_array($row) && !empty($row["sections"])) {
        $decoded = json_decode((string) $row["sections"], true);
        if (is_array($decoded)) {
            $sections = $decoded;
        }
    }

    $converted = is_array($row) ? (int) $row["converted"] : 0;
    $convert_type = is_array($row) ? (string) $row["convert_type"] : "";
    $time_sec = is_array($row) ? (int) $row["time_sec"] : 0;

    foreach ($events as $event) {
        $name = (string) ($event["name"] ?? "");
        $section = (string) ($event["section"] ?? "");
        $extra = (string) ($event["extra"] ?? "");
        if ($name === "section_view" && $section !== "" && !in_array($section, $sections, true)) {
            $sections[] = $section;
        }
        if ($name === "engage") {
            $time_sec = max($time_sec, (int) $extra);
        }
        if ($name === "form_submit" && $extra !== "attempt") {
            $converted = 1;
            $convert_type = "form";
        }
        if ($name === "form_submit" && $extra === "attempt" && $convert_type === "") {
            $convert_type = $convert_type;
        }
        if ($name === "tel_click") {
            $converted = 1;
            if ($convert_type === "") {
                $convert_type = "tel";
            }
        }
    }

    $payload = [
        "landing_key" => $landing,
        "session_key" => $session,
        "visitor_key" => $visitor,
        "last_at" => $now,
        "device" => substr($device, 0, 16),
        "utm_source" => substr((string) ($utm["source"] ?? ""), 0, 80),
        "utm_medium" => substr((string) ($utm["medium"] ?? ""), 0, 80),
        "utm_campaign" => substr((string) ($utm["campaign"] ?? ""), 0, 120),
        "sections" => wp_json_encode(array_values($sections)),
        "converted" => $converted,
        "convert_type" => substr($convert_type, 0, 16),
        "time_sec" => min(3600, $time_sec),
    ];

    if (is_array($row)) {
        $wpdb->update($table, $payload, ["id" => (int) $row["id"]]);
        return;
    }

    $payload["first_at"] = $now;
    $wpdb->insert($table, $payload);
}

function upsellio_btk_track_ajax()
{
    if (!check_ajax_referer("ups_btk_track", "nonce", false)) {
        wp_send_json_error(["ok" => false], 403);
    }
    if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
        wp_send_json_success(["ok" => true, "skipped" => "internal"]);
    }
    $ua = isset($_SERVER["HTTP_USER_AGENT"]) ? (string) wp_unslash($_SERVER["HTTP_USER_AGENT"]) : "";
    if (upsellio_btk_is_bot($ua)) {
        wp_send_json_success(["ok" => true, "skipped" => "bot"]);
    }

    $landing = sanitize_key((string) ($_POST["landing"] ?? upsellio_btk_landing_key()));
    if ($landing !== upsellio_btk_landing_key()) {
        wp_send_json_error(["ok" => false], 400);
    }
    $session = preg_replace("/[^a-f0-9]/", "", strtolower((string) ($_POST["session"] ?? "")));
    $visitor = preg_replace("/[^a-f0-9]/", "", strtolower((string) ($_POST["visitor"] ?? "")));
    if (strlen($session) < 16) {
        wp_send_json_error(["ok" => false], 400);
    }
    $session = substr($session, 0, 32);
    $visitor = substr($visitor, 0, 32);
    $device = sanitize_key((string) ($_POST["device"] ?? ""));
    if (!in_array($device, ["mobile", "tablet", "desktop"], true)) {
        $device = "desktop";
    }

    $rate_key = "ups_btk_rate_" . $session;
    $hits = (int) get_transient($rate_key);
    if ($hits > 120) {
        wp_send_json_success(["ok" => true, "skipped" => "rate"]);
    }

    $raw = isset($_POST["events"]) ? wp_unslash($_POST["events"]) : "[]";
    $events = json_decode((string) $raw, true);
    if (!is_array($events)) {
        $events = [];
    }
    $events = array_slice($events, 0, 12);
    $allowed = upsellio_btk_allowed_events();
    $clean = [];
    foreach ($events as $event) {
        if (!is_array($event)) {
            continue;
        }
        $name = sanitize_key((string) ($event["name"] ?? ""));
        if (!in_array($name, $allowed, true)) {
            continue;
        }
        $section = sanitize_key((string) ($event["section"] ?? ""));
        $extra = sanitize_text_field((string) ($event["extra"] ?? ""));
        $clean[] = [
            "name" => $name,
            "section" => substr($section, 0, 40),
            "extra" => substr($extra, 0, 80),
        ];
        upsellio_btk_bump_event($landing, $name, substr($section, 0, 40), substr($extra, 0, 80));
    }

    if ($clean) {
        upsellio_btk_upsert_session($landing, $session, $visitor, $device, [
            "source" => sanitize_text_field((string) ($_POST["utm_source"] ?? "")),
            "medium" => sanitize_text_field((string) ($_POST["utm_medium"] ?? "")),
            "campaign" => sanitize_text_field((string) ($_POST["utm_campaign"] ?? "")),
        ], $clean);
        set_transient($rate_key, $hits + count($clean), HOUR_IN_SECONDS);
    }

    wp_send_json_success(["ok" => true]);
}
add_action("wp_ajax_upsellio_btk_track", "upsellio_btk_track_ajax");
add_action("wp_ajax_nopriv_upsellio_btk_track", "upsellio_btk_track_ajax");

function upsellio_btk_on_lead_created($lead_id)
{
    $lead_id = (int) $lead_id;
    $origin = (string) get_post_meta($lead_id, "_upsellio_lead_form_origin", true);
    if ($origin !== "landing-butik-form") {
        return;
    }
    upsellio_btk_bump_event(upsellio_btk_landing_key(), "form_submit", "contact", "crm");
}
add_action("upsellio_crm_contact_lead_created", "upsellio_btk_on_lead_created");

function upsellio_btk_event_sum($from, $to, $name, $section = null, $extra = null)
{
    global $wpdb;
    $table = upsellio_btk_events_table();
    $sql = "SELECT SUM(hits) FROM {$table} WHERE landing_key = %s AND event_date BETWEEN %s AND %s AND event_name = %s";
    $args = [upsellio_btk_landing_key(), $from, $to, $name];
    if ($section !== null) {
        $sql .= " AND section_id = %s";
        $args[] = $section;
    }
    if ($extra !== null) {
        $sql .= " AND extra = %s";
        $args[] = $extra;
    }
    return (int) $wpdb->get_var($wpdb->prepare($sql, $args));
}

function upsellio_btk_event_groups($from, $to, $name)
{
    global $wpdb;
    $table = upsellio_btk_events_table();
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT section_id, extra, SUM(hits) AS hits
             FROM {$table}
             WHERE landing_key = %s AND event_date BETWEEN %s AND %s AND event_name = %s
             GROUP BY section_id, extra
             ORDER BY hits DESC",
            upsellio_btk_landing_key(),
            $from,
            $to,
            $name
        ),
        ARRAY_A
    );
    return is_array($rows) ? $rows : [];
}

function upsellio_btk_sessions_in_range($from, $to)
{
    global $wpdb;
    $table = upsellio_btk_sessions_table();
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT session_key, visitor_key, device, utm_source, utm_medium, utm_campaign, sections, converted, convert_type, time_sec
             FROM {$table}
             WHERE landing_key = %s AND first_at >= %s AND first_at <= %s",
            upsellio_btk_landing_key(),
            $from . " 00:00:00",
            $to . " 23:59:59"
        ),
        ARRAY_A
    );
    return is_array($rows) ? $rows : [];
}

function upsellio_btk_pct($part, $whole)
{
    if ((int) $whole <= 0) {
        return "—";
    }
    return number_format(((float) $part / (float) $whole) * 100, 1, ",", " ") . "%";
}

function upsellio_btk_build_insights($sessions, $cta_rows, $plan_rows, $faq_rows, $form_starts, $form_crm, $tel)
{
    $insights = [];
    $n = count($sessions);
    $converted = 0;
    $tel_conv = 0;
    $form_conv = 0;
    $section_hits = [];
    foreach (upsellio_btk_section_order() as $id) {
        $section_hits[$id] = 0;
    }
    $devices = ["mobile" => 0, "desktop" => 0, "tablet" => 0];
    $engage60 = 0;
    foreach ($sessions as $row) {
        if ((int) $row["converted"] === 1) {
            $converted++;
            if (($row["convert_type"] ?? "") === "tel") {
                $tel_conv++;
            }
            if (($row["convert_type"] ?? "") === "form") {
                $form_conv++;
            }
        }
        $dev = (string) ($row["device"] ?? "desktop");
        if (!isset($devices[$dev])) {
            $devices[$dev] = 0;
        }
        $devices[$dev]++;
        if ((int) ($row["time_sec"] ?? 0) >= 60) {
            $engage60++;
        }
        $seen = json_decode((string) ($row["sections"] ?? "[]"), true);
        if (!is_array($seen)) {
            continue;
        }
        foreach ($seen as $sid) {
            if (isset($section_hits[$sid])) {
                $section_hits[$sid]++;
            }
        }
    }

    if ($n < 8) {
        $insights[] = [
            "level" => "info",
            "title" => "Za mało danych do twardych wniosków",
            "text" => "Masz {$n} sesji. Wnioski stabilizują się od ok. 30–50 wizyt. Zalogowany admin nie jest liczony — testuj w oknie incognito.",
        ];
        return $insights;
    }

    $cr = $n > 0 ? $converted / $n : 0;
    if ($cr < 0.02) {
        $insights[] = [
            "level" => "warn",
            "title" => "Konwersja poniżej 2%",
            "text" => "Z {$n} sesji tylko {$converted} skończyło się telefonem albo leadem. Najpierw sprawdź hero i pierwszy CTA — większość osób nie dochodzi do formularza.",
        ];
    } elseif ($cr >= 0.08) {
        $insights[] = [
            "level" => "ok",
            "title" => "Landing konwertuje solidnie",
            "text" => number_format($cr * 100, 1, ",", " ") . "% sesji kończy się kontaktem. Skaluj ruch, nie przebudowuj układu.",
        ];
    }

    $order = upsellio_btk_section_order();
    $labels = upsellio_btk_section_labels();
    $worst_drop = null;
    for ($i = 0; $i < count($order) - 1; $i++) {
        $a = $order[$i];
        $b = $order[$i + 1];
        $from = (int) $section_hits[$a];
        $to = (int) $section_hits[$b];
        if ($from < 10) {
            continue;
        }
        $drop = 1 - ($to / $from);
        if ($drop >= 0.4 && ($worst_drop === null || $drop > $worst_drop["drop"])) {
            $worst_drop = [
                "from" => $labels[$a],
                "to" => $labels[$b],
                "drop" => $drop,
                "from_n" => $from,
                "to_n" => $to,
            ];
        }
    }
    if (is_array($worst_drop)) {
        $insights[] = [
            "level" => "warn",
            "title" => "Największy spadek: " . $worst_drop["from"] . " → " . $worst_drop["to"],
            "text" => "Z " . $worst_drop["from_n"] . " osób, które zobaczyły «" . $worst_drop["from"] . "», do «" . $worst_drop["to"] . "» doszło " . $worst_drop["to_n"] . " (" . upsellio_btk_pct($worst_drop["to_n"], $worst_drop["from_n"]) . "). Tu tracisz uwagę — skróć sekcję albo daj CTA wcześniej.",
        ];
    }

    $hero_cta = 0;
    $process_cta = 0;
    $pain_cta = 0;
    foreach ($cta_rows as $row) {
        $hits = (int) ($row["hits"] ?? 0);
        $sid = (string) ($row["section_id"] ?? "");
        if ($sid === "hero") {
            $hero_cta += $hits;
        }
        if ($sid === "process") {
            $process_cta += $hits;
        }
        if ($sid === "pain") {
            $pain_cta += $hits;
        }
    }
    if ($process_cta > $hero_cta * 1.4 && $process_cta >= 5) {
        $insights[] = [
            "level" => "ok",
            "title" => "CTA pod «Jak działam?» działa lepiej niż hero",
            "text" => "Proces: {$process_cta} kliknięć, hero: {$hero_cta}. Osoby decydują się po zobaczeniu kroków. Zostaw ten przycisk i rozważ analogiczny komunikat już w hero.",
        ];
    }
    if ($pain_cta > $hero_cta && $pain_cta >= 5) {
        $insights[] = [
            "level" => "ok",
            "title" => "Sekcja «Znasz to?» pcha do kontaktu",
            "text" => "Kliknięcia CTA: {$pain_cta} vs hero {$hero_cta}. Ból sprzedaje lepiej niż obietnica. Nie rozwadniaj tych kart.",
        ];
    }

    $contact_seen = (int) ($section_hits["contact"] ?? 0);
    if ($contact_seen >= 10 && $form_starts > 0 && $form_crm / max(1, $form_starts) < 0.25) {
        $insights[] = [
            "level" => "warn",
            "title" => "Formularz startuje, ale mało osób go kończy",
            "text" => "Startów: {$form_starts}, leadów w CRM: {$form_crm}. Skróć pola albo daj telefon jako łatwiejszą ścieżkę obok formularza.",
        ];
    }

    if ($tel > $form_crm * 2 && $tel >= 5) {
        $insights[] = [
            "level" => "ok",
            "title" => "Telefon wygrywa z formularzem",
            "text" => "Kliknięcia tel.: {$tel}, leady z formularza: {$form_crm}. Na mobile trzymaj sticky «Zadzwoń». W reklamach możesz dać call-only jako test.",
        ];
    }

    $plan_top = $plan_rows[0] ?? null;
    if (is_array($plan_top) && (int) $plan_top["hits"] >= 4) {
        $plan = (string) ($plan_top["extra"] ?? "");
        $insights[] = [
            "level" => "info",
            "title" => "Najczęściej klikany pakiet: " . ($plan !== "" ? $plan : "nieznany"),
            "text" => "To pakiet, który osoby porównują najchętniej. W rozmowie startuj od niego, nie od Startu.",
        ];
    }

    $faq_top = $faq_rows[0] ?? null;
    if (is_array($faq_top) && (int) $faq_top["hits"] >= 3) {
        $insights[] = [
            "level" => "info",
            "title" => "FAQ, które otwierają najczęściej",
            "text" => "«" . (string) $faq_top["extra"] . "» — to zastrzeżenie przed zakupem. Odpowiedź powinna być też wyżej (hero / pakiety), nie tylko na dole strony.",
        ];
    }

    $mobile = (int) ($devices["mobile"] ?? 0);
    if ($n > 0 && $mobile / $n >= 0.7) {
        $insights[] = [
            "level" => "info",
            "title" => "Ruch jest głównie mobile (" . upsellio_btk_pct($mobile, $n) . ")",
            "text" => "Optymalizuj pod kciuk: sticky telefon, krótki hero, pakiet Optymalny w pierwszym ekranie cennika.",
        ];
    }

    if ($n >= 15 && $engage60 / $n < 0.25) {
        $insights[] = [
            "level" => "warn",
            "title" => "Mało osób zostaje ponad minutę",
            "text" => upsellio_btk_pct($engage60, $n) . " sesji trwa ≥ 60 s. Hero albo pierwsze 2 sekcje nie zatrzymują. Skróć lead albo od razu pokaż cenę / CTA.",
        ];
    }

    if (!$insights) {
        $insights[] = [
            "level" => "ok",
            "title" => "Brak ostrych anomalii",
            "text" => "Lejek wygląda równo. Patrz na tabelę sekcji i źródła UTM — tam wyjdzie, który kanał reklamowy dowozi kontakt.",
        ];
    }

    return $insights;
}

function upsellio_btk_lead_has_consent($lead_id)
{
    return trim((string) get_post_meta((int) $lead_id, "_upsellio_lead_consent_at", true)) !== "";
}

function upsellio_btk_lead_status_label($lead_id)
{
    $terms = get_the_terms((int) $lead_id, "lead_status");
    if (is_wp_error($terms) || empty($terms) || !is_array($terms)) {
        return "Nowy";
    }
    return (string) $terms[0]->name;
}

function upsellio_btk_analyze_lead($lead_id)
{
    $lead_id = (int) $lead_id;
    $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
    $reason = trim((string) get_post_meta($lead_id, "_upsellio_lead_score_reason", true));
    $package = trim((string) get_post_meta($lead_id, "_upsellio_lead_package", true));
    $shop_url = trim((string) get_post_meta($lead_id, "_upsellio_lead_shop_url", true));
    $utm_source = trim((string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true));
    $utm_medium = trim((string) get_post_meta($lead_id, "_upsellio_lead_utm_medium", true));
    $utm_campaign = trim((string) get_post_meta($lead_id, "_upsellio_lead_utm_campaign", true));
    $gclid = trim((string) get_post_meta($lead_id, "_upsellio_lead_gclid", true));
    $message = trim(wp_strip_all_tags((string) get_post_field("post_content", $lead_id)));
    $internal = (string) get_post_meta($lead_id, "_upsellio_lead_internal_tester", true) === "1";
    $bits = [];

    if ($internal) {
        $bits[] = "To zgłoszenie testowe (zalogowany admin) — nie licz go do skuteczności reklam.";
    }
    if ($package !== "") {
        $bits[] = "Wybrała pakiet «" . $package . "» — startuj rozmowę od tego zakresu, nie od pełnego cennika.";
    } else {
        $bits[] = "Nie kliknęła pakietu. Najpierw diagnoza butiku, dopiero potem oferta.";
    }
    if ($shop_url !== "") {
        $bits[] = "Podesłała butik: " . $shop_url . " — sprawdź profil przed rozmową.";
    } else {
        $bits[] = "Brak linku do butiku — dopytaj o Instagram / sklep zanim wejdziesz w reklamę.";
    }
    if ($gclid !== "") {
        $bits[] = "Przyszła z Google Ads (gclid).";
    } elseif ($utm_source !== "") {
        $src = $utm_source . ($utm_medium !== "" ? " / " . $utm_medium : "");
        $bits[] = "Źródło: " . $src . ($utm_campaign !== "" ? " · " . $utm_campaign : "") . ".";
    } else {
        $bits[] = "Brak UTM — ruch bezpośredni, organic albo utracona atrybucja.";
    }
    if ($reason !== "") {
        $bits[] = $reason;
    } elseif ($score >= 70) {
        $bits[] = "Wysoki score (" . $score . ") — oddzwoń w pierwszej kolejności.";
    } elseif ($score > 0 && $score < 40) {
        $bits[] = "Niski score (" . $score . ") — krótka kwalifikacja, bez długiej oferty.";
    }
    $msg_l = function_exists("mb_strtolower") ? mb_strtolower($message) : strtolower($message);
    if ($msg_l !== "") {
        if (strpos($msg_l, "instagram") !== false || strpos($msg_l, "ig") !== false) {
            $bits[] = "W wiadomości wraca Instagram — pytaj o zasięg, materiały i czy reklamy idą w profil czy w sklep.";
        }
        if (strpos($msg_l, "magazyn") !== false || strpos($msg_l, "stan") !== false) {
            $bits[] = "Temat magazynu / stanów — od razu wyjaśnij, co z reklamą wyprzedanego produktu.";
        }
        if (strpos($msg_l, "agencj") !== false) {
            $bits[] = "Była lub jest agencja — dopytaj o rozliczenie od przychodu i czego nie dowoziły.";
        }
    }

    return implode(" ", $bits);
}

function upsellio_btk_landing_leads($from, $to, $limit = 80)
{
    $query = new WP_Query([
        "post_type" => "lead",
        "post_status" => "any",
        "posts_per_page" => $limit,
        "orderby" => "date",
        "order" => "DESC",
        "date_query" => [
            [
                "after" => $from . " 00:00:00",
                "before" => $to . " 23:59:59",
                "inclusive" => true,
            ],
        ],
        "meta_query" => [
            [
                "key" => "_upsellio_lead_form_origin",
                "value" => "landing-butik-form",
            ],
        ],
        "fields" => "ids",
        "no_found_rows" => false,
    ]);
    return $query;
}

function upsellio_btk_render_admin_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }

    $days = isset($_GET["days"]) ? (int) $_GET["days"] : 14;
    if (!in_array($days, [7, 14, 30, 90], true)) {
        $days = 14;
    }
    $to = current_time("Y-m-d");
    $from = gmdate("Y-m-d", strtotime($to . " -" . ($days - 1) . " days"));
    $labels = upsellio_btk_section_labels();
    $order = upsellio_btk_section_order();

    $sessions = upsellio_btk_sessions_in_range($from, $to);
    $session_n = count($sessions);
    $unique_visitors = count(array_unique(array_filter(array_map(static function ($row) {
        return (string) ($row["visitor_key"] ?? "");
    }, $sessions))));
    $converted_n = 0;
    $section_unique = [];
    foreach ($order as $id) {
        $section_unique[$id] = 0;
    }
    $sources = [];
    $devices = [];
    foreach ($sessions as $row) {
        if ((int) $row["converted"] === 1) {
            $converted_n++;
        }
        $dev = (string) ($row["device"] ?? "desktop");
        $devices[$dev] = ($devices[$dev] ?? 0) + 1;
        $src = trim((string) ($row["utm_source"] ?? ""));
        $med = trim((string) ($row["utm_medium"] ?? ""));
        $key = $src !== "" ? $src . ($med !== "" ? " / " . $med : "") : "(direct / brak UTM)";
        $sources[$key] = ($sources[$key] ?? 0) + 1;
        $seen = json_decode((string) ($row["sections"] ?? "[]"), true);
        if (!is_array($seen)) {
            continue;
        }
        foreach ($seen as $sid) {
            if (isset($section_unique[$sid])) {
                $section_unique[$sid]++;
            }
        }
    }

    $pageviews = upsellio_btk_event_sum($from, $to, "page_view");
    $tel = upsellio_btk_event_sum($from, $to, "tel_click");
    $mail = upsellio_btk_event_sum($from, $to, "mail_click");
    $form_starts = upsellio_btk_event_sum($from, $to, "form_start");
    $form_crm = upsellio_btk_event_sum($from, $to, "form_submit", "contact", "crm");
    $cta_rows = upsellio_btk_event_groups($from, $to, "cta_click");
    $plan_rows = upsellio_btk_event_groups($from, $to, "plan_click");
    $faq_rows = upsellio_btk_event_groups($from, $to, "faq_open");
    $insights = upsellio_btk_build_insights($sessions, $cta_rows, $plan_rows, $faq_rows, $form_starts, $form_crm, $tel);
    $leads = upsellio_btk_landing_leads($from, $to, 80);
    $leads_consented = 0;
    $lead_packages = [];
    foreach ($leads->posts as $lead_id_stat) {
        if (upsellio_btk_lead_has_consent((int) $lead_id_stat)) {
            $leads_consented++;
        }
        $pkg = trim((string) get_post_meta((int) $lead_id_stat, "_upsellio_lead_package", true));
        $pkg_key = $pkg !== "" ? $pkg : "Bez pakietu";
        $lead_packages[$pkg_key] = ($lead_packages[$pkg_key] ?? 0) + 1;
    }
    arsort($lead_packages);
    $base = admin_url("admin.php?page=upsellio-butik-landing-analytics");
    $ga4 = "https://analytics.google.com/";
    ?>
    <div class="wrap btk-an">
      <h1>Analityka landingu — Marketing dla butiku</h1>
      <p style="max-width:780px;color:#50575e;">Każda sekcja i CTA ląduje w GA4 jako event <code>btk_*</code> (kategoria <code>butik_landing</code>) oraz tutaj, żebys mógł wyciągać wnioski bez czekania na raporty Google. Adminzy są wyłączeni z pomiaru — testuj w oknie incognito.</p>
      <p>
        <a href="<?php echo esc_url(home_url("/marketing-dla-butiku/")); ?>" target="_blank" rel="noopener">Otwórz landing</a>
        ·
        <a href="<?php echo esc_url($ga4); ?>" target="_blank" rel="noopener">Google Analytics 4</a>
        · Zdarzenia: <code>btk_section_view</code>, <code>btk_cta_click</code>, <code>btk_tel_click</code>, <code>btk_form_start</code>, <code>btk_faq_open</code>, <code>btk_plan_click</code>, <code>btk_scroll_depth</code>
      </p>
      <p>
        <?php foreach ([7, 14, 30, 90] as $d) : ?>
          <a class="button <?php echo $d === $days ? "button-primary" : ""; ?>" href="<?php echo esc_url(add_query_arg("days", $d, $base)); ?>"><?php echo (int) $d; ?> dni</a>
        <?php endforeach; ?>
        <span style="margin-left:8px;color:#50575e;"><?php echo esc_html($from); ?> — <?php echo esc_html($to); ?></span>
      </p>

      <div class="btk-an-kpis">
        <div><span>Sesje</span><strong><?php echo (int) $session_n; ?></strong></div>
        <div><span>Unikalni</span><strong><?php echo (int) $unique_visitors; ?></strong></div>
        <div><span>Page view</span><strong><?php echo (int) $pageviews; ?></strong></div>
        <div><span>Konwersje (tel + sesja)</span><strong><?php echo (int) $converted_n; ?></strong><small><?php echo esc_html(upsellio_btk_pct($converted_n, $session_n)); ?></small></div>
        <div><span>Leady CRM</span><strong><?php echo (int) $form_crm; ?></strong></div>
        <div><span>Leady ze zgodą (e-mail)</span><strong><?php echo (int) $leads_consented; ?></strong><small><?php echo (int) $leads->found_posts; ?> w okresie</small></div>
        <div><span>Kliknięcia telefonu</span><strong><?php echo (int) $tel; ?></strong></div>
        <div><span>Start formularza</span><strong><?php echo (int) $form_starts; ?></strong></div>
        <div><span>Mailto</span><strong><?php echo (int) $mail; ?></strong></div>
      </div>

      <h2>Wnioski</h2>
      <div class="btk-an-insights">
        <?php foreach ($insights as $item) : ?>
          <article class="is-<?php echo esc_attr((string) $item["level"]); ?>">
            <strong><?php echo esc_html((string) $item["title"]); ?></strong>
            <p><?php echo esc_html((string) $item["text"]); ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <h2>Lejek sekcji</h2>
      <p class="description">Unikalne sesje, które zobaczyły sekcję (≥35% w viewportcie). Spadek = ile osób nie doszło do następnej sekcji.</p>
      <table class="widefat striped" style="max-width:920px;">
        <thead>
          <tr><th>Sekcja</th><th>Sesje</th><th>% wizyt</th><th>Spadek do następnej</th></tr>
        </thead>
        <tbody>
          <?php
          $prev = $session_n;
          $count_order = count($order);
          for ($i = 0; $i < $count_order; $i++) :
              $id = $order[$i];
              $n = (int) $section_unique[$id];
              $next_n = $i < $count_order - 1 ? (int) $section_unique[$order[$i + 1]] : null;
              $drop = $next_n === null ? "—" : ($n > 0 ? upsellio_btk_pct($n - $next_n, $n) : "—");
              ?>
            <tr>
              <td><strong><?php echo esc_html($labels[$id]); ?></strong> <code><?php echo esc_html($id); ?></code></td>
              <td><?php echo (int) $n; ?></td>
              <td><?php echo esc_html(upsellio_btk_pct($n, $session_n)); ?></td>
              <td><?php echo esc_html($drop); ?></td>
            </tr>
              <?php
              $prev = $n;
          endfor;
          ?>
        </tbody>
      </table>

      <div class="btk-an-grid">
        <div>
          <h2>CTA — które przyciski klikają</h2>
          <table class="widefat striped">
            <thead><tr><th>Sekcja</th><th>CTA</th><th>Kliknięcia</th></tr></thead>
            <tbody>
              <?php if (!$cta_rows) : ?>
                <tr><td colspan="3">Brak kliknięć w tym okresie.</td></tr>
              <?php endif; ?>
              <?php foreach ($cta_rows as $row) : ?>
                <tr>
                  <td><?php echo esc_html($labels[$row["section_id"]] ?? $row["section_id"]); ?></td>
                  <td><code><?php echo esc_html((string) $row["extra"]); ?></code></td>
                  <td><?php echo (int) $row["hits"]; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div>
          <h2>Pakiety</h2>
          <table class="widefat striped">
            <thead><tr><th>Pakiet</th><th>Kliknięcia</th></tr></thead>
            <tbody>
              <?php if (!$plan_rows) : ?>
                <tr><td colspan="2">Brak kliknięć w pakiety.</td></tr>
              <?php endif; ?>
              <?php foreach ($plan_rows as $row) : ?>
                <tr><td><?php echo esc_html((string) $row["extra"]); ?></td><td><?php echo (int) $row["hits"]; ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <h2>FAQ</h2>
          <table class="widefat striped">
            <thead><tr><th>Pytanie</th><th>Otwarcia</th></tr></thead>
            <tbody>
              <?php if (!$faq_rows) : ?>
                <tr><td colspan="2">Nikt nie otworzył FAQ.</td></tr>
              <?php endif; ?>
              <?php foreach ($faq_rows as $row) : ?>
                <tr><td><?php echo esc_html((string) $row["extra"]); ?></td><td><?php echo (int) $row["hits"]; ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="btk-an-grid">
        <div>
          <h2>Źródła (UTM)</h2>
          <table class="widefat striped">
            <thead><tr><th>Źródło</th><th>Sesje</th></tr></thead>
            <tbody>
              <?php
              arsort($sources);
              if (!$sources) :
                  ?>
                <tr><td colspan="2">Brak sesji.</td></tr>
              <?php endif; ?>
              <?php foreach ($sources as $name => $n) : ?>
                <tr><td><?php echo esc_html((string) $name); ?></td><td><?php echo (int) $n; ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div>
          <h2>Urządzenia</h2>
          <table class="widefat striped">
            <thead><tr><th>Urządzenie</th><th>Sesje</th><th>%</th></tr></thead>
            <tbody>
              <?php
              arsort($devices);
              foreach ($devices as $name => $n) :
                  ?>
                <tr><td><?php echo esc_html((string) $name); ?></td><td><?php echo (int) $n; ?></td><td><?php echo esc_html(upsellio_btk_pct($n, $session_n)); ?></td></tr>
              <?php endforeach; ?>
              <?php if (!$devices) : ?>
                <tr><td colspan="3">Brak danych.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <h2>Leady z landingu — analiza po zgodzie</h2>
      <p class="description" style="max-width:920px;">Każdy lead z formularza <code>landing-butik-form</code>. Adres e-mail pokazujemy tylko gdy w CRM jest zapisana zgoda na kontakt (<code>_upsellio_lead_consent_at</code>). Bez zgody mail zostaje ukryty.</p>
      <?php if ($lead_packages) : ?>
        <p style="max-width:920px;">Pakiety w leadach:
          <?php
          $pkg_bits = [];
          foreach ($lead_packages as $pkg_name => $pkg_n) {
              $pkg_bits[] = esc_html($pkg_name) . " · " . (int) $pkg_n;
          }
          echo implode(" · ", $pkg_bits);
          ?>
        </p>
      <?php endif; ?>
      <div class="btk-an-leads">
        <?php if (!$leads->have_posts()) : ?>
          <p>Brak leadów z origin <code>landing-butik-form</code> w tym okresie.</p>
        <?php endif; ?>
        <?php foreach ($leads->posts as $lead_id) :
            $lead_id = (int) $lead_id;
            $has_consent = upsellio_btk_lead_has_consent($lead_id);
            $email = sanitize_email((string) get_post_meta($lead_id, "_upsellio_lead_email", true));
            $phone = trim((string) get_post_meta($lead_id, "_upsellio_lead_phone", true));
            $package = trim((string) get_post_meta($lead_id, "_upsellio_lead_package", true));
            $shop_url = trim((string) get_post_meta($lead_id, "_upsellio_lead_shop_url", true));
            $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
            $consent_at = trim((string) get_post_meta($lead_id, "_upsellio_lead_consent_at", true));
            $utm_source = trim((string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true));
            $utm_campaign = trim((string) get_post_meta($lead_id, "_upsellio_lead_utm_campaign", true));
            $message = trim(wp_strip_all_tags((string) get_post_field("post_content", $lead_id)));
            $status = upsellio_btk_lead_status_label($lead_id);
            $analysis = upsellio_btk_analyze_lead($lead_id);
            $edit = get_edit_post_link($lead_id);
            $score_class = $score >= 70 ? "ok" : ($score > 0 && $score < 40 ? "warn" : "info");
            ?>
          <article class="btk-an-lead <?php echo $has_consent ? "has-consent" : "no-consent"; ?>">
            <header>
              <div>
                <strong><?php echo esc_html(get_the_title($lead_id)); ?></strong>
                <span class="btk-an-lead-meta"><?php echo esc_html(get_the_date("Y-m-d H:i", $lead_id)); ?> · <?php echo esc_html($status); ?></span>
              </div>
              <?php if ($score > 0) : ?>
                <span class="btk-an-score is-<?php echo esc_attr($score_class); ?>"><?php echo (int) $score; ?></span>
              <?php endif; ?>
            </header>
            <dl>
              <div>
                <dt>E-mail</dt>
                <dd>
                  <?php if ($has_consent && is_email($email)) : ?>
                    <a href="<?php echo esc_url("mailto:" . $email); ?>"><?php echo esc_html($email); ?></a>
                    <small>zgoda <?php echo esc_html($consent_at); ?></small>
                  <?php else : ?>
                    <em>Ukryty — brak zapisanej zgody</em>
                  <?php endif; ?>
                </dd>
              </div>
              <div>
                <dt>Telefon</dt>
                <dd>
                  <?php if ($has_consent && $phone !== "") : ?>
                    <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $phone)); ?>"><?php echo esc_html($phone); ?></a>
                  <?php elseif ($phone !== "") : ?>
                    <em>Ukryty — brak zgody</em>
                  <?php else : ?>
                    —
                  <?php endif; ?>
                </dd>
              </div>
              <div>
                <dt>Pakiet</dt>
                <dd><?php echo $package !== "" ? esc_html($package) : "nie wybrano"; ?></dd>
              </div>
              <div>
                <dt>Butik</dt>
                <dd><?php echo $shop_url !== "" ? esc_html($shop_url) : "—"; ?></dd>
              </div>
              <div>
                <dt>Źródło</dt>
                <dd><?php echo esc_html(trim($utm_source . " " . $utm_campaign) !== "" ? trim($utm_source . " " . $utm_campaign) : "brak UTM"); ?></dd>
              </div>
            </dl>
            <p class="btk-an-lead-insight"><?php echo esc_html($analysis); ?></p>
            <?php if ($message !== "") : ?>
              <blockquote><?php echo esc_html(wp_trim_words($message, 42, "…")); ?></blockquote>
            <?php endif; ?>
            <?php if ($edit) : ?>
              <a class="button button-small" href="<?php echo esc_url($edit); ?>">Otwórz w CRM</a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ((int) $leads->found_posts > count($leads->posts)) : ?>
        <p class="description">Pokazano <?php echo count($leads->posts); ?> z <?php echo (int) $leads->found_posts; ?> leadów. Zawęź zakres dni albo otwórz CRM.</p>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>
    <style>
      .btk-an-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; max-width:1100px; margin:16px 0 28px; }
      .btk-an-kpis div { background:#fff; border:1px solid #dcdcde; border-radius:12px; padding:14px 16px; }
      .btk-an-kpis span { display:block; color:#646970; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
      .btk-an-kpis strong { display:block; font-size:26px; margin-top:4px; }
      .btk-an-kpis small { color:#646970; }
      .btk-an-insights { display:grid; gap:10px; max-width:920px; margin:0 0 28px; }
      .btk-an-insights article { background:#fff; border-left:4px solid #2271b1; border:1px solid #dcdcde; border-left-width:4px; border-radius:10px; padding:12px 14px; }
      .btk-an-insights article.is-warn { border-left-color:#dba617; }
      .btk-an-insights article.is-ok { border-left-color:#00a32a; }
      .btk-an-insights article.is-info { border-left-color:#2271b1; }
      .btk-an-insights p { margin:6px 0 0; color:#3c434a; }
      .btk-an-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:24px; max-width:1100px; margin:8px 0 28px; }
      .btk-an-leads { display:grid; gap:12px; max-width:920px; margin:12px 0 28px; }
      .btk-an-lead { background:#fff; border:1px solid #dcdcde; border-radius:12px; padding:14px 16px 16px; }
      .btk-an-lead.has-consent { border-left:4px solid #00a32a; }
      .btk-an-lead.no-consent { border-left:4px solid #dba617; }
      .btk-an-lead header { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:10px; }
      .btk-an-lead-meta { display:block; color:#646970; font-size:12px; margin-top:3px; }
      .btk-an-score { display:inline-flex; align-items:center; justify-content:center; min-width:40px; height:40px; border-radius:10px; font-weight:700; background:#f0f0f1; }
      .btk-an-score.is-ok { background:#edfaef; color:#005c12; }
      .btk-an-score.is-warn { background:#fcf9e8; color:#614200; }
      .btk-an-score.is-info { background:#f0f6fc; color:#0a4b78; }
      .btk-an-lead dl { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px 16px; margin:0 0 10px; }
      .btk-an-lead dt { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#646970; margin:0 0 2px; }
      .btk-an-lead dd { margin:0; font-weight:600; }
      .btk-an-lead dd small { display:block; font-weight:400; color:#646970; margin-top:2px; }
      .btk-an-lead dd em { font-style:normal; font-weight:500; color:#646970; }
      .btk-an-lead-insight { margin:0 0 10px; color:#1d2327; line-height:1.5; }
      .btk-an-lead blockquote { margin:0 0 12px; padding:8px 10px; background:#f6f7f7; border-left:3px solid #c3c4c7; color:#3c434a; font-size:13px; }
    </style>
    <?php
}
