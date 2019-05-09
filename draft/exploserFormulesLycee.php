<?php
bugbugbug
require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set("allow_url_fopen", 1);
ini_set("allow_url_include", 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 *  pour exploser une formule lycée en terminale, première et seconde
 * 
 */

$planMg = new \spamtonprof\stp_api\StpPlanManager();

$formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

$ref_formule_in = 79;

$formule = $formuleMg->get(array(
    'ref_formule' => $ref_formule_in
));

$formule_name = $formule->getFormule();
$classes = $formule->getClasses();

$classes_t = [];
$classes_p = [];
$classes_s = [];

foreach ($classes as $classe) {
    if (substr($classe, 0, 1) == 't') {
        $classes_t[] = $classe;
    } else if (substr($classe, 0, 1) == 'p') {
        $classes_p[] = $classe;
    } else {
        $classes_s[] = $classe;
    }
}

$formule_t = clone $formule;
$formule_p = clone $formule;
$formule_s = clone $formule;

$formule_p->setClasses($classes_p);
$formule_t->setClasses($classes_t);
$formule_s->setClasses($classes_s);

$formule_s->setFormule(str_replace("filière", "Seconde", $formule_name));
$formule_p->setFormule(str_replace("filière", "Première", $formule_name));
$formule_t->setFormule(str_replace("filière", "Terminale", $formule_name));

$formules = array(
    $formule_t,
    $formule_p,
    $formule_s
);

foreach ($formules as $formule_to_add) {
    
    if (count($formule_to_add->getClasses()) != 0) {
        
        $formule_to_add = $formuleMg->add($formule_to_add);
        
        $formuleMg->updateClasses($formule_to_add);
        $formuleMg->updateMatieres($formule_to_add);
        $formuleMg->updateRefProductStripe($formule_to_add);
        $formuleMg->updateRefProductStripeTest($formule_to_add);
        $formuleMg->update_from_tool($formule_to_add);
        $formuleMg->update_ref_prof($formule_to_add);
    }
}

$formule->setFrom_tool(false);
$formuleMg->update_from_tool($formule);

$plan = $planMg->get(array(
    'ref_formule' => $formule->getRef_formule(),
    'nom' => 'defaut'
));

$plan_s = clone $plan;
$plan_p = clone $plan;
$plan_t = clone $plan;

$plan_s->setRef_formule($formule_s->getRef_formule());
$plan_p->setRef_formule($formule_p->getRef_formule());
$plan_t->setRef_formule($formule_t->getRef_formule());

$plans = array(
    $plan_s,
    $plan_p,
    $plan_t
);

foreach ($plans as $plan) {
    
    if ($plan->getRef_formule() != $ref_formule_in) {
        
        $planMg->add($plan);
        $planMg->updateRefPlanStripe($plan);
        $planMg->updateRefPlanStripeTest($plan);
    }
}

prettyPrint(array(
    $formule_t,
    $formule_p,
    $formule_s
));

die();
// pour pondre un article sur wordpress automatiquement

$start = DateTime::createFromFormat('Y-m-d', '2018-01-01', new \DateTimeZone("Europe/Paris"));
$end = new DateTime(null, new \DateTimeZone("Europe/Paris"));

$publication_date = randomDateInRange($start, $end);

$postarr = array(
    'post_status' => 'draft', // publish
    'post_date' => $publication_date->format('Y-m-d H:i:s'),
    'post_date_gmt' => $publication_date->format('Y-m-d H:i:s'),
    'post_content' => 'test',
    'post_title' => 'test'
);

wp_insert_post($postarr);