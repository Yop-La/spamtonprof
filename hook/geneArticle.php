<?php
/**
 * 
 *  pour générer un article à partir d'un mot clé
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

// recuperation des entrees
$password = $_GET["password"];
$keyword = $_GET["keyword"];


if ($password == CRON_KEY) {
    
    $url_serp = "https://www.serprobot.com/api/v1/api.php?api_key=" . SERP_ROBOT_KEY . "&action=get_serps&region=www.google.fr&keyword=".urlencode($keyword)."&device=desktop&hl=fr";
    $urls = url_get_contents($url_serp);
    
    $urls = json_decode($urls);

    
    if(property_exists($urls, 'error')){
        
        $slack = new \spamtonprof\slack\Slack();
        $slack->sendMessages('log-spam-google', array('Erreur fatale lors de la génération d\'un article: ',$urls->error));
        die();
        
    }
    
    $urls = $urls -> serps;
    
    $urlParser = new \spamtonprof\stp_api\UrlParser();
    
    $result = $urlParser->parseUrls($urls, 20);
    
    $texts = $result['texts'];
    $titles = $result['titles'];
    $images = $result['images'];
    
    $corpus_body = implode(" ", $texts);
    $corpus_title = implode(" ", $titles);
    
    $textGenerator = new \spamtonprof\stp_api\TextGenerator();
    $text = $textGenerator->generate_text($corpus_body, 20000, 1);
    $title = $textGenerator->generate_text($corpus_title, 1000, 1);
    
    serializeTemp($text, "/text");
    serializeTemp($title, "/title");
    serializeTemp($images, "/images");
    
    
    $articleGenerator = new \spamtonprof\stp_api\ArticleGenerator();
    $article = $articleGenerator->generate_article($images, $text, $title);
    
    echo ($articleGenerator->get_article());
    
    die();
}

