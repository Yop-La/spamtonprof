<?php
use spamtonprof\stp_api;

use spamtonprof\stp_api\CampaignManager;
use spamtonprof\stp_api\Abonnement;
use spamtonprof\stp_api\AbonnementManager;

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans adds-back-office.js sur la page dont le slug est adds
add_action('wp_ajax_ajaxGetAddsTitle', 'ajaxGetAddsTitle');

add_action('wp_ajax_nopriv_ajaxGetAddsTitle', 'ajaxGetAddsTitle');

add_action('wp_ajax_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_nopriv_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_nopriv_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_ajaxGetAddsTexteType', 'ajaxGetAddsTexteType');

add_action('wp_ajax_nopriv_ajaxGetAddsTexteType', 'ajaxGetAddsTexteType');

add_action('wp_ajax_ajaxGetTextes', 'ajaxGetTextes');

add_action('wp_ajax_nopriv_ajaxGetTextes', 'ajaxGetTextes');

/* retourne la liste des types de textes des annonces lbc*/
function ajaxGetAddsTexteType()

{
    header('Content-type: application/json');
    
    $lbcTexteMg = new \spamtonprof\stp_api\LbcTexteManager();
    
    echo (json_encode($lbcTexteMg -> getAllType()));
    
    die();
}


/* retourne la liste des types de titres des annonces lbc*/
function ajaxGetAddsTitle()

{
    header('Content-type: application/json');
    
    $accountManager = new \spamtonprof\stp_api\LbcTitleManager();
    
    echo (json_encode($accountManager -> getAllType()));
    
    die();
}

function ajaxGetTitles()

{
    header('Content-type: application/json');
    
    $typeTitle = $_POST["typeTitle"];
    
    $accountManager = new \spamtonprof\stp_api\LbcTitleManager();
    
    $titles = $accountManager -> getAll($typeTitle);
    
    $csvName = "titles.csv";
    
    saveArrayAsCsv($titles, $csvName);
    
    echo (json_encode(array("titles" => $titles , "csvPath" => plugins_url( "spamtonprof/tempo/".$csvName))));
    
    die();
}

function ajaxGetTextes()

{
    header('Content-type: application/json');
    
    $typeTexte = $_POST["typeTexte"];
    
    $texteMg = new \spamtonprof\stp_api\LbcTexteManager();
    
    $textes = $texteMg -> getAll($typeTexte);
    
    $csvName = "titles.csv";
    
    saveArrayAsCsv($textes, $csvName);
    
    echo (json_encode(array("textes" => $textes, "csvPath" => plugins_url( "spamtonprof/tempo/".$csvName))));
    
    die();
}


