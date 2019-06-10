<?php

// toutes ces fonction seront �x�cut�s par un appel ajax r�alis� dans ad-review.js sur la page dont le slug est ad-review
add_action('wp_ajax_ajaxAdsReview', 'ajaxAdsReview');

add_action('wp_ajax_nopriv_ajaxAdsReview', 'ajaxAdsReview');

add_action('wp_ajax_ajaxGetConfClient', 'ajaxGetConfClient');

add_action('wp_ajax_nopriv_ajaxGetConfClient', 'ajaxGetConfClient');

add_action('wp_ajax_ajaxGetTextesByRef', 'ajaxGetTextesByRef');

add_action('wp_ajax_nopriv_ajaxGetTextesByRef', 'ajaxGetTextesByRef');

add_action('wp_ajax_ajaxGetTitlesByRef', 'ajaxGetTitlesByRef');

add_action('wp_ajax_nopriv_ajaxGetTitlesByRef', 'ajaxGetTitlesByRef');

add_action('wp_ajax_ajaxUpdateCfgClient', 'ajaxUpdateCfgClient');

add_action('wp_ajax_nopriv_ajaxUpdateCfgClient', 'ajaxUpdateCfgClient');

add_action('wp_ajax_ajaxGetReponsesByRef', 'ajaxGetReponsesByRef');

add_action('wp_ajax_nopriv_ajaxGetReponsesByRef', 'ajaxGetReponsesByRef');

add_action('wp_ajax_ajaxGetPrenomsByCat', 'ajaxGetPrenomsByCat');

add_action('wp_ajax_nopriv_ajaxGetPrenomsByCat', 'ajaxGetPrenomsByCat');

function ajaxAdsReview()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));

    $refClient = $fields->choisir_client_leboncoin; // vaut false si prospect ou nouveau ou ref_eleve (si !prospect )

    $phone = $fields->phone;

    if (! $phone) {
        $phone = 'pas-de-num';
    }

    $lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();
    $lbcAcctMg = new \spamtonprof\stp_api\LbcAccountManager();

    $ads = $lbcProcessMg->generateAds($refClient, 50, $phone);
    $lbcAccts = $lbcAcctMg->getAll(array(
        'ref_client' => $refClient
    ));

    $emails = [];
    foreach ($lbcAccts as $lbcAcct) {
        $emails[] = $lbcAcct->getMail();
    }

    // récupération des prénoms du client
    
    $clientMg = new \spamtonprof\stp_api\LbcClientManager();
    $client = $clientMg->get(array('ref_client' => $refClient));
    
    $prenomMg = new \spamtonprof\stp_api\PrenomLbcManager();
    $prenoms = $prenomMg -> getAll(array('ref_cat_prenom' => $client->getRef_cat_prenom()));
    
    
    
    // récupération des réponses du client
    $texteMg = new \spamtonprof\stp_api\LbcTexteManager();
    $reponses = $texteMg -> getAll(array("ref_type_texte" => $client->getRef_reponse_lbc()));
    
    
    $retour->phone = $phone;
    $retour->refClient = $refClient;
    $retour->ads = $ads;
    $retour->emails = $emails;
    $retour->prenoms = $prenoms;
    $retour->reponses = $reponses;
    
    
    
    echo (json_encode($retour));

    die();
}

function ajaxUpdateCfgClient()
{
    $hasTypeTitleMg = new \spamtonprof\stp_api\HasTitleTypeManager();
    $hasTypeTexteMg = new \spamtonprof\stp_api\HasTextTypeManager();
    $clientMg = new \spamtonprof\stp_api\LbcClientManager();

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));

    $client_action = $fields->client_action; // vaut ajout ou update
    $choisir_client = $fields->choisir_client;
    $prenom = $fields->prenom;
    $nom = $fields->nom;
    $type_titre = $fields->type_titre;
    $type_texte = $fields->type_texte;
    $domain = $fields->domain;
    $folder_img = $fields->folder_img;
    $reponse_lbc = $fields->reponse_lbc;
    $ref_cat_prenom = $fields->prenom_lbc;
    $label = $fields->label;

    if ($client_action == 'ajout') {
        $client = new \spamtonprof\stp_api\LbcClient(array(
            'nom_client' => $nom,
            'prenom_client' => $prenom,
            'domain' => $domain,
            'img_folder' => $folder_img
        ));
        $clientMg->add($client);

        $client->setRef_reponse_lbc($reponse_lbc);
        $clientMg->updateRefReponseLbc($client);

        $client->setRef_cat_prenom($ref_cat_prenom);
        $clientMg->update_ref_cat_prenom($client);

        $client->setLabel($label);
        $clientMg->update_label($client);

        $choisir_client = $client->getRef_client();
    } else if ($client_action == 'update') {
        $client = $clientMg->get(array(
            'ref_client' => $choisir_client
        ));

        $client->setPrenom_client($prenom);
        $clientMg->updatePrenom($client);

        $client->setNom_client($nom);
        $clientMg->updateNom($client);

        $client->setDomain($domain);
        $clientMg->updateDomain($client);

        $client->setImg_folder($folder_img);
        $clientMg->updateImgFolder($client);

        $client->setRef_reponse_lbc($reponse_lbc);
        $clientMg->updateRefReponseLbc($client);

        $client->setRef_cat_prenom($ref_cat_prenom);
        $clientMg->update_ref_cat_prenom($client);

        $client->setLabel($label);
        $clientMg->update_label($client);
    } else if ($client_action == 'delete') {

        $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

        $lbcAccountMg->updateAll(array(
            "dumpRefClient" => $choisir_client
        ));

        $hasTypeTexteMg->deleteAll(array(
            'ref_client' => $choisir_client
        ));
        $hasTypeTitleMg->deleteAll(array(
            'ref_client' => $choisir_client
        ));

        $clientMg->deleteAll(array(
            'ref_client' => $choisir_client
        ));

        echo (json_encode($retour));

        die();
    }

    $hasTypeTexteMg->deleteAll(array(
        'ref_client' => $choisir_client
    ));
    $hasTypeTitleMg->deleteAll(array(
        'ref_client' => $choisir_client
    ));

    $hasTypeTitle = new \spamtonprof\stp_api\HasTitleType(array(
        "ref_client" => $choisir_client,
        "ref_type_titre" => $type_titre
    ));
    $hasTypeTitleMg->add($hasTypeTitle);

    $hasTypeTexte = new \spamtonprof\stp_api\HasTextType(array(
        'ref_type' => $type_texte,
        'ref_client' => $choisir_client
    ));

    $hasTypeTexteMg->add($hasTypeTexte);

    echo (json_encode($retour));

    die();
}

function ajaxGetConfClient()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $refClient = $_POST['ref_client'];

    $lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();

    $conf = $lbcProcessMg->getDefaultConf($refClient);

    $retour->conf = $conf;

    echo (json_encode($retour));

    die();
}

function ajaxGetTitlesByRef()

{
    header('Content-type: application/json');

    $refType = $_POST["ref_type_titre"];

    $accountManager = new \spamtonprof\stp_api\LbcTitleManager();

    $titles = $accountManager->getAll(array(
        "ref_type_titre" => $refType
    ));

    echo (json_encode(array(
        "titles" => $titles
    )));

    die();
}

function ajaxGetPrenomsByCat()

{
    header('Content-type: application/json');

    $cat_prenom = $_POST["cat_prenom"];

    $prenom_mg = new \spamtonprof\stp_api\PrenomLbcManager();

    $prenoms = $prenom_mg->getAll(array(
        "ref_cat_prenom" => $cat_prenom
    ));

    echo (json_encode(array(
        "reponses" => $prenoms
    )));

    die();
}

function ajaxGetReponsesByRef()

{
    header('Content-type: application/json');

    $ref_reponse = $_POST["ref_reponse"];

    $texte_mg = new \spamtonprof\stp_api\LbcTexteManager();

    $reponses = $texte_mg->getAll(array(
        "ref_type_texte" => $ref_reponse
    ));

    echo (json_encode(array(
        "reponses" => $reponses
    )));

    die();
}

function ajaxGetTextesByRef()

{
    header('Content-type: application/json');

    $refType = $_POST["ref_type_texte"];
    $limit = $_POST["limit"];

    $texteMg = new \spamtonprof\stp_api\LbcTexteManager();

    $textes = $texteMg->getAll(array(
        "ref_type_texte" => $refType,
        'limit' => $limit
    ));

    echo (json_encode(array(
        "textes" => $textes
    )));

    die();
}