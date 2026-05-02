<?php

if (!defined('ABSPATH')) {
    exit;
}

// ─────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────

function upsellio_ai_tests_pass(string $label, string $detail = ''): array {
    return ['status' => 'pass', 'label' => $label, 'detail' => $detail];
}
function upsellio_ai_tests_warn(string $label, string $detail = ''): array {
    return ['status' => 'warn', 'label' => $label, 'detail' => $detail];
}
function upsellio_ai_tests_fail(string $label, string $detail = ''): array {
    return ['status' => 'fail', 'label' => $label, 'detail' => $detail];
}
function upsellio_ai_tests_info(string $label, string $detail = ''): array {
    return ['status' => 'info', 'label' => $label, 'detail' => $detail];
}

// ─────────────────────────────────────────────
// GRUPY TESTÓW
// ─────────────────────────────────────────────

function upsellio_ai_test_group_api(): array {
    $results = [];

    // 1. Klucz API
    $key = '';
    if (defined('UPSELLIO_ANTHROPIC_API_KEY') && (string) UPSELLIO_ANTHROPIC_API_KEY !== '') {
        $key = (string) UPSELLIO_ANTHROPIC_API_KEY;
        $results[] = upsellio_ai_tests_pass('Klucz API', 'Załadowany ze stałej UPSELLIO_ANTHROPIC_API_KEY w wp-config.php');
    } else {
        $key = trim((string) get_option('ups_anthropic_api_key', ''));
        if ($key !== '') {
            $results[] = upsellio_ai_tests_pass('Klucz API', 'Załadowany z wp_options (ups_anthropic_api_key)');
        } else {
            $results[] = upsellio_ai_tests_fail('Klucz API', 'Brak klucza. Dodaj UPSELLIO_ANTHROPIC_API_KEY w wp-config.php lub wpisz w CRM → Ustawienia → Ogólne.');
        }
    }

    // 2. Format klucza
    if ($key !== '') {
        if (str_starts_with($key, 'sk-ant-')) {
            $masked = substr($key, 0, 14) . str_repeat('*', max(4, strlen($key) - 18)) . substr($key, -4);
            $results[] = upsellio_ai_tests_pass('Format klucza', $masked);
        } else {
            $results[] = upsellio_ai_tests_fail('Format klucza', 'Klucz nie zaczyna się od "sk-ant-". Sprawdź czy to właściwy klucz Anthropic.');
        }
    }

    // 3. Model
    $model = trim((string) get_option('ups_anthropic_model', ''));
    if ($model === '') {
        $model = defined('UPSELLIO_ANTHROPIC_DEFAULT_MODEL') ? (string) UPSELLIO_ANTHROPIC_DEFAULT_MODEL : 'claude-haiku-4-5-20251001';
        $results[] = upsellio_ai_tests_warn('Model CRM', "Brak ustawienia — używa domyślnego: {$model}");
    } else {
        $crm_effective = function_exists('upsellio_anthropic_crm_normalize_model_id')
            ? upsellio_anthropic_crm_normalize_model_id($model)
            : $model;
        if (strtolower($crm_effective) !== strtolower($model)) {
            $results[] = upsellio_ai_tests_pass('Model CRM', "W opcji: {$model} — do API idzie: {$crm_effective}");
        } else {
            $results[] = upsellio_ai_tests_pass('Model CRM', $model);
        }
    }

    $blog_model = trim((string) get_option('ups_blog_bot_model', ''));
    if ($blog_model === '') {
        $results[] = upsellio_ai_tests_warn('Model Blog Bot', 'Brak — używa domyślnego: claude-haiku-4-5-20251001');
    } else {
        $blog_effective = function_exists('upsellio_anthropic_crm_normalize_model_id')
            ? upsellio_anthropic_crm_normalize_model_id($blog_model)
            : $blog_model;
        if (strtolower($blog_effective) !== strtolower($blog_model)) {
            $results[] = upsellio_ai_tests_pass(
                'Model Blog Bot',
                "W opcji: {$blog_model} — do API idzie: {$blog_effective} (poprawka nieistniejącego snapshotu)"
            );
        } else {
            $results[] = upsellio_ai_tests_pass('Model Blog Bot', $blog_model);
        }
    }

    // 4. Żywy ping do API
    if ($key !== '') {
        $ping = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'timeout' => 18,
            'headers' => [
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ],
            'body' => wp_json_encode([
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 20,
                'messages'   => [['role' => 'user', 'content' => 'Odpowiedz jednym słowem: OK']],
            ]),
        ]);

        if (is_wp_error($ping)) {
            $results[] = upsellio_ai_tests_fail('Ping Anthropic API', 'Błąd sieci: ' . $ping->get_error_message());
        } else {
            $code = (int) wp_remote_retrieve_response_code($ping);
            $body = json_decode((string) wp_remote_retrieve_body($ping), true);
            if ($code === 200 && is_array($body)) {
                $text = '';
                foreach ((array) ($body['content'] ?? []) as $block) {
                    if (($block['type'] ?? '') === 'text') {
                        $text .= $block['text'];
                    }
                }
                $results[] = upsellio_ai_tests_pass('Ping Anthropic API', "HTTP 200 — odpowiedź: \"{$text}\"");
            } elseif ($code === 401) {
                $results[] = upsellio_ai_tests_fail('Ping Anthropic API', 'HTTP 401 — klucz API nieprawidłowy lub wygasły.');
            } elseif ($code === 429) {
                $results[] = upsellio_ai_tests_warn('Ping Anthropic API', 'HTTP 429 — limit zapytań. Klucz działa, ale przekroczono rate limit.');
            } else {
                $err = is_array($body) ? (string) ($body['error']['message'] ?? '') : '';
                $results[] = upsellio_ai_tests_fail('Ping Anthropic API', "HTTP {$code}" . ($err !== '' ? " — {$err}" : ''));
            }
        }
    } else {
        $results[] = upsellio_ai_tests_info('Ping Anthropic API', 'Pominięto — brak klucza API.');
    }

    return $results;
}

function upsellio_ai_test_group_prompts(): array {
    $results = [];

    $ctx = trim((string) get_option('ups_ai_company_context', ''));
    if ($ctx === '') {
        $results[] = upsellio_ai_tests_fail('Kontekst firmy', 'Pole puste. Bez kontekstu każdy prompt działa "w ciemno" — AI nie wie kim jesteś. Uzupełnij w CRM → Ustawienia → AI.');
    } elseif (mb_strlen($ctx) < 80) {
        $results[] = upsellio_ai_tests_warn('Kontekst firmy', 'Bardzo krótki (' . mb_strlen($ctx) . ' znaków). Rekomendowane min. 200 znaków z opisem specjalizacji i stylu.');
    } else {
        $results[] = upsellio_ai_tests_pass('Kontekst firmy', mb_strlen($ctx) . ' znaków — OK');
    }

    $prompts = [
        'ups_ai_prompt_lead_scoring' => ['Prompt scoringu leada', '{lead_blob}'],
        'ups_ai_prompt_inbox_draft'  => ['Prompt draft Inbox',   '{thread}'],
        'ups_ai_prompt_followup'     => ['Prompt follow-up',     '{hours_silence}'],
        'ups_ai_prompt_blog_post'    => ['Prompt blog bot',      '{keyword}'],
    ];

    foreach ($prompts as $opt => [$label, $required_var]) {
        $val = trim((string) get_option($opt, ''));
        if ($val === '') {
            $results[] = upsellio_ai_tests_warn($label, 'Puste — używa domyślnego szablonu z kodu. Działa, ale nie jest dostosowany do Twojego stylu.');
        } elseif (strpos($val, $required_var) === false) {
            $results[] = upsellio_ai_tests_warn($label, "Brak wymaganego placeholdera {$required_var} — AI nie dostanie kluczowych danych.");
        } else {
            $results[] = upsellio_ai_tests_pass($label, mb_strlen($val) . ' znaków, placeholder ' . $required_var . ' obecny');
        }
    }

    // Prompt Caching możliwy?
    if ($ctx !== '' && mb_strlen($ctx) > 100) {
        $results[] = upsellio_ai_tests_pass('Prompt Caching', 'Kontekst firmy jest wystarczająco długi — Prompt Caching aktywny przy wywołaniach blog bota.');
    } else {
        $results[] = upsellio_ai_tests_info('Prompt Caching', 'Nieaktywny — wymaga niepustego kontekstu firmy > 100 znaków.');
    }

    return $results;
}

function upsellio_ai_test_group_features(): array {
    $results = [];

    $features = [
        ['ups_anthropic_wp_lead_form_enabled', 'Scoring leadów z formularza (AI)'],
        ['ups_anthropic_inbox_draft_enabled',   'Przycisk ✨ draft w Inbox'],
        ['ups_anthropic_inbox_auto_followup_enabled', 'Automatyczny follow-up (cron)'],
        ['ups_blog_bot_enabled',                'Blog Bot'],
    ];

    foreach ($features as [$opt, $label]) {
        $val = (string) get_option($opt, '0');
        if ($val === '1') {
            $results[] = upsellio_ai_tests_pass($label, 'Włączona');
        } else {
            $results[] = upsellio_ai_tests_warn($label, 'Wyłączona — włącz w CRM → Ustawienia → AI gdy gotowa do użycia');
        }
    }

    // Follow-up hours
    $fu_hours = (int) get_option('ups_anthropic_inbox_auto_followup_hours', 24);
    if ($fu_hours < 6) {
        $results[] = upsellio_ai_tests_warn('Follow-up — próg ciszy', "{$fu_hours}h — bardzo krótki. Minimalne 6h, rekomendowane 24h.");
    } else {
        $results[] = upsellio_ai_tests_pass('Follow-up — próg ciszy', "{$fu_hours}h");
    }

    return $results;
}

function upsellio_ai_test_group_blogbot(): array {
    $results = [];

    // Blog bot aktywny
    $enabled = (string) get_option('ups_blog_bot_enabled', '0') === '1';

    // Kolejka fraz
    $queue_raw = (string) get_option('ups_blog_bot_keywords_queue', '');
    $queue_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', $queue_raw)), fn($l) => $l !== ''));
    $queue_count = count($queue_lines);

    if ($queue_count === 0) {
        $results[] = upsellio_ai_tests_fail('Kolejka tematów', 'Pusta! Bot nie ma o czym pisać — email powiadomień zostanie wysłany zamiast draftu. Dodaj tematy w CRM → Ustawienia → AI.');
    } elseif ($queue_count < 3) {
        $results[] = upsellio_ai_tests_warn('Kolejka tematów', "{$queue_count} temat — wystarczy na kilka uruchomień. Rekomendowane min. 10.");
        $results[] = upsellio_ai_tests_info('Kolejny temat do napisania', '"' . $queue_lines[0] . '"');
    } else {
        $results[] = upsellio_ai_tests_pass('Kolejka tematów', "{$queue_count} tematów w kolejce");
        $results[] = upsellio_ai_tests_info('Kolejny temat do napisania', '"' . $queue_lines[0] . '"');
    }

    // Użyte tematy
    $used_raw = (string) get_option('ups_blog_bot_keywords_used', '');
    $used_lines = array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', $used_raw)), fn($l) => $l !== '');
    $used_count = count($used_lines);
    if ($used_count > 0) {
        $results[] = upsellio_ai_tests_info('Użyte tematy (historia)', "{$used_count} tematów już wygenerowanych");
    }

    // Ostatnie uruchomienie
    $last_run = (string) get_option('ups_blog_bot_last_run', '');
    if ($last_run === '') {
        $results[] = upsellio_ai_tests_info('Ostatnie uruchomienie', 'Bot nie uruchamiał się jeszcze. Użyj przycisku "Uruchom ręcznie" poniżej.');
    } else {
        $results[] = upsellio_ai_tests_pass('Ostatnie uruchomienie', $last_run);
    }

    // Ostatni draft
    $last_draft_id = (int) get_option('ups_blog_bot_last_draft_id', 0);
    if ($last_draft_id > 0) {
        $post = get_post($last_draft_id);
        if ($post instanceof WP_Post) {
            $edit_url = get_edit_post_link($last_draft_id, 'raw');
            $kw = (string) get_post_meta($last_draft_id, '_ups_blog_bot_keyword', true);
            $status_label = $post->post_status === 'draft' ? 'szkic' : $post->post_status;
            $results[] = upsellio_ai_tests_pass('Ostatni wygenerowany draft', "\"{$post->post_title}\" [{$status_label}]" . ($kw !== '' ? " | fraza: \"{$kw}\"" : '') . " | " . $edit_url);
        }
    }

    // Harmonogram cron
    $cron_ts = wp_next_scheduled('upsellio_blog_bot_cron_run');
    if ($cron_ts) {
        $human = wp_date('d.m.Y H:i', $cron_ts);
        $results[] = upsellio_ai_tests_pass('Harmonogram WP-Cron', "Następne uruchomienie: {$human}");
    } elseif ($enabled) {
        $results[] = upsellio_ai_tests_fail('Harmonogram WP-Cron', 'Bot jest włączony, ale zadanie cron NIE jest zaplanowane. Odśwież stronę lub wywołaj init przez odwiedzenie witryny.');
    } else {
        $results[] = upsellio_ai_tests_info('Harmonogram WP-Cron', 'Bot wyłączony — cron nieaktywny.');
    }

    // Powiadomienia email
    $notify = (string) get_option('ups_blog_bot_notify_email', '');
    if ($notify === '') {
        $results[] = upsellio_ai_tests_warn('Email powiadomień', 'Brak — nie dostaniesz informacji gdy draft będzie gotowy.');
    } elseif (!is_email($notify)) {
        $results[] = upsellio_ai_tests_fail('Email powiadomień', "Nieprawidłowy email: \"{$notify}\"");
    } else {
        $results[] = upsellio_ai_tests_pass('Email powiadomień', $notify);
    }

    // Kategoria
    $cat_id = (int) get_option('ups_blog_bot_category', 0);
    if ($cat_id === 0) {
        $results[] = upsellio_ai_tests_warn('Kategoria draftów', 'Brak — drafty trafiają bez kategorii. Trudniej je filtrować.');
    } else {
        $term = get_term($cat_id, 'category');
        $cat_name = ($term instanceof WP_Term) ? $term->name : "ID #{$cat_id}";
        $results[] = upsellio_ai_tests_pass('Kategoria draftów', $cat_name);
    }

    return $results;
}

function upsellio_ai_test_group_data(): array {
    $results = [];

    // GSC dane
    $gsc_rows = get_option('upsellio_keyword_metrics_rows', []);
    $gsc_count = is_array($gsc_rows) ? count($gsc_rows) : 0;
    $gsc_source = (string) get_option('upsellio_keyword_metrics_source', '');
    $gsc_sync   = (string) get_option('upsellio_keyword_metrics_last_sync', '');

    if ($gsc_count === 0) {
        $results[] = upsellio_ai_tests_warn('Dane GSC (frazy/pozycje)', 'Brak danych. AI blog bot i analityka nie wiedzą które frazy konwertują. Podłącz GSC według instrukcji.');
        $results[] = upsellio_ai_tests_info('Wpływ braku GSC', 'Blog bot działa, ale pisze "w ciemno" bez wiedzy o frazach z pozycjami 11–20 wartych rozbudowy.');
    } else {
        $source_label = match($gsc_source) {
            'gsc_service_account' => 'Service Account (automatyczny sync)',
            'gsc_live'            => 'OAuth live sync',
            'csv_import'          => 'Ręczny import CSV',
            default               => $gsc_source ?: 'nieznane',
        };
        $results[] = upsellio_ai_tests_pass('Dane GSC', "{$gsc_count} rekordów | źródło: {$source_label}");
        if ($gsc_sync !== '') {
            $results[] = upsellio_ai_tests_pass('Ostatni sync GSC', $gsc_sync);
        }

        // Top 3 frazy
        $top = array_slice($gsc_rows, 0, 3);
        usort($top, fn($a,$b) => (int)$b['clicks'] <=> (int)$a['clicks']);
        foreach ($top as $row) {
            $results[] = upsellio_ai_tests_info(
                'Top fraza: "' . $row['keyword'] . '"',
                "poz. {$row['position']} | {$row['clicks']} kliknięć | CTR {$row['ctr']}%"
            );
        }
    }

    // GA4 channel scores
    $ga4_scores = get_option('ups_automation_channel_quality_scores', []);
    $ga4_count  = is_array($ga4_scores) ? count($ga4_scores) : 0;
    $ga4_sync   = (string) get_option('ups_automation_ga4_last_sync', '');
    $ga4_enabled = (string) get_option('ups_automation_ga4_sync_enabled', '0') === '1';

    if ($ga4_count === 0) {
        if ($ga4_enabled) {
            $results[] = upsellio_ai_tests_warn('Dane GA4 (kanały)', 'Flaga włączona, ale brak danych. Użyj WordPress: Wpisy → Analityka SEO → sekcja „GA4 — kanały do CRM” (OAuth) albo endpoint REST / skrypt ga4-sync.py — sprawdź logi GSC/GA4 w tym panelu.');
        } else {
            $results[] = upsellio_ai_tests_info('Dane GA4 (kanały)', 'Brak — flaga ups_automation_ga4_sync_enabled wyłączona. Scoring nie zna jakości kanałów. Działa poprawnie bez GA4.');
        }
        $results[] = upsellio_ai_tests_info('Wpływ braku GA4', 'Scoring leadów nie wie czy lead pochodzi z dobrej kampanii. Nadal działa — po prostu nie ma tej informacji.');
    } else {
        $results[] = upsellio_ai_tests_pass('Dane GA4 (channel scores)', "{$ga4_count} kanałów");
        if ($ga4_sync !== '') {
            $results[] = upsellio_ai_tests_pass('Ostatni sync GA4', $ga4_sync);
        }
        // Top kanał
        $best = null;
        foreach ($ga4_scores as $s) {
            if ($best === null || (int)$s['score'] > (int)$best['score']) { $best = $s; }
        }
        if ($best) {
            $results[] = upsellio_ai_tests_info('Najlepszy kanał', "{$best['source']} / {$best['campaign']} — score {$best['score']}/100 | {$best['sessions']} sesji");
        }
    }

    // REST endpoint GSC dostępny?
    $rest_url = rest_url('upsellio/v1/gsc-keywords');
    $results[] = upsellio_ai_tests_info('Endpoint REST GSC', $rest_url . ' (POST, wymaga X-Upsellio-Secret)');

    $rest_url_ga4 = rest_url('upsellio/v1/ga4-aggregate');
    $results[] = upsellio_ai_tests_info('Endpoint REST GA4', $rest_url_ga4 . ' (POST, wymaga X-Upsellio-Secret)');

    // Secret key (pusty = auto-ustawiany przy admin_init dla administratora — followups.php)
    $secret = (string) get_option('ups_followup_inbound_secret', '');
    if ($secret === '') {
        $results[] = upsellio_ai_tests_fail('Tajny klucz REST (ups_followup_inbound_secret)', 'Brak — odśwież stronę (sekret ustawia się przy pierwszym wejściu admina do panelu) lub ustaw ręcznie: wp option update ups_followup_inbound_secret "$(openssl rand -hex 32)"');
    } else {
        $masked = substr($secret, 0, 6) . str_repeat('*', max(4, strlen($secret) - 10)) . substr($secret, -4);
        $results[] = upsellio_ai_tests_pass('Tajny klucz REST', $masked);
    }

    return $results;
}

function upsellio_ai_test_group_crm(): array {
    $results = [];

    // Lead scoring — ostatnie wyniki
    $leads = get_posts([
        'post_type'      => 'lead',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ]);

    if (empty($leads)) {
        $results[] = upsellio_ai_tests_info('Leady w CRM', 'Brak leadów — scoring nie miał jeszcze co oceniać.');
    } else {
        $with_score = 0;
        $without_score = 0;
        $scores = [];
        foreach ($leads as $lid) {
            $s = get_post_meta((int)$lid, '_upsellio_lead_score', true);
            if ($s !== '' && $s !== false) {
                $with_score++;
                $scores[] = (int)$s;
            } else {
                $without_score++;
            }
        }
        $results[] = upsellio_ai_tests_info('Ostatnie 5 leadów', "ze scoringiem AI: {$with_score} | bez: {$without_score}");
        if (!empty($scores)) {
            $avg = round(array_sum($scores) / count($scores));
            $results[] = upsellio_ai_tests_info('Średni score (ostatnie 5)', "{$avg}/100");
        }
        if ($without_score > 0 && (string)get_option('ups_anthropic_wp_lead_form_enabled','0') === '1') {
            $results[] = upsellio_ai_tests_warn('Leady bez scoringu', "{$without_score} leadów nie ma wyniku AI — być może błąd API lub lead dodany przed włączeniem funkcji.");
        }
    }

    // Inbox — oferty z AI follow-up
    $offers_with_fu = get_posts([
        'post_type'      => 'crm_offer',
        'post_status'    => ['publish', 'private'],
        'posts_per_page' => 3,
        'fields'         => 'ids',
        'meta_query'     => [['key' => '_ups_offer_ai_fu_sent_msg_id', 'compare' => 'EXISTS']],
    ]);
    if (!empty($offers_with_fu)) {
        $results[] = upsellio_ai_tests_pass('Auto follow-up AI', count($offers_with_fu) . ' ofert otrzymało automatyczny follow-up');
    } else {
        $results[] = upsellio_ai_tests_info('Auto follow-up AI', 'Brak wysłanych — funkcja nie uruchomiła się lub brak wątków czekających na odpowiedź > próg godzin.');
    }

    // Channel quality scores na ofertach
    $offers_with_ch = get_posts([
        'post_type'      => 'crm_offer',
        'post_status'    => ['publish', 'private'],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [['key' => '_ups_offer_channel_quality_score', 'compare' => 'EXISTS']],
    ]);
    if (!empty($offers_with_ch)) {
        $results[] = upsellio_ai_tests_pass('GA4 channel score na ofertach', 'Przynajmniej jedna oferta ma przypisany score kanału z GA4');
    } else {
        $results[] = upsellio_ai_tests_info('GA4 channel score na ofertach', 'Brak — wymaga danych GA4 + uruchomienia upsellio_automation_sync_ga4_channel_quality()');
    }

    return $results;
}

// ─────────────────────────────────────────────
// RĘCZNE URUCHOMIENIE BLOG BOTA (AJAX)
// ─────────────────────────────────────────────

function upsellio_ai_tests_run_blog_bot_ajax(): void {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }
    check_ajax_referer('upsellio_ai_tests_nonce', 'nonce');

    if (!function_exists('upsellio_blog_bot_generate_and_save')) {
        wp_send_json_error(['message' => 'Funkcja upsellio_blog_bot_generate_and_save() nie istnieje. Sprawdź czy inc/anthropic-blog-bot.php jest zaincludowany w functions.php.']);
    }

    $keyword = function_exists('upsellio_blog_bot_peek_keyword') ? upsellio_blog_bot_peek_keyword() : '';
    if ($keyword === '') {
        wp_send_json_error(['message' => 'Kolejka tematów jest pusta — dodaj tematy w CRM → Ustawienia → AI → Kolejka tematów.']);
    }

    $before_id = (int) get_option('ups_blog_bot_last_draft_id', 0);
    upsellio_blog_bot_generate_and_save();
    $after_id = (int) get_option('ups_blog_bot_last_draft_id', 0);

    if ($after_id > 0 && $after_id !== $before_id) {
        $post = get_post($after_id);
        $edit  = get_edit_post_link($after_id, 'raw');
        wp_send_json_success([
            'message'  => "Draft utworzony dla frazy: \"{$keyword}\"",
            'draft_id' => $after_id,
            'title'    => $post instanceof WP_Post ? $post->post_title : '',
            'edit_url' => $edit,
        ]);
    } else {
        $last_run = (string) get_option('ups_blog_bot_last_run', '');
        $diag = get_option('ups_blog_bot_last_error', null);
        $diag_txt = '';
        if (is_array($diag) && !empty($diag['code'])) {
            $labels = [
                'disabled'       => 'Blog Bot wyłączony',
                'no_api_key'     => 'Brak klucza Anthropic',
                'empty_queue'    => 'Pusta kolejka',
                'api_null'       => 'Błąd wywołania API',
                'bad_json'       => 'Niepoprawny JSON z modelu',
                'empty_fields'   => 'Brak title/content w JSON (sprawdź prompt ups_ai_prompt_blog_post)',
                'wp_insert_failed' => 'Błąd zapisu wpisu',
            ];
            $code = (string) $diag['code'];
            $diag_txt = ' ' . ($labels[$code] ?? $code);
            if (!empty($diag['detail'])) {
                $diag_txt .= ': ' . (string) $diag['detail'];
            }
        }
        wp_send_json_error([
            'message' => "Bot uruchomiony, ale draft nie powstał.{$diag_txt} Ostatnie pomyślne uruchomienie (last_run): {$last_run}",
        ]);
    }
}
add_action('wp_ajax_upsellio_ai_tests_run_blog_bot', 'upsellio_ai_tests_run_blog_bot_ajax');

// ─────────────────────────────────────────────
// AJAX RUNNER
// ─────────────────────────────────────────────

function upsellio_ai_tests_run_ajax(): void {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }
    check_ajax_referer('upsellio_ai_tests_nonce', 'nonce');

    $group = isset($_POST['group']) ? sanitize_key((string) wp_unslash($_POST['group'])) : 'all';

    $groups = [
        'api'      => ['Anthropic API',         'upsellio_ai_test_group_api'],
        'prompts'  => ['Prompty i kontekst',     'upsellio_ai_test_group_prompts'],
        'features' => ['Funkcje AI',             'upsellio_ai_test_group_features'],
        'blogbot'  => ['Blog Bot',               'upsellio_ai_test_group_blogbot'],
        'data'     => ['Dane GSC i GA4',         'upsellio_ai_test_group_data'],
        'crm'      => ['CRM — historia AI',      'upsellio_ai_test_group_crm'],
    ];

    $output = [];
    foreach ($groups as $key => [$label, $fn]) {
        if ($group !== 'all' && $group !== $key) {
            continue;
        }
        $output[$key] = ['label' => $label, 'results' => $fn()];
    }

    wp_send_json_success(['groups' => $output]);
}
add_action('wp_ajax_upsellio_ai_tests_run', 'upsellio_ai_tests_run_ajax');

// ─────────────────────────────────────────────
// ADMIN PAGE
// ─────────────────────────────────────────────

function upsellio_ai_tests_register_admin_page(): void {
    add_submenu_page(
        'tools.php',
        'Testy AI — Upsellio',
        'Testy AI',
        'manage_options',
        'upsellio-ai-tests',
        'upsellio_ai_tests_render_page',
        98
    );
}
add_action('admin_menu', 'upsellio_ai_tests_register_admin_page');

function upsellio_ai_tests_render_page(): void {
    if (!current_user_can('manage_options')) { return; }
    $nonce    = wp_create_nonce('upsellio_ai_tests_nonce');
    $ajax_url = admin_url('admin-ajax.php');
    ?>
    <div class="wrap" id="ups-ai-tests-wrap">
    <style>
    #ups-ai-tests-wrap{max-width:980px}
    .ups-at-header{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px}
    .ups-at-header h1{margin:0}
    .ups-at-toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .ups-at-groups{display:grid;gap:16px}
    .ups-at-group{background:#fff;border:1px solid #dcdcdc;border-radius:14px;overflow:hidden}
    .ups-at-group-head{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f0f0f0;cursor:pointer;user-select:none}
    .ups-at-group-head h2{margin:0;font-size:15px}
    .ups-at-group-body{padding:0}
    .ups-at-row{display:grid;grid-template-columns:22px 1fr auto;gap:8px;align-items:baseline;padding:9px 16px;border-bottom:1px solid #f5f5f5;font-size:13px}
    .ups-at-row:last-child{border-bottom:none}
    .ups-at-row.pass .ups-at-dot{color:#1a7f37}
    .ups-at-row.fail .ups-at-dot{color:#cf2222}
    .ups-at-row.warn .ups-at-dot{color:#9a5700}
    .ups-at-row.info .ups-at-dot{color:#0969da}
    .ups-at-row.pass{background:#f8fef9}
    .ups-at-row.fail{background:#fff8f8}
    .ups-at-row.warn{background:#fffbf0}
    .ups-at-row.info{background:#f6faff}
    .ups-at-label{font-weight:600;color:#1a1a1a}
    .ups-at-detail{color:#555;font-size:12px;margin-top:2px;word-break:break-all}
    .ups-at-badge{font-size:11px;font-weight:700;border-radius:4px;padding:2px 7px;white-space:nowrap}
    .ups-at-badge.pass{background:#dafbe1;color:#1a7f37}
    .ups-at-badge.fail{background:#ffdce0;color:#cf2222}
    .ups-at-badge.warn{background:#fff3cd;color:#9a5700}
    .ups-at-badge.info{background:#dbeafe;color:#1d4ed8}
    .ups-at-summary{display:flex;gap:6px;flex-wrap:wrap;margin-left:auto}
    .ups-at-loading{padding:14px 16px;color:#777;font-style:italic;font-size:13px}
    .ups-at-separator{height:1px;background:#f0f0f0;margin:0}
    .ups-at-blogbot-box{margin:14px 16px;padding:14px;background:#f6faff;border:1px solid #bfdbfe;border-radius:10px}
    .ups-at-blogbot-box h3{margin:0 0 8px;font-size:14px;color:#1e40af}
    .ups-at-blogbot-result{margin-top:10px;padding:10px;border-radius:8px;font-size:13px;display:none}
    .ups-at-blogbot-result.ok{background:#dafbe1;color:#1a7f37;display:block}
    .ups-at-blogbot-result.err{background:#ffdce0;color:#cf2222;display:block}
    .ups-at-filter-btns{display:flex;gap:6px;flex-wrap:wrap}
    .ups-at-filter-btn{border:1px solid #c3c4c7;background:#fff;border-radius:6px;padding:5px 11px;font-size:12px;cursor:pointer;font-weight:600}
    .ups-at-filter-btn.active{background:#1d2327;color:#fff;border-color:#1d2327}
    @media(max-width:680px){.ups-at-row{grid-template-columns:18px 1fr}}
    </style>

    <div class="ups-at-header">
        <h1>🧪 Testy integracji AI</h1>
        <div class="ups-at-toolbar">
            <button class="button button-primary" id="ups-at-run-all">▶ Uruchom wszystkie testy</button>
            <span id="ups-at-status" style="font-size:12px;color:#666"></span>
        </div>
    </div>

    <p style="color:#555;margin-bottom:16px">
        Diagnostyka integracji AI — Anthropic API, prompty, Blog Bot, dane GSC/GA4 i historia CRM.
        Testy działają bez żadnych zewnętrznych zależności bezpośrednio z poziomu WordPress.
        <strong>Brak danych GSC/GA4 nie blokuje działania AI</strong> — każda funkcja ma fallback.
    </p>

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <span style="font-size:12px;font-weight:600;color:#555">Filtruj grupę:</span>
        <div class="ups-at-filter-btns">
            <button class="ups-at-filter-btn active" data-group="all">Wszystkie</button>
            <button class="ups-at-filter-btn" data-group="api">API</button>
            <button class="ups-at-filter-btn" data-group="prompts">Prompty</button>
            <button class="ups-at-filter-btn" data-group="features">Funkcje</button>
            <button class="ups-at-filter-btn" data-group="blogbot">Blog Bot</button>
            <button class="ups-at-filter-btn" data-group="data">GSC / GA4</button>
            <button class="ups-at-filter-btn" data-group="crm">CRM</button>
        </div>
    </div>

    <div class="ups-at-groups" id="ups-at-groups">
        <div style="padding:20px;text-align:center;color:#999;font-size:14px">
            Kliknij "Uruchom wszystkie testy" aby rozpocząć diagnostykę.
        </div>
    </div>

    <div class="ups-at-blogbot-box" style="margin-top:20px">
        <h3>🤖 Ręczne uruchomienie Blog Bota</h3>
        <p style="font-size:13px;color:#444;margin:0 0 10px">
            Uruchamia bot jednorazowo poza harmonogramem — bierze pierwszy temat z kolejki, wywołuje Claude API i tworzy draft w WP Admin → Wpisy → Szkice.
            Wymaga niepustej kolejki tematów i działającego klucza API.
        </p>
        <button class="button button-secondary" id="ups-at-run-blogbot">🚀 Uruchom Blog Bota teraz</button>
        <span id="ups-at-blogbot-loading" style="display:none;margin-left:10px;font-size:12px;color:#666">Generuję wpis… (może potrwać 15–60 sekund)</span>
        <div class="ups-at-blogbot-result" id="ups-at-blogbot-result"></div>
    </div>

    <script>
    (function(){
        const nonce   = <?php echo wp_json_encode($nonce); ?>;
        const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
        let activeGroup = 'all';

        const icons = { pass:'✓', fail:'✗', warn:'⚠', info:'ℹ' };
        const labels = { pass:'OK', fail:'BŁĄD', warn:'UWAGA', info:'INFO' };

        document.querySelectorAll('.ups-at-filter-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                document.querySelectorAll('.ups-at-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeGroup = this.dataset.group;
                runTests(activeGroup);
            });
        });

        document.getElementById('ups-at-run-all').addEventListener('click', function(){
            document.querySelectorAll('.ups-at-filter-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('[data-group="all"]').classList.add('active');
            activeGroup = 'all';
            runTests('all');
        });

        function runTests(group){
            const container = document.getElementById('ups-at-groups');
            const status    = document.getElementById('ups-at-status');
            container.innerHTML = '<div style="padding:20px;text-align:center;color:#999;font-size:14px">⏳ Uruchamiam testy…</div>';
            status.textContent = 'Trwa diagnostyka…';

            const body = new FormData();
            body.append('action', 'upsellio_ai_tests_run');
            body.append('nonce', nonce);
            body.append('group', group);

            fetch(ajaxUrl, { method:'POST', body })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        container.innerHTML = '<div style="padding:20px;color:#cf2222">Błąd: ' + (data.data?.message || 'Nieznany błąd') + '</div>';
                        status.textContent = '';
                        return;
                    }
                    renderGroups(data.data.groups);
                    const stats = countStats(data.data.groups);
                    status.textContent = '✓ ' + stats.pass + ' OK  ⚠ ' + stats.warn + ' uwag  ✗ ' + stats.fail + ' błędów';
                    status.style.color = stats.fail > 0 ? '#cf2222' : stats.warn > 0 ? '#9a5700' : '#1a7f37';
                })
                .catch(err => {
                    container.innerHTML = '<div style="padding:20px;color:#cf2222">Błąd sieci: ' + err.message + '</div>';
                    status.textContent = '';
                });
        }

        function countStats(groups){
            let pass=0, warn=0, fail=0;
            Object.values(groups).forEach(g => {
                (g.results||[]).forEach(r => {
                    if(r.status==='pass') pass++;
                    else if(r.status==='warn') warn++;
                    else if(r.status==='fail') fail++;
                });
            });
            return {pass, warn, fail};
        }

        function renderGroups(groups){
            const container = document.getElementById('ups-at-groups');
            container.innerHTML = '';
            Object.entries(groups).forEach(([key, group]) => {
                const results = group.results || [];
                const pass = results.filter(r=>r.status==='pass').length;
                const warn = results.filter(r=>r.status==='warn').length;
                const fail = results.filter(r=>r.status==='fail').length;

                const el = document.createElement('div');
                el.className = 'ups-at-group';
                el.innerHTML = `
                    <div class="ups-at-group-head" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'">
                        <h2>${escH(group.label)}</h2>
                        <div class="ups-at-summary">
                            ${pass>0?`<span class="ups-at-badge pass">${pass} OK</span>`:''}
                            ${warn>0?`<span class="ups-at-badge warn">${warn} uwag</span>`:''}
                            ${fail>0?`<span class="ups-at-badge fail">${fail} błędów</span>`:''}
                        </div>
                    </div>
                    <div class="ups-at-group-body">
                        ${results.map(r => `
                        <div class="ups-at-row ${escH(r.status)}">
                            <span class="ups-at-dot" title="${escH(labels[r.status]||r.status)}">${icons[r.status]||'•'}</span>
                            <div>
                                <div class="ups-at-label">${escH(r.label)}</div>
                                ${r.detail?`<div class="ups-at-detail">${escH(r.detail)}</div>`:''}
                            </div>
                            <span class="ups-at-badge ${escH(r.status)}">${escH(labels[r.status]||r.status)}</span>
                        </div>`).join('')}
                    </div>`;
                container.appendChild(el);
            });
        }

        function escH(str){ const d=document.createElement('div'); d.textContent=str||''; return d.innerHTML; }

        // Blog Bot runner
        document.getElementById('ups-at-run-blogbot').addEventListener('click', function(){
            const btn     = this;
            const loading = document.getElementById('ups-at-blogbot-loading');
            const result  = document.getElementById('ups-at-blogbot-result');
            btn.disabled  = true;
            loading.style.display = 'inline';
            result.className = 'ups-at-blogbot-result';
            result.textContent = '';

            const body = new FormData();
            body.append('action', 'upsellio_ai_tests_run_blog_bot');
            body.append('nonce', nonce);

            fetch(ajaxUrl, { method:'POST', body })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    loading.style.display = 'none';
                    if (data.success) {
                        result.className = 'ups-at-blogbot-result ok';
                        result.innerHTML = '✓ ' + escH(data.data.message)
                            + (data.data.title ? '<br>Tytuł: <strong>' + escH(data.data.title) + '</strong>' : '')
                            + (data.data.edit_url ? '<br><a href="' + escH(data.data.edit_url) + '" target="_blank">→ Edytuj draft w WP Admin</a>' : '');
                    } else {
                        result.className = 'ups-at-blogbot-result err';
                        result.textContent = '✗ ' + (data.data?.message || 'Nieznany błąd');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    loading.style.display = 'none';
                    result.className = 'ups-at-blogbot-result err';
                    result.textContent = 'Błąd sieci: ' + err.message;
                });
        });
    })();
    </script>
    </div>
    <?php
}
