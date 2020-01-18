<?php
/*
 * cron de récupération des payouts profs
 * pas encore en prod
 *
 *
 */
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


define('PROBLEME_CLIENT', true);

$test_mode = false;
$get_past_payout = false;


$profMg = new \spamtonprof\stp_api\StpProfManager();
$profs = $profMg->getAll(array(
    'inbox_ready' => true
));


$key_payout = 'last_payout_of_prof';
if ($get_past_payout) {
    $key_payout = 'first_payout_of_prof';
}

foreach ($profs as $prof) {
    
    $stripe_prof = new \spamtonprof\stp_api\StripeManager($test_mode, $prof->getEmail_stp());
    $stripePayoutMg = new \spamtonprof\stp_api\StripePayoutManager();
    
    $payout = $stripePayoutMg->get(array(
        'key' => $key_payout,
        'params' => array(
            'ref_prof' => $prof->getRef_prof()
        )
    ));
    
    $payouts = [];
    $stripe_payout_id = false;
    if ($payout) {
        $stripe_payout_id = $payout->getRef_stripe();
        
        
        if (!$get_past_payout) {
            $payouts = $stripe_prof->list_payouts(false, $stripe_payout_id);
        } else {
            $payouts = $stripe_prof->list_payouts($stripe_payout_id, false);
        }
    } else {
        $payouts = $stripe_prof->list_payouts();
    }
    
    foreach ($payouts as $payout) {
        
        $arrival_date = new \DateTime();
        $arrival_date->setTimestamp($payout->arrival_date);
        
        $created = new \DateTime();
        $created->setTimestamp($payout->created);
        
        $stripePayout = new \spamtonprof\stp_api\StripePayout(array(
            'ref_stripe' => $payout->id,
            'ref_prof' => $prof->getRef_prof(),
            'amount' => $payout->amount,
            'date_versement' => $arrival_date->format(PG_DATETIME_FORMAT),
            'test_mode' => $test_mode,
            'created' => $created->format(PG_DATETIME_FORMAT)
        ));
        $stripePayoutMg->add($stripePayout);
    }
    
}
exit();


