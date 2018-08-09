<?php

// toutes ces fonction seront �x�cut�s par un appel ajax r�alis� dans choisir-prof.js sur la page dont le slug est choisir-prof
add_action('wp_ajax_ajaxAttribuerProf', 'ajaxAttribuerProf');

add_action('wp_ajax_nopriv_ajaxAttribuerProf', 'ajaxAttribuerProf');

/* pour g�rer la soumission du formulaire d'essai */
function ajaxAttribuerProf()

{
    header('Content-type: application/json');
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $now = new \DateTime(null,new \DateTimeZone("Europe/Paris"));

    $now = $now->add(new \DateInterval("PT30M"));
    
    $refAbonnement = $_POST["refAbonnement"];
    $refProf = $_POST["refProf"];
    
    $slack = new \spamtonprof\slack\Slack();
    
    $slack -> sendMessages("log", array('ref abo' , $refAbonnement, 'ref prof' ,$refProf));
    
    $abonnement = new \spamtonprof\stp_api\stpAbonnement(array("ref_abonnement" => $refAbonnement, "ref_prof" => $refProf, "date_attribution_prof" => $now ));
    
    $abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
    $abonnementMg -> updateRefProf($abonnement);
    $abonnementMg -> updateDateAttributionProf($abonnement);
    
    $profMg = new \spamtonprof\stp_api\stpProfManager();
    
    $prof = $profMg -> get(array('ref_prof' => $refProf));
    
    $retour->prof = $prof;
    
    echo (json_encode($retour));
    
    die();
}
