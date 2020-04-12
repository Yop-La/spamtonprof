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

// en prod. Toutes les heures à 42
/*
 * pour envoyer une notification au prof en cas d'impayés
 */

/*
 * todo:
 * - inserer invoice id dans la table et mettre une unique constraint pour éviter les doublons
 * - lister dans le mail le nombre de facture impayés avec une liste si possible ?
 * - voir pourquoi ref abo pas inséré dans certains cas
 *
 *
 */

$stripeChargeFailedMg = new \spamtonprof\stp_api\StripeChargeFailedManager();
$stripeMg = new \spamtonprof\stp_api\StripeManager(false);

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$slack = new \spamtonprof\slack\Slack();

$constructor = array(
    "construct" => array(
        'ref_prof',
        'ref_eleve',
        'ref_parent'
    )
);

$charges = $stripeChargeFailedMg->getAll(array(
    'key' => 'to_send'
));

// $charges = [];
// $charges[] = $stripeChargeFailedMg->get(237);

foreach ($charges as $charge) {

    $email_client = $charge->getCus_email();
    $montant_facture = false;
    $date_begin = false;
    $date_end = false;
    $description = false;
    $email_eleve = false;
    $eleve_name = false;
    $email_parent = false;
    $parent_name = false;
    $dernier_contact = false;
    $email_prof = $charge->getEmail_prof();
    $invoice_id = false;

    $evt = $stripeMg->retrieve_event($charge->getEvt_id());

    $invoice_id = $evt->data->object->id;
    $hosted_invoice_url = $evt->data->object->hosted_invoice_url;

    $invoice = $evt->data->object->lines->data[0];

    $description = $invoice->description;
    $period = $invoice->period;

    $montant_facture = $invoice->amount / 100;

    $end_ts = $period->end;
    $start_ts = $period->start;

    $start = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
    $start->setTimestamp($start_ts);

    $end = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
    $end->setTimestamp($end_ts);

    $date_begin = $start->format(PG_DATETIME_FORMAT);
    $date_end = $end->format(PG_DATETIME_FORMAT);

    $abo = false;
    if ($charge->getRef_abo()) {

        $abo = $aboMg->get(array(
            "ref_abonnement" => $charge->getRef_abo()
        ), $constructor);

        $eleve = $abo->getEleve();
        $parent = $abo->getProche();
        $prof = $abo->getProf();

        $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));

        $dernier_contact = $abo->getDernier_contact();
        if (! is_null($dernier_contact)) {
            $dernier_contact = date_create_from_format(PG_DATETIME_FORMAT, $dernier_contact, new \DateTimeZone('Europe/Paris'));
            $dernier_contact = $dernier_contact->format(FR_DATETIME_FORMAT);
        } else {
            $dernier_contact = "Aucun message reçu pour le moment :/";
        }

        $name_parent = 'pas de parent :/';
        $email_parent = 'pas de parent :/';

        if ($parent) {
            $parent = \spamtonprof\stp_api\StpProche::cast($parent);
            $name_parent = ucfirst($parent->getPrenom()) . ' ' . ucfirst($parent->getNom());
            $email_parent = $parent->getEmail();
        }
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
        $prof = \spamtonprof\stp_api\StpProf::cast($prof);

        $email_eleve = $eleve->getEmail();
        $eleve_name = ucfirst($eleve->getPrenom()) . ' ' . ucfirst($eleve->getNom());
        $email_parent = $parent->getEmail();
        $parent_name = $name_parent;

        $email_prof = $prof->getEmail_stp();
    }

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

    $to = $email_prof;

    if (! $to) {
        $to = "alexandre@spamtonprof.com";
    }

    $prof_name = ucfirst(explode('@', $to)[0]);

    $params = [
        'hosted_invoice_url' => $hosted_invoice_url,
        'invoice_id' => $invoice_id,
        "prof_name" => $prof_name,
        "email_client" => $email_client,
        "montant_facture" => "" . $montant_facture,
        "date_begin" => $date_begin,
        "date_end" => $date_end,
        "description" => $description,
        "invoice_id" => $invoice_id,
        "email_eleve" => $email_eleve,
        "eleve_name" => $eleve_name,
        "email_parent" => $email_parent,
        "parent_name" => $parent_name,
        "dernier_contact" => $dernier_contact
    ];

    try {

        $email->addTo($to, $prof_name, $params, 0);

        $email->addCc('alexandre@spamtonprof.com');

        $email->setTemplateId("d-7a8a3eac55b144b1bc57406acc95d4e6");
        $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

        $response = $sendgrid->send($email);

        $charge->setSent(true);
        $stripeChargeFailedMg->updateSent($charge);

        $slack->sendMessages('log_unpaid_invoice', array(
            '-----------------------',
            json_encode($params)
        ));

        echo ($response->body());
    } catch (\Exception $e) {

        echo ($e->getMessage());

        $slack->sendMessages('log_unpaid_invoice', array(
            'Erreur d\envoi du mail de relance',
            'evt id: ' . $charge->getEvt_id(),
            'Caught exception: ' . $e->getMessage()
        ));
    }
}
