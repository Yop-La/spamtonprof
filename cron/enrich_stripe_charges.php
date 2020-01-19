<?php
/*
 * cron de récupération des transactions des profs
 * pas encore en prod
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

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('stripe_charges', array(
    '------',
    "Debut d'enrichissement des charges"
));

$test_mode = false;

$stripePayoutMg = new \spamtonprof\stp_api\StripePayoutManager();
$stripeTransactionMg = new \spamtonprof\stp_api\StripeTransactionManager();
$stripeChargeManagerMg = new \spamtonprof\stp_api\StripeChargeManager();
$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$stripe = new \spamtonprof\stp_api\StripeManager($test_mode);

$charges = $stripeChargeManagerMg->getAll(array(
    'key' => 'updated_is_null'
));

$messages = [];

if (count($charges) == 0) {
    $slack->sendMessages('stripe_charges', array(
        'Aucune charge à traiter ...'
    ));
}

foreach ($charges as $charge) {

    if (count($messages) == 50) {

        $slack->sendMessages('stripe_charges', $messages);
        $messages = [];
    }

    $invoice = $stripe->retrieve_invoice($charge->getInvoice());

    $lines = $invoice->lines->data;
    $ref_abo = false;
    $description = false;

    foreach ($lines as $line) {

        $description = $line->description;

        // on tente avec la ref_abo dans les metadata
        $ref_abo = $line->metadata->ref_abonnement;
        if ($ref_abo) {
            break;
        }
    }

    if (! $ref_abo) {

        // on tente avec la sub_id
        $sub_id = $invoice->subscription;
        if ($sub_id) {
            $abo = $aboMg->get(array(
                'subs_id' => $sub_id
            ));

            if ($abo) {
                $ref_abo = $abo->getRef_abonnement();
            }
        }
    }

    if (! $ref_abo) {

        $customer = $stripe->retrieve_customer($charge->getCustomer());

        $ref_compte = $customer->metadata->compte;

        if ($ref_compte) {

            $abos = $aboMg->getAll(array(
                'ref_compte' => $ref_compte
            ));

            if (count($abos) == 1) {
                $abo = $abos[0];
                $ref_abo = $abo->getRef_abonnement();
            }
            
            if (count($abos) > 1) {
                
                $same_eleve = true;
                $abo = $abos[0];
                $ref_eleve = $abo->getRef_eleve();
                foreach ($abos as $abo) {
                    if ($ref_eleve != $abo->getRef_eleve()) {
                        $same_eleve = false;
                    }
                }
                if ($same_eleve) {
                    $ref_abo = $abo->getRef_abonnement();
                }
            }
            
        }
    }

    if (! $ref_abo) {

        $customer = $stripe->retrieve_customer($charge->getCustomer());
        $email = $customer->email;

        if ($email) {

            $abos = $aboMg->getAll(array(
                'email' => $email
            ));

            if (count($abos) == 1) {
                $abo = $abos[0];
                $ref_abo = $abo->getRef_abonnement();
            }
            if (count($abos) > 1) {
                
                $same_eleve = true;
                $abo = $abos[0];
                $ref_eleve = $abo->getRef_eleve();
                foreach ($abos as $abo) {
                    if ($ref_eleve != $abo->getRef_eleve()) {
                        $same_eleve = false;
                    }
                }
                if ($same_eleve) {
                    $ref_abo = $abo->getRef_abonnement();
                }
            }
        }
    }

    $updated = true;
    
    if ($ref_abo) {

        $messages[] = 'Ref abo ' . $charge->getRef_stripe() . ' à jour';

        $charge->setRef_abo($ref_abo);
        $stripeChargeManagerMg->update_ref_abo($charge);
    } else {
        $updated = false;
        
        $messages[] = '---->>>>>>>> Impossible de récupérer ref abo de: ' . $charge->getRef_stripe() . '<<<<<<<<<----';
    }

    if ($description) {

        $messages[] = 'Nom de formule de ' . $charge->getRef_stripe() . ' à jour';

        $charge->setNom_formule($description);
        $stripeChargeManagerMg->update_nom_formule($charge);
    } else {
        $updated = false;
        
        $messages[] = '---->>>>>>>> Impossible de récupérer le nom de formule : ' . $charge->getRef_stripe() . '<<<<<<<<<----';
    }
    
    $charge->setUpdated($updated);
    $stripeChargeManagerMg->update_updated($charge);
    
}

if (count($messages) != 0) {

    $slack->sendMessages('stripe_charges', $messages);
    $messages = [];
}

$slack->sendMessages('stripe_charges', array(
    '------',
    "Fin d'enrichissement des charges"
));

exit();


