<?php
/**
 * 
 *  ppour g�n�rer un compte lbc avant publication d'annonces par zenno ( en prod )
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

// voir "Sp�cification hook - creation compte lbc depuis zenno" dans evernote - en prod - date cr�ation : 08/10/2018

// r�cup�ration des entr�es
$refClient = $_POST["ref_client"];
$numTel = $_POST["num_tel"];

// �tape 1 : on r�cup�re le client pour avoir le nom de domaine
$clientMg = new \spamtonprof\stp_api\LbcClientManager();
$client = $clientMg->get(array(
    "ref_client" => $refClient
));

// �tape 1 : r�cup�rer un compte � cloner
$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

$lbcAccount = $lbcAccountMg->get(array(
    "refClient" => $refClient,
    "query" => "shortestEmail"
));

// �tape 2 : g�n�rer un nouvelle email qui n'existe pas � partir de l'email du compte r�cup�r� en 1
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

// �tape 3 : faire un clone du compte r�cup�r� en 1 et le mettre � jour
$newAccount = new \spamtonprof\stp_api\LbcAccount(json_decode(json_encode($lbcAccount), true));

$newAccount->setRef_compte(null);
$newAccount->setMail($newEmail);
$newAccount->setCode_promo(null);
$newAccount->setControle_date(null);
$newAccount->setTelephone($numTel);
$newAccount->setPassword(wp_generate_password() . rand(12, 100));
$newAccount = $lbcAccountMg->add($newAccount);



$prenomLbcMg = new \spamtonprof\stp_api\PrenomLbcManager();
$prenom = $prenomLbcMg->get(array(
    'moins_utilise' => 'moins_utilise',
    "ref_cat_prenom" => $client->getRef_cat_prenom()
));


$prenom->inc_nb_use();
$prenomLbcMg->updateNbUse($prenom);

$newAccount->setPrenom($prenom->getPrenom());
$lbcAccountMg->updatePrenom($newAccount);

// �tape 4 : g�n�ration du compte promo
// $hashids = new \Hashids\Hashids("stpsalt", 5); // g�n�ration du code promo
// $code_promo = $hashids->encode($newAccount->getRef_compte());
// $newAccount->setCode_promo($code_promo);
// $lbcAccountMg->updateCodePromo($newAccount);


prettyPrint($newAccount);