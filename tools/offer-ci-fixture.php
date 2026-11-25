<?php
// Run only via wp eval-file on the disposable CI WordPress database.
if (!defined('WP_CLI') || !WP_CLI || wp_get_environment_type() !== 'local') { exit(1); }
$client = wp_insert_post(['post_type'=>'crm_client','post_status'=>'publish','post_title'=>'Klient testowy']);
$offer = wp_insert_post(['post_type'=>'crm_offer','post_status'=>'publish','post_title'=>'Oferta testowa — kampania sprzedażowa','post_content'=>'<h3>Dodatkowe informacje</h3><p>Zakres syntetyczny do testów.</p>']);
if (!$offer || !$client) { WP_CLI::error('Fixture insert failed'); }
$meta = [
'_ups_offer_client_id'=>$client, '_ups_offer_status'=>'sent', '_ups_offer_public_slug'=>'ci-offer-synthetic-42',
'_ups_offer_current_version'=>1, '_ups_offer_price'=>'1 800 zł', '_ups_offer_timeline'=>'7 dni',
'_ups_offer_expires_at'=>time()+86400, '_ups_offer_owner_id'=>1, '_ups_offer_has_google'=>'1',
'_ups_offer_has_meta'=>'0','_ups_offer_has_web'=>'0',
'_ups_offer_questions_raw'=>'Jaki jest budżet?','_ups_offer_services_json'=>wp_json_encode([
['key'=>'base','label'=>'Podstawowy','price_hint'=>'1 800 zł'],['key'=>'plus','label'=>'Rozszerzony','price_hint'=>'2 800 zł']]),
'_ups_offer_lead'=>'Syntetyczna oferta do testów interfejsu i analityki.',
'_ups_offer_include_lines'=>'Audyt i strategia','_ups_offer_option_lines'=>'Rozszerzenie zakresu'
];
foreach ($meta as $k=>$v) update_post_meta($offer,$k,$v);
update_post_meta($client,'_ups_client_email','fixture@example.invalid');
WP_CLI::line((string)$offer);
