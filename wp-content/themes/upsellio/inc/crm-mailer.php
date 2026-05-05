<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_email_is_bounced(string $email): bool
{
    $list = (array) get_option("ups_email_bounced", []);
    return isset($list[strtolower(trim($email))]);
}

function upsellio_send_crm_mail(string $to, string $subject, string $body_html, array $opts = []): bool
{
    if (!is_email($to)) {
        return false;
    }
    if (upsellio_email_is_bounced($to)) {
        error_log("[crm-mailer] Skipped bounced email: {$to}");
        return false;
    }

    $type = (string) ($opts["type"] ?? "transactional");
    if ($type === "marketing" && function_exists("upsellio_email_is_unsubscribed") && upsellio_email_is_unsubscribed($to)) {
        error_log("[crm-mailer] Skipped marketing email to unsubscribed: {$to}");
        return false;
    }

    $allow_internal = !empty($opts["allow_internal"]);
    if (!$allow_internal && function_exists("get_user_by")) {
        $user = get_user_by("email", $to);
        if ($user && in_array("administrator", (array) $user->roles, true) && $type === "marketing") {
            error_log("[crm-mailer] Skipped marketing to internal user: {$to}");
            return false;
        }
    }

    $token = function_exists("upsellio_email_unsub_token") ? upsellio_email_unsub_token($to) : "";
    $rendered_html = function_exists("upsellio_email_render")
        ? upsellio_email_render($body_html, [
            "preheader" => (string) ($opts["preheader"] ?? ""),
            "recipient_email" => $to,
            "unsubscribe_token" => $token,
        ])
        : $body_html;

    $domain = (string) (parse_url(home_url("/"), PHP_URL_HOST) ?: "upsellio.pl");
    $unsub_url = add_query_arg(["ups_unsub" => $token, "email" => rawurlencode($to)], home_url("/"));
    $from_email = (string) get_option("ups_followup_from_email", get_bloginfo("admin_email"));
    $from_name = (string) get_bloginfo("name");
    $reply_to = (string) ($opts["reply_to"] ?? $from_email);
    $message_id = "<" . wp_generate_uuid4() . "@{$domain}>";

    $headers = [
        "Content-Type: text/html; charset=UTF-8",
        "From: {$from_name} <{$from_email}>",
        "Reply-To: <{$reply_to}>",
        "Message-ID: {$message_id}",
        "X-Mailer: Upsellio-CRM/1.0",
        "List-Unsubscribe: <{$unsub_url}>, <mailto:unsub@{$domain}?subject=Unsubscribe>",
        "List-Unsubscribe-Post: List-Unsubscribe=One-Click",
    ];
    if (!empty($opts["in_reply_to"])) {
        $thread_id = trim((string) $opts["in_reply_to"], "<>");
        $headers[] = "In-Reply-To: <{$thread_id}>";
        $headers[] = "References: <{$thread_id}>";
    }

    $alt_body = function_exists("upsellio_email_to_plain") ? upsellio_email_to_plain($rendered_html) : wp_strip_all_tags($rendered_html);
    $set_alt = function ($phpmailer) use ($alt_body) {
        $phpmailer->AltBody = $alt_body;
    };
    add_action("phpmailer_init", $set_alt);

    $sent = false;
    $smtp_enabled = (string) get_option("ups_followup_smtp_enabled", "0") === "1";
    if ($smtp_enabled && function_exists("upsellio_followup_send_html_mail")) {
        $sent = (bool) upsellio_followup_send_html_mail($to, $subject, $rendered_html, ["crm_smtp" => true, "headers" => $headers]);
    } else {
        $sent = (bool) wp_mail($to, $subject, $rendered_html, $headers);
    }

    remove_action("phpmailer_init", $set_alt);
    upsellio_email_log($to, $subject, $type, $sent);
    return $sent;
}

function upsellio_email_log(string $to, string $subject, string $type, bool $sent): void
{
    $log = get_option("ups_crm_email_log", []);
    if (!is_array($log)) {
        $log = [];
    }
    array_unshift($log, [
        "ts" => current_time("mysql"),
        "to" => $to,
        "subject" => $subject,
        "type" => $type,
        "sent" => $sent,
    ]);
    if (count($log) > 500) {
        $log = array_slice($log, 0, 500);
    }
    update_option("ups_crm_email_log", $log, false);
}

add_action("admin_menu", function () {
    add_submenu_page(
        "upsellio-site-analytics",
        "Email log",
        "Email log",
        "edit_posts",
        "upsellio-email-log",
        "upsellio_render_email_log"
    );
});

function upsellio_render_email_log(): void
{
    $log = (array) get_option("ups_crm_email_log", []);
    ?>
    <div class="wrap">
      <h1>Email log — ostatnie 200</h1>
      <table class="wp-list-table widefat striped">
        <thead><tr><th>Czas</th><th>Do</th><th>Temat</th><th>Typ</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($log, 0, 200) as $row) : ?>
          <tr>
            <td><?php echo esc_html((string) ($row["ts"] ?? "")); ?></td>
            <td><?php echo esc_html((string) ($row["to"] ?? "")); ?></td>
            <td><?php echo esc_html((string) mb_substr((string) ($row["subject"] ?? ""), 0, 60)); ?></td>
            <td><?php echo esc_html((string) ($row["type"] ?? "")); ?></td>
            <td><?php echo !empty($row["sent"]) ? "✅" : "❌"; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
}
