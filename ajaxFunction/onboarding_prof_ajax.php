<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans inscription-prof.js sur la page dont le slug est inscription-prof
add_action('wp_ajax_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_nopriv_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

/* pour gérer la soumission du formulaire d'inscription des profs */
function ajaxCreateStripAccount()

{
    $error = false;
    $retour = "ok";
    
    $slack = new \spamtonprof\slack\Slack();
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    
    header('Content-type: application/json');
    
    $tokenId = trim($_POST['tokenId']);
    $testMode = trim($_POST['testMode']);
    $pays = trim($_POST['pays']);
    
    $slack->sendMessages('log', array(
        'tokenId : ' . $tokenId,
        'test mode : ' . $testMode
    ));
    
    $stripeManager = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $accountStripeId = $stripeManager->createCustomAccount($tokenId, $pays);
    
    // enregistrement de custom accnt id dans la bdd stp
    $current_user = wp_get_current_user();
    
    $prof = $stpProfMg->get(array(
        'user_id_wp' => $current_user->ID
    ));
    
    $prof -> setStripe_id($accountStripeId);
    
    $stpProfMg -> updateStripeId($prof);
    
    if ($error) {
        $retour = $error;
    }
    
    echo (json_encode($retour));
    
    die();
}
