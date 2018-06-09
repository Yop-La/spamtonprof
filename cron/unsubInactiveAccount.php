<?php
require_once (dirname(__FILE__) . '/wp-config.php');
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



$accountMg = new \spamtonprof\stp_api\AccountManager();

$getResponseMg = new \spamtonprof\stp_api\GetResponseManager();

$accounts = $accountMg->unsubInactiveAccounts();

$smtpServerMg = new \spamtonprof\stp_api\SmtpServerManager();
$smtpEmailMg = new \spamtonprof\stp_api\SmtpEmailManager();

$smtpEmailEleve = $smtpEmailMg->get(array(
    'ref_smtp_email' => 1
));
$smtpEmailParent = $smtpEmailMg->get(array(
    'ref_smtp_email' => 2
));

// set email expediteur
$smtpServer = $smtpServerMg->get(array("mail" => $smtpServerMg::alexandreAtSpamTonProf));
$prenomSender = 'Alexandre';

foreach ($accounts as $account) {
    
    $mailParent = $account->proche()->adresse_mail();
    $mailEleve = $account->eleve()->adresse_mail();
    $prenomParent = ucfirst($account->proche()->prenom());
    $prenomEleve = ucfirst($account->eleve()->prenom());
    
    $infosEmail = array(
        ":prenom_eleve:" => $prenomEleve,
        ":prenom_parent:" => $prenomParent,
        ":prenom_sender:" => $prenomSender,
        ":email_sender:" => $smtpServer->getUsername()
    );
    
    if (! $account->getSame_email()) {
        
        $getResponseMg->removeAll($mailEleve);
        
        $smtpServer->sendEmail($smtpEmailEleve->getSubject(), $mailEleve, strtr($smtpEmailEleve->getContent(), $infosEmail), $smtpServer->getUsername());
    }
    $smtpServer->sendEmail($smtpEmailParent->getSubject(), $mailParent, strtr($smtpEmailParent->getContent(), $infosEmail), $smtpServer->getUsername());
    
    $getResponseMg->removeAll($mailParent);
}

