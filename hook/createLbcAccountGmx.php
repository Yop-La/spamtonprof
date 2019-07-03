<?php
/**
 * 
 *  pour g�n�rer un compte lbc avant publication d'annonces par zenno avec un compte gmx associ�( en prod ) 
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

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log', [
    json_encode($_POST)
]);

// récupération des entrées
$ref_client = $_POST["ref_client"];
$telephone = $_POST["telephone"];
$ref_gmx_act = $_POST["ref_gmx_act"];

$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

// étape 1 : on récupère le client
$clientMg = new \spamtonprof\stp_api\LbcClientManager();
$client = $clientMg->get(array(
    "ref_client" => $ref_client
));

// étape 2 : on récupère le gmx act
$gmxActMg = new \spamtonprof\stp_api\GmxActManager();
$gmxAct = $gmxActMg->get(array(
    'ref_gmx_act' => $ref_gmx_act
));

$lbcAct = $lbcAccountMg->get(array(
    "ref_compte" => $gmxAct->getRef_compte_lbc()
));
if ($lbcAct) {
    $ret = new \stdClass();
    $ret->client = $client;
    $ret->lbcAct = $lbcAct;

    $slack = new \spamtonprof\slack\Slack();
    $slack->sendMessages('log-lbc', array(
        "Ce compte leboncoin " . $lbcAct->getMail() . " existe déjà. Pas besoin de le créer à nouveau"
    ));

    prettyPrint($ret);
}

// étape 3 : on récupère un prénom

$prenomLbcMg = new \spamtonprof\stp_api\PrenomLbcManager();
$prenom = $prenomLbcMg->get(array(
    'moins_utilise' => 'moins_utilise',
    "ref_cat_prenom" => $client->getRef_cat_prenom()
));

$prenom->inc_nb_use();
$prenomLbcMg->updateNbUse($prenom);

// étape 4 : on créé un compte lbc dans compte_lbc (adresse mail, password,
$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
$lbcAct = new \spamtonprof\stp_api\LbcAccount();
$lbcAct->setMail($gmxAct->getMail());
$lbcAct->setRef_client($ref_client);
$lbcAct->setTelephone($telephone);
$lbcAct->setPassword($gmxAct->getPassword());
$lbcAct->setPrenom($prenom->getPrenom());
$lbcAct = $lbcAccountMg->add($lbcAct);

$lbcAccountMg->updatePrenom($lbcAct);

// étape 5 : on associe au compte gmx le compte lbc créé
$gmxAct->setRef_compte_lbc($lbcAct->getRef_compte());
$gmxActMg->update_ref_compte_lbc($gmxAct);

$ret = new \stdClass();
$ret->client = $client;
$ret->lbcAct = $lbcAct;

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log-lbc', array(
    "Ajout à la bdd du compte leboncoin " . $lbcAct->getMail()
));

prettyPrint($ret);
