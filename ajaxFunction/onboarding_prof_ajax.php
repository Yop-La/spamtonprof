<?php

// toutes ces fonction seront �x�cut�s par un appel ajax r�alis� dans inscription-prof.js sur la page dont le slug est inscription-prof
add_action('wp_ajax_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_nopriv_ajaxCreateStripAccount', 'ajaxCreateStripAccount');

add_action('wp_ajax_ajaxUpdateCustomAccounts', 'ajaxUpdateCustomAccounts');

add_action('wp_ajax_nopriv_ajaxUpdateCustomAccounts', 'ajaxUpdateCustomAccounts');

add_action('wp_ajax_addIbanProf', 'addIbanProf');

add_action('wp_ajax_nopriv_addIbanProf', 'addIbanProf');

/* pour g�rer la soumission du formulaire d'inscription des profs */
function ajaxCreateStripAccount()

{
    
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $slack =new \spamtonprof\slack\Slack();
    
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    
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
        $retour->message = utf8_encode("Impossible de cr�er le compte Stripe");
        echo (json_encode($retour));
        die();
    }
    
    // mise � jour du prof
    
    $prof = $stpProfMg->get(array(
        'ref_prof' => $refProf
    ));
    
    if ($testMode == "true") {
        $prof->setStripe_id_test($accountStripeId);
        $stpProfMg->updateStripeIdTest($prof);
    } else {
        $prof->setStripe_id($accountStripeId);
        $stpProfMg->updateStripeId($prof);
    }
    
    $prof->setAdresse($adresse);
    $stpProfMg->updateAdresse($prof);
    
    $prof->setVille($ville);
    $stpProfMg->updateVille($prof);
    
    $prof->setCode_postal($codePostal);
    $stpProfMg->updateCodePostal($prof);
    
    $prof->setPays($pays);
    $stpProfMg->updatePays($prof);
    
    $prof->setOnboarding_step("step-1");
    $stpProfMg->updateOnboarding_step($prof);
    
    echo (json_encode($retour));
    
    die();
}

/* pour uploader les pi�ces d'identit� */
function ajaxUpdateCustomAccounts()

{
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $tokenId = trim($_POST['tokenId']);
    $testMode = trim($_POST['testMode']);
    $refProf = trim($_POST['refProf']);
    
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    $prof = $stpProfMg->get(array(
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
        $retour->message = utf8_encode("Impossible de mettre � jour le compte Stripe");
        echo (json_encode($retour));
        die();
    }
    
    $prof->setOnboarding_step("step-2");
    $stpProfMg->updateOnboarding_step($prof);
    
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
    
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    $prof = $stpProfMg->get(array(
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
    $stpProfMg->updateOnboarding($prof);
    
    echo (json_encode($retour));
    
    die();
}
