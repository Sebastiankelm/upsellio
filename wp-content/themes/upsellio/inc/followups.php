<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_mailbox_log_verbose_enabled(): bool
{
    return (string) get_option("ups_mailbox_log_verbose", "0") === "1";
}

/**
 * Dziennik zdarzeń skrzynki / SMTP / IMAP (Ustawienia CRM → logi).
 *
 * @param mixed $detail Dodatkowy tekst lub dane (zostaną zserializowane)
 */
function upsellio_mailbox_log(string $channel, string $level, string $message, $detail = null): void
{
    $channel = sanitize_key($channel);
    if (!in_array($level, ["debug", "info", "warn", "error"], true)) {
        $level = "info";
    }
    $entry = [
        "ts" => current_time("mysql"),
        "channel" => $channel,
        "level" => $level,
        "message" => $message,
    ];
    if ($detail !== null && $detail !== "") {
        $d = is_string($detail) ? $detail : wp_json_encode($detail, JSON_UNESCAPED_UNICODE);
        if (!is_string($d)) {
            $d = "";
        }
        if (strlen($d) > 12000) {
            $d = substr($d, 0, 12000) . "…";
        }
        $entry["detail"] = $d;
    }
    $log = get_option("ups_mailbox_activity_log", []);
    if (!is_array($log)) {
        $log = [];
    }
    $log[] = $entry;
    if (count($log) > 400) {
        $log = array_slice($log, -400);
    }
    update_option("ups_mailbox_activity_log", $log, false);
}

function upsellio_mailbox_log_render_text(): string
{
    $log = get_option("ups_mailbox_activity_log", []);
    if (!is_array($log) || $log === []) {
        return "";
    }
    $lines = [];
    foreach (array_reverse($log) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $ts = (string) ($row["ts"] ?? "");
        $ch = (string) ($row["channel"] ?? "");
        $lv = (string) ($row["level"] ?? "");
        $msg = (string) ($row["message"] ?? "");
        $lines[] = "[{$ts}] [{$ch}] [{$lv}] {$msg}";
        if (!empty($row["detail"])) {
            $lines[] = "  " . str_replace("\n", "\n  ", (string) $row["detail"]);
        }
    }

    return implode("\n", $lines);
}

/**
 * Zapisuje pliki z inbox_files[] do katalogu tymczasowego — usuń po wysyłce.
 *
 * @return array<int, array{path: string, name: string}>|WP_Error
 */
function upsellio_mailbox_save_uploaded_attachments()
{
    if (empty($_FILES["inbox_files"])) {
        return [];
    }
    $files = $_FILES["inbox_files"];
    if (!isset($files["name"]) || !isset($files["tmp_name"])) {
        return [];
    }
    if (!is_array($files["name"])) {
        $files = [
            "name" => [$files["name"]],
            "type" => [$files["type"]],
            "tmp_name" => [$files["tmp_name"]],
            "error" => [$files["error"]],
            "size" => [$files["size"]],
        ];
    }
    $upload_dir = wp_upload_dir();
    if (!empty($upload_dir["error"])) {
        return new WP_Error("upload_dir", "Brak katalogu uploadów.");
    }
    $base = trailingslashit($upload_dir["basedir"]) . "upsellio-mail-tmp";
    if (!wp_mkdir_p($base)) {
        return new WP_Error("mkdir", "Nie można utworzyć katalogu tymczasowego.");
    }

    $allowed_mimes = [
        "application/pdf",
        "image/jpeg",
        "image/png",
        "image/gif",
        "image/webp",
        "text/plain",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/zip",
    ];
    $max_bytes = 5 * 1024 * 1024;
    $max_files = 6;
    $out = [];
    $n = count($files["name"]);
    for ($i = 0; $i < $n && count($out) < $max_files; $i++) {
        if (($files["error"][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }
        $tmp = (string) ($files["tmp_name"][$i] ?? "");
        if ($tmp === "" || !is_uploaded_file($tmp)) {
            continue;
        }
        $size = (int) ($files["size"][$i] ?? 0);
        if ($size <= 0 || $size > $max_bytes) {
            return new WP_Error("size", "Załącznik za duży (max 5 MB na plik).");
        }
        $check = wp_check_filetype_and_ext($tmp, (string) ($files["name"][$i] ?? ""), $allowed_mimes);
        if (empty($check["type"]) || !in_array($check["type"], $allowed_mimes, true)) {
            return new WP_Error("type", "Niedozwolony typ załącznika: " . (string) ($files["name"][$i] ?? ""));
        }
        $orig = sanitize_file_name((string) ($files["name"][$i] ?? "attachment"));
        if ($orig === "") {
            $orig = "attachment";
        }
        $dest = $base . "/" . wp_unique_filename($base, $orig);
        if (!@move_uploaded_file($tmp, $dest)) {
            return new WP_Error("move", "Nie udało się zapisać załącznika.");
        }
        $out[] = ["path" => $dest, "name" => $orig];
    }

    return $out;
}

function upsellio_mailbox_delete_temp_attachments(array $attachments): void
{
    foreach ($attachments as $att) {
        if (!is_array($att)) {
            continue;
        }
        $p = (string) ($att["path"] ?? "");
        if ($p !== "" && is_file($p) && strpos($p, "upsellio-mail-tmp") !== false) {
            @unlink($p);
        }
    }
}

function upsellio_followup_phpmailer_add_inbox_attachments($phpmailer): void
{
    $batch = $GLOBALS["upsellio_crm_mail_attachments"] ?? [];
    if (!is_array($batch) || $batch === []) {
        return;
    }
    foreach ($batch as $att) {
        if (!is_array($att)) {
            continue;
        }
        $path = (string) ($att["path"] ?? "");
        $name = (string) ($att["name"] ?? "");
        if ($path !== "" && is_readable($path)) {
            $phpmailer->addAttachment($path, $name !== "" ? $name : basename($path));
        }
    }
}

function upsellio_followup_phpmailer_maybe_verbose_smtp($phpmailer): void
{
    if (!function_exists("upsellio_mailbox_log_verbose_enabled") || !upsellio_mailbox_log_verbose_enabled()) {
        return;
    }
    if (!is_object($phpmailer) || !isset($phpmailer->Mailer) || (string) $phpmailer->Mailer !== "smtp") {
        return;
    }
    if (!class_exists("\PHPMailer\PHPMailer\SMTP", false)) {
        return;
    }
    $phpmailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
    $phpmailer->Debugoutput = static function (...$args) {
        $str = isset($args[0]) ? (string) $args[0] : "";
        $s = trim($str);
        if ($s !== "" && function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", "debug", $s);
        }
    };
}

function upsellio_followup_register_post_type()
{
    register_post_type("ups_followup_template", [
        "labels" => [
            "name" => "Follow-upy",
            "singular_name" => "Follow-up",
            "menu_name" => "Follow-upy",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => "edit.php?post_type=crm_offer",
        "menu_icon" => "dashicons-email-alt2",
        "supports" => ["title", "editor"],
    ]);
}
add_action("init", "upsellio_followup_register_post_type");

function upsellio_followup_get_sender_settings()
{
    $from_name = sanitize_text_field((string) get_option("ups_followup_from_name", get_bloginfo("name")));
    $from_email = sanitize_email((string) get_option("ups_followup_from_email", get_option("admin_email")));
    $inbound_secret = sanitize_text_field((string) get_option("ups_followup_inbound_secret", ""));
    $mailbox_host = sanitize_text_field((string) get_option("ups_followup_mailbox_host", ""));
    $mailbox_port = max(1, (int) get_option("ups_followup_mailbox_port", 993));
    $mailbox_encryption = sanitize_key((string) get_option("ups_followup_mailbox_encryption", "ssl"));
    if (!in_array($mailbox_encryption, ["ssl", "tls", "none"], true)) {
        $mailbox_encryption = "ssl";
    }
    $mailbox_username = sanitize_text_field((string) get_option("ups_followup_mailbox_username", ""));
    $mailbox_folder = sanitize_text_field((string) get_option("ups_followup_mailbox_folder", "INBOX"));
    $mailbox_enabled = (string) get_option("ups_followup_mailbox_enabled", "0") === "1";
    $mailbox_password = upsellio_followup_get_mailbox_password();
    $smtp_enabled = (string) get_option("ups_followup_smtp_enabled", "0") === "1";
    $smtp_host = sanitize_text_field((string) get_option("ups_followup_smtp_host", ""));
    $smtp_port = max(1, (int) get_option("ups_followup_smtp_port", 587));
    $smtp_encryption = sanitize_key((string) get_option("ups_followup_smtp_encryption", "tls"));
    if (!in_array($smtp_encryption, ["ssl", "tls", "none"], true)) {
        $smtp_encryption = "tls";
    }
    $smtp_username = sanitize_text_field((string) get_option("ups_followup_smtp_username", ""));
    $smtp_password = upsellio_followup_get_smtp_password();
    return [
        "from_name" => $from_name,
        "from_email" => $from_email,
        "inbound_secret" => $inbound_secret,
        "mailbox_enabled" => $mailbox_enabled,
        "mailbox_host" => $mailbox_host,
        "mailbox_port" => $mailbox_port,
        "mailbox_encryption" => $mailbox_encryption,
        "mailbox_username" => $mailbox_username,
        "mailbox_password" => $mailbox_password,
        "mailbox_folder" => $mailbox_folder !== "" ? $mailbox_folder : "INBOX",
        "smtp_enabled" => $smtp_enabled,
        "smtp_host" => $smtp_host,
        "smtp_port" => $smtp_port,
        "smtp_encryption" => $smtp_encryption,
        "smtp_username" => $smtp_username,
        "smtp_password" => $smtp_password,
    ];
}

function upsellio_followup_get_crypto_key()
{
    return hash("sha256", wp_salt("auth") . "|" . NONCE_SALT, true);
}

function upsellio_followup_encrypt_secret($raw)
{
    $raw = (string) $raw;
    if ($raw === "" || !function_exists("openssl_encrypt")) {
        return $raw;
    }
    $iv_length = openssl_cipher_iv_length("aes-256-cbc");
    $iv = random_bytes($iv_length > 0 ? $iv_length : 16);
    $cipher = openssl_encrypt($raw, "aes-256-cbc", upsellio_followup_get_crypto_key(), OPENSSL_RAW_DATA, $iv);
    if (!is_string($cipher) || $cipher === "") {
        return $raw;
    }
    return "enc:v1:" . base64_encode($iv) . ":" . base64_encode($cipher);
}

function upsellio_followup_decrypt_secret($stored)
{
    $stored = (string) $stored;
    if ($stored === "" || strpos($stored, "enc:v1:") !== 0 || !function_exists("openssl_decrypt")) {
        return $stored;
    }
    $parts = explode(":", $stored, 4);
    if (count($parts) !== 4) {
        return "";
    }
    $iv = base64_decode((string) $parts[2], true);
    $cipher = base64_decode((string) $parts[3], true);
    if (!is_string($iv) || !is_string($cipher) || $iv === "" || $cipher === "") {
        return "";
    }
    $plain = openssl_decrypt($cipher, "aes-256-cbc", upsellio_followup_get_crypto_key(), OPENSSL_RAW_DATA, $iv);
    return is_string($plain) ? $plain : "";
}

function upsellio_followup_get_mailbox_password()
{
    $stored = (string) get_option("ups_followup_mailbox_password", "");
    return upsellio_followup_decrypt_secret($stored);
}

function upsellio_followup_store_mailbox_password($raw_password)
{
    $raw_password = (string) $raw_password;
    if ($raw_password === "") {
        return;
    }
    update_option("ups_followup_mailbox_password", upsellio_followup_encrypt_secret($raw_password));
}

function upsellio_followup_get_smtp_password()
{
    $stored = (string) get_option("ups_followup_smtp_password", "");

    return upsellio_followup_decrypt_secret($stored);
}

function upsellio_followup_store_smtp_password($raw_password)
{
    $raw_password = (string) $raw_password;
    if ($raw_password === "") {
        return;
    }
    update_option("ups_followup_smtp_password", upsellio_followup_encrypt_secret($raw_password));
}

function upsellio_followup_should_send_crm_via_smtp()
{
    $settings = upsellio_followup_get_sender_settings();

    return !empty($settings["smtp_enabled"]) && trim((string) $settings["smtp_host"]) !== "";
}

function upsellio_followup_load_phpmailer_classes()
{
    if (class_exists("\PHPMailer\PHPMailer\PHPMailer", false)) {
        return true;
    }
    $base = ABSPATH . WPINC . "/PHPMailer/";
    $files = ["PHPMailer.php", "SMTP.php", "Exception.php"];
    foreach ($files as $file) {
        $path = $base . $file;
        if (!is_readable($path)) {
            return false;
        }
        require_once $path;
    }

    return class_exists("\PHPMailer\PHPMailer\PHPMailer", false);
}

/**
 * Wysyłka wyłącznie dla ścieżki CRM (follow-up + inbox): SMTP z ustawień, bez wp_mail().
 */
function upsellio_followup_send_html_mail_via_smtp($to_email, $subject, $html, $args = [])
{
    $args = is_array($args) ? $args : [];
    $to_email = sanitize_email((string) $to_email);
    if (!is_email($to_email)) {
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", "error", "SMTP CRM: niepoprawny adres głównego odbiorcy.");
        }

        return false;
    }
    $subject = sanitize_text_field((string) $subject);
    $html = (string) $html;
    if (!upsellio_followup_load_phpmailer_classes()) {
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", "error", "Brak biblioteki PHPMailer (wp-includes/PHPMailer).");
        }

        return false;
    }
    $settings = upsellio_followup_get_sender_settings();
    $host = trim((string) $settings["smtp_host"]);
    if ($host === "") {
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", "error", "Nie skonfigurowano hosta SMTP — sprawdź Ustawienia → Mail / Skrzynki.");
        }

        return false;
    }
    $from_email = is_email($settings["from_email"]) ? $settings["from_email"] : "";
    if ($from_email === "") {
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", "error", "SMTP CRM: ustaw poprawny „From email” w Ustawienia → Mail / Skrzynki.");
        }

        return false;
    }
    $from_name = $settings["from_name"] !== "" ? $settings["from_name"] : get_bloginfo("name");
    $port = max(1, (int) $settings["smtp_port"]);
    $enc = (string) $settings["smtp_encryption"];
    $user = (string) $settings["smtp_username"];
    $pass = (string) $settings["smtp_password"];

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = $user !== "";
        if ($user !== "") {
            $mail->Username = $user;
            $mail->Password = $pass;
        }
        if ($enc === "none") {
            $mail->SMTPSecure = "";
            $mail->SMTPAutoTLS = false;
        } else {
            $mail->SMTPSecure = $enc;
            $mail->SMTPAutoTLS = true;
        }
        if (function_exists("upsellio_mailbox_log_verbose_enabled") && upsellio_mailbox_log_verbose_enabled() && class_exists("\PHPMailer\PHPMailer\SMTP", false)) {
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $mail->Debugoutput = static function ($str, $level) {
                $s = trim((string) $str);
                if ($s !== "" && function_exists("upsellio_mailbox_log")) {
                    upsellio_mailbox_log("smtp", "debug", $s);
                }
            };
        }
        if (function_exists("upsellio_mailbox_log")) {
            $att_n = is_array($args["attachments"] ?? null) ? count($args["attachments"]) : 0;
            upsellio_mailbox_log("smtp", "info", "Start wysyłki przez SMTP", "Host: {$host}:{$port}, enc: {$enc}, temat: {$subject}, załączników: {$att_n}");
        }
        $mail->setFrom($from_email, $from_name, false);
        $mail->addAddress($to_email);
        foreach (($args["additional_to"] ?? []) as $xto) {
            $xto = sanitize_email((string) $xto);
            if (is_email($xto)) {
                $mail->addAddress($xto);
            }
        }
        foreach (($args["cc"] ?? []) as $cce) {
            $cce = sanitize_email((string) $cce);
            if (is_email($cce)) {
                $mail->addCC($cce);
            }
        }
        foreach (($args["bcc"] ?? []) as $bcce) {
            $bcce = sanitize_email((string) $bcce);
            if (is_email($bcce)) {
                $mail->addBCC($bcce);
            }
        }
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $html;
        $mail->AltBody = wp_strip_all_tags($html);
        foreach (($args["attachments"] ?? []) as $att) {
            if (!is_array($att)) {
                continue;
            }
            $p = (string) ($att["path"] ?? "");
            $n = (string) ($att["name"] ?? "");
            if ($p !== "" && is_readable($p)) {
                $mail->addAttachment($p, $n !== "" ? $n : basename($p));
            }
        }

        $ok = (bool) $mail->send();
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", $ok ? "info" : "error", $ok ? "Zakończono wysyłkę SMTP pomyślnie." : "PHPMailer zwrócił false przy send().");
        }

        return $ok;
    } catch (\Throwable $e) {
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("smtp", "error", "Wyjątek przy wysyłce SMTP: " . $e->getMessage(), $e->getTraceAsString());
        }
        if (defined("WP_DEBUG") && WP_DEBUG) {
            error_log("upsellio_followup_send_html_mail_via_smtp: " . $e->getMessage());
        }

        return false;
    }
}

function upsellio_followup_test_smtp_connection()
{
    $settings = upsellio_followup_get_sender_settings();
    if (empty($settings["smtp_enabled"]) || trim((string) $settings["smtp_host"]) === "") {
        return ["ok" => false, "message" => "Włącz SMTP (CRM) i podaj host serwera."];
    }
    if (!upsellio_followup_load_phpmailer_classes()) {
        return ["ok" => false, "message" => "Brak biblioteki PHPMailer w instalacji WordPress (wp-includes/PHPMailer)."];
    }
    $host = trim((string) $settings["smtp_host"]);
    $port = max(1, (int) $settings["smtp_port"]);
    $enc = (string) $settings["smtp_encryption"];
    $user = (string) $settings["smtp_username"];
    $pass = (string) $settings["smtp_password"];

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->Timeout = 12;
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = $user !== "";
        if ($user !== "") {
            $mail->Username = $user;
            $mail->Password = $pass;
        }
        if ($enc === "none") {
            $mail->SMTPSecure = "";
            $mail->SMTPAutoTLS = false;
        } else {
            $mail->SMTPSecure = $enc;
            $mail->SMTPAutoTLS = true;
        }
        if (!$mail->smtpConnect()) {
            return ["ok" => false, "message" => "Nie udało się zestawić sesji SMTP."];
        }
        $mail->smtpClose();

        return ["ok" => true, "message" => "Połączenie SMTP (CRM) działa poprawnie."];
    } catch (\Throwable $e) {
        return ["ok" => false, "message" => $e->getMessage()];
    }
}

function upsellio_followup_mask_secret($value)
{
    $value = (string) $value;
    if ($value === "") {
        return "";
    }
    $len = strlen($value);
    if ($len <= 4) {
        return str_repeat("*", $len);
    }
    return substr($value, 0, 1) . str_repeat("*", max(2, $len - 3)) . substr($value, -2);
}

function upsellio_followup_test_mailbox_connection()
{
    $settings = upsellio_followup_get_sender_settings();
    if (!function_exists("imap_open")) {
        return ["ok" => false, "message" => "Brak rozszerzenia PHP IMAP na serwerze."];
    }
    $host = (string) $settings["mailbox_host"];
    $username = (string) $settings["mailbox_username"];
    $password = (string) $settings["mailbox_password"];
    if ($host === "" || $username === "" || $password === "") {
        return ["ok" => false, "message" => "Uzupełnij host/login/hasło skrzynki IMAP."];
    }
    $port = max(1, (int) $settings["mailbox_port"]);
    $folder = (string) $settings["mailbox_folder"];
    $enc = (string) $settings["mailbox_encryption"];
    $flags = $enc === "ssl" ? "/imap/ssl" : ($enc === "tls" ? "/imap/tls" : "/imap/notls");
    $mailbox = "{" . $host . ":" . $port . $flags . "}" . $folder;
    $imap = @imap_open($mailbox, $username, $password);
    if (!$imap) {
        $errors = imap_errors();
        $msg = is_array($errors) && !empty($errors) ? (string) end($errors) : "Nie można połączyć się ze skrzynką IMAP.";
        return ["ok" => false, "message" => $msg];
    }
    imap_close($imap);
    return ["ok" => true, "message" => "Połączenie IMAP działa poprawnie."];
}

function upsellio_followup_add_template_meta_box()
{
    add_meta_box(
        "upsellio_followup_template_config",
        "Konfiguracja automatyzacji",
        "upsellio_followup_render_template_meta_box",
        "ups_followup_template",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_followup_add_template_meta_box");

function upsellio_followup_render_template_meta_box($post)
{
    $post_id = (int) $post->ID;
    $trigger_event = (string) get_post_meta($post_id, "_ups_followup_trigger_event", true);
    $stage = (string) get_post_meta($post_id, "_ups_followup_stage", true);
    $delay_minutes = (int) get_post_meta($post_id, "_ups_followup_delay_minutes", true);
    $subject = (string) get_post_meta($post_id, "_ups_followup_subject", true);
    $custom_html = (string) get_post_meta($post_id, "_ups_followup_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_followup_css", true);
    $is_active = (string) get_post_meta($post_id, "_ups_followup_active", true) === "1";
    $hint_awareness = (string) get_option("ups_followup_hint_awareness", "");
    $hint_consideration = (string) get_option("ups_followup_hint_consideration", "");
    $hint_decision = (string) get_option("ups_followup_hint_decision", "");
    wp_nonce_field("upsellio_followup_template_meta", "upsellio_followup_template_meta_nonce");
    ?>
    <p>
      <label><strong>Trigger event</strong></label><br />
      <select class="widefat" name="ups_followup_trigger_event">
        <?php
        $events = ["any", "offer_view", "offer_section_view", "offer_engagement_tick", "offer_cta_click", "offer_hot_detected", "inbound_positive", "inbound_price_objection", "inbound_timing_objection", "inbound_no_priority"];
        foreach ($events as $event) :
        ?>
          <option value="<?php echo esc_attr($event); ?>" <?php selected($trigger_event, $event); ?>><?php echo esc_html($event); ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label><strong>Etap lejka</strong></label><br />
      <select class="widefat" name="ups_followup_stage">
        <?php foreach (["any", "awareness", "consideration", "decision"] as $stage_option) : ?>
          <option value="<?php echo esc_attr($stage_option); ?>" <?php selected($stage, $stage_option); ?>><?php echo esc_html($stage_option); ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p><label><strong>Delay (min)</strong></label><br /><input class="widefat" type="number" min="0" step="1" name="ups_followup_delay_minutes" value="<?php echo esc_attr((string) max(0, $delay_minutes)); ?>" /></p>
    <p><label><strong>Temat maila</strong></label><br /><input class="widefat" type="text" name="ups_followup_subject" value="<?php echo esc_attr($subject); ?>" placeholder="np. Czy wracamy do oferty {{offer_title}}?" /></p>
    <p><label><strong>HTML maila follow-up</strong></label><br /><textarea class="widefat code" rows="10" name="ups_followup_html"><?php echo esc_textarea($custom_html); ?></textarea></p>
    <p><label><strong>Dodatkowy CSS (dla maila HTML)</strong></label><br /><textarea class="widefat" rows="6" name="ups_followup_css"><?php echo esc_textarea($custom_css); ?></textarea></p>
    <p><label style="display:flex;gap:8px;align-items:flex-start;"><input type="checkbox" name="ups_followup_active" value="1" <?php checked($is_active); ?> /><span>Aktywna automatyzacja</span></label></p>
    <p><small>Jeśli pole HTML jest puste, system użyje treści z głównego edytora wpisu. Dostępne zmienne: <code>{{client_name}}</code>, <code>{{offer_title}}</code>, <code>{{offer_url}}</code>, <code>{{offer_score}}</code>, <code>{{offer_stage}}</code>.</small></p>
    <hr />
    <p><strong>Podpowiedz dopasowania do lejka</strong></p>
    <p><strong>Awareness:</strong> <?php echo esc_html($hint_awareness); ?></p>
    <p><strong>Consideration:</strong> <?php echo esc_html($hint_consideration); ?></p>
    <p><strong>Decision:</strong> <?php echo esc_html($hint_decision); ?></p>
    <?php
}

function upsellio_followup_save_template_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "ups_followup_template" || !isset($_POST["upsellio_followup_template_meta_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_followup_template_meta_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_followup_template_meta") || (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) || !current_user_can("edit_post", (int) $post_id)) {
        return;
    }
    update_post_meta((int) $post_id, "_ups_followup_trigger_event", isset($_POST["ups_followup_trigger_event"]) ? sanitize_key(wp_unslash($_POST["ups_followup_trigger_event"])) : "any");
    update_post_meta((int) $post_id, "_ups_followup_stage", isset($_POST["ups_followup_stage"]) ? sanitize_key(wp_unslash($_POST["ups_followup_stage"])) : "any");
    update_post_meta((int) $post_id, "_ups_followup_delay_minutes", isset($_POST["ups_followup_delay_minutes"]) ? max(0, (int) wp_unslash($_POST["ups_followup_delay_minutes"])) : 0);
    update_post_meta((int) $post_id, "_ups_followup_subject", isset($_POST["ups_followup_subject"]) ? sanitize_text_field(wp_unslash($_POST["ups_followup_subject"])) : "");
    update_post_meta((int) $post_id, "_ups_followup_html", isset($_POST["ups_followup_html"]) ? wp_kses_post((string) wp_unslash($_POST["ups_followup_html"])) : "");
    update_post_meta((int) $post_id, "_ups_followup_css", isset($_POST["ups_followup_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["ups_followup_css"])) : "");
    update_post_meta((int) $post_id, "_ups_followup_active", isset($_POST["ups_followup_active"]) ? "1" : "0");
}
add_action("save_post", "upsellio_followup_save_template_meta_box");

function upsellio_followup_register_settings_page()
{
    add_submenu_page(
        "edit.php?post_type=crm_offer",
        "Konfiguracja follow-up",
        "Konfiguracja follow-up",
        "manage_options",
        "upsellio-followup-settings",
        "upsellio_followup_render_settings_page"
    );
}
add_action("admin_menu", "upsellio_followup_register_settings_page");

function upsellio_followup_render_settings_page()
{
    if (isset($_POST["ups_followup_settings_nonce"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["ups_followup_settings_nonce"])), "ups_followup_settings_save")) {
        update_option("ups_followup_from_name", isset($_POST["ups_followup_from_name"]) ? sanitize_text_field(wp_unslash($_POST["ups_followup_from_name"])) : "");
        update_option("ups_followup_from_email", isset($_POST["ups_followup_from_email"]) ? sanitize_email(wp_unslash($_POST["ups_followup_from_email"])) : "");
        update_option("ups_followup_inbound_secret", isset($_POST["ups_followup_inbound_secret"]) ? sanitize_text_field(wp_unslash($_POST["ups_followup_inbound_secret"])) : "");
        update_option("ups_offer_stage_consideration_views", isset($_POST["ups_offer_stage_consideration_views"]) ? max(1, (int) wp_unslash($_POST["ups_offer_stage_consideration_views"])) : 2);
        update_option("ups_offer_stage_decision_views", isset($_POST["ups_offer_stage_decision_views"]) ? max(1, (int) wp_unslash($_POST["ups_offer_stage_decision_views"])) : 3);
        update_option("ups_offer_stage_decision_require_cta", isset($_POST["ups_offer_stage_decision_require_cta"]) ? "1" : "0");
        update_option("ups_followup_hint_awareness", isset($_POST["ups_followup_hint_awareness"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_followup_hint_awareness"])) : "");
        update_option("ups_followup_hint_consideration", isset($_POST["ups_followup_hint_consideration"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_followup_hint_consideration"])) : "");
        update_option("ups_followup_hint_decision", isset($_POST["ups_followup_hint_decision"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_followup_hint_decision"])) : "");
        update_option("ups_offer_email_subject", isset($_POST["ups_offer_email_subject"]) ? sanitize_text_field(wp_unslash($_POST["ups_offer_email_subject"])) : "");
        update_option("ups_offer_email_html", isset($_POST["ups_offer_email_html"]) ? wp_kses_post((string) wp_unslash($_POST["ups_offer_email_html"])) : "");
        update_option("ups_offer_email_css", isset($_POST["ups_offer_email_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["ups_offer_email_css"])) : "");
        update_option("ups_followup_max_per_offer", isset($_POST["ups_followup_max_per_offer"]) ? max(1, (int) wp_unslash($_POST["ups_followup_max_per_offer"])) : 5);
        update_option("ups_followup_cooldown_hours", isset($_POST["ups_followup_cooldown_hours"]) ? max(0, (int) wp_unslash($_POST["ups_followup_cooldown_hours"])) : 24);
        echo '<div class="notice notice-success"><p>Zapisano ustawienia follow-up.</p></div>';
    }
    $settings = upsellio_followup_get_sender_settings();
    $consideration_views = (int) get_option("ups_offer_stage_consideration_views", 2);
    $decision_views = (int) get_option("ups_offer_stage_decision_views", 3);
    $decision_require_cta = (string) get_option("ups_offer_stage_decision_require_cta", "0") === "1";
    $hint_awareness = (string) get_option("ups_followup_hint_awareness", "Klient dopiero poznaje oferte. Wyslij edukacyjny follow-up z case study i odpowiedzia na FAQ.");
    $hint_consideration = (string) get_option("ups_followup_hint_consideration", "Klient porownuje opcje. Wyslij follow-up z ROI, timeline i obiekcjami cenowymi.");
    $hint_decision = (string) get_option("ups_followup_hint_decision", "Klient jest blisko decyzji. Wyslij follow-up domykajacy: call-to-action, termin startu, ograniczona dostepnosc.");
    $offer_email_subject = (string) get_option("ups_offer_email_subject", "Twoja oferta: {{offer_title}}");
    $offer_email_html = (string) get_option("ups_offer_email_html", "<p>Czesc {{client_name}},</p><p>Twoja oferta jest gotowa:</p><p><a href='{{offer_url}}'>{{offer_url}}</a></p>");
    $offer_email_css = (string) get_option("ups_offer_email_css", "body{font-family:Arial,sans-serif;color:#0f172a}a{color:#0ea5e9}");
    $max_followups_per_offer = (int) get_option("ups_followup_max_per_offer", 5);
    $followup_cooldown_hours = (int) get_option("ups_followup_cooldown_hours", 24);
    ?>
    <div class="wrap">
      <h1>Konfiguracja follow-up</h1>
      <form method="post">
        <?php wp_nonce_field("ups_followup_settings_save", "ups_followup_settings_nonce"); ?>
        <table class="form-table">
          <tr><th scope="row">Nadawca (nazwa)</th><td><input class="regular-text" type="text" name="ups_followup_from_name" value="<?php echo esc_attr($settings["from_name"]); ?>" /></td></tr>
          <tr><th scope="row">Nadawca (e-mail)</th><td><input class="regular-text" type="email" name="ups_followup_from_email" value="<?php echo esc_attr($settings["from_email"]); ?>" /></td></tr>
          <tr><th scope="row">Inbound secret</th><td><input class="regular-text" type="text" name="ups_followup_inbound_secret" value="<?php echo esc_attr($settings["inbound_secret"]); ?>" /><p class="description">Sekret do webhooka odpowiedzi mailowych (CRM/inbound parser).</p></td></tr>
          <tr><th scope="row">Lejek: min. views dla consideration</th><td><input class="small-text" type="number" min="1" name="ups_offer_stage_consideration_views" value="<?php echo esc_attr((string) $consideration_views); ?>" /><p class="description">Gdy liczba wejsc osiagnie prog, klient moze wejsc w etap consideration.</p></td></tr>
          <tr><th scope="row">Lejek: min. views dla decision</th><td><input class="small-text" type="number" min="1" name="ups_offer_stage_decision_views" value="<?php echo esc_attr((string) $decision_views); ?>" /><p class="description">Wysoka liczba powrotow do oferty moze oznaczac gotowosc do decyzji.</p></td></tr>
          <tr><th scope="row">Lejek: decision tylko po CTA</th><td><label><input type="checkbox" name="ups_offer_stage_decision_require_cta" value="1" <?php checked($decision_require_cta); ?> /> Wymagaj klikniecia CTA, by wejsc w decision.</label></td></tr>
          <tr><th scope="row">Podpowiedz follow-up: awareness</th><td><textarea class="large-text" rows="3" name="ups_followup_hint_awareness"><?php echo esc_textarea($hint_awareness); ?></textarea></td></tr>
          <tr><th scope="row">Podpowiedz follow-up: consideration</th><td><textarea class="large-text" rows="3" name="ups_followup_hint_consideration"><?php echo esc_textarea($hint_consideration); ?></textarea></td></tr>
          <tr><th scope="row">Podpowiedz follow-up: decision</th><td><textarea class="large-text" rows="3" name="ups_followup_hint_decision"><?php echo esc_textarea($hint_decision); ?></textarea></td></tr>
          <tr><th scope="row">Temat maila oferty</th><td><input class="regular-text" type="text" name="ups_offer_email_subject" value="<?php echo esc_attr($offer_email_subject); ?>" /></td></tr>
          <tr><th scope="row">HTML maila oferty</th><td><textarea class="large-text code" rows="8" name="ups_offer_email_html"><?php echo esc_textarea($offer_email_html); ?></textarea></td></tr>
          <tr><th scope="row">CSS maila oferty</th><td><textarea class="large-text code" rows="6" name="ups_offer_email_css"><?php echo esc_textarea($offer_email_css); ?></textarea></td></tr>
          <tr><th scope="row">Max follow-upów / oferta</th><td><input class="small-text" type="number" min="1" name="ups_followup_max_per_offer" value="<?php echo esc_attr((string) $max_followups_per_offer); ?>" /></td></tr>
          <tr><th scope="row">Cooldown follow-up (godz.)</th><td><input class="small-text" type="number" min="0" name="ups_followup_cooldown_hours" value="<?php echo esc_attr((string) $followup_cooldown_hours); ?>" /></td></tr>
        </table>
        <p><button class="button button-primary" type="submit">Zapisz</button></p>
      </form>
      <h2>Jak dopasowac follow-upy do zachowan (podpowiedzi)</h2>
      <ol>
        <li><strong>Awareness:</strong> eventy <code>offer_view</code>, <code>offer_section_view</code>. Follow-up edukacyjny, bez presji.</li>
        <li><strong>Consideration:</strong> eventy <code>offer_engagement_tick</code> + dluzszy czas na sekcji ceny. Follow-up z ROI i porownaniem opcji.</li>
        <li><strong>Decision:</strong> event <code>offer_cta_click</code> lub wysoki score. Follow-up domykajacy z terminem startu.</li>
      </ol>
      <h2>Webhook odpowiedzi</h2>
      <p><code><?php echo esc_html(rest_url("upsellio/v1/followup-inbound")); ?></code></p>
    </div>
    <?php
}

function upsellio_followup_resolve_placeholders($text, $offer_id, $stage)
{
    $offer_id = (int) $offer_id;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $score = (int) get_post_meta($offer_id, "_ups_offer_score", true);
    $replace = [
        "{{client_name}}" => $client_name,
        "{{offer_title}}" => (string) get_the_title($offer_id),
        "{{offer_url}}" => function_exists("upsellio_offer_get_public_url") ? (string) upsellio_offer_get_public_url($offer_id) : "",
        "{{offer_score}}" => (string) $score,
        "{{offer_stage}}" => (string) $stage,
    ];
    return strtr((string) $text, $replace);
}

function upsellio_followup_queue_message($offer_id, $template_id, $stage)
{
    $offer_id = (int) $offer_id;
    $template_id = (int) $template_id;
    if ($offer_id <= 0 || $template_id <= 0) {
        return;
    }
    $queue = get_post_meta($offer_id, "_ups_offer_followup_queue", true);
    if (!is_array($queue)) {
        $queue = [];
    }
    $max_per_offer = max(1, (int) get_option("ups_followup_max_per_offer", 5));
    $cooldown_hours = max(0, (int) get_option("ups_followup_cooldown_hours", 24));
    $sent_count = 0;
    $last_sent_at = 0;
    foreach ($queue as $item) {
        if ((string) ($item["status"] ?? "") === "sent") {
            $sent_count++;
            $sent_ts = strtotime((string) ($item["sent_at"] ?? ""));
            if ($sent_ts !== false) {
                $last_sent_at = max($last_sent_at, $sent_ts);
            }
        }
    }
    if ($sent_count >= $max_per_offer) {
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "followup_skipped", "Limit follow-upow osiagniety dla oferty.");
        }
        return;
    }
    if ($cooldown_hours > 0 && $last_sent_at > 0 && (time() - $last_sent_at) < ($cooldown_hours * HOUR_IN_SECONDS)) {
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "followup_skipped", "Cooldown follow-up aktywny.");
        }
        return;
    }
    $delay_minutes = (int) get_post_meta($template_id, "_ups_followup_delay_minutes", true);
    $signature = "tpl:" . $template_id . "|stage:" . sanitize_key((string) $stage);
    foreach ($queue as $item) {
        if ((string) ($item["signature"] ?? "") === $signature && (string) ($item["status"] ?? "") !== "sent") {
            return;
        }
    }
    $queue[] = [
        "template_id" => $template_id,
        "stage" => sanitize_key((string) $stage),
        "signature" => $signature,
        "status" => "queued",
        "created_at" => current_time("mysql"),
        "send_at" => gmdate("Y-m-d H:i:s", time() + max(0, $delay_minutes) * MINUTE_IN_SECONDS),
    ];
    update_post_meta($offer_id, "_ups_offer_followup_queue", $queue);
    if (function_exists("upsellio_offer_add_timeline_event")) {
        upsellio_offer_add_timeline_event($offer_id, "followup_queued", "Dodano follow-up do kolejki: " . (string) get_the_title($template_id));
    }
}

function upsellio_followup_find_matching_templates($event_name, $stage)
{
    $templates = get_posts([
        "post_type" => "ups_followup_template",
        "post_status" => "publish",
        "posts_per_page" => 200,
        "orderby" => "date",
        "order" => "ASC",
    ]);
    $matches = [];
    foreach ($templates as $template) {
        $template_id = (int) $template->ID;
        if ((string) get_post_meta($template_id, "_ups_followup_active", true) !== "1") {
            continue;
        }
        $trigger = (string) get_post_meta($template_id, "_ups_followup_trigger_event", true);
        $stage_rule = (string) get_post_meta($template_id, "_ups_followup_stage", true);
        if ($trigger !== "any" && $trigger !== (string) $event_name) {
            continue;
        }
        if ($stage_rule !== "any" && $stage_rule !== (string) $stage) {
            continue;
        }
        $matches[] = [
            "id" => $template_id,
            "primary" => (string) get_post_meta($template_id, "_ups_followup_is_primary", true) === "1" ? 1 : 0,
        ];
    }
    usort($matches, function ($a, $b) {
        $pa = isset($a["primary"]) ? (int) $a["primary"] : 0;
        $pb = isset($b["primary"]) ? (int) $b["primary"] : 0;
        if ($pa === $pb) {
            return 0;
        }
        return $pa > $pb ? -1 : 1;
    });
    $ordered = [];
    foreach ($matches as $match) {
        $ordered[] = (int) ($match["id"] ?? 0);
    }
    return array_values(array_filter($ordered));
}

function upsellio_followup_handle_offer_event($offer_id, $event_name, $summary, $stage)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return;
    }
    $stage = sanitize_key((string) $stage);
    $event_name = sanitize_key((string) $event_name);
    foreach (upsellio_followup_find_matching_templates($event_name, $stage) as $template_id) {
        upsellio_followup_queue_message($offer_id, (int) $template_id, $stage);
    }
    if (!empty($summary["is_hot"])) {
        foreach (upsellio_followup_find_matching_templates("offer_hot_detected", "decision") as $template_id) {
            upsellio_followup_queue_message($offer_id, (int) $template_id, "decision");
        }
    }
}
add_action("upsellio_offer_event_tracked", "upsellio_followup_handle_offer_event", 10, 4);

function upsellio_followup_handle_inbound_class_event($offer_id, $classification, $stage)
{
    $event_map = [
        "positive" => "inbound_positive",
        "price_objection" => "inbound_price_objection",
        "timing_objection" => "inbound_timing_objection",
        "no_priority" => "inbound_no_priority",
    ];
    $event_name = isset($event_map[$classification]) ? (string) $event_map[$classification] : "";
    if ($event_name === "") {
        return;
    }
    foreach (upsellio_followup_find_matching_templates($event_name, (string) $stage) as $template_id) {
        upsellio_followup_queue_message((int) $offer_id, (int) $template_id, (string) $stage);
    }
}
add_action("upsellio_inbound_classified", "upsellio_followup_handle_inbound_class_event", 10, 3);

function upsellio_followup_resolve_crm_email_footer_placeholders(string $fragment): string
{
    $fragment = str_replace(
        ["{{site_name}}", "{{year}}", "{{home_url}}"],
        [get_bloginfo("name"), gmdate("Y"), (string) home_url("/")],
        $fragment
    );

    return $fragment;
}

/**
 * Dokleja globalną stopkę CRM (HTML + CSS z ustawień) przed wysyłką follow-up / inbox.
 */
function upsellio_followup_apply_crm_email_footer(string $html): string
{
    $footer_html = trim((string) get_option("ups_crm_email_footer_html", ""));
    $footer_css = (string) get_option("ups_crm_email_footer_css", "");
    $footer_css = wp_strip_all_tags($footer_css);
    if ($footer_html === "" && $footer_css === "") {
        return $html;
    }
    if ($footer_html !== "") {
        $footer_html = upsellio_followup_resolve_crm_email_footer_placeholders($footer_html);
    }
    $out = $html;
    $lower = strtolower($out);
    if (strpos($lower, "<html") === false || strpos($lower, "<body") === false) {
        $out = "<html><head><meta charset=\"utf-8\"></head><body>" . $out . "</body></html>";
    } elseif (!preg_match('#</body\s*>#i', $out)) {
        if (preg_match('#</html\s*>#i', $out)) {
            $out = preg_replace('#</html\s*>#i', "</body></html>", $out, 1);
        } else {
            $out .= "</body></html>";
        }
    }
    if ($footer_css !== "") {
        $style_inject = '<style type="text/css">' . $footer_css . "</style>";
        if (preg_match('#</head\s*>#i', $out)) {
            $out = preg_replace('#</head\s*>#i', $style_inject . "</head>", $out, 1);
        } else {
            $out = preg_replace('#<html\b[^>]*>#i', '$0<head><meta charset="utf-8">' . $style_inject . "</head>", $out, 1);
        }
    }
    if ($footer_html !== "") {
        $block = '<div class="ups-crm-email-footer">' . $footer_html . "</div>";
        $out = preg_replace('#</body\s*>#i', $block . "</body>", $out, 1);
    }

    return $out;
}

function upsellio_followup_finalize_crm_html(string $html, array $args): string
{
    $args = is_array($args) ? $args : [];
    if (!empty($args["crm_smtp"]) && empty($args["skip_footer"])) {
        return upsellio_followup_apply_crm_email_footer($html);
    }

    return $html;
}

function upsellio_followup_mail_from($email)
{
    $settings = upsellio_followup_get_sender_settings();
    return is_email($settings["from_email"]) ? $settings["from_email"] : $email;
}

function upsellio_followup_mail_from_name($name)
{
    $settings = upsellio_followup_get_sender_settings();
    return $settings["from_name"] !== "" ? $settings["from_name"] : $name;
}

function upsellio_followup_send_html_mail($to_email, $subject, $html, $args = [])
{
    $args = is_array($args) ? $args : [];
    $html = upsellio_followup_finalize_crm_html((string) $html, $args);
    $use_crm_smtp = !empty($args["crm_smtp"]) && upsellio_followup_should_send_crm_via_smtp();
    $attachments = isset($args["attachments"]) && is_array($args["attachments"]) ? $args["attachments"] : [];
    $att_n = count($attachments);
    $to_list = [];
    if (!empty($args["to"]) && is_array($args["to"])) {
        foreach ($args["to"] as $addr) {
            $addr = sanitize_email((string) $addr);
            if (is_email($addr)) {
                $to_list[] = $addr;
            }
        }
    }
    if ($to_list === []) {
        $one = sanitize_email((string) $to_email);
        if (is_email($one)) {
            $to_list[] = $one;
        }
    }
    if ($to_list === []) {
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("mail", "error", "Brak poprawnego adresu odbiorcy — wysyłka przerwana.");
        }

        return false;
    }
    $primary_to = array_shift($to_list);
    $args["additional_to"] = $to_list;
    if ($use_crm_smtp) {
        if (function_exists("upsellio_mailbox_log")) {
            $all_to = array_merge([$primary_to], $to_list);
            upsellio_mailbox_log(
                "mail",
                "info",
                "Wysyłka CRM — ścieżka PHPMailer SMTP (dedykowany serwer z ustawień).",
                "Do: " . implode(", ", $all_to) . " · załączników: {$att_n}"
            );
        }

        return upsellio_followup_send_html_mail_via_smtp($primary_to, $subject, $html, $args);
    }
    $to_email = $primary_to;
    $subject = sanitize_text_field((string) $subject);
    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log(
            "mail",
            "info",
            "Wysyłka CRM — ścieżka wp_mail() (transport jak dla reszty WordPressa).",
            "Załączników: {$att_n} · sprawdź też pluginy SMTP/WP Mail."
        );
    }
    $headers = ["Content-Type: text/html; charset=UTF-8"];
    $cc_join = [];
    foreach (($args["cc"] ?? []) as $cce) {
        $cce = sanitize_email((string) $cce);
        if (is_email($cce)) {
            $cc_join[] = $cce;
        }
    }
    if ($cc_join !== []) {
        $headers[] = "Cc: " . implode(", ", $cc_join);
    }
    $bcc_join = [];
    foreach (($args["bcc"] ?? []) as $bcce) {
        $bcce = sanitize_email((string) $bcce);
        if (is_email($bcce)) {
            $bcc_join[] = $bcce;
        }
    }
    if ($bcc_join !== []) {
        $headers[] = "Bcc: " . implode(", ", $bcc_join);
    }
    add_filter("wp_mail_from", "upsellio_followup_mail_from");
    add_filter("wp_mail_from_name", "upsellio_followup_mail_from_name");
    $GLOBALS["upsellio_crm_mail_attachments"] = $attachments;
    add_action("phpmailer_init", "upsellio_followup_phpmailer_add_inbox_attachments", 10, 1);
    add_action("phpmailer_init", "upsellio_followup_phpmailer_maybe_verbose_smtp", 20, 1);
    $wp_to = $to_email;
    $extra_to = $args["additional_to"] ?? [];
    if (is_array($extra_to) && $extra_to !== []) {
        $merge = [$to_email];
        foreach ($extra_to as $xt) {
            $xt = sanitize_email((string) $xt);
            if (is_email($xt)) {
                $merge[] = $xt;
            }
        }
        $wp_to = array_values(array_unique($merge));
    }
    $sent = wp_mail($wp_to, $subject, $html, $headers);
    remove_action("phpmailer_init", "upsellio_followup_phpmailer_add_inbox_attachments", 10);
    remove_action("phpmailer_init", "upsellio_followup_phpmailer_maybe_verbose_smtp", 20);
    unset($GLOBALS["upsellio_crm_mail_attachments"]);
    remove_filter("wp_mail_from", "upsellio_followup_mail_from");
    remove_filter("wp_mail_from_name", "upsellio_followup_mail_from_name");
    if (function_exists("upsellio_mailbox_log")) {
        if ($sent) {
            upsellio_mailbox_log("mail", "info", "wp_mail zakończone powodzeniem.", "Temat: {$subject}");
        } else {
            global $phpmailer;
            $err = is_object($phpmailer) && isset($phpmailer->ErrorInfo) ? (string) $phpmailer->ErrorInfo : "";
            upsellio_mailbox_log("mail", "error", "wp_mail zwróciło false.", $err !== "" ? $err : null);
        }
    }

    return (bool) $sent;
}

function upsellio_followup_send_due_queue()
{
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 300,
        "fields" => "ids",
    ]);
    foreach ($offers as $offer_id) {
        $offer_id = (int) $offer_id;
        if (function_exists("upsellio_offer_is_expired") && upsellio_offer_is_expired($offer_id)) {
            $queue = get_post_meta($offer_id, "_ups_offer_followup_queue", true);
            if (is_array($queue) && !empty($queue)) {
                $updated = false;
                foreach ($queue as $idx => $item) {
                    if ((string) ($item["status"] ?? "") === "queued") {
                        $queue[$idx]["status"] = "cancelled_expired";
                        $queue[$idx]["cancelled_at"] = current_time("mysql");
                        $updated = true;
                    }
                }
                if ($updated) {
                    update_post_meta($offer_id, "_ups_offer_followup_queue", $queue);
                    if (function_exists("upsellio_offer_add_timeline_event")) {
                        upsellio_offer_add_timeline_event($offer_id, "followup_cancelled", "Wstrzymano kolejke follow-up: oferta wygasla.");
                    }
                }
            }
            continue;
        }
        $queue = get_post_meta($offer_id, "_ups_offer_followup_queue", true);
        if (!is_array($queue) || empty($queue)) {
            continue;
        }
        $snooze_until_raw = (string) get_post_meta($offer_id, "_ups_offer_followup_snooze_until", true);
        $snooze_until_ts = $snooze_until_raw !== "" ? strtotime($snooze_until_raw) : false;
        if ($snooze_until_ts !== false && $snooze_until_ts > time()) {
            continue;
        }
        $updated = false;
        foreach ($queue as $idx => $item) {
            if ((string) ($item["status"] ?? "") !== "queued") {
                continue;
            }
            $send_at = strtotime((string) ($item["send_at"] ?? ""));
            if ($send_at === false || $send_at > time()) {
                continue;
            }
            $template_id = (int) ($item["template_id"] ?? 0);
            if ($template_id <= 0) {
                $queue[$idx]["status"] = "skipped";
                $updated = true;
                continue;
            }
            $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
            $client_email = $client_id > 0 ? sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true)) : "";
            if (!is_email($client_email)) {
                $queue[$idx]["status"] = "failed";
                $queue[$idx]["error"] = "missing_client_email";
                $updated = true;
                continue;
            }
            $stage = sanitize_key((string) ($item["stage"] ?? "awareness"));
            $subject_tpl = (string) get_post_meta($template_id, "_ups_followup_subject", true);
            $subject = upsellio_followup_resolve_placeholders($subject_tpl, $offer_id, $stage);
            if ($subject === "") {
                $subject = "Follow-up oferty: " . (string) get_the_title($offer_id);
            }
            if (strpos($subject, "[OFFER#") === false) {
                $subject .= " [OFFER#" . $offer_id . "]";
            }
            $html_tpl = (string) get_post_meta($template_id, "_ups_followup_html", true);
            if ($html_tpl === "") {
                $content_tpl = (string) get_post_field("post_content", $template_id);
                $content = upsellio_followup_resolve_placeholders($content_tpl, $offer_id, $stage);
                $html_tpl = wpautop(wp_kses_post($content));
            }
            $html_content = upsellio_followup_resolve_placeholders($html_tpl, $offer_id, $stage);
            $css = (string) get_post_meta($template_id, "_ups_followup_css", true);
            $html = "<html><head><meta charset='utf-8'><style>" . $css . "</style></head><body>" . $html_content . "</body></html>";
            $crm_mail_args = ["crm_smtp" => true];
            $sent = upsellio_followup_send_html_mail($client_email, $subject, $html, $crm_mail_args);
            $queue[$idx]["status"] = $sent ? "sent" : "failed";
            $queue[$idx]["sent_at"] = current_time("mysql");
            $queue[$idx]["template_id"] = $template_id;
            $updated = true;
            update_post_meta($offer_id, "_ups_offer_last_followup_template_id", $template_id);
            do_action("upsellio_followup_delivery_status", $offer_id, $template_id, $sent ? "sent" : "failed");
            if (function_exists("upsellio_offer_add_timeline_event")) {
                upsellio_offer_add_timeline_event($offer_id, $sent ? "followup_sent" : "followup_failed", "Follow-up: " . $subject);
            }
            if ($sent && function_exists("upsellio_inbox_append_message")) {
                $sender = upsellio_followup_get_sender_settings();
                $html_for_thread = upsellio_followup_finalize_crm_html($html, $crm_mail_args);
                upsellio_inbox_append_message($offer_id, [
                    "direction" => "out",
                    "from" => (string) ($sender["from_email"] ?? ""),
                    "to" => $client_email,
                    "subject" => $subject,
                    "body_plain" => wp_strip_all_tags($html_content),
                    "body_html" => $html_for_thread,
                    "source" => "followup_auto",
                    "read" => true,
                ]);
            }
        }
        if ($updated) {
            update_post_meta($offer_id, "_ups_offer_followup_queue", $queue);
        }
    }
}

function upsellio_followup_schedule_cron()
{
    if (!wp_next_scheduled("upsellio_followup_process_queue")) {
        wp_schedule_event(time() + 60, "upsellio_five_minutes", "upsellio_followup_process_queue");
    }
}
add_action("init", "upsellio_followup_schedule_cron");

function upsellio_followup_cron_intervals($schedules)
{
    $schedules["upsellio_five_minutes"] = [
        "interval" => 300,
        "display" => "Every 5 minutes",
    ];
    return $schedules;
}
add_filter("cron_schedules", "upsellio_followup_cron_intervals");
add_action("upsellio_followup_process_queue", "upsellio_followup_send_due_queue");

function upsellio_followup_extract_offer_id_from_text($text)
{
    $text = (string) $text;
    if ($text === "") {
        return 0;
    }
    if (preg_match("/\\[OFFER#(\\d+)\\]/", $text, $matches)) {
        return isset($matches[1]) ? (int) $matches[1] : 0;
    }
    return 0;
}

function upsellio_followup_find_offer_by_email($from_email)
{
    $from_email = sanitize_email((string) $from_email);
    if (!is_email($from_email)) {
        return 0;
    }
    $clients = get_posts([
        "post_type" => "crm_client",
        "post_status" => ["publish", "draft"],
        "posts_per_page" => 1,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_client_email",
            "value" => $from_email,
        ]],
    ]);
    $client_id = !empty($clients) ? (int) $clients[0] : 0;
    if ($client_id <= 0) {
        return 0;
    }
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 1,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_offer_client_id",
            "value" => (string) $client_id,
        ]],
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    return !empty($offers) ? (int) $offers[0] : 0;
}

function upsellio_followup_store_inbound_reply($offer_id, $from_email, $subject, $body, $source = "webhook")
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return false;
    }
    $subject = sanitize_text_field((string) $subject);
    $body = sanitize_textarea_field((string) $body);
    $from_email = sanitize_email((string) $from_email);
    $inbound = get_post_meta($offer_id, "_ups_offer_inbound_replies", true);
    if (!is_array($inbound)) {
        $inbound = [];
    }
    $fingerprint = md5(strtolower($from_email) . "|" . strtolower($subject) . "|" . $body);
    foreach (array_reverse($inbound) as $reply) {
        $existing_fingerprint = isset($reply["fingerprint"]) ? (string) $reply["fingerprint"] : "";
        if ($existing_fingerprint !== "" && hash_equals($existing_fingerprint, $fingerprint)) {
            return false;
        }
    }
    $inbound[] = [
        "ts" => current_time("mysql"),
        "from_email" => $from_email,
        "subject" => $subject,
        "body" => $body,
        "source" => sanitize_key((string) $source),
        "fingerprint" => $fingerprint,
    ];
    if (count($inbound) > 100) {
        $inbound = array_slice($inbound, -100);
    }
    update_post_meta($offer_id, "_ups_offer_inbound_replies", $inbound);
    if (function_exists("upsellio_inbox_append_message")) {
        $sender = upsellio_followup_get_sender_settings();
        $src_key = sanitize_key((string) $source);
        $inbox_src = $src_key === "imap" ? "reply_imap" : "reply_webhook";
        upsellio_inbox_append_message($offer_id, [
            "direction" => "in",
            "from" => $from_email,
            "to" => (string) ($sender["from_email"] ?? ""),
            "subject" => $subject,
            "body_plain" => $body,
            "body_html" => "",
            "source" => $inbox_src,
            "classification" => "",
            "read" => false,
        ]);
    }
    if (function_exists("upsellio_offer_add_timeline_event")) {
        upsellio_offer_add_timeline_event($offer_id, "inbound_reply", "Klient odpowiedzial na follow-up: " . $subject);
    }
    do_action("upsellio_followup_inbound_received", (int) $offer_id, (string) $subject, (string) $body, (string) $from_email);
    return true;
}

/**
 * Jednorazowe pobranie nieprzeczytanych z IMAP i dopisanie do wątków ofert.
 *
 * @return array{ok: bool, imported: int, processed: int, message: string}
 */
function upsellio_followup_run_mailbox_poll(): array
{
    $out = [
        "ok" => false,
        "imported" => 0,
        "processed" => 0,
        "message" => "",
    ];
    $settings = upsellio_followup_get_sender_settings();
    if (!$settings["mailbox_enabled"]) {
        $out["message"] = "Pobieranie odpowiedzi ze skrzynki (IMAP) jest wyłączone — włącz w Ustawienia → Mail / Skrzynki.";
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("imap", "warn", "Poll IMAP przerwany: skrzynka wyłączona w ustawieniach.");
        }

        return $out;
    }
    if (!function_exists("imap_open")) {
        $out["message"] = "Na serwerze brak rozszerzenia PHP IMAP — skontaktuj się z hostingiem.";
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("imap", "error", "Poll IMAP: brak rozszerzenia PHP imap.");
        }

        return $out;
    }
    $host = (string) $settings["mailbox_host"];
    $username = (string) $settings["mailbox_username"];
    $password = (string) $settings["mailbox_password"];
    if ($host === "" || $username === "" || $password === "") {
        $out["message"] = "Uzupełnij host, login i hasło IMAP w ustawieniach skrzynki.";
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("imap", "warn", "Poll IMAP: niepełna konfiguracja (host/login/hasło).");
        }

        return $out;
    }
    $port = max(1, (int) $settings["mailbox_port"]);
    $folder = (string) $settings["mailbox_folder"];
    $enc = (string) $settings["mailbox_encryption"];
    $flags = $enc === "ssl" ? "/imap/ssl" : ($enc === "tls" ? "/imap/tls" : "/imap/notls");
    $mailbox = "{" . $host . ":" . $port . $flags . "}" . $folder;
    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log("imap", "info", "Łączenie z IMAP…", "Mailbox string: {" . $host . ":" . $port . $flags . "}" . $folder . " · user: " . $username);
    }
    $imap = @imap_open($mailbox, $username, $password);
    if (!$imap) {
        $errs = imap_errors();
        $detail = is_array($errs) && $errs !== [] ? (string) end($errs) : "nie udało się połączyć";

        $out["message"] = "Błąd IMAP: " . $detail;
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("imap", "error", "imap_open nie powiodło się.", $detail);
        }

        return $out;
    }
    $messages = @imap_search($imap, "UNSEEN");
    if (!is_array($messages) || $messages === []) {
        imap_close($imap);
        update_option("ups_followup_mailbox_last_poll_at", current_time("mysql"), false);
        $out["ok"] = true;
        $out["message"] = "Brak nowych nieprzeczytanych wiadomości w folderze.";
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log("imap", "info", "Poll IMAP: brak wiadomości UNSEEN w folderze „{$folder}”.");
        }

        return $out;
    }
    $imported = 0;
    $processed = 0;
    $batch = array_slice($messages, 0, 25);
    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log("imap", "info", "Znaleziono nieprzeczytane wiadomości.", "UNSEEN count (batch max 25): " . count($batch));
    }
    foreach ($batch as $msg_no) {
        $processed++;
        $overview_list = @imap_fetch_overview($imap, (string) $msg_no, 0);
        $overview = is_array($overview_list) && isset($overview_list[0]) ? $overview_list[0] : null;
        $subject_raw = $overview && isset($overview->subject) ? (string) $overview->subject : "";
        $subject = function_exists("imap_utf8") ? (string) imap_utf8($subject_raw) : $subject_raw;
        $from_raw = $overview && isset($overview->from) ? (string) $overview->from : "";
        $from_email = "";
        if ($from_raw !== "" && preg_match("/<([^>]+)>/", $from_raw, $matches)) {
            $from_email = sanitize_email((string) ($matches[1] ?? ""));
        }
        if ($from_email === "") {
            $from_email = sanitize_email($from_raw);
        }
        $body_raw = (string) @imap_fetchbody($imap, (int) $msg_no, "1.1");
        if ($body_raw === "") {
            $body_raw = (string) @imap_body($imap, (int) $msg_no);
        }
        $body = trim(wp_strip_all_tags($body_raw));
        $offer_id = upsellio_followup_extract_offer_id_from_text($subject);
        if ($offer_id <= 0) {
            $offer_id = upsellio_followup_find_offer_by_email($from_email);
        }
        if ($offer_id > 0 && $body !== "") {
            upsellio_followup_store_inbound_reply($offer_id, $from_email, $subject, $body, "imap");
            $imported++;
            if (function_exists("upsellio_mailbox_log")) {
                upsellio_mailbox_log("imap", "info", "Zaimportowano odpowiedź do oferty #{$offer_id}.", "Od: {$from_email} · " . substr($subject, 0, 120));
            }
        } elseif (function_exists("upsellio_mailbox_log") && upsellio_mailbox_log_verbose_enabled()) {
            upsellio_mailbox_log(
                "imap",
                "debug",
                "Wiadomość pominięta (brak dopasowania oferty lub pusta treść).",
                "msg #{$msg_no} · od: {$from_email} · offer guess: {$offer_id}"
            );
        }
        @imap_setflag_full($imap, (string) $msg_no, "\\Seen");
    }
    imap_close($imap);
    update_option("ups_followup_mailbox_last_poll_at", current_time("mysql"), false);
    $out["ok"] = true;
    $out["imported"] = $imported;
    $out["processed"] = $processed;
    $out["message"] =
        $processed === 0
            ? "Nic do przetworzenia."
            : sprintf(
                "Przetworzono %d wiadomości, dopisano do CRM: %d (pozostałe bez dopasowania oferty lub pustej treści).",
                $processed,
                $imported
            );
    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log("imap", "info", "Poll IMAP zakończony.", "processed={$processed}, imported={$imported}");
    }

    return $out;
}

function upsellio_followup_poll_mailbox()
{
    upsellio_followup_run_mailbox_poll();
}
add_action("upsellio_followup_process_queue", "upsellio_followup_poll_mailbox", 20);

function upsellio_followup_register_rest_routes()
{
    register_rest_route("upsellio/v1", "/followup-inbound", [
        "methods" => "POST",
        "permission_callback" => "__return_true",
        "callback" => "upsellio_followup_handle_inbound",
    ]);
}
add_action("rest_api_init", "upsellio_followup_register_rest_routes");

function upsellio_followup_handle_inbound(WP_REST_Request $request)
{
    $settings = upsellio_followup_get_sender_settings();
    $secret_param = sanitize_text_field((string) $request->get_param("secret"));
    $secret_header = sanitize_text_field((string) $request->get_header("x-upsellio-secret"));
    $provided_secret = $secret_header !== "" ? $secret_header : $secret_param;
    $expected_secret = (string) $settings["inbound_secret"];
    if ($expected_secret === "" || $provided_secret === "" || !hash_equals($expected_secret, $provided_secret)) {
        return new WP_REST_Response(["ok" => false, "message" => "unauthorized"], 401);
    }

    $offer_id = (int) $request->get_param("offer_id");
    $from_email = sanitize_email((string) $request->get_param("from_email"));
    $subject = sanitize_text_field((string) $request->get_param("subject"));
    $body = sanitize_textarea_field((string) $request->get_param("body"));

    if ($offer_id <= 0 && is_email($from_email)) {
        $clients = get_posts([
            "post_type" => "crm_client",
            "post_status" => ["publish", "draft"],
            "posts_per_page" => 1,
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_client_email",
                "value" => $from_email,
            ]],
        ]);
        $client_id = !empty($clients) ? (int) $clients[0] : 0;
        if ($client_id > 0) {
            $offers = get_posts([
                "post_type" => "crm_offer",
                "post_status" => ["publish", "draft", "pending", "private"],
                "posts_per_page" => 1,
                "fields" => "ids",
                "meta_query" => [[
                    "key" => "_ups_offer_client_id",
                    "value" => (string) $client_id,
                ]],
                "orderby" => "modified",
                "order" => "DESC",
            ]);
            $offer_id = !empty($offers) ? (int) $offers[0] : 0;
        }
    }

    if ($offer_id <= 0) {
        return new WP_REST_Response(["ok" => false, "message" => "offer_not_found"], 404);
    }

    upsellio_followup_store_inbound_reply($offer_id, $from_email, $subject, $body, "webhook");

    return new WP_REST_Response(["ok" => true], 200);
}
