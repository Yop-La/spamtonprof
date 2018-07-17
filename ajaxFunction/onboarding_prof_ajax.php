<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans inscription-prof.js sur la page dont le slug est inscription-prof
add_action('wp_ajax_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_nopriv_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_ajaxGetProf', 'ajaxGetProf');

add_action('wp_ajax_nopriv_ajaxGetProf', 'ajaxGetProf');

add_action('wp_ajax_updateIbanProf', 'updateIbanProf');

add_action('wp_ajax_nopriv_updateIbanProf', 'updateIbanProf');

/* pour gérer la soumission du formulaire d'inscription des profs */
function ajaxCreateStripAccount()

{
    $error = false;
    $retour = "ok";
    
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    
    header('Content-type: application/json');
    
    $tokenId = trim($_POST['tokenId']);
    $testMode = trim($_POST['testMode']);
    $pays = trim($_POST['pays']);
    
    $stripeManager = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $accountStripeId = $stripeManager->createCustomAccount($tokenId, $pays);
    
    // enregistrement de custom accnt id dans la bdd stp
    $current_user = wp_get_current_user();
    
    $prof = $stpProfMg->get(array(
        'user_id_wp' => $current_user->ID
    ));
    
    $prof->setOnboarding_step("step-1");
    $stpProfMg->updateOnboarding_step($prof);
    
    $prof->setStripe_id($accountStripeId);
    $stpProfMg->updateStripeId($prof);
    
    if ($error) {
        $retour = $error;
    } else {
        $slack = new \spamtonprof\slack\Slack();
        
        $slack->sendMessages("onboarding-prof", $prof->toSlack("------ Inscription d'un nouveau prof ---->>> mettre à jour son profil stripe"));
    }
    
    echo (json_encode($retour));
    
    die();
}

function updateIbanProf()

{
    $slack = new \spamtonprof\slack\Slack();
    
    header('Content-type: application/json');
    
    $iban = $_POST['iban'];
    
    $slack->sendMessages("log", array(
        "iban",
        $iban
    ));
    
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    
    $current_user = wp_get_current_user();
    
    $prof = $stpProfMg->get(array(
        'user_id_wp' => $current_user->ID
    ));
    
    $prof->setIban($iban);
    $stpProfMg->updateIban($prof);
    
    $prof->setOnboarding(true);
    $stpProfMg->updateOnboarding($prof);
    
    $slack = new \spamtonprof\slack\Slack();
    
    $slack->sendMessages("onboarding-prof", $prof->toSlack("------ Ajout d'un iban ---->>> mettre à jour l'iban de ce prof sur stripe"));
    
    echo (json_encode("ok"));
    
    die();
}
