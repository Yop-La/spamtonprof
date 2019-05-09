

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


$lbcApi = new \spamtonprof\stp_api\LbcApi();

$offset = 0;
$all_txt = [];
while ($txts = $lbcApi->getTexts(array(
    'code_promo' => utf8_encode("maths lyc�e �cole d'ing�nieur")
), $offset)) {
    $all_txt = array_merge($all_txt, $txts);
    $offset = $offset + 100;
}

$final_txts = [];

foreach ($all_txt as $txt) {
    
    if (strlen($txt) > 500) {
        $final_txts[] = $txt;
    }
}

$typeTxtMg = new \spamtonprof\stp_api\TypeTexteManager();

$typeTxt = $typeTxtMg->get(array(
    'type' => 'maths_lycee_ecole_inge'
));

if (! $typeTxt) {
    $typeTxt = $typeTxtMg->add(new \spamtonprof\stp_api\TypeTexte(array(
        'type' => 'maths_lycee_ecole_inge'
    )));
}

$txtMg = new \spamtonprof\stp_api\LbcTexteManager();

foreach ($final_txts as $txt) {
    
    $txtMg->add(new \spamtonprof\stp_api\LbcTexte(array(
        'texte' => $txt . '    not_valid',
        'type' => $typeTxt->getType(),
        'ref_type_texte' => $typeTxt->getRef_type()
    )));
}

prettyPrint($final_txts);

