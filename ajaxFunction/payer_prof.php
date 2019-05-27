<?php

// toutes ces fonction seront executes par un appel ajax realise dans payer-prof.js sur la page dont le slug est payer-prof
add_action('wp_ajax_ajax_payement_seb', 'ajax_payement_seb');
add_action('wp_ajax_nopriv_ajax_payement_seb', 'ajax_payement_seb');

function ajax_payement_seb()
{
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    $retour->error = false;
    
    $source = $_POST["source"];
    $testMode = $_POST["testMode"];
    $montant = $_POST["montant"];
    
    
    $stripe = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $rep = $stripe->prof_charging($montant*100, $source);
    
    if (! $rep) {
        $retour->error = true;
        $retour->message = "Abonnez vous avant d'ajouter une CB";
    }
    
    echo (json_encode($retour));
    
    die();
}

