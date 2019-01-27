<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans lbc-report.js sur la page dont le slug est lbc-report
add_action('wp_ajax_lbcReport', 'lbcReport');

add_action('wp_ajax_nopriv_lbcReport', 'lbcReport');

function lbcReport()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';
    
    $lbcCompteMg = new \spamtonprof\stp_api\LbcAccountManager();
    $data = $lbcCompteMg ->getReport(array('global_nb_ads' => 'global_nb_ads'));
    
    $retour->tab1 = $data;
    
    $lbcCompteMg = new \spamtonprof\stp_api\LbcAccountManager();
    $data = $lbcCompteMg ->getReport(array('ads_by_day' => 'ads_by_day'));
    
    $retour->tab2 = $data;
    
    $lbcCompteMg = new \spamtonprof\stp_api\LbcAccountManager();
    $data = $lbcCompteMg ->getReport(array('ads_by_domain' => 'ads_by_domain'));
    
    $retour->tab3 = $data;
    
    
    echo (json_encode($retour));

    die();
}
