<?php
require_once (dirname(__FILE__) . "/wp-config.php");
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
 * ce script sert à ajouter à pagelife les conversions ratés
 * c'est à dire les emails dans la recherche sauvegardée : missed_conversions_pagelife
 *
 */

$get = array();

$get['account_login'] = 'alexandre@spamtonprof.com';
$get['contact_origin'] = 'panel';
$get['CAMPAIGN_ID'] = '4TPZW';
$get['action'] = 'subscribe';
$get['ACCOUNT_ID'] = 'nn6Ze';
$get['contact_ip'] = '90.91.169.5';
$get['contact_email'] = 'testt@yopla.33mail.com';
$get['CONTACT_ID'] = 'gx6nGc';
$get['contact_name'] = 'Testt464';
$get['campaign_name'] = 'eleve_en_essai';

$getResponse = new GetResponse(GR_API);
$slack = new \spamtonprof\slack\Slack();
$msgs = array();

$iFile = dirname(__FILE__) . "\indiceContactPl";

$i = unserializeTemp($iFile, false);



if ($i == false) {
    $i = 1;
} else {
    $i ++;
}

serializeTemp($i, $iFile, false);

$contacts = $getResponse->getContactsSearchContacts('Zkhg');

$contacts = (array) $contacts;

$nbContacts = count($contacts);

if ($i > $nbContacts) {
    
    $msgs[] = " tous les contacts en retard sont maintenant dans page life";
    $msgs[] = 'il faut arrêter le cron du fichier addToPageLife.php';
} else {
    
    $msgs[] = " ajout du contact n° " . $i." à page life";
    $contact = $contacts[$i-1];
    
    
    
    $contact = $getResponse -> getContact($contact -> contactId);
    
    echo(json_encode($contact));
    
    $get['contact_ip'] = $contact->ipAddress;
    $get['contact_email'] = $contact->email;
    $get['contact_name'] = $contact->name;
    
    $url = "https://us-central1-pali-c323a.cloudfunctions.net/getrespcv/-L6zpT-ZsqyuHk97mw0i/0?" . http_build_query($get);
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

$slack->sendMessages("log", $msgs);

