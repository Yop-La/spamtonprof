<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans dashboard-eleve.js sur la page dont le slug est dashboard-eleve
add_action('wp_ajax_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_nopriv_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

function ajaxGetTrialAbonnements()
{
    header('Content-type: application/json');
    
    $stpProfilMg = new \spamtonprof\stp_api\stpProfilManager();
    
    $profils = $stpProfilMg->getAll();
    
    echo (json_encode($profils));
    
    die();
}
