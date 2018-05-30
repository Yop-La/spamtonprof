<?php

// todostp faire le nécessaire pour éviter la mise en cache sur ce fichier et ces fichiers de ce genre

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/* ne pas mettre en cache */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();


date_default_timezone_set('Europe/Paris');

/* ce script sert à attribuer un plan de paiement à tous les comptes en essai */


$accountManager = new \spamtonprof\stp_api\AccountManager();
$accountManager -> attribuerPlanPlaiement();

echo("done");



?>