<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans choisir-prof.js sur la page dont le slug est choisir-prof
add_action('wp_ajax_ajaxAttribuerProf', 'ajaxAttribuerProf');

add_action('wp_ajax_nopriv_ajaxAttribuerProf', 'ajaxAttribuerProf');

/* pour gérer la soumission du formulaire d'essai */
function ajaxAttribuerProf()

{
    header('Content-type: application/json');

    $retour = new \stdClass();

    $retour->error = false;

    $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

    
    


    $refAbonnement = $_POST["refAbonnement"];
    $refProf = $_POST["refProf"];

    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $abonnement = $abonnementMg->get(array('ref_abonnement' => $refAbonnement));

    
    
    if (! $abonnement->getTest()) {
        
        $now = $now->add(new \DateInterval("PT30M"));
        
    }
    
    
    $abonnement->setRef_prof($refProf);
    $abonnement->setDate_attribution_prof($now);
    $abonnement->setFirst_prof_assigned(false);
    

    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $abonnementMg->updateRefProf($abonnement);

    $abonnementMg->updateDateAttributionProf($abonnement);

    $abonnementMg->updateFirstProfAssigned($abonnement);

    $profMg = new \spamtonprof\stp_api\StpProfManager();

    $prof = $profMg->get(array(
        'ref_prof' => $refProf
    ));

    $retour->prof = $prof;

    echo (json_encode($retour));

    die();
}

