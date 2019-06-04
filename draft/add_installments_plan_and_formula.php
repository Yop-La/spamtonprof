<?php

/*
 *
 * pour ajouter des formules, des plans, des dates de plans dans le cadre d'un stage sur spamtonprof et stripe
 * 
 * ici un plan désigne une durée ( nb de jours du stage ) et un plan de paiement ( facilité de paiement, prix ) 
 *
 */
require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(- 1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// on ajoute d'abord les formules et leurs plans ( etape 1 ) - puis etape 2 - puis etape 3


$formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
$nom_formule = 'Stage maths-physique pour le bac';
$formule = $formuleMg->get(array(
    'formule' => $nom_formule,
    array(
        "construct" => array(
            'plans'
        )
    )
));
if (! $formule) {
    $formule = $formuleMg->add(new \spamtonprof\stp_api\StpFormule(array(
        'formule' => $nom_formule,
        'from_tool' => false
    )));
}

$nom_plans = [
    'Stage bac maths-physique à la journée',
    'Stage bac maths-physique à la semaine'
];
$tarifs = [
    60,
    250
];
$defauts = [
    false,
    true
];
$installments = [
    1,
    1
];
$label_installments = [
    'Paiement en une fois de 60 €',
    'Paiement en une fois de 250 €'
];
$nb_days = [
    1,
    7
];

$planMg = new \spamtonprof\stp_api\StpPlanManager();
for ($i = 0; $i < count($nom_plans); $i ++) {
    
    $plan = $planMg->add(new \spamtonprof\stp_api\StpPlan(array(
        'nom' => $nom_plans[$i],
        'tarif' => $tarifs[$i],
        'ref_formule' => $formule->getRef_formule()
    )));
    
    $plan->setDefaut($defauts[$i]);
    $plan->setInstallments($installments[$i]);
    $plan->setLabel_installment($label_installments[$i]);
    $plan->setNb_days($nb_days[$i]);
    
    $planMg->update_defaut($plan);
    $planMg->update_installments($plan);
    $planMg->update_label_installment($plan);
    $planMg->update_nb_days($plan);
}

exit();

// on ajoute les dates de départ des différents plans ensuite - etape 2


$formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
$nom_formule = 'Stage de maths pour préparer la rentrée';
$formule = $formuleMg->get(array(
    'formule' => $nom_formule
), array(
    "construct" => array(
        'plans'
    )
));

$plans = $formule->getPlans();

$dateFormuleMg = new \spamtonprof\stp_api\StpDateFormuleManager();

foreach ($plans as $plan) {
    $plan = \spamtonprof\stp_api\StpPlan::cast($plan);
    $nb_days = $plan->getNb_days();
    $dates = [
        '01-07-2019',
        '08-07-2019',
        '15-07-2019',
        '05-08-2019',
        '12-08-2019',
        '16-08-2019'
    ];
    
    foreach ($dates as $date) {
        $date_deb = DateTime::createFromFormat('d-m-Y', $date);
        
        $day_date_deb = IntlDateFormatter::formatObject($date_deb, // a DateTime object
            "eeee dd MMMM", // UCI standard formatted string
            'fr_FR' // the locale
            );
        
        
        
        $date_fin = $date_deb->add(new DateInterval('P' . ($nb_days -1). 'D'));
        
        if($date_fin->format('N') == 7){
            $date_fin = $date_fin->add(new DateInterval('P1D'));
        }
        
        $day_date_fin = IntlDateFormatter::formatObject($date_fin, // a DateTime object
            "eeee dd MMMM", // UCI standard formatted string
            'fr_FR' // the locale
            );
        
        
        $libelle = "Du " . $day_date_deb . " matin au $day_date_fin en soirée. ";
        
        
        $dateFormuleMg->add(new \spamtonprof\stp_api\StpDateFormule(array(
            'ref_plan' => $plan->getRef_plan(),
            'ref_formule' => $formule->getRef_formule(),
            'libelle' => $libelle,
            'date_debut' => $date_deb->format(PG_DATE_FORMAT)
        )));
    }
}
