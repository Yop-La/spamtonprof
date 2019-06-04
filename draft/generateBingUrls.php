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
 *  pour générer des urls bings à partir d'une liste de mots clés. Les urls permettent de récupérer le flux RSS.
 */

$rows = readCsv('keywords.csv');

$nb_rows = count($rows);

$urls = [];

foreach ($rows as $row) {
    
    $col = $row[0];
    $keyword = str_replace(' ', '+', $col);
    $url = 'https://www.bing.com/search?q=' . $keyword . '&format=rss' . PHP_EOL;
    
    $urls[] = $url;
}

$url_packs = array_chunk($urls, $nb_rows / 10);

$i = 1;
foreach ($url_packs as $url_pack) {
    $file = "./black_hat/pack$i.txt";
    foreach ($url_pack as $url) {
        file_put_contents($file, $url, FILE_APPEND);
    }
    $i = $i + 1;
}
