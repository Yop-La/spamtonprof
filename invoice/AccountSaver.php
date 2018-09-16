<?php
use spamtonprof\stp_api\AccountManager;

/*
 * ce script sert à sauvegarder les comptes à facturer pour la facturation manuelle
 * cf fonction generateInvoicesCsv de facture manager
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

$slack = new \spamtonprof\slack\Slack();

$refComptes = $_GET["refComptes"];



$accountMg = new \spamtonprof\stp_api\AccountManager();

$accounts = $accountMg ->getAll($refComptes);

$s = serialize($accounts);

file_put_contents('../tempo/invoice/accounts' . $refComptes[0] , $s);

$slack -> sendMessages("invoicing", array(count($accounts) . " comptes viennent d'êtres traités"));