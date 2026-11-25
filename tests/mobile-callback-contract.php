<?php
// Isolated contract test: WP/CRM side effects are stubbed; no live data is used.
$source = file_get_contents(__DIR__ . "/../wp-content/themes/upsellio/inc/crm.php");
$start = strpos($source, "function upsellio_crm_handle_mobile_callback()");
$end = strpos($source, 'add_action("wp_ajax_upsellio_mobile_callback"', $start);
if ($start === false || $end === false) {
    throw new RuntimeException("Callback handler not found");
}
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
check(request()->getCode() === 429, "Phone rate limit across formats");
$saveFails = true;
check(request(["lead_phone"=>"575123457"])->getCode() === 500, "Save failure");
check(count($notifications) === 3 && count($followups) === 3, "No notification on failed save");
echo "Mobile callback contract checks passed.\n";
