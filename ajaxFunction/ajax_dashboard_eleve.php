<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans dashboard-eleve.js sur la page dont le slug est dashboard-eleve
add_action('wp_ajax_ajaxCreateSubscription', 'ajaxCreateSubscription');

add_action('wp_ajax_nopriv_ajaxCreateSubscription', 'ajaxCreateSubscription');

function ajaxCreateSubscription()
{
    header('Content-type: application/json');
    
    $slack = new \spamtonprof\slack\Slack();
    
 
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $refAbonnement = $_POST["ref_abonnement"];
    $source = $_POST["source"];
    $testMode = $_POST["testMode"];
   
    // on récupère l'abonnement
    $abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
    $constructor = array(
        "construct" => array(
            'ref_prof',
            'ref_eleve',
            'ref_parent',
            'ref_formule',
            'ref_plan'
        )
    );

    $abonnement = $abonnementMg->get(array(
        "ref_abonnement" => $refAbonnement
    ), $constructor);
    
   
    $eleve = $abonnement->getEleve();
    $proche = $abonnement->getProche();
    $prof = $abonnement->getProf();
    $plan = $abonnement->getPlan();
    
  
    
    
    $eleve = \spamtonprof\stp_api\stpEleve::cast($eleve);
    $prof = \spamtonprof\stp_api\stpProf::cast($prof);
    $plan = \spamtonprof\stp_api\StpPlan::cast($plan);

    // détermination de l'email client
    $emailClient = "alexandre@spamtonprof.com";
    if ($proche) {
        $proche = \spamtonprof\stp_api\stpProche::cast($proche);
        $emailClient = $proche->getEmail();
    } else {
        $emailClient = $eleve->getEmail();
    }
    
 
    // on ajoute l'abonnement à stripe pour débiter le client de manière récurrente
    $stripeMg = new \spamtonprof\stp_api\StripeManager($testMode);
    
  
    $subscriptionCreated = false;
    if ($testMode == "true") {
        
        $subscriptionCreated = $stripeMg->addConnectSubscription($emailClient, $source, $abonnement->getRef_compte(), $plan->getRef_plan_stripe_test(), $prof->getStripe_id_test());
    } else {
        
        $subscriptionCreated = $stripeMg->addConnectSubscription($emailClient, $source, $abonnement->getRef_compte(), $plan->getRef_plan_stripe(), $prof->getStripe_id());
    }
    
    if (!$subscriptionCreated) {
        
        
        $retour->error = true;
        $retour->message = utf8_encode("Impossible de débiter votre moyen de paiement");
        echo (json_encode($retour));
        die();
        
    } else {
        
        
        // mettre à jour le statut de l'abonnement : de essai à inscrit
        // envoyer mail d'inscription avec smtp2go
        
    }
    
    //
    

    
    $slack->sendMessages("log", array(
        "message de test",
        "ref abo : " . $refAbonnement,
        " source : " . $source,
        "test mode : " . $testMode
    ));
    
    echo (json_encode($retour));
    
    die();
}
