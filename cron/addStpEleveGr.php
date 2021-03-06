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
 * première étape : récupérer les élèves tq add_to_gr = true
 *
 * deuxième étape : ajouter l'élève à liste ( prenom + email )
 *
 * troisième étape : mettre à jour gr_id + add_to_gr = false
 *
 */

$gr = new \GetResponse();

$eleveMg = new \spamtonprof\stp_api\StpEleveManager();

$eleves = $eleveMg->getAll(array(
    'eleve_to_ad_in_gr'
));

foreach ($eleves as $eleve) {

    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

    // ajouter le contact à la liste getresponse
    $gr->add_to_stp_eleve($eleve);

    $contacts = $gr->retrieve_from_stp_eleve($eleve->getEmail());

    if (count($contacts) == 0) {
        continue;
    }

    $contact = $contacts[0];
    $contactId = $contact->contactId;

    $eleve->setGr_id($contactId);
    $eleveMg->updateGrId($eleve);

    $eleve->setAdd_to_gr(false);
    $eleveMg->update_add_to_gr($eleve);
}
