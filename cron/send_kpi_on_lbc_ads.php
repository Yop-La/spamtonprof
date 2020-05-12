<?php
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

// en prod. une fois par jour à 5h42
/*
 * pour donner des kpis sur les impayés au gestionnaire
 */

$lbcActMg = new \spamtonprof\stp_api\LbcAccountManager();

$clientMg = new \spamtonprof\stp_api\LbcClientManager();

$ref_clients = [
    24,
    25,
    33,
    34,
    35,
    36,
    37,
    38,
    39,
    40,
    41,
    42,
    43,
    44,
    45,
    46,
    47
];

$nb_valid_accounts = [];

$params = [];

$params['clients'] = [];

foreach ($ref_clients as $ref_client) {

    $client_stat = new \stdClass();

    $client = $clientMg->get(array(
        'ref_client' => $ref_client
    ));

    $acts = $lbcActMg->getAll(array(
        'key' => 'valid_lbc_act',
        'ref_client' => $ref_client
    ));

    $communeMg = new \spamtonprof\stp_api\LbcCommuneManager();

    $communes = $communeMg->getAll(array(
        "ref_client" => $ref_client,
        'target_big_city' => true
    ));

    $client_stat->prenom = $client->getPrenom_client() . " " . $client->getNom_client() . "( " . $ref_client . " )";
    $client_stat->nb_valid_account = count($acts);
    $client_stat->nb_town = count($communes);

    $params['clients'][] = $client_stat;
}

$email = new \SendGrid\Mail\Mail();
$email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

try {

//     prettyPrint($params);
    
    $email->addTo("alexandre@spamtonprof.com", "Alexandre", $params, 0);

    $email->setTemplateId("d-856c14976351439880614746290d9463");
    $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

    $response = $sendgrid->send($email);

    echo ($response->statusCode());
} catch (Exception $e) {

    echo ($e->getMessage());
}

exit();



