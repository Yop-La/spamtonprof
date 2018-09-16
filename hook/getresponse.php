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

/*
 * ce script est appelÃ© par getreponse sur certains Ã©vÃ¨nements ( inscription/dÃ©sinscription Ã  une liste emails)
 * il sert :
 * - Ã  envoyer les inscrits dans page life
 * - Ã  enregistrer les dÃ©inscrits dans une table
 *
 *
 *
 */

$slack = new \spamtonprof\slack\slack();

$accountMg = new \spamtonprof\stp_api\AccountManager();

$msgs = array();

$adresseMail = $_GET['contact_email'];
$campaignName = $_GET['campaign_name'];
$action = $_GET['action'];

if ($action == "subscribe") {
    
    $pageLifeCampaigns = [
        '4TP5I',
        '4TPZW',
        '4t7ut',
        '4t7kQ',
        '6CYEe',
        '6Cbps',
        '675Ij'
    ]; // contient toutes les séquences d'essais
    
    if (in_array($_GET['CAMPAIGN_ID'], $pageLifeCampaigns)) {
        $_GET['CAMPAIGN_ID'] = '4TPZW';
        
        $url = "https://us-central1-pali-c323a.cloudfunctions.net/getrespcv/-L6zpT-ZsqyuHk97mw0i/0?" . http_build_query($_GET);
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
    }
} else if ($action == "unsubscribe") {
    
    $smtpServerMg = new \spamtonprof\stp_api\SmtpServerManager();
    
    $smtpServer = $smtpServerMg->get(array(
        'ref_smtp_server' => $smtpServerMg::smtp2Go
    ));
    
    $body = file_get_contents(ABSPATH . "/wp-content/plugins/spamtonprof/emails/mail_desins_getresponse.txt");
    
    $smtpServer->sendEmail('Vous nous quittez ? ', $adresseMail, $body, 'info@spamtonprof.com', utf8_encode("L'équipe de SpamTonProf"));
    
    $accounts = $accountMg->getList($adresseMail);
    
    foreach ($accounts as $account) {
        
        $statut = $account->statut();
        $refCompte = $account->ref_compte();
        
        $msgs[] = ' ---------------- ';
        $msgs[] = $adresseMail . "vient de se désinscrire de la liste email : " . $campaignName;
        $msgs[] = 'ref compte associée : ' . $refCompte;
        $msgs[] = 'statut : ' . $statut;
        $msgs[] = "Il faut voir si il doit être désinscrit ou non de spamtonprof";
        $slack->sendMessages("fini_get_response", $msgs);
    }
    
    if (! $accounts) {
        
        $msgs[] = ' ---------------- ';
        $msgs[] = $adresseMail . "vient de se désinscrire de la liste email : " . $campaignName;
        $msgs[] = "aucune info sur son statut. Il faut voir si il doit déinscrit aussi de spamtonprof";
        $slack->sendMessages("fini_get_response", $msgs);
    }
}

?>