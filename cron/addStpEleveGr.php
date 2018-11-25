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

$gr = new \GetResponse();
$slack = new \spamtonprof\slack\Slack();

// script d'ajout des élèves
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

    $niveau = $eleve->getNiveau()->getGr_id();
    $formules = [];
    $matieres = [];
    $statuts = [];
    $profs = [];

    $parentRequired = $eleve->getParent_required();
    $prenomProche = 'undefined';

    $abos = $eleve->getAbos();

    foreach ($abos as $abo) {

        $formule = $abo->getFormule();
        $nomFormule = $formule->getFormule();
        $matieresObj = $formule->getMatieres();
        $statut = $abo->getStatut()->getGr_id();

        $prof = $abo->getProf();
        $profGrId = $prof->getGr_id();

        foreach ($matieresObj as $matiere) {

            $matieres[] = $matiere->getGr_id();
        }

        if ($parentRequired) {
            $proche = $abo->getProche();
            if ($proche) {
                $prenomProche = $abo->getProche()->getPrenom();
            } else {
                $slack->sendMessages('log', array(
                    'cet eleve a parent required = true mais impossible de récup proche. ref_eleve :' . $eleve->getRef_eleve()
                ));
                continue;
            }
        }

        $statuts[] = $statut;
        $profs[] = $profGrId;
        $formules[] = $nomFormule;
    }

    $params = '{
            "name": "' . $eleve->getPrenom() . '",
            "email": "' . $eleve->getEmail() . '",
            "campaign": {
                "campaignId": "' . GrCampaignMg::STP_ELEVE . '"
            }
        }';

    $params = json_decode($params);

    // ajout des tags et des champs
    $tags = [];
    $customFieldValues = [];

    foreach ($statuts as $statut) {
        $tag = new \stdClass();
        $tag->tagId = $statut;
        $tags[] = $tag;
    }

    foreach ($matieres as $matiere) {
        $tag = new \stdClass();
        $tag->tagId = $matiere;
        $tags[] = $tag;
    }

    foreach ($profs as $prof) {
        $tag = new \stdClass();
        $tag->tagId = $prof;
        $tags[] = $tag;
    }

    $tag = new \stdClass();
    $tag->tagId = $niveau;
    $tags[] = $tag;

    if ($parentRequired) {
        $tag->tagId = GrTagMg::PARENT_REQUIRED;
        $customFieldValue = new \stdClass();

        $customFieldValue->customFieldId = GrCustomFieldMg::PRENOM_PROCHE_ID;
        $customFieldValue->value = array(
            $prenomProche
        );

        $customFieldValues[] = $customFieldValue;
    }

    $customFieldValue = new \stdClass();

    $customFieldValue->customFieldId = GrCustomFieldMg::REF_ELEVE_ID;
    $customFieldValue->value = array(
        $eleve->getRef_eleve()
    );

    $customFieldValues[] = $customFieldValue;

    $params->tags = $tags;
    $params->customFieldValues = $customFieldValues;

    // ajouter le contact à la liste getresponse

    $contact = $gr->addContact($params);

    // récupérer le contact et mettre à jour la getresponse id

    $params = array(
        "query" => array(
            "email" => $eleve->getEmail(),
            "campaignId" => GrCampaignMg::STP_ELEVE
        )
    );

    $contacts = (array) $gr->getContacts($params);

    if (count($contacts) == 0) {
        $slack->sendMessages('log', array(
            'contact pas ajouté à stp_eleve de ref_eleve :' . $eleve->getRef_eleve()
        ));
        serializeTemp($eleve);
        continue;
    }

    $contact = $contacts[0];
    $contactId = $contact->contactId;

    $eleve->setGr_id($contactId);
    $eleveMg->updateGrId($eleve);
}

