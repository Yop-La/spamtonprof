<?php


/*
 lien : https://spamtonprof.com/wp-content/plugins/spamtonprof/cnl/getAccountPasCree.php
 */

use spamtonprof\stp_api\AccountManager;
use spamtonprof\stp_api\EleveManager;
use spamtonprof\stp_api\ClasseManager;
use spamtonprof\stp_api\GmailLabelManager;
use spamtonprof\stp_api\GmailLabel;
use spamtonprof\stp_api\NbEmailManager;
use spamtonprof\stp_api\GetResponseManager;
use spamtonprof\getresponse_api\CampaignManager;


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

/* espace de travail */

// resetCompteTest();

// printInfoCompteTest();


$cnlGmailAccountMg = new \spamtonprof\cnl\GmailAccountManager();

$cnlGmailAccount  = $cnlGmailAccountMg -> getPasCree();

prettyPrint($cnlGmailAccount);

return;