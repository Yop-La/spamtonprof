<?php
/**
 * 
 *  ppour generer un compte lbc avant publication d'annonces par zenno ( en prod )
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

// voir "Specification hook - creation compte lbc depuis zenno" dans evernote - en prod - date creation : 08/10/2018

// recuperation des entrees
$refClient = $_POST["ref_client"];
$numTel = $_POST["num_tel"];

// etape 1 : on recupere le client pour avoir le nom de domaine
$clientMg = new \spamtonprof\stp_api\LbcClientManager();
$client = $clientMg->get(array(
    "ref_client" => $refClient
));

// etape 2 : recuperer un compte e cloner
$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

$lbcAccount = $lbcAccountMg->get(array(
    "query" => "shortestEmail"
));

// etape 3 : generer un nouvelle email qui n'existe pas a partir de l'email du compte recupere en 1
$mail = trim($lbcAccount->getMail());
$radical = explode("@", $mail)[0];
$domain = explode("@", $mail)[1];

$matches = null;
$pattern = '/\d+$/';
preg_match($pattern, $radical, $matches);

$number = - 1;

if ($matches) {
    $radical = preg_replace($pattern, '', $radical);
    $number = $matches[0];
}

$i = $number + 1;
$exist = true;
while ($exist) {
    $newEmail = $radical . $i . "@" . $client->getDomain();
    $exist = $lbcAccountMg->get(array(
        "mail" => $newEmail
    ));
    $i ++;
}

// etape 4 : faire un clone du compte recupere en 1 et le mettre a jour
$newAccount = new \spamtonprof\stp_api\LbcAccount(json_decode(json_encode($lbcAccount), true));

$newAccount->setRef_compte(null);
$newAccount->setMail($newEmail);
$newAccount->setCode_promo(null);
$newAccount->setControle_date(null);
$newAccount->setTelephone($numTel);
$newAccount->setPassword(wp_generate_password() . rand(12, 100));
$newAccount = $lbcAccountMg->add($newAccount);

$prenom = $client->getPrenom_client();

if ($client->getRef_cat_prenom()) {
    $prenomLbcMg = new \spamtonprof\stp_api\PrenomLbcManager();
    $prenom = $prenomLbcMg->get(array(
        'moins_utilise' => 'moins_utilise',
        "ref_cat_prenom" => $client->getRef_cat_prenom()
    ));

    $prenom->inc_nb_use();

    $prenomLbcMg->updateNbUse($prenom);
    $prenom = $prenom->getPrenom();
}

$newAccount->setPrenom($prenom);
$lbcAccountMg->updatePrenom($newAccount);

// etape 4 : generation du compte promo
// $hashids = new \Hashids\Hashids("stpsalt", 5); // generation du code promo
// $code_promo = $hashids->encode($newAccount->getRef_compte());
// $newAccount->setCode_promo($code_promo);
// $lbcAccountMg->updateCodePromo($newAccount);

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log-lbc', array(
    "Ajout à la bdd du compte leboncoin " . $newAccount->getMail()
));

prettyPrint($newAccount);