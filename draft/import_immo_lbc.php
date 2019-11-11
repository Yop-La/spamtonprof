<?php

/*
 *
 * pour récupérer des annonces immo sur leboncoin
 * 
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

$lbc_api = new \spamtonprof\stp_api\LbcApi();

$filters_bagnoles_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":6000,"lat":48.55707,"lng":-0.41296,"radius":5000},"city":"Bagnoles-de-l\'Orne","label":"Bagnoles-de-l\'Orne (61140)","locationType":"city","zipcode":"61140"}]},"ranges":{}}}';

$filters_lorient_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":3897,"lat":47.75017999999999,"lng":-3.36685,"radius":5000},"city":"Lorient","department_id":"56","label":"Lorient (toute la ville)","locationType":"city","region_id":"6"}]},"ranges":{}}}';

$filters_nantes_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":10000,"lat":47.23898554566441,"lng":-1.5262136157260586,"radius":5000},"city":"Nantes","department_id":"44","label":"Nantes (toute la ville)","locationType":"city","region_id":"18"}]},"ranges":{}}}';

$filters_vannes_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":5716,"lat":47.65778,"lng":-2.75527,"radius":5000},"city":"Vannes","department_id":"56","label":"Vannes (56000)","locationType":"city","region_id":"6","zipcode":"56000"}]},"ranges":{}}}';

$filters_rennes_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{"parrot_used":7},"location":{"locations":[{"area":{"default_radius":5000,"lat":48.10717427604917,"lng":-1.6693251892374632,"radius":10000},"city":"Rennes","department_id":"35","label":"Rennes (toute la ville)","locationType":"city","region_id":"6"}]},"ranges":{}}}';

$filters_angers_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":10000,"lat":47.500870253051815,"lng":-0.524992922636301,"radius":5000},"city":"Angers","department_id":"49","label":"Angers (toute la ville)","locationType":"city","region_id":"18"}]},"ranges":{}}}';

$filters_quimper_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":8052,"lat":47.99471,"lng":-4.10792},"city":"Quimper","department_id":"29","label":"Quimper (29000)","locationType":"city","region_id":"6","zipcode":"29000"}]},"ranges":{}}}';

$filters_le_mans_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":10000,"lat":47.98418574871565,"lng":0.1904810906326669,"radius":5000},"city":"Le Mans","department_id":"72","label":"Le Mans (toute la ville)","locationType":"city","region_id":"18"}]},"ranges":{}}}';

$filters_brest_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":7622,"lat":48.41652,"lng":-4.49945,"radius":5000},"city":"Brest","department_id":"29","label":"Brest (29200)","locationType":"city","region_id":"6","zipcode":"29200"}]},"ranges":{}}}';

$filters_laval_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":6198,"lat":48.05569,"lng":-0.7466,"radius":5000},"city":"Laval","department_id":"53","label":"Laval (53000)","locationType":"city","region_id":"18","zipcode":"53000"}]},"ranges":{}}}';

$filters_fougeres_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":3057,"lat":48.35112,"lng":-1.1942},"city":"Fougères","department_id":"35","label":"Fougères (35300)","locationType":"city","region_id":"6","zipcode":"35300"}]},"ranges":{}}}';

$filters_le_mans_coloc = '{"filters":{"category":{"id":"11"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":10000,"lat":47.98418574871565,"lng":0.1904810906326669,"radius":5000},"city":"Le Mans","department_id":"72","label":"Le Mans (toute la ville)","locationType":"city","region_id":"18"}]},"ranges":{}}}';

$filters_vitre_immo = '{"filters":{"category":{"id":"9"},"enums":{"ad_type":["offer"]},"keywords":{},"location":{"locations":[{"area":{"default_radius":6182,"lat":48.123079999999995,"lng":-1.21184,"radius":5000},"city":"Vitré","department_id":"35","label":"Vitré (toute la ville)","locationType":"city","region_id":"6"}]},"ranges":{}}}';

$lbc_api->extract_from_ads($lbc_api::vente_immo, "Lorient", "", false, $filters_lorient_immo);
