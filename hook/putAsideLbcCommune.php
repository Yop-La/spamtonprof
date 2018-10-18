<?php
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/**
 * appelé par un template zenno lorsque qu'une annonce n'est pas reconnu par leboncoin
 * le script se charge de mettre la colonne lbc à true ne pas sélectionner lors de prochaines publications
 */
$commune = 'toulelknefe56v4zvezvvv 1 2121 e';
echo($commune);


$commune = explode(" ", $commune);

$codePostal = $commune[count($commune) - 1];

unset($commune[count($commune) - 1]);
$commune = implode(" ", $commune);

$lbcCommuneMg = new \spamtonprof\stp_api\LbcCommuneManager();

$lbcCommune = $lbcCommuneMg->get(array(
    "code_postal" => trim($codePostal),
    "libelle" => trim($commune)
));
$lbcCommune->setLbc(true);
$lbcCommuneMg->updateLbc($lbcCommune);
