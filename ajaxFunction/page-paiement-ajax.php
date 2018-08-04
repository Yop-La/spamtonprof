<?php
use spamtonprof\stp_api;

use spamtonprof\stp_api\CampaignManager;
use spamtonprof\stp_api\Abonnement;
use spamtonprof\stp_api\AbonnementManager;

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans paiement-apres-essai-complet.js sur la page de paiement

add_action('wp_ajax_ajaxGetTrialAccountComplet', 'ajaxGetTrialAccountComplet');

add_action('wp_ajax_nopriv_ajaxGetTrialAccountComplet', 'ajaxGetTrialAccountComplet');

add_action('wp_ajax_ajaxStripeDoSubscription', 'ajaxStripeDoSubscription');

add_action('wp_ajax_nopriv_ajaxStripeDoSubscription', 'ajaxStripeDoSubscription');

add_action('wp_ajax_getBillingAgreement', 'getBillingAgreement');

add_action('wp_ajax_nopriv_getBillingAgreement', 'getBillingAgreement');

add_action('wp_ajax_exeBillingAgreement', 'exeBillingAgreement');

add_action('wp_ajax_nopriv_exeBillingAgreement', 'exeBillingAgreement');

add_action('wp_ajax_updateAccountAndGrAfterSubscription', 'updateAccountAndGrAfterSubscription');

add_action('wp_ajax_nopriv_updateAccountAndGrAfterSubscription', 'updateAccountAndGrAfterSubscription');

/* sert à envoyer la liste de compte après la saisie de l'adresse mail sur la page de paiement */
function ajaxGetTrialAccountComplet()

{
    header('Content-type: application/json');
    
    $accountManager = new \spamtonprof\stp_api\AccountManager();
    
    $accounts = $accountManager->getList($_POST["email"]);
    
    $accounts = $accountManager->filterByAttentePaiement($accounts, true);
    
    echo (json_encode($accounts));
    
    die();
}

/* sert à réaliser le paiement par cb */
function ajaxStripeDoSubscription()

{
    header('Content-type: application/json');
    
    $source = $_POST['source'];
    
    $refCompte = $_POST['ref_compte'];
    
    $emailParent = $_POST['email_parent'];
    
    $planStripe = $_POST['plan_stripe'];
    
    $testMode = $_POST['testMode'];
    
    $stripeManager = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $sub = $stripeManager->createSubscription($emailParent, $source, $refCompte, $planStripe);
    
    if (is_null($sub)) {
        
        echo (json_encode("paiement_failure"));
        
        
        
    } else {
        
        echo (json_encode("done"));
        
        $abonnementMg = new \spamtonprof\stp_api\AbonnementManager();
          
        $abonnement = new \spamtonprof\stp_api\Abonnement(array("ref_compte" => $refCompte, "ref_stripe_subscription" => $sub->id));
        
        $abonnementMg -> add($abonnement);
        
        $accountManager = new \spamtonprof\stp_api\AccountManager();
        
        $account = $accountManager->get($refCompte);
        
        $account->updateAfterSubscription();
        
        $accountManager->updateAfterSubsCreated($account);
        
        $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();
        
        $getResponseManager->changeListAfterSubAccount($account);
        
        
    }
    
    die();
    
}

/* sert à créer le billing agreement dans le paiement paypal */
function getBillingAgreement()

{
    header('Content-type: application/json');
        
    $planPaypal = $_POST["planPaypal"];
    
    $testMode = $_POST["testMode"];
    
    $paypalManager = new \spamtonprof\stp_api\PaypalManager($testMode);
    
    $token = $paypalManager->createBilingAgreement($planPaypal);
    
    echo (json_encode($token));
    
    die();
}

/* sert à éxécuter le billing agreement dans le paiement paypal */
function exeBillingAgreement()

{
    header('Content-type: application/json');
    
    $paiementToken = $_POST["paiementToken"];
    
    $testMode = $_POST["testMode"];
    
    $refCompte = $_POST["refCompteCheckout"];
    
    $emailCheckout = $_POST["emailCheckout"];
    
    $paypalManager = new \spamtonprof\stp_api\PaypalManager($testMode);
    
    $agreementId = $paypalManager->executeBilingAgreement($_POST["paiementToken"]);
    
    if (is_null($agreementId)) {
        echo (json_encode("fail"));
    } else {
        
        echo (json_encode("done"));
        
        to_log_abonnement(array(
            
            "str1" => "email parent : " . $emailCheckout,
            
            "str2" => "agreementId paypal: " . $agreementId,
            
            "str3" => "refCompte : " . $refCompte
        
        ));
        
        $abonnementPaypal = new Abonnement(array(
            'ref_compte' => $refCompte,
            "ref_paypal_agreement" => $agreementId
        ));
        
        $abonnementPaypalMg = new AbonnementManager();
        
        $abonnementPaypalMg->add($abonnementPaypal);
    }
    
    die();
}

/* sert à faire la maj du compte et de getresponse après inscription */
function updateAccountAndGrAfterSubscription()

{
    header('Content-type: application/json');
    
    $refCompte = $_POST['refCompteCheckout'];
    
    $accountManager = new \spamtonprof\stp_api\AccountManager();
    
    $account = $accountManager->get($refCompte);
    
    $account->updateAfterSubscription();
    
    $accountManager->updateAfterSubsCreated($account);
    
    $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();
    
    $getResponseManager->changeListAfterSubAccount($account);
    
    echo (json_encode("done"));
    
    die();
}


