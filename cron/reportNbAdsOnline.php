<?php

/* pour donner le nombre d'ads par client publiés chaque jour dans le channel reporting-lbc */
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
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



define('PROBLEME_CLIENT', true);

$today = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
$today->sub(new \DateInterval('P1D'));

$tomorrow = clone $today;

$tomorrow->add(new \DateInterval('P1D'));


$clientMg = new \spamtonprof\stp_api\LbcClientManager();

$lbcActMg = new \spamtonprof\stp_api\LbcAccountManager();

$res_publication = array();

$lbcAdValidationMg = new \spamtonprof\stp_api\LbcAdValidationEmailManager();

$msgs = $lbcAdValidationMg->getAll(array('day' => $today->format(PG_DATE_FORMAT)));

$clients = $clientMg->getAll(array(
    'all'
));

foreach ($msgs as $msg) {
    
    
    $lbcAct = $lbcActMg->get(array(
        "ref_compte" => $msg->getRef_compte_lbc()
    ));
    
    if ($lbcAct) {
        
        $key_client = "aucun";
        foreach ($clients as $client) {
            if ($client->getRef_client() == $lbcAct->getRef_client()) {
                $key_client = $client->getLabel();
                break;
            }
        }
        
        if (array_key_exists($key_client, $res_publication)) {
            $res_publication[$key_client] = $res_publication[$key_client] + 1;
        } else {
            $res_publication[$key_client] = 1;
        }
    }
}

$slack = new \spamtonprof\slack\Slack();

$msgs = [];

$msgs[] ="----------";
$msgs[] ="Reporting sur le nombre d'annonces publiés le " . $today->format(FR_DATE_FORMAT);

foreach ($res_publication as $key => $value) {
    $msgs[] = "Pour le client " . $key . ": " . $value . " publiées.";
    
}

$msgs[] = "Fin reporting";

$slack->sendMessages('reporting-lbc', $msgs);

prettyPrint($res_publication);
