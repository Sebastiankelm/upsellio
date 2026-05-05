<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_email_render(string $content_html, array $opts = []): string
{
    $preheader = (string) ($opts["preheader"] ?? "");
    $recipient = (string) ($opts["recipient_email"] ?? "");
    $unsub_token = (string) ($opts["unsubscribe_token"] ?? "");
    $unsub_url = $unsub_token !== ""
        ? add_query_arg(["ups_unsub" => $unsub_token, "email" => rawurlencode($recipient)], home_url("/"))
        : home_url("/polityka-prywatnosci/");

    $brand_color = "#0d9488";
    $site_name = esc_html(get_bloginfo("name"));
    $site_url = esc_url(home_url("/"));
    $site_address = esc_html((string) apply_filters("upsellio_email_address", "Upsellio · Polska"));

    $css = "
        body { margin:0; padding:0; background:#f8fafc; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#0f172a; }
        .ups-container { max-width:580px; margin:0 auto; background:#ffffff; }
        .ups-header { padding:24px 32px 16px; border-bottom:1px solid #e5e7eb; }
        .ups-logo { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:22px; font-weight:800; color:{$brand_color}; text-decoration:none; }
        .ups-tagline { font-size:11px; color:#64748b; margin-top:4px; letter-spacing:0.3px; }
        .ups-body { padding:28px 32px; font-size:15px; line-height:1.6; }
        .ups-body p { margin:0 0 14px; }
        .ups-body a { color:{$brand_color}; text-decoration:underline; }
        .ups-cta { display:inline-block; padding:12px 24px; background:{$brand_color}; color:#fff !important; text-decoration:none !important; border-radius:8px; font-weight:700; font-size:14px; margin:8px 0; }
        .ups-footer { padding:20px 32px; border-top:1px solid #e5e7eb; background:#f8fafc; font-size:11px; color:#64748b; line-height:1.5; }
        .ups-footer a { color:#64748b; }
        .ups-preheader { display:none; max-height:0; overflow:hidden; opacity:0; }
        @media (max-width:600px) {
          .ups-container { width:100% !important; }
          .ups-header, .ups-body, .ups-footer { padding-left:18px !important; padding-right:18px !important; }
        }
    ";

    return "<!DOCTYPE html><html lang=\"pl\"><head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><meta name=\"x-apple-disable-message-reformatting\"><title>{$site_name}</title><style>{$css}</style></head><body><span class=\"ups-preheader\">{$preheader}</span><div class=\"ups-container\"><div class=\"ups-header\"><a href=\"{$site_url}\" class=\"ups-logo\">Upsellio</a><div class=\"ups-tagline\">Marketing B2B · System sprzedażowy</div></div><div class=\"ups-body\">{$content_html}</div><div class=\"ups-footer\">Wiadomość wysłana z systemu CRM Upsellio do " . esc_html($recipient) . ".<br>{$site_address} · <a href=\"{$site_url}\">{$site_url}</a><br><a href=\"" . esc_url($unsub_url) . "\">Wypisz się z dalszych wiadomości</a> · <a href=\"" . esc_url(home_url("/polityka-prywatnosci/")) . "\">Polityka prywatności</a></div></div></body></html>";
}

function upsellio_email_to_plain(string $html): string
{
    $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $text = preg_replace('/<\/p>/i', "\n\n", (string) $text);
    $text = preg_replace('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i', '$2 ($1)', (string) $text);
    $text = wp_strip_all_tags((string) $text);
    $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $text = preg_replace("/\n{3,}/", "\n\n", (string) $text);
    return trim((string) $text);
}

function upsellio_email_unsub_token(string $email): string
{
    $secret = wp_salt("auth");
    $payload = strtolower(trim($email)) . "|" . floor(time() / DAY_IN_SECONDS / 365);
    return hash_hmac("sha256", $payload, $secret);
}

function upsellio_email_verify_unsub_token(string $email, string $token): bool
{
    return hash_equals(upsellio_email_unsub_token($email), $token);
}
