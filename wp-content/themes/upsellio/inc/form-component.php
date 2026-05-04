<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Główna funkcja renderująca formularz leadowy.
 *
 * @param array $args {
 *   @type string $origin               Identyfikator źródła (wymagane). Np. 'contact-page-form'.
 *   @type string $redirect_url         URL po sukcesie. Domyślnie: bieżąca strona.
 *   @type string $variant              Wariant pól: 'full'|'compact'|'micro'. Domyślnie: 'full'.
 *   @type string $heading              Tytuł nad formularzem.
 *   @type string $subheading           Podtytuł.
 *   @type string $submit_label         Tekst przycisku. Domyślnie: 'Wyślij →'.
 *   @type string $fineprint            Drobny tekst pod przyciskiem.
 *   @type array  $service_options      Opcje selecta usług.
 *   @type string $hidden_service       Ukryte lead_service (bez selecta usług).
 *   @type bool   $show_budget          Czy pole budżetu.
 *   @type array  $budget_options       Mapa wartość => etykieta dla budżetu (nadpisuje domyślne).
 *   @type bool   $show_goal            Pole lead_goal (np. audyt Meta).
 *   @type string $css_class            Dodatkowa klasa CSS na <form>.
 *   @type string $form_id              Atrybut id formularza.
 *   @type string $submit_button_id     Atrybut id przycisku wysyłki.
 *   @type string $preset_message       Pre-wypełniona wiadomość.
 * }
 */
function upsellio_render_lead_form(array $args = [])
{
    $origin = sanitize_key((string) ($args["origin"] ?? "contact-form"));
    $redirect_url = esc_url_raw((string) ($args["redirect_url"] ?? (get_permalink() ?: home_url("/"))));
    $variant = in_array($args["variant"] ?? "full", ["full", "compact", "micro"], true)
        ? $args["variant"]
        : "full";
    $heading = sanitize_text_field((string) ($args["heading"] ?? ""));
    $subheading = sanitize_text_field((string) ($args["subheading"] ?? ""));
    $submit_label = sanitize_text_field((string) ($args["submit_label"] ?? "Wyślij →"));
    $fineprint = sanitize_text_field((string) ($args["fineprint"] ?? ""));
    $show_budget = !empty($args["show_budget"]);
    $show_goal = !empty($args["show_goal"]);
    $css_class = sanitize_html_class((string) ($args["css_class"] ?? ""));
    $preset_msg = sanitize_textarea_field((string) ($args["preset_message"] ?? ""));
    $form_id = isset($args["form_id"]) ? sanitize_html_class((string) $args["form_id"]) : "";
    $submit_button_id = isset($args["submit_button_id"]) ? sanitize_html_class((string) $args["submit_button_id"]) : "";
    $hidden_service = isset($args["hidden_service"]) ? sanitize_text_field((string) $args["hidden_service"]) : "";
    $budget_label = sanitize_text_field((string) ($args["budget_label"] ?? "Orientacyjny budżet (opcjonalnie)"));

    $default_services = [
        "Kampanie Google Ads",
        "Kampanie Meta Ads",
        "Tworzenie strony lub landing page",
        "Marketing + strona (oba)",
        "Nie wiem — chcę porozmawiać",
    ];
    $raw_service_options = isset($args["service_options"]) && is_array($args["service_options"])
        ? $args["service_options"]
        : null;
    $service_options = $raw_service_options !== null && $raw_service_options !== []
        ? $raw_service_options
        : $default_services;

    $default_budget_map = [
        "" => "Wybierz lub pomiń",
        "do 2000 zł" => "do 2 000 zł",
        "2000–5000 zł" => "2 000–5 000 zł",
        "5000–10000 zł" => "5 000–10 000 zł",
        "powyżej 10000 zł" => "powyżej 10 000 zł",
        "nie wiem" => "nie wiem",
    ];
    $budget_options = isset($args["budget_options"]) && is_array($args["budget_options"])
        ? $args["budget_options"]
        : $default_budget_map;

    $goal_options = [
        "Lead generation",
        "Sprzedaż w sklepie",
        "Ruch na stronę",
        "Remarketing",
        "Nie jestem pewien",
    ];

    $status = isset($_GET["ups_lead_status"])
        ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"]))
        : "";
    $reason = isset($_GET["ups_lead_reason"])
        ? sanitize_key(wp_unslash($_GET["ups_lead_reason"]))
        : "";

    $error_messages = [
        "nonce" => "Sesja wygasła. Odśwież stronę (F5) i wyślij ponownie.",
        "fields" => "Uzupełnij wymagane pola (imię, e-mail, wiadomość) i zaznacz zgodę.",
        "rate" => "Zbyt wiele prób. Spróbuj ponownie za godzinę lub napisz na kontakt@upsellio.pl.",
        "save" => "Błąd serwera. Napisz bezpośrednio na kontakt@upsellio.pl.",
    ];
    $error_text = $error_messages[$reason] ?? "Nie udało się wysłać. Spróbuj ponownie.";

    ob_start();
    ?>
    <form
        class="ups-form ups-form--<?php echo esc_attr($variant); ?><?php echo $variant === "compact" ? " hr-contact-form" : ""; ?><?php echo $css_class !== "" ? " " . esc_attr($css_class) : ""; ?>"
        method="post"
        action="<?php echo esc_url(admin_url("admin-post.php")); ?>"
        novalidate
        data-upsellio-lead-form="1"
        data-upsellio-server-form="1"
        <?php echo $form_id !== "" ? ' id="' . esc_attr($form_id) . '"' : ""; ?>
    >
        <input type="hidden" name="action" value="upsellio_submit_lead" />
        <input type="hidden" name="redirect_url" value="<?php echo esc_url($redirect_url); ?>" />
        <input type="hidden" name="lead_form_origin" value="<?php echo esc_attr($origin); ?>" />
        <input type="hidden" name="lead_source" value="<?php echo esc_attr($origin); ?>" />
        <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
        <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
        <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
        <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
        <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
        <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off"
               style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true" />
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>

        <?php if ($hidden_service !== "") : ?>
            <input type="hidden" name="lead_service" value="<?php echo esc_attr($hidden_service); ?>" />
        <?php endif; ?>

        <?php if ($heading !== "") : ?>
            <?php if ($variant === "compact") : ?>
            <div class="hr-contact-head">
                <div class="hr-eyebrow"><?php esc_html_e("Kontakt", "upsellio"); ?></div>
                <h2 class="hr-contact-title"><?php echo esc_html($heading); ?></h2>
                <?php if ($subheading !== "") : ?>
                    <p class="hr-contact-lead"><?php echo esc_html($subheading); ?></p>
                <?php endif; ?>
            </div>
            <?php else : ?>
            <div class="ups-form__head">
                <strong class="ups-form__title"><?php echo esc_html($heading); ?></strong>
                <?php if ($subheading !== "") : ?>
                    <p class="ups-form__sub"><?php echo esc_html($subheading); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($status === "success") : ?>
            <div class="ups-form__notice ups-form__notice--ok" role="alert">
                Dziękuję! Wiadomość dotarła. Odezwę się w ciągu 24h.
            </div>
        <?php elseif ($status === "error") : ?>
            <div class="ups-form__notice ups-form__notice--err" role="alert">
                <?php echo esc_html($error_text); ?>
            </div>
        <?php endif; ?>

        <?php if ($variant === "micro") : ?>
            <div class="ups-form__row">
                <label class="ups-form__label" for="ups-f-email-<?php echo esc_attr($origin); ?>">
                    E-mail firmowy <span aria-hidden="true">*</span>
                </label>
                <input class="ups-form__input" id="ups-f-email-<?php echo esc_attr($origin); ?>"
                       type="email" name="lead_email" placeholder="kontakt@firma.pl"
                       autocomplete="email" required />
            </div>
            <input type="hidden" name="lead_name" value="Szybka analiza" />
            <input type="hidden" name="lead_message" value="Prośba o szybką analizę strony." />
            <input type="hidden" name="lead_consent" value="1" />

        <?php elseif ($variant === "compact") : ?>
            <?php if ($hidden_service !== "") : ?>
                <input type="hidden" name="lead_service" value="<?php echo esc_attr($hidden_service); ?>" />
            <?php endif; ?>
            <div class="hr-contact-grid">
                <div class="hr-contact-field">
                    <label for="ups-f-name-<?php echo esc_attr($origin); ?>">
                        Imię <span aria-hidden="true">*</span>
                    </label>
                    <input id="ups-f-name-<?php echo esc_attr($origin); ?>"
                           type="text" name="lead_name" placeholder="Sebastian"
                           autocomplete="given-name" required />
                </div>
                <div class="hr-contact-field">
                    <label for="ups-f-email-<?php echo esc_attr($origin); ?>">
                        E-mail <span aria-hidden="true">*</span>
                    </label>
                    <input id="ups-f-email-<?php echo esc_attr($origin); ?>"
                           type="email" name="lead_email" placeholder="kontakt@firma.pl"
                           autocomplete="email" required />
                </div>
                <div class="hr-contact-field full">
                    <label for="ups-f-phone-<?php echo esc_attr($origin); ?>">Telefon (opcjonalnie)</label>
                    <input id="ups-f-phone-<?php echo esc_attr($origin); ?>"
                           type="tel" name="lead_phone" placeholder="+48..."
                           autocomplete="tel" />
                </div>
                <div class="hr-contact-field full">
                    <label for="ups-f-msg-<?php echo esc_attr($origin); ?>">
                        Wiadomość <span aria-hidden="true">*</span>
                    </label>
                    <textarea id="ups-f-msg-<?php echo esc_attr($origin); ?>"
                              name="lead_message" rows="4" required><?php echo esc_textarea($preset_msg); ?></textarea>
                </div>
                <div class="hr-contact-field full">
                    <label class="hr-contact-consent">
                        <input type="checkbox" name="lead_consent" value="1" required />
                        <span>Wyrażam zgodę na kontakt w sprawie przesłanego zapytania.</span>
                    </label>
                </div>
            </div>

        <?php else : ?>
            <div class="ups-form__row-2">
                <div>
                    <label class="ups-form__label" for="ups-f-name-<?php echo esc_attr($origin); ?>">
                        Imię <span aria-hidden="true">*</span>
                    </label>
                    <input class="ups-form__input" id="ups-f-name-<?php echo esc_attr($origin); ?>"
                           type="text" name="lead_name" placeholder="Sebastian"
                           autocomplete="given-name" required />
                </div>
                <div>
                    <label class="ups-form__label" for="ups-f-company-<?php echo esc_attr($origin); ?>">
                        Firma
                    </label>
                    <input class="ups-form__input" id="ups-f-company-<?php echo esc_attr($origin); ?>"
                           type="text" name="lead_company" placeholder="Nazwa firmy"
                           autocomplete="organization" />
                </div>
            </div>
            <div class="ups-form__row-2">
                <div>
                    <label class="ups-form__label" for="ups-f-email-<?php echo esc_attr($origin); ?>">
                        E-mail firmowy <span aria-hidden="true">*</span>
                    </label>
                    <input class="ups-form__input" id="ups-f-email-<?php echo esc_attr($origin); ?>"
                           type="email" name="lead_email" placeholder="kontakt@firma.pl"
                           autocomplete="email" required />
                </div>
                <div>
                    <label class="ups-form__label" for="ups-f-phone-<?php echo esc_attr($origin); ?>">
                        Telefon (opcjonalnie)
                    </label>
                    <input class="ups-form__input" id="ups-f-phone-<?php echo esc_attr($origin); ?>"
                           type="tel" name="lead_phone" placeholder="+48..."
                           autocomplete="tel" />
                </div>
            </div>
            <?php if ($hidden_service === "" && !empty($service_options)) : ?>
                <label class="ups-form__label" for="ups-f-service-<?php echo esc_attr($origin); ?>">
                    Czego szukasz?
                </label>
                <select class="ups-form__select" id="ups-f-service-<?php echo esc_attr($origin); ?>"
                        name="lead_service">
                    <option value="">Wybierz obszar</option>
                    <?php foreach ($service_options as $opt) : ?>
                        <option value="<?php echo esc_attr((string) $opt); ?>">
                            <?php echo esc_html((string) $opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if ($show_budget) : ?>
                <label class="ups-form__label" for="ups-f-budget-<?php echo esc_attr($origin); ?>">
                    <?php echo esc_html($budget_label); ?>
                </label>
                <select class="ups-form__select" id="ups-f-budget-<?php echo esc_attr($origin); ?>"
                        name="lead_budget">
                    <?php foreach ($budget_options as $val => $label) : ?>
                        <option value="<?php echo esc_attr((string) $val); ?>">
                            <?php echo esc_html((string) $label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if ($show_goal) : ?>
                <label class="ups-form__label" for="ups-f-goal-<?php echo esc_attr($origin); ?>">
                    Cel kampanii
                </label>
                <select class="ups-form__select" id="ups-f-goal-<?php echo esc_attr($origin); ?>"
                        name="lead_goal">
                    <option value="">— wybierz —</option>
                    <?php foreach ($goal_options as $gopt) : ?>
                        <option value="<?php echo esc_attr($gopt); ?>"><?php echo esc_html($gopt); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <label class="ups-form__label" for="ups-f-msg-<?php echo esc_attr($origin); ?>">
                Wiadomość <span aria-hidden="true">*</span>
            </label>
            <textarea class="ups-form__textarea" id="ups-f-msg-<?php echo esc_attr($origin); ?>"
                      name="lead_message"
                      placeholder="Krótko opisz sytuację: co działa, co nie, jaki jest cel."
                      rows="5" required><?php echo esc_textarea($preset_msg); ?></textarea>
            <label class="ups-form__consent">
                <input type="checkbox" name="lead_consent" value="1" required />
                <span>Wyrażam zgodę na kontakt w sprawie przesłanego zapytania.</span>
            </label>
        <?php endif; ?>

        <button
            type="submit"
            class="<?php echo $variant === "compact" ? "btn btn-primary hr-contact-submit" : "ups-form__submit"; ?>"
            <?php echo $submit_button_id !== "" ? ' id="' . esc_attr($submit_button_id) . '"' : ""; ?>
        >
            <?php echo esc_html($submit_label); ?>
        </button>
        <?php if ($fineprint !== "") : ?>
            <p class="ups-form__fineprint"><?php echo esc_html($fineprint); ?></p>
        <?php endif; ?>
    </form>
    <?php
    return (string) ob_get_clean();
}

/**
 * Shortcode: [upsellio_contact_form]
 *
 * @param array $atts Atrybuty shortcode.
 */
function upsellio_contact_form_shortcode($atts)
{
    $atts = shortcode_atts(
        [
            "variant" => "compact",
            "heading" => "Chcesz, żebym przeanalizował Twoją sytuację?",
            "subheading" => "Wyślij krótką wiadomość. Otrzymasz konkretną odpowiedź.",
            "submit_label" => "Wyślij wiadomość",
            "origin" => "blog-form",
        ],
        $atts,
        "upsellio_contact_form"
    );

    $form_html = upsellio_render_lead_form([
        "origin" => $atts["origin"],
        "variant" => $atts["variant"],
        "heading" => $atts["heading"],
        "subheading" => $atts["subheading"],
        "submit_label" => $atts["submit_label"],
        "redirect_url" => get_permalink(get_the_ID()) ?: home_url("/"),
    ]);

    return '<div class="hr-contact-shell ups-inline-contact" id="kontakt-wpis">' . $form_html . "</div>";
}
add_shortcode("upsellio_contact_form", "upsellio_contact_form_shortcode");
