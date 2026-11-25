<?php
// Isolated contract test: WP/CRM side effects are stubbed; no live data is used.
$source = file_get_contents(__DIR__ . "/../wp-content/themes/upsellio/inc/crm.php");
$start = strpos($source, "function upsellio_crm_handle_mobile_callback()");
$end = strpos($source, 'add_action("wp_ajax_upsellio_mobile_callback"', $start);
if ($start === false || $end === false) {
    throw new RuntimeException("Callback handler not found");
}
$helpersStart = strpos($source, "function upsellio_crm_is_short_diagnosis_origin(");
$helpersEnd = strpos($source, "function upsellio_crm_handle_lead_submission()", $helpersStart);
if ($helpersStart === false || $helpersEnd === false) throw new RuntimeException("Diagnosis helpers not found");
eval(substr($source, $helpersStart, $helpersEnd - $helpersStart));
eval(substr($source, $start, $end - $start));

class CallbackResponse extends Exception {
    public $success;
    public $data;
    public function __construct($success, $data, $status) {
        parent::__construct("Response", $status);
        $this->success = $success;
        $this->data = $data;
    }
}
function wp_send_json_error($data, $status = 400) { throw new CallbackResponse(false, $data, $status); }
function wp_send_json_success($data) { throw new CallbackResponse(true, $data, 200); }
function wp_unslash($value) { return $value; }
function sanitize_text_field($value) { return trim(strip_tags($value)); }
function wp_verify_nonce($value, $action) { return $value === "valid" && $action === "upsellio_unified_lead_form"; }
function upsellio_crm_is_endpoint_rate_limited() { return $GLOBALS["endpointLimited"]; }
function upsellio_crm_is_rate_limited($email) { return $GLOBALS["ipLimited"]; }
function wp_salt($scheme) { return "test-salt"; }
function get_transient($key) { return $GLOBALS["attempts"][$key] ?? 0; }
function set_transient($key, $value, $ttl) { $GLOBALS["attempts"][$key] = $value; }
function current_time($type) { return "2026-11-25 12:00:00"; }
function get_option($key, $default) { return $default; }
function upsellio_crm_create_lead($payload) { $GLOBALS["leads"][] = $payload; return $GLOBALS["saveFails"] ? 0 : 42; }
function upsellio_crm_send_emails($id, $name, $email, $message) { $GLOBALS["notifications"][] = [$id,$email,$message]; }
function upsellio_crm_schedule_followup($id) { $GLOBALS["followups"][] = $id; }
define("HOUR_IN_SECONDS", 3600);
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function request($overrides = [], $method = "POST") {
    $_SERVER["REQUEST_METHOD"] = $method;
    $_POST = array_merge(["nonce"=>"valid","lead_phone"=>"575 123 456","lead_consent"=>"1"], $overrides);
    try { upsellio_crm_handle_mobile_callback(); }
    catch (CallbackResponse $response) { return $response; }
    throw new RuntimeException("Handler returned without JSON");
}
$endpointLimited = $ipLimited = $saveFails = false;
$attempts = $leads = $notifications = $followups = [];
check(request([], "GET")->getCode() === 405, "GET rejected");
check(request(["nonce"=>"invalid"])->getCode() === 403, "Nonce rejected");
check(request(["lead_consent"=>"0"])->getCode() === 400, "Consent required");
check(request(["lead_website"=>"spam"])->getCode() === 400, "Honeypot rejected");
check(request(["lead_phone"=>"123"])->getCode() === 400, "Short phone rejected");
check(request(["lead_phone"=>["invalid"]])->getCode() === 400, "Array phone rejected");
check(count($leads) === 0, "Invalid requests never save");
$endpointLimited = true;
check(request()->getCode() === 429, "Endpoint limit");
$endpointLimited = false;
$ipLimited = true;
check(request()->getCode() === 429, "IP limit");
$ipLimited = false;
check(request()->success, "Valid callback succeeds");
check($leads[0]["phone"] === "+48575123456", "Phone normalization");
check($leads[0]["email"] === "" && $leads[0]["form_origin"] === "mobile-callback", "No invented email; correct origin");
check($leads[0]["consent_version"] === "v1.0" && $leads[0]["consent_timestamp"] !== "", "Consent recorded");
check(count($notifications) === 1 && $notifications[0][1] === "" && count($followups) === 1, "Admin notification and followup");
check(request(["lead_phone"=>"+48 575 123 456"])->success, "Second equivalent phone");
check(request(["lead_phone"=>"48575123456"])->success, "Third equivalent phone");
check(request(["lead_phone"=>"0048 575 123 456"])->getCode() === 429, "Phone rate limit across formats including 00");
check(request(["lead_phone"=>"+48 1234567"])->getCode() === 400, "Invalid Polish international length");
check(request(["lead_phone"=>"123456789012"])->getCode() === 400, "Ambiguous foreign number requires prefix");
check(request(["lead_phone"=>"+44 7700 900123"])->success, "Explicit foreign number accepted");
check($leads[3]["phone"] === "+447700900123", "International number normalized");
$saveFails = true;
check(request(["lead_phone"=>"575123457"])->getCode() === 500, "Save failure");
check(count($notifications) === 4 && count($followups) === 4, "No notification on failed save");
echo "Mobile callback contract checks passed.\n";

// Exercise the real sync functions against failed and successful WordPress inserts.
$startSync = strpos($source, "function upsellio_crm_sync_wp_lead_to_crm_lead_module(");
$endSync = strpos($source, "function upsellio_crm_find_existing_inbox_offer_by_email(", $startSync);
check($startSync !== false && $endSync !== false, "Sync functions available");
eval(substr($source, $startSync, $endSync - $startSync));
class WP_Error {}
class WP_Post {
    public $post_title = "Prośba o oddzwonienie";
    public $post_content = "Test callback";
    public $post_author = 7;
}
function get_post_type($id) { return $id === 42 ? "lead" : ""; }
function post_type_exists($type) { return true; }
function get_post_meta($id, $key, $single) {
    return ["_upsellio_lead_phone"=>"+48575123456", "_upsellio_lead_form_origin"=>"mobile-callback"][$key] ?? "";
}
function get_post($id) { return new WP_Post(); }
function sanitize_email($value) { return $value; }
function sanitize_textarea_field($value) { return $value; }
function upsellio_crm_run_as_user($id, $callback) { return $callback(); }
function wp_insert_post($data, $returnError) { return $GLOBALS["insertFails"] ? new WP_Error() : ($data["post_type"] === "crm_lead" ? 101 : 102); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function update_post_meta($id, $key, $value) { $GLOBALS["metaWrites"][] = [$id,$key,$value]; }
function upsellio_crm_find_existing_inbox_offer_by_email($email) { return 0; }
function upsellio_inbox_append_message($id, $message) { $GLOBALS["inboxWrites"][] = [$id,$message]; }
$insertFails = true;
$metaWrites = $inboxWrites = [];
check(upsellio_crm_sync_wp_lead_to_crm_lead_module(42) === 0, "CRM insert error is not cast to ID");
check(upsellio_crm_sync_wp_lead_to_inbox_offer(42) === 0, "Inbox insert error is not cast to ID");
check($metaWrites === [] && $inboxWrites === [], "Failed sync never writes metadata or messages");
$insertFails = false;
check(upsellio_crm_sync_wp_lead_to_crm_lead_module(42) === 101, "Successful CRM sync returns real ID");
check(upsellio_crm_sync_wp_lead_to_inbox_offer(42) === 102, "Successful Inbox sync returns real ID");
check(in_array([101,"_ups_lead_phone","+48575123456"], $metaWrites, true), "CRM phone preserved");
check(in_array([42,"_upsellio_synced_crm_lead_id",101], $metaWrites, true), "Lead linked to CRM");
check(in_array([42,"_upsellio_synced_inbox_offer_id",102], $metaWrites, true), "Lead linked to inbox");
check(count($inboxWrites) === 1 && $inboxWrites[0][0] === 102, "Inbox message stored on correct record");
echo "CRM sync failure and success contracts passed.\n";

foreach (["home-hero-form","home-contact-form","contact-page-form"] as $origin) {
    check(upsellio_crm_is_short_diagnosis_origin($origin), "Allowed short origin: ".$origin);
}
foreach (["blog-form","landing-butik-form","audit-form","contact-form","unknown","mobile-callback"] as $origin) {
    check(!upsellio_crm_is_short_diagnosis_origin($origin), "Legacy origin unchanged: ".$origin);
}
check(upsellio_crm_normalize_diagnosis_phone("0048 575 123 456") === "+48575123456", "Shared phone normalization");
check(upsellio_crm_is_phone_rate_limited("+48575123456"), "Shared rate limit with callback");
echo "Short diagnosis origin and shared phone contracts passed.\n";

// Run the real unified POST handler without WordPress or a database.
$handlerStart = strpos($source, "function upsellio_crm_handle_lead_submission()");
$handlerEnd = strpos($source, 'add_action("admin_post_upsellio_submit_lead"', $handlerStart);
check($handlerStart !== false && $handlerEnd !== false, "Unified handler available");
eval(substr($source, $handlerStart, $handlerEnd - $handlerStart));
function home_url($path = "/") { return "https://example.test" . $path; }
function is_email($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function esc_url_raw($url) { return $url; }
function upsellio_extract_search_query_from_referrer($referrer) { return ""; }
function upsellio_crm_redirect_lead_form_success($url) { throw new CallbackResponse(true, [], 200); }
function upsellio_crm_redirect_lead_form_error($url, $reason) { throw new CallbackResponse(false, ["reason"=>$reason], 400); }
function diagnosis($overrides = []) {
    $_POST = array_merge([
        "upsellio_lead_form_nonce"=>"valid",
        "lead_form_origin"=>"home-hero-form",
        "lead_name"=>"Jan",
        "lead_phone"=>"575123458",
        "lead_consent"=>"1",
        "lead_company"=>"example.test",
    ], $overrides);
    try { upsellio_crm_handle_lead_submission(); }
    catch (CallbackResponse $response) { return $response; }
    throw new RuntimeException("Diagnosis handler returned without redirect");
}
$saveFails = false;
$before = count($leads);
check(diagnosis()->success, "Homepage diagnosis accepted without email");
check($leads[$before]["name"] === "Jan" && $leads[$before]["email"] === "", "Real name retained, no invented email");
check($leads[$before]["phone"] === "+48575123458", "Canonical phone stored");
check($leads[$before]["company"] === "example.test", "Optional site stored");
check(diagnosis(["lead_form_origin"=>"home-contact-form","lead_phone"=>"575123459"])->success, "Bottom homepage form accepts short contract");
check(diagnosis(["lead_form_origin"=>"contact-page-form","lead_phone"=>"575123460"])->success, "Contact page accepts short contract");
check(!diagnosis(["lead_form_origin"=>"blog-form"])->success, "Blog email remains required");
check(!diagnosis(["lead_form_origin"=>"landing-butik-form"])->success, "Boutique email remains required");
check(!diagnosis(["lead_shop_url"=>""])->success, "Shop requirement cannot be bypassed");
check(!diagnosis(["lead_name"=>""])->success, "Name required");
check(!diagnosis(["lead_consent"=>"0"])->success, "Contact consent required");
check(!diagnosis(["lead_phone"=>"123"])->success, "Valid phone required");
check(!diagnosis(["lead_email"=>"not-an-email"])->success, "Malformed legacy optional email rejected");
check(count($leads) === $before + 3, "Invalid diagnosis never stored");
check(diagnosis(["lead_form_origin"=>"blog-form","lead_email"=>"jan@example.test"])->success, "Legacy form with email still works");
echo "Unified diagnosis POST and legacy form contracts passed.\n";
