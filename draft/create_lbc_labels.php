<?php

/*
 *
 * pour créer les labels leboncoin dans une boite gmail
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




$labelMg = new \spamtonprof\stp_api\GmailLabelManager();
$gmailManager = new \spamtonprof\googleMg\GoogleManager("mailsfromlbc@gmail.com");

$labels = $labelMg->getAll(array('type' => 'lbc'));

foreach ($labels as $label){
    
    $gmailManager->createLabel($label->getNom_label(), $label->getColor_label());
    
}