<?php

/**
 *  pour la boite de seb - adaption possible sur d'autres boites ( voir la "Tracking - Labels gmail api" dans evernote pour mise en place )
 *  il ne traque que les emails d'élève ( pas les mails des étudiants et des parents )
 *  
 * ce script sert : 
 *   - à stocker dans mail eleve - les messages des élèves 
 *   - à attribuer des libellées aux emails
 *   - il tourne tous les 5 minutes
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/wp-config.php");
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

$gmailManager = new spamtonprof\gmailManager\GmailManager("profdemathsenligne@gmail.com");

$mailEleveMg = new \spamtonprof\stp_api\EmailManager();

$lastEmail = $mailEleveMg->getLastEmail();

$dateLastEmail = $lastEmail->getDate_reception();

$searchOperator = "after:" . $dateLastEmail->getTimestamp() . " -from:sebastien@spamtonprof.com";

$messages = $gmailManager->listMessages($searchOperator);

$accountMg = new \spamtonprof\stp_api\AccountManager();

foreach ($messages as $message) {
    
    $messageId = $message->getId();
    
    $message = $gmailManager->getMessage($messageId);
    
    $email = new \spamtonprof\stp_api\Email(array(
        "message" => $message
    ));
    
    // récupération du compte
    
    $mailExpe = $email->getMail_expe();

    
    $matches = array();
    $account;
    
    if (preg_match('/\d\d(\d*)@mail.mightytext.net/', $mailExpe, $matches)) {
        
        $phoneNumber = $matches[1];
        
        $account = $accountMg->get(array(
            "query" => array(
                'phone_eleve' => $phoneNumber
            )
        ));
        
    } else {
        
        $account = $accountMg->get(array(
            "query" => array(
                'adresse_mail_eleve' => $mailExpe
            )
        ));
    }
    
    
    if ($account) {
        
        echo("account id ".$account->ref_compte(). "<br>");
        
        // add le mail dans la base
        $email->setRef_compte($account->ref_compte());
        
        $mailEleveMg->add($email);
        
        // attribution des labels
        
        $labelsToAdd = $gmailManager->getCustomLabelsToAdd($account);
        
        $gmailManager->modifyMessage($messageId, $labelsToAdd, array());
        
        // mise à jour de la date de dernier contact 
        
        $account->setLast_contact_eleve($email->getDate_reception());
        
        $accountMg->updateLastContactEleve($account);
    }
}

