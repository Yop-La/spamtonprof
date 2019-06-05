<?php
use spamtonprof\stp_api;

use spamtonprof\stp_api\CampaignManager;
use spamtonprof\stp_api\Abonnement;
use spamtonprof\stp_api\AbonnementManager;
use spamtonprof\slack\Slack;

// toutes ces fonction seront �x�cut�s par un appel ajax r�alis� dans adds-back-office.js sur la page dont le slug est gestion-formule
add_action('wp_ajax_ajaxAddFormula', 'ajaxAddFormula');

add_action('wp_ajax_nopriv_ajaxAddFormula', 'ajaxAddFormula');

add_action('wp_ajax_ajaxGetFormula', 'ajaxGetFormula');

add_action('wp_ajax_nopriv_ajaxGetFormula', 'ajaxGetFormula');

add_action('wp_ajax_ajaxGetBusyLevels', 'ajaxGetBusyLevels');

add_action('wp_ajax_nopriv_ajaxGetBusyLevels', 'ajaxGetBusyLevels');

add_action('wp_ajax_ajaxEditFormula', 'ajaxEditFormula');

add_action('wp_ajax_nopriv_ajaxEditFormula', 'ajaxEditFormula');

function ajaxAddFormula()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    $nomFormule = $_POST['nomFormule'];
    $tarif = $_POST['tarif'];

    // on ajoute la formule
    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
    $formule = new \spamtonprof\stp_api\StpFormule(array(
        "formule" => $nomFormule,
        'from_tool' => true
    ));
    $formuleMg->add($formule);

    // on ajoute le plan par défaut
    $planMg = new \spamtonprof\stp_api\StpPlanManager();
    $plan = new \spamtonprof\stp_api\StpPlan(array(
        'nom' => 'defaut',
        'tarif' => $tarif,
        'ref_formule' => $formule->getRef_formule()
    ));
    $plan = $planMg->add($plan);

    $plan->setDefaut(true);
    $planMg->update_defaut($plan);

    $retour->formule = $formule;

    echo (json_encode($retour));

    die();
}

function ajaxEditFormula()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    $refFormule = $_POST['refFormule'];
    $matieres = $_POST['matieres'];
    $niveaux = $_POST['niveaux'];
    $nomFormule = $_POST['nomFormule'];

    // on ajoute la formule
    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
    $formule = $formuleMg->get(array(
        'ref_formule' => $refFormule
    ));

    sort($matieres);
    sort($formule);

    $formule->setFormule($nomFormule);
    $formuleMg->updateFormule($formule);

    $formule->setMatieres($matieres);
    $formuleMg->updateMatieres($formule);

    $formule->setClasses($niveaux);
    $formuleMg->updateClasses($formule);

    $retour->formule = $formule;

    echo (json_encode($retour));

    die();
}

function ajaxGetFormula()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    $refFormule = $_POST['refFormule'];

    // on ajoute la formule
    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

    $formule = $formuleMg->get(array(
        'ref_formule' => $refFormule
    ));

    $formule->setClasses($formule->getClasses());
    $formule->setMatieres($formule->getMatieres());

    $retour->formule = $formule;

    echo (json_encode($retour));

    die();
}

function ajaxGetBusyLevels()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $niveaux = [];

    $slack = new \spamtonprof\slack\Slack();

    $matieres = $_POST['matieres'];
    $refFormule = $_POST['refFormule'];

    if (empty($matieres)) {

        $retour->niveaux = $niveaux;

        echo (json_encode($retour));

        die();
    }

    // on ajoute la formule
    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

    sort($matieres);

    $formules = $formuleMg->getAll(array(
        'matieres' => $matieres,
        'from_tool' => true,
        'ref_formule' => $refFormule
    ));

    $retour->formules = $formules;

    foreach ($formules as $formule) {

        $classes = $formule->getClasses();
        if (count($classes) != 0) {
            $niveaux = array_merge($niveaux, $classes);
        }
    }
    $niveaux = array_unique($niveaux);

    $retour->niveaux = $niveaux;
    $retour->matieres = $matieres;

    echo (json_encode($retour));

    die();
}



