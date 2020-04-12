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

$ref_clients = [
    24,
    25
];

$nb_valid_accounts = [];

$nb_free_cities = [];

foreach ($ref_clients as $ref_client) {

    $acts = $lbcActMg->getAll(array(
        'key' => 'valid_lbc_act',
        'ref_client' => $ref_client
    ));

    $communeMg = new \spamtonprof\stp_api\LbcCommuneManager();

    $communes = $communeMg->getAll(array(
        "ref_client" => $ref_client,
        'target_big_city' => true
    ));

    $nb_valid_accounts[$ref_client] = count($acts);
    $nb_free_cities[$ref_client] = count($communes);
}

$params = [];

foreach ($nb_valid_accounts as $key => $value) {
    $params["nb_valid_account_" . $key] = "" . $value;
}

foreach ($nb_free_cities as $key => $value) {
    $params["nb_town_" . $key] = "" . $value;
}

$email = new \SendGrid\Mail\Mail();
$email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

try {

    $email->addTo("alexandre@spamtonprof.com", "Alexandre", $params, 0);

    $email->setTemplateId("d-856c14976351439880614746290d9463");
    $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

    $response = $sendgrid->send($email);

    echo ($response->statusCode());
} catch (Exception $e) {

    echo ($e->getMessage());
}

exit();



