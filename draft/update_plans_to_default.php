<?php
bugbugbug
require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set("allow_url_fopen", 1);
ini_set("allow_url_include", 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$planMg = new \spamtonprof\stp_api\StpPlanManager();

$formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

$formules = $formuleMg->getAll(array(
    'custom' => " where ref_formule > 84 and lower(formule) not like '%termi%' order by ref_formule desc limit 50;"
));

foreach ($formules as $formule) {
    
    $planMg->update_defaut_plan($formule->getRef_formule(), 'defaut');
}

die();
