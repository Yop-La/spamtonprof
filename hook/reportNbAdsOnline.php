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

$gmailManager = new \spamtonprof\googleMg\GoogleManager("mailsfromlbc@gmail.com");
$msgs = $gmailManager->listMessages("subject:ligne after:" . $today->format(GMAIL_DATE_FORMAT) . "before:" . $tomorrow->format(GMAIL_DATE_FORMAT), 20, 20);

$clientMg = new \spamtonprof\stp_api\LbcClientManager();

$lbcActMg = new \spamtonprof\stp_api\LbcAccountManager();

$res_publication = array();

$clients = $clientMg->getAll(array(
    'all'
));


foreach ($msgs as $msg) {

    $msg_id = $msg->id;

    $message = $gmailManager->getMessage($msg_id, [
        'format' => 'full'
    ]);

    $to = $gmailManager->getHeader($message, "To");

    $lbcAct = $lbcActMg->get(array(
        "mail" => str_replace(array(
            '<',
            '>'
        ), "", $to)
    ));

    foreach ($clients as $client) {
        if ($client->getRef_client() == $lbcAct->getRef_client()) {
            $key_client = $client->get_label_client();
            break;
        }
    }

    if (array_key_exists($key_client, $res_publication)) {
        $res_publication[$key_client] = $res_publication[$key_client] + 1;
    } else {
        $res_publication[$key_client] = 1;
    }
}

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('reporting-lbc', array(
    "----------",
    "Reporting sur le nombre d'annonces publiés le " . $tomorrow->format(FR_DATE_FORMAT)
));

foreach ($res_publication as $key => $value) {
    $slack->sendMessages('reporting-lbc', array(
        "Pour le client " . $key . ": " . $value . " publiées."
    ));
}
$slack->sendMessages('reporting-lbc', array(
    "Fin reporting"
));

prettyPrint($res_publication);
