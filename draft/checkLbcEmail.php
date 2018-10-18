<?php

/*
 * 
 * pour faire un contrôle des publications en ligne avec les mails envoyés par leboncon
 * 
 * */
use spamtonprof\stp_api\AccountManager;
use spamtonprof\stp_api\EleveManager;
use spamtonprof\stp_api\ClasseManager;
use spamtonprof\stp_api\GmailLabelManager;
use spamtonprof\stp_api\GmailLabel;
use spamtonprof\stp_api\NbEmailManager;
use spamtonprof\stp_api\GetResponseManager;
use spamtonprof\getresponse_api\CampaignManager;
use spamtonprof\stp_api\LbcProcessManager;
use Hashids\Hashids;

require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(- 1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$slack = new \spamtonprof\slack\Slack();

$ads = unserializeTemp();

$refComptes = [];
$i = 0;
foreach ($ads as $ad) {

    $refCompte = $ad->getRef_compte();
    if (! in_array($refCompte, $refComptes)) {
        $refComptes[] = $refCompte;
    }
}

$adTempoMg = new \spamtonprof\stp_api\AddsTempoManager();

foreach ($refComptes as $refCompte) {
    // 4-1-2 : on va mettre à jour la ref_commune de adds_tempo
    $adsTemp = $adTempoMg->getAll(array(
        "ref_compte" => $refCompte
    ));

    $adTempoMg->updateAllRefCommune($adsTemp);
}

prettyPrint($refComptes);

// prettyPrint($adIds);

exit(0);

$rows = unserializeTemp();

$ads = [];

foreach ($rows as $row) {
    $ad = new \spamtonprof\stp_api\AddsTempo(array(
        "id" => $row[1],
        "ref_compte" => $row[0]
    ));
    $ads[] = $ad;
}

serializeTemp($ads);
prettyPrint($ads);

exit(0);

$rows = unserializeTemp();

$ads = [];

foreach ($rows as $row) {
    $ad = [];
    $email = $row[0];
    $url = $row[1];

    if (strpos($row[1], 'cours') !== false) {
        $url = str_replace(".", "/", $url);
        $url = explode("/", $url);
        $adId = $url[6];
        $ad[] = $email;
        $ad[] = $adId;
        $ads[] = $ad;
    }
}

serializeTemp($ads);
prettyPrint($ads);

exit(0);

$gmail = new \spamtonprof\gmailManager\GmailManager("mailsfromlbc@gmail.com");

$messages = $gmail->listMessages("subject:ligne after:2018/10/12 from:no.reply@leboncoin.fr");

$rows = [];
foreach ($messages as $message) {

    $row = [];

    $message = $gmail->getMessage($message->id, [
        'format' => 'full'
    ]);
    $body = $gmail->getBody($message);

    // récupération de l'email
    $match = [];
    preg_match_all("/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/", $body, $match);
    $email = $match[0][0];
    $row[] = $email;

    // récupération de l'ad id
    $match = [];
    // preg_match_all('#cours_particuliers/([0-9]+)\.htm#', $body, $match);
    preg_match_all('#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#', $body, $match);

    $adId = $match[0][1];
    $row[] = $adId;

    $slack->sendMessages("log", $row);

    $rows[] = $row;
}

serializeTemp($rows);