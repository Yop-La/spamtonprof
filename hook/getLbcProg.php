<?php
/**
 * 
 *  ppour générer un compte lbc avant publication d'annonces par zenno ( en prod )
 *  
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

$csvUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQTjgHZnlP89zb95zChBhC8pk2ozd-2-K2aJqitVnil7Qs8vyhfUrZEPfqC37WjBSezJFkUvbI_TTCY/pub?output=csv';

$rows = readCsv($csvUrl);

prettyPrint($rows);