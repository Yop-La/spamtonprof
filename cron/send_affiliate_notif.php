<?php
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

// en prod. Toutes les heures
/* cron d'envoi des emails de relance */

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$slack = new \spamtonprof\slack\Slack();

$constructor = array(
    "construct" => array(
        'ref_eleve',
        'ref_parent'
    )
);

$abos = $aboMg->getAll(array(
    "key" => "notify_to_affiliate"
), $constructor);

foreach ($abos as $abo) {

    $eleve = $abo->getEleve();
    $parent = $abo->getProche();

    $name_parent = 'pas de parent :/';

    if ($parent) {
        $parent = \spamtonprof\stp_api\StpProche::cast($parent);
        $name_parent = ucfirst($parent->getPrenom()) . ' ' . ucfirst($parent->getNom());
    }
    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

    /*
     *
     * envoi du mail
     *
     */

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

    $affiliate_email = false;

    $utm_source = $abo->getUtm_source_stp();

    if ($utm_source == 'bhm') {

        $affiliate_email = 'gaucher_martin@yahoo.fr';
        $affiliate_name = 'Martin';
    }

    if ($utm_source == 'starenmaths') {
        $affiliate_email = 'star.en.maths@gmail.com';
        $affiliate_name = 'Romain';
    }

    // $affiliate_email = 'alexandre@spamtonprof.com';

    if ($affiliate_email) {

        $to = $affiliate_email;
        $to_name = $affiliate_name;

        $email->addTo($to, $to_name, [

            "name" => $to_name,
            "eleve_name" => ucfirst($eleve->getPrenom()) . ' ' . ucfirst($eleve->getNom()),
            "prenom_parent" => $name_parent,
            "utm_source" => $utm_source,
            "utm_campaign" => $abo->getUtm_campaign_stp(),
            "utm_medium" => $abo->getUtm_medium_stp()
        ], 0);

        try {

            $email->addCc('alexandre@spamtonprof.com');

            $email->setTemplateId("d-c7341219f23048b2af3b956f87087f0a");
            $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

            $response = $sendgrid->send($email);

            echo ($response->body());
        } catch (\Exception $e) {

            echo ($e->getMessage());

            $slack->sendMessages('relance', array(
                'Erreur d\envoi du mail de relance',
                'Caught exception: ' . $e->getMessage()
            ));
        }
    }

    /*
     *
     * mise à jour en base
     *
     */

    $abo->setSent_to_affiliate(true);
    $aboMg->updateSentToAffiliate($abo);
}