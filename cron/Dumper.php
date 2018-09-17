<?php



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

$date = new DateTime(null,new DateTimeZone("Europe/Paris"));

echo "<h3>Backing up mysql database to dump  </h3>";

$slack = new \spamtonprof\slack\Slack();

$filename_to_add = dirname(dirname(__FILE__)) . '/dump/dump/'.$date->format(PG_DATE_FORMAT).'.sql';

try {
    $dump = new \Ifsnop\Mysqldump\Mysqldump('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
    $dump->start($filename_to_add);
} catch (\Exception $e) {
    echo 'mysqldump-php error: ' . $e->getMessage();
}

$date -> sub(new DateInterval('P5D'));

$filename_to_drop = dirname(dirname(__FILE__)) . '/dump/dump/'.$date->format(PG_DATE_FORMAT).'.sql';

if(file_exists ($filename_to_drop)){
    unlink($filename_to_drop);
}

$slack->sendMessages($slack::Log, array("wordpress database dumped", "size dump : " . filesize ($filename_to_add) . " octets"," ---- "));