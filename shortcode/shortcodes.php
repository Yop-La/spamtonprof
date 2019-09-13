<?php
use spamtonprof\stp_api;

use spamtonprof\stp_api\CampaignManager;
use spamtonprof\stp_api\Abonnement;
use spamtonprof\stp_api\AbonnementManager;
use spamtonprof\slack\Slack;

function email_prof_get()
{
    if (array_key_exists('ref_abo', $_GET)) {

        $abo_mg = new \spamtonprof\stp_api\StpAbonnementManager();

        $ref_abo = $_GET['ref_abo'];

        $constructor = array(
            "construct" => array(
                'ref_prof',
                'ref_eleve',
                'ref_parent',
                'ref_formule',
                'ref_plan',
                'ref_compte'
            )
        );

        $abonnement = $abo_mg->get(array(
            "ref_abonnement" => $ref_abo
        ), $constructor);

        $prof = $abonnement->getProf();

        $profMg = new \spamtonprof\stp_api\StpProfManager();

        $prof = $profMg->cast($prof);

        return ($prof->getEmail_stp());
    } else {

        return ("Erreur pas paramètre ref_abonnement passé à l'url");
    }
}

add_shortcode('email_prof_get', 'email_prof_get');