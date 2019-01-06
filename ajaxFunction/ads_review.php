<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans ad-review.js sur la page dont le slug est ad-review
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

    $ads = $lbcProcessMg->generateAds($refClient, 50, $phone, 815, false);
    //815 est un compte pris au hasard pour tester la récupération du code promo
    $lbcAccts = $lbcAcctMg->getAll(array(
        'ref_client' => $refClient
    ));

    $emails = [];
    foreach ($lbcAccts as $lbcAcct) {
        $emails[] = $lbcAcct->getMail();
    }

    $retour->phone = $phone;
    $retour->refClient = $refClient;
    $retour->ads = $ads;
    $retour->emails = $emails;

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

    if ($client_action == 'ajout') {
        $client = new \spamtonprof\stp_api\LbcClient(array(
            'nom_client' => $prenom,
            'prenom_client' => $nom,
            'domain' => $domain,
            'img_folder' => $folder_img
        ));
        $clientMg->add($client);

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