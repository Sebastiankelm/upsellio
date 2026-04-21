<?php
if (!defined("ABSPATH")) {
    exit;
}

$ups_error_code = isset($GLOBALS["upsellio_forced_error_code"]) ? (int) $GLOBALS["upsellio_forced_error_code"] : (int) get_query_var("ups_error_code");
if ($ups_error_code <= 0) {
    $ups_error_code = is_404() ? 404 : 500;
}

$ups_error_variants = [
    400 => [
        "label" => "Błędne zapytanie",
        "title" => "Żądanie wymaga poprawki",
        "description" => "Serwer otrzymał nieprawidłowe dane. Sprawdź adres lub wróć do głównej nawigacji.",
        "accent" => "#d97706",
    ],
    401 => [
        "label" => "Brak autoryzacji",
        "title" => "Wymagane logowanie",
        "description" => "Ta sekcja wymaga autoryzacji. Zaloguj się i spróbuj ponownie.",
        "accent" => "#b45309",
    ],
    403 => [
        "label" => "Dostęp zabroniony",
        "title" => "Brak uprawnień do zasobu",
        "description" => "Nie masz dostępu do tej strony. Jeśli to błąd, skontaktuj się z administratorem.",
        "accent" => "#dc2626",
    ],
    404 => [
        "label" => "Nie znaleziono",
        "title" => "Ta strona już tu nie mieszka",
        "description" => "Sprawdź adres URL albo przejdź do portfolio i bloga, aby szybko znaleźć właściwe treści.",
        "accent" => "#2563eb",
    ],
    429 => [
        "label" => "Za dużo żądań",
        "title" => "Zwolnij na chwilę",
        "description" => "Wykonano zbyt wiele zapytań w krótkim czasie. Odczekaj moment i spróbuj ponownie.",
        "accent" => "#7c3aed",
    ],
    500 => [
        "label" => "Błąd serwera",
        "title" => "Coś poszło nie tak po naszej stronie",
        "description" => "Pracujemy nad rozwiązaniem problemu. Spróbuj odświeżyć stronę za kilka minut.",
        "accent" => "#be123c",
    ],
    503 => [
        "label" => "Serwis niedostępny",
        "title" => "Trwa krótka przerwa techniczna",
        "description" => "Usługa jest tymczasowo niedostępna. Wróć za chwilę.",
        "accent" => "#0f766e",
    ],
];

$ups_error_data = isset($ups_error_variants[$ups_error_code]) ? $ups_error_variants[$ups_error_code] : $ups_error_variants[500];
$ups_error_label = (string) $ups_error_data["label"];
$ups_error_title = (string) $ups_error_data["title"];
$ups_error_description = (string) $ups_error_data["description"];
$ups_error_accent = (string) $ups_error_data["accent"];
$ups_error_context = isset($GLOBALS["upsellio_error_context"]) && is_array($GLOBALS["upsellio_error_context"]) ? (array) $GLOBALS["upsellio_error_context"] : [];
$ups_incident_id = isset($ups_error_context["incident_id"]) ? (string) $ups_error_context["incident_id"] : "";
$ups_timestamp_iso = isset($ups_error_context["timestamp_iso"]) ? (string) $ups_error_context["timestamp_iso"] : current_time("c");
$ups_show_support_meta = in_array($ups_error_code, [500, 503], true);
if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("error_modern", [
        "code" => (string) $ups_error_code,
    ]);
}

get_header();
?>
<style>
  .ups-error{
    min-height:calc(100vh - 140px);
    display:flex;
    align-items:center;
    background:radial-gradient(circle at 12% 20%, rgba(37,99,235,.08), transparent 35%), radial-gradient(circle at 88% 78%, rgba(124,58,237,.1), transparent 38%), #f8fafc;
    padding:72px 0;
  }
  .ups-error-wrap{width:min(980px, calc(100% - 48px));margin:0 auto}
  .ups-error-shell{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:28px;
    padding:42px;
    box-shadow:0 24px 60px rgba(15,23,42,.08);
    display:grid;
    grid-template-columns:minmax(0,1fr) 240px;
    gap:32px;
    align-items:center;
  }
  .ups-error-pill{
    display:inline-flex;
    align-items:center;
    gap:8px;
    border:1px solid #d1d5db;
    background:#f9fafb;
    border-radius:999px;
    padding:8px 14px;
    margin-bottom:20px;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#4b5563;
    font-weight:700;
  }
  .ups-error-dot{width:9px;height:9px;border-radius:50%;background:<?php echo esc_attr($ups_error_accent); ?>}
  .ups-error h1{
    margin:0 0 12px;
    font-family:"Syne",sans-serif;
    font-size:clamp(34px,5vw,56px);
    line-height:1.03;
    letter-spacing:-1px;
    color:#0f172a;
  }
  .ups-error p{margin:0 0 24px;color:#475569;line-height:1.7;max-width:620px}
  .ups-error-actions{display:flex;gap:10px;flex-wrap:wrap}
  .ups-error-btn{
    display:inline-flex;align-items:center;justify-content:center;
    padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;border:1px solid transparent;
  }
  .ups-error-btn.primary{background:<?php echo esc_attr($ups_error_accent); ?>;color:#fff}
  .ups-error-btn.ghost{background:#fff;color:#0f172a;border-color:#cbd5e1}
  .ups-error-badge{
    justify-self:end;
    width:220px;height:220px;border-radius:28px;
    background:linear-gradient(160deg, <?php echo esc_attr($ups_error_accent); ?>, #0f172a);
    display:grid;place-items:center;
    color:#fff;font-family:"Syne",sans-serif;font-size:72px;font-weight:800;letter-spacing:-2px;
    box-shadow:inset 0 0 0 1px rgba(255,255,255,.16);
  }
  .ups-error-links{margin-top:22px;display:flex;gap:12px;flex-wrap:wrap}
  .ups-error-links a{font-size:13px;color:#334155;text-decoration:underline;text-decoration-color:#cbd5e1}
  .ups-error-meta{
    margin-top:18px;
    border:1px dashed #cbd5e1;
    border-radius:12px;
    background:#f8fafc;
    padding:12px 14px;
    color:#334155;
    font-size:13px;
    line-height:1.6;
  }
  .ups-error-meta code{
    font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    background:#eef2ff;
    border-radius:6px;
    padding:2px 6px;
  }
  @media (max-width:860px){
    .ups-error-shell{grid-template-columns:1fr;padding:30px}
    .ups-error-badge{justify-self:start;width:132px;height:132px;border-radius:18px;font-size:42px}
  }
</style>

<main class="ups-error">
  <div class="ups-error-wrap">
    <section class="ups-error-shell">
      <div>
        <div class="ups-error-pill"><span class="ups-error-dot"></span><?php echo esc_html($ups_error_label); ?></div>
        <h1><?php echo esc_html($ups_error_title); ?></h1>
        <p><?php echo esc_html($ups_error_description); ?></p>
        <div class="ups-error-actions">
          <a class="ups-error-btn primary" href="<?php echo esc_url(home_url("/")); ?>">Wróć na stronę główną</a>
          <a class="ups-error-btn ghost" href="<?php echo esc_url(home_url("/portfolio-marketingowe/")); ?>">Portfolio marketingowe</a>
        </div>
        <div class="ups-error-links">
          <a href="<?php echo esc_url(home_url("/blog/")); ?>">Blog</a>
          <a href="<?php echo esc_url(home_url("/lead-magnety/")); ?>">Materiały</a>
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Kontakt</a>
        </div>
        <?php if ($ups_show_support_meta) : ?>
          <div class="ups-error-meta">
            <div><strong>Incident ID:</strong> <code><?php echo esc_html($ups_incident_id !== "" ? $ups_incident_id : "N/A"); ?></code></div>
            <div><strong>Timestamp:</strong> <code><?php echo esc_html($ups_timestamp_iso); ?></code></div>
          </div>
        <?php endif; ?>
      </div>
      <div class="ups-error-badge"><?php echo esc_html((string) $ups_error_code); ?></div>
    </section>
  </div>
</main>
<?php
get_footer();
