<?php
// Verify persistence only in the disposable integration database.
if (!defined('WP_CLI') || !WP_CLI || wp_get_environment_type() !== 'local') { exit(1); }
$ids = get_posts(['post_type'=>'crm_offer','post_status'=>'publish','fields'=>'ids','posts_per_page'=>1,'meta_key'=>'_ups_offer_public_slug','meta_value'=>'ci-offer-synthetic-42']);
if (!$ids) { WP_CLI::error('Fixture missing'); }
$id = (int) $ids[0];
function ci_expect($condition, $label) {
    if (!$condition) { WP_CLI::error($label); }
    WP_CLI::line('PASS: '.$label);
}
ci_expect(get_post_meta($id,'_ups_offer_status',true)==='won','Persisted acceptance status');
$accept = get_post_meta($id,'_ups_offer_accept_payload',true);
ci_expect(is_array($accept) && ($accept['commit_key']??'')==='base' && ($accept['commit_price']??'')==='1 800 zł','Canonical selected variant and price');
ci_expect(count(get_post_meta($id,'_ups_offer_accepted_terms_snapshot',false))===1,'Exactly one accepted terms snapshot');
ci_expect(get_post_meta($id,'_ups_offer_timeline',true)==='7 dni','Commercial timeline unchanged');
$client = (int)get_post_meta($id,'_ups_offer_client_id',true);
ci_expect(get_post_meta($client,'_ups_client_email',true)==='fixture@example.invalid','Public form did not overwrite verified contact');
$qa = get_post_meta($id,'_ups_offer_qa_responses',true);
ci_expect(count($qa['question_answers']??[])===1 && ($qa['question_answers'][0]['question']??'')==='Jaki jest budżet?','Q&A contains original question and ignores unknown field');
$events = get_post_meta($id,'_ups_offer_events',true);
ci_expect(is_array($events)&&count($events)>0,'Real AJAX events persisted');
$eventIds = array_column($events,'event_id');
ci_expect(count($eventIds)===count(array_unique($eventIds)),'No duplicate event IDs');
ci_expect(!in_array('step_9',array_column($events,'section_id'),true),'Forged step not stored');
ci_expect(!upsellio_offer_build_analytics_summary($id)['is_hot'],'Closed offer not HOT');
$snapshot = get_post_meta($id,'_ups_offer_accepted_terms_snapshot',true);
ci_expect(($snapshot['timeline']??'')==='7 dni' && array_key_exists('scope_extra_html',$snapshot),'Snapshot preserves commercial timeline and scope');
ci_expect(!get_post_meta($id,'_ups_offer_accept_integrity_error',true),'No acceptance integrity error');
$captured = get_option('offer_ci_mail_capture', []);
ci_expect(count($captured)>0,'Internal mail notification captured without actual delivery');
foreach ($captured as $mail) {
    $headers = is_array($mail['headers']) ? implode("\n",$mail['headers']) : (string)$mail['headers'];
    ci_expect(stripos($headers,'Cc: test@example.invalid')===false && stripos($headers,'Bcc: test@example.invalid')===false,'Unverified address does not receive private copy');
}
ci_expect(upsellio_offer_public_verified_copy_allowed($id,'fixture@example.invalid'),'CRM contact is eligible for private copy');
ci_expect(!upsellio_offer_public_verified_copy_allowed($id,'unknown@example.invalid'),'Other address is not eligible');
$payload = upsellio_offer_get_landing_payload($id);
ci_expect($payload['duration']==='' && $payload['billing']==='' && $payload['price_note']==='' ,'Unsaved terms do not appear in public offer');
WP_CLI::success('Database persistence checks passed.');
