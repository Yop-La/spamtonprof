<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans inscription-prof.js sur la page dont le slug est inscription-prof
add_action('wp_ajax_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_nopriv_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_ajaxUpdateCustomAccounts', 'ajaxUpdateCustomAccounts');

add_action('wp_ajax_nopriv_ajaxUpdateCustomAccounts', 'ajaxUpdateCustomAccounts');

add_action('wp_ajax_addIbanProf', 'addIbanProf');

add_action('wp_ajax_nopriv_addIbanProf', 'addIbanProf');

/* pour gérer la soumission du formulaire d'inscription des profs */
function ajaxCreateStripAccount()

{
    
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $slack =new \spamtonprof\slack\Slack();
    
    $StpProfMg = new \spamtonprof\stp_api\StpProfManager();
    
    $tokenId = trim($_POST['tokenId']);
    $testMode = trim($_POST['testMode']);
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $codePostal = trim($_POST['codePostal']);
    $pays = trim($_POST['pays']);
    $refProf = trim($_POST['refProf']);
   
    $stripeManager = new \spamtonprof\stp_api\StripeManager($testMode);
    $accountStripeId = $stripeManager->createCustomAccount($tokenId, $pays);
    
    
    if (! $accountStripeId) {
        
        $retour->error = true;
        $retour->message = utf8_encode("Impossible de créer le compte Stripe");
        echo (json_encode($retour));
        die();
    }
    
    // mise à jour du prof
    
    $prof = $StpProfMg->get(array(
        'ref_prof' => $refProf
    ));
    
    if ($testMode == "true") {
        $prof->setStripe_id_test($accountStripeId);
        $StpProfMg->updateStripeIdTest($prof);
    } else {
        $prof->setStripe_id($accountStripeId);
        $StpProfMg->updateStripeId($prof);
    }
    
    $prof->setAdresse($adresse);
    $StpProfMg->updateAdresse($prof);
    
    $prof->setVille($ville);
    $StpProfMg->updateVille($prof);
    
    $prof->setCode_postal($codePostal);
    $StpProfMg->updateCodePostal($prof);
    
    $prof->setPays($pays);
    $StpProfMg->updatePays($prof);
    
    $prof->setOnboarding_step("step-1");
    $StpProfMg->updateOnboarding_step($prof);
    
    echo (json_encode($retour));
    
    die();
}

/* pour uploader les pièces d'identité */
function ajaxUpdateCustomAccounts()

{
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $tokenId = trim($_POST['tokenId']);
    $testMode = trim($_POST['testMode']);
    $refProf = trim($_POST['refProf']);
    
    $StpProfMg = new \spamtonprof\stp_api\StpProfManager();
    $prof = $StpProfMg->get(array(
        'ref_prof' => $refProf
    ));
    
    $stripeManager = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $accountStripeId = false;
    if ($testMode == "true") {
        $accountStripeId = $stripeManager->updateCustomAccount($tokenId, $prof->getStripe_id_test());
    } else {
        $accountStripeId = $stripeManager->updateCustomAccount($tokenId, $prof->getStripe_id());
    }
    
    if (! $accountStripeId) {
        $retour->error = true;
        $retour->message = utf8_encode("Impossible de mettre à jour le compte Stripe");
        echo (json_encode($retour));
        die();
    }
    
    $prof->setOnboarding_step("step-2");
    $StpProfMg->updateOnboarding_step($prof);
    
    echo (json_encode($retour));
    
    die();
}

function addIbanProf()

{
    
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $tokenId = $_POST['tokenId'];
    $refProf = trim($_POST['refProf']);
    $testMode = trim($_POST['testMode']);
    
    $StpProfMg = new \spamtonprof\stp_api\StpProfManager();
    $prof = $StpProfMg->get(array(
        'ref_prof' => $refProf
    ));
    
    $stripeManager = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $accountStripeId = false;
    if ($testMode == "true") {
        $accountStripeId = $stripeManager->addExternalAccount($tokenId, $prof->getStripe_id_test());
    } else {
        $accountStripeId = $stripeManager->addExternalAccount($tokenId, $prof->getStripe_id());
    }
    
    if (! $accountStripeId) {
        $retour->error = true;
        $retour->message = utf8_encode("Impossible d'ajouter votre iban.");
        echo (json_encode($retour));
        die();
    }
    
    
    $prof->setOnboarding(true);
    $StpProfMg->updateOnboarding($prof);
    
    echo (json_encode($retour));
    
    die();
}
