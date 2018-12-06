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

use Ovh\Api;

// Informations about your application
$applicationKey = "At40SjPHzysRGhkL";
$applicationSecret = "qwOB6M0tBRgHH8hzebtWGFqZzcK4UMry";
$redirection = "http://localhost/spamtonprof/wd2.php";

// Information about API and rights asked
$endpoint = 'ovh-eu';
$rights = array(
    (object) [
        'method' => 'POST',
        'path' => '/*'
    ]
);

// Get credentials
$conn = new Api($applicationKey, $applicationSecret, $endpoint);
$credentials = $conn->requestCredentials($rights, $redirection);

// Save consumer key and redirect to authentication page
serializeTemp($credentials["consumerKey"], "/tempo/consumerKey");
header('location: ' . $credentials["validationUrl"]);
