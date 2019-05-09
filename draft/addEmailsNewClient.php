<?php
bugbugbug
/*
 *
 * pour faire un contr�le des publications en ligne avec les mails envoy�s par leboncon
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

/**
 * 
 *  � utiliser quand ajout d'un client pour lui attribuer des emails
 */


$compteMg = new \spamtonprof\stp_api\LbcAccountManager();

$comptes = $compteMg->getAll(array(
    'ref_client' => 12
));

foreach ($comptes as $compte) {
    $mail = $compte->getMail();
    $mail = strtolower($mail);
    $mail = str_replace('thomas', 'valentin', $mail);
    
    $pattern = '/\d+/i';
    $replacement = '';
    $mail = preg_replace($pattern, $replacement, $mail);
    $compteMg->add(new \spamtonprof\stp_api\LbcAccount(array('mail' => $mail,'ref_client' => 25)));
}

prettyPrint($comptes);