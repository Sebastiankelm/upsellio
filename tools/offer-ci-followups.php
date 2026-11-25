<?php
// Execute on the disposable CI database only. No SMTP call is expected.
if (!defined('WP_CLI') || !WP_CLI || wp_get_environment_type() !== 'local') { exit(1); }
require get_template_directory() . '/inc/followups.php';
function ci_fu_expect($ok, $label) {
    if (!$ok) { WP_CLI::error($label); }
    WP_CLI::line('PASS: ' . $label);
}
$now = time();
ci_fu_expect(upsellio_followup_delivery_policy([], $now, 5, 24)['reason'] === '', 'Empty queue permits delivery');
ci_fu_expect(upsellio_followup_delivery_policy([['status'=>'sent','sent_at_ts'=>$now-10]],$now,5,24)['reason']==='cooldown','Cooldown enforced at delivery time');
ci_fu_expect(upsellio_followup_delivery_policy([['status'=>'sent','sent_at_ts'=>$now-90000]],$now,1,0)['reason']==='limit','Maximum delivery count enforced');
ci_fu_expect(upsellio_followup_delivery_policy([['status'=>'delivery_unknown']],$now,5,0)['reason']==='uncertain_delivery','Uncertain delivery blocks repeat send');
$id = wp_insert_post(['post_type'=>'crm_offer','post_status'=>'publish','post_title'=>'CI follow-up policy only']);
if (!$id || is_wp_error($id)) { WP_CLI::error('Fixture insert failed'); }
try {
    update_post_meta($id,'_ups_offer_status','sent');
    update_post_meta($id,'_ups_offer_expires_at',$now+3600);
    ci_fu_expect(upsellio_followup_queue_lock($id,0),'MariaDB advisory lock available');
    global $wpdb;
    $lock = 'ups_fu_' . md5($wpdb->postmeta . ':' . $id);
    $second = new mysqli('127.0.0.1', DB_USER, DB_PASSWORD, DB_NAME, 3306);
    $result = $second->query("SELECT GET_LOCK('" . $second->real_escape_string($lock) . "',0) AS acquired")->fetch_assoc();
    ci_fu_expect((int)$result['acquired']===0,'Second DB connection cannot acquire held offer lock');
    upsellio_followup_queue_unlock($id);
    $result = $second->query("SELECT GET_LOCK('" . $second->real_escape_string($lock) . "',0) AS acquired")->fetch_assoc();
    ci_fu_expect((int)$result['acquired']===1,'Lock released for another worker');
    $second->close();
    $due = gmdate('Y-m-d H:i:s',$now-60);
    update_post_meta($id,'_ups_offer_followup_queue',[
        ['status'=>'sending','signature'=>'ci-interrupted','claimed_at_ts'=>$now-60],
        ['status'=>'queued','signature'=>'ci-second','send_at'=>$due,'template_id'=>0]
    ]);
    upsellio_followup_send_due_queue();
    $q=get_post_meta($id,'_ups_offer_followup_queue',true);
    ci_fu_expect($q[0]['status']==='delivery_unknown' && $q[1]['status']==='queued','Interrupted delivery does not automatically resend');
    update_post_meta($id,'_ups_offer_followup_queue',[['status'=>'queued','signature'=>'ci-pause','send_at'=>$due,'template_id'=>0]]);
    update_post_meta($id,'_ups_offer_followup_pending_pause',$now);
    upsellio_followup_send_due_queue();
    $q=get_post_meta($id,'_ups_offer_followup_queue',true);
    ci_fu_expect($q[0]['status']==='paused_inbound_reply','Pending inbound pause applied before delivery');
    update_post_meta($id,'_ups_offer_status','won');
    update_post_meta($id,'_ups_offer_followup_queue',[['status'=>'queued','signature'=>'ci-closed','send_at'=>$due,'template_id'=>0]]);
    upsellio_followup_send_due_queue();
    $q=get_post_meta($id,'_ups_offer_followup_queue',true);
    ci_fu_expect($q[0]['status']==='cancelled_closed','Closed offer queue cancelled');
} finally { wp_delete_post($id,true); }
WP_CLI::success('Follow-up delivery policy checks passed.');
