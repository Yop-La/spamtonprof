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
 * ce script sert � ajouter � la liste email stp_eleve les nouveaux �l�ves ( ceux sans gr_id )
 * tourne tous les jours
 *
 */

$gr = new \GetResponse();
$slack = new \spamtonprof\slack\Slack();

// script d'ajout des �l�ves
$constructor = array(
    "construct" => array(
        "abonnements",
        "ref_niveau"
    ),
    "abonnements" => array(
        "construct" => array(
            'ref_formule',
            "ref_statut_abonnement",
            "ref_prof",
            "ref_parent"
        ),
        'ref_formule' => array(
            "construct" => array(
                "matieres"
            )
        )
    )
);

$eleveMg = new \spamtonprof\stp_api\StpEleveManager();

$eleves = $eleveMg->getAll(array(
    "eleve_to_ad_in_gr"
), false, $constructor);

$slack->sendMessages('log', array(
    'count eleves : ' . count($eleves)
));

foreach ($eleves as $eleve) {

    // ajouter le contact � la liste getresponse

    $params = $eleveMg->toStpEleveGr($eleve, false);
    $contact = $gr->addContact($params);

    // r�cup�rer le contact et mettre � jour la getresponse id

    $params = array(
        "query" => array(
            "email" => $eleve->getEmail(),
            "campaignId" => GrCampaignMg::STP_ELEVE
        )
    );

    $contacts = (array) $gr->getContacts($params);

    if (count($contacts) == 0) {
        $slack->sendMessages('log', array(
            'contact pas ajout� � stp_eleve de ref_eleve :' . $eleve->getRef_eleve()
        ));
        serializeTemp($eleve);
        continue;
    }

    $contact = $contacts[0];
    $contactId = $contact->contactId;

    $eleve->setGr_id($contactId);
    $eleveMg->updateGrId($eleve);
}
