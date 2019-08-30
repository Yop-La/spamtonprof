<?php

/*
 *
 * pour sotcker les emails de validation d'annonces en base
 *
 *
 */
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



$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

$gmailMg = new \spamtonprof\googleMg\GoogleManager("mailsfromlbc@gmail.com");
$msgs = $gmailMg->listMessages('renouvelez gratuitement votre annonce ', 60, 50);

$lbcRenewalUrlManager= new \spamtonprof\stp_api\LbcRenewalUrlManager();

foreach ($msgs as $msg) {
    
    $gmail_id = $msg->id;
    
    $message = $gmailMg->getMessage($gmail_id, [
        'format' => 'full'
    ]);
    
   
    $gmailId = $message->id;
    
    $from = $gmailMg->getHeader($message, "From");
    $snippet = $message->snippet;
    $subject = $gmailMg->getHeader($message, "Subject");
    $to = $gmailMg->getHeader($message, "To");
    $messageId = $gmailMg->getHeader($message, "Message-Id");
    $body = $gmailMg->getBody($message);
    
    
    
    $timeStamp = $message->internalDate / 1000;
    $dateReception = new \DateTime();
    $dateReception->setTimestamp($timeStamp);
    $dateReception->setTimezone(new \DateTimeZone('Europe/Paris'));
    
    
    $to = extractFirstMail($to);
    
    $urls = extract_url($body);
    $url = $urls[0][2];
    
    $url = htmlspecialchars_decode($url);
    
    
    $act = $lbcAccountMg->get(array(
        "mail" => $to
    ));
    
    $renewalUrl = $lbcRenewalUrlManager->add(new \spamtonprof\stp_api\LbcRenewalUrl(array(
        "url" => $url,
        "statut" => 1,
        "ref_compte_lbc" => $act->getRef_compte(),
        "date_reception" => $dateReception->format(PG_DATETIME_FORMAT)
    )));
    
    
    prettyPrint($renewalUrl)
}


exit();



$gmailMg = new \spamtonprof\googleMg\GoogleManager("mailsfromlbc@gmail.com");
$msgs = $gmailMg->listMessages('leboncoin "mise en ligne"', 60, 50);

$lbcAdValidationEmailMg = new \spamtonprof\stp_api\LbcAdValidationEmailManager();

foreach ($msgs as $msg) {

    $gmail_id = $msg->id;

    $message = $gmailMg->getMessage($gmail_id, [
        'format' => 'full'
    ]);

    $timeStamp = $message->internalDate / 1000;
    $dateReception = new \DateTime();
    $dateReception->setTimestamp($timeStamp);
    $dateReception->setTimezone(new \DateTimeZone('Europe/Paris'));

    $to = $gmailMg->getHeader($message, "To");
    $to = extractFirstMail($to);

    $lbcAdValidationEmailMg->add(new \spamtonprof\stp_api\LbcAdValidationEmail(array(
        'gmail_id' => $gmail_id,
        'date_reception' => $dateReception->format(PG_DATETIME_FORMAT),
        'destinataire' => $to
    )));
}