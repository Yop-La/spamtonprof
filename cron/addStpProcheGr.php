<?php
use spamtonprof\stp_api\GrCampaignMg;
use spamtonprof\stp_api\GrCustomFieldMg;
use spamtonprof\stp_api\GrTagMg;

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

/*
 * ce script sert à ajouter à la liste email stp_eleve les nouveaux élèves ( ceux sans gr_id )
 * tourne tous les jours
 *
 */

/*
 * première étape : récupérer les proches tq add_to_gr = true
 *
 * deuxième étape : ajouter les proches à liste ( prenom + email )
 *
 * troisième étape : mettre à jour gr_id + add_to_gr = false
 *
 */

$gr = new \GetResponse();

$procheMg = new \spamtonprof\stp_api\StpProcheManager();

$proches = $procheMg->getAll(array(
    'proche_to_ad_in_gr'
));

foreach ($proches as $proche) {

    $proche = \spamtonprof\stp_api\StpProche::cast($proche);

    // ajouter le contact à la liste getresponse
    $gr->add_to_stp_proche($proche);

    $contacts = $gr->retrieve_from_stp_proche($proche->getEmail());

    if (count($contacts) == 0) {
        continue;
    }

    $contact = $contacts[0];
    $contactId = $contact->contactId;

    $proche->setGr_id($contactId);
    $procheMg->update_gr_id($proche);

    $proche->setAdd_to_gr(false);
    $procheMg->update_add_to_gr($proche);
}
