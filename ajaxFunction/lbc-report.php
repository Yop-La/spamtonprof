<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans lbc-report.js sur la page dont le slug est lbc-report
add_action('wp_ajax_lbcReport', 'lbcReport');

add_action('wp_ajax_nopriv_lbcReport', 'lbcReport');

add_action('wp_ajax_updateDomain', 'updateDomain');

add_action('wp_ajax_nopriv_updateDomain', 'updateDomain');

function lbcReport()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $lbcCompteMg = new \spamtonprof\stp_api\LbcAccountManager();
    $data = $lbcCompteMg->getReport(array(
        'global_nb_ads' => 'global_nb_ads'
    ));

    $retour->tab1 = $data;

    
    $data = $lbcCompteMg->getReport(array(
        'ads_by_day' => 'ads_by_day'
    ));

    $retour->tab2 = $data;

    $data = $lbcCompteMg->getReport(array(
        'ads_by_domain' => 'ads_by_domain'
    ));

    $retour->tab3 = $data;

    $data = $lbcCompteMg->getReport(array(
        'domains_stats' => 'domains_stats'
    ));

    $retour->tab4 = $data;

    $data = $lbcCompteMg->getReport(array(
        'acts_details' => 'acts_details'
    ));
    
    $retour->tab5 = $data;
    
    
    echo (json_encode($retour));

    die();
}

function updateDomain()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $domain_name = $_POST['domain_name'];
    $disabled = $_POST['disabled'];

    
    
    
    $domainMg = new \spamtonprof\stp_api\StpDomainManager();
    $domain = $domainMg->get(array(
        'name' => $domain_name
    ));

    $domain->setDisabled($disabled);
    $domainMg->updateDisabled($domain);

    $retour->disabled = $disabled;

    echo (json_encode($retour));

    die();
}
