<?php
/**
 * 
 *  pour recevoir les hooks de stripe en mode prof
 *  Voil� les hooks re�us :
 *  - invoice.payment_succeeded pour transf�rer les fonds au prof
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

$test_mode = false;

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

$slack = new \spamtonprof\slack\Slack();

$slack->sendMessages('log-stripe', array(
    $event_json->type
));

if ($event_json->type == "invoice.payment_succeeded") {

    $stripeMg = new \spamtonprof\stp_api\StripeManager($test_mode);

    $custom_fields = $event_json->data->object->custom_fields;
    $email_prof = false;
    foreach ($custom_fields as $custom_field) {
        if ($custom_field->name == 'email_prof') {
            $email_prof = $custom_field->value;
        }
    }

    if ($email_prof) {

        $stripeMg->transfert_custom_facture($event_json, $email_prof);
    } else {
        $stripeMg->transfertSubscriptionCharge($event_json);
    }
}

// pour mettre fin à l'interruption
if ($event_json->type == "customer.subscription.updated") {

    $interruptionMg = new \spamtonprof\stp_api\StpInterruptionManager();

    $stripeUtils = new \spamtonprof\stp_api\StripeUtils();

    $states = $stripeUtils->extract($event_json, 'states_end_trial');

    if ($states['current'] == 'active' && $states['previous'] == 'trialing') {

        $ref_abonnement = $event_json->data->object->metadata->ref_abonnement;

        $interruption = $interruptionMg->get(array(
            'key' => 'to_stop',
            'params' => array(
                'ref_abo' => $ref_abonnement
            )
        ));

        if ($interruption) {

            $interruption->setStatut($interruptionMg::stopping);
            $interruptionMg->update_statut($interruption);
        } else {

            $slack->sendMessages('interruption', array(
                "impossible de trouver la suspension à terminer suite à fin d'interruption dans stripe"
            ));
        }
    }
}

// pour mettre à jour la cb
if ($event_json->type == "checkout.session.completed") {

    $session_id = $event_json->data->object->id;

    $test_mode = ! $event_json->livemode;

    $stripeMg = new \spamtonprof\stp_api\StripeManager($test_mode);

    $session = $stripeMg->retrieve_session($session_id);
    
    $setup_intent_id = $session->setup_intent;

    $setup_intent = $stripeMg->retrieve_setup_intent($setup_intent_id);

    $customer = $setup_intent->metadata['customer_id'];
    $payment_method = $setup_intent->payment_method;

    $stripeMg->attach_payment_method($payment_method, $customer);
    $stripeMg->set_default_payment_method($payment_method, $customer);

    $slack->sendMessages('log-stripe', array(
        'Mise à jour du moyen de paiement de ' . $customer
    ));
}





