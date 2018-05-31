<?php
use spamtonprof\slack\Slack;

/**
 * pour la boite de seb - adaption possible sur d'autres boites ( voir la "Tracking - Labels gmail api" dans evernote pour mise en place )
 * il ne traque que les emails d'élève ( pas les mails des étudiants et des parents )
 *
 * ce script sert :
 * - à stocker dans mail eleve - les messages des élèves
 * - à attribuer des libellées aux emails
 * - il tourne tous les 5 minutes
 */

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

$slack = new Slack();

$gmailManager = new spamtonprof\gmailManager\GmailManager("profdemathsenligne@gmail.com");

$mailEleveMg = new \spamtonprof\stp_api\EmailManager();

$accountMg = new \spamtonprof\stp_api\AccountManager();

$last = $mailEleveMg->getLastEmail();

$hitories = $gmailManager->listHistory($last->getHistory_id());

$indexMessage = 0;

foreach ($hitories as $hitory) {
    
    if ($indexMessage == 20) { // on arrête dès que 10 messages ont été traités pour éviter le time out
        exit(0);
    }
    
    $hitoryId = $hitory->id;
    
    $messageId = $hitory->messages[0]->id;
    
    try {
        
        $message = $gmailManager->getMessage($messageId);
    } catch (\Exception $e) {
        echo ("404 not found " . "<br>" . "<br>");
        continue;
    }
    
    $email = new \spamtonprof\stp_api\Email(array(
        "message" => $message
    ));
    
    $indexMessage = $indexMessage + 1;
    
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
        
        echo ("ref gmail : " . $email->getRef_gmail() . "<br>");
        echo ("account id " . $account->ref_compte() . "<br>". "<br>");
        
        
        $email->setRef_compte($account->ref_compte());
        
        $email->setHistory_id($hitoryId);
        
        // add le mail dans la base
        
        
        $mailEleve = $mailEleveMg->get(array("ref_gmail" =>$email->getRef_gmail()));
        if($mailEleve){
            if(is_null($mailEleve->getHistory_id())){
                $mailEleveMg->updateHistoryId($email);
                $slack->sendMessages($slack::MessagEleve, array(
                    "maj de l'history id du message de " . $account->eleve()
                    ->adresse_mail(),"envoyé le " . $email->getDate_reception()->format(PG_DATETIME_FORMAT), 
                    "ref gmail : " . $email->getRef_gmail() ,
                    "------"
                ));
            }
        }else{
            $mailEleveMg->add($email);
            $slack->sendMessages($slack::MessagEleve, array(
                "nouveau message de " . $account->eleve()
                ->adresse_mail(),"envoyé le " . $email->getDate_reception()->format(PG_DATETIME_FORMAT), 
                "ref gmail : " . $email->getRef_gmail() ,
                "------"
            ));
        }
        
        
        // attribution des labels
        
        $labelsToAdd = $gmailManager->getCustomLabelsToAdd($account);
        
        $gmailManager->modifyMessage($messageId, $labelsToAdd, array());
        
        // mise à jour de la date de dernier contact
        
        $account->setLast_contact_eleve($email->getDate_reception());
        
        $accountMg->updateLastContactEleve($account);
    }
    
    $indexMessage = $indexMessage + 1;
}

