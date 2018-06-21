<?php
namespace spamtonprof\stp_api;

use PDO;

class StpPlanManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    public function add(\spamtonprof\stp_api\StpPlan $plan){
        
        
        $q = $this->_db->prepare("insert into stp_plan_paiement( nom, tarif, ref_formule, ref_plan_stripe, ref_plan_paypal, ref_plan_stripe_test, ref_plan_paypal_test, ref_plan_old) 
                        values( :nom, :tarif, :ref_formule, :ref_plan_stripe, :ref_plan_paypal, :ref_plan_stripe_test, :ref_plan_paypal_test, :ref_plan_old);");
        
        $q -> bindValue(":nom", $plan->getNom());
        $q -> bindValue(":tarif", $plan->getTarif());
        $q -> bindValue(":ref_formule", $plan->getRef_formule());
        $q -> bindValue(":ref_plan_stripe", $plan->getRef_plan_stripe());
        $q -> bindValue(":ref_plan_paypal", $plan->getRef_plan_paypal());
        $q -> bindValue(":ref_plan_stripe_test", $plan->getRef_plan_stripe_test());
        $q -> bindValue(":ref_plan_paypal_test", $plan->getRef_plan_paypal_test());
        $q -> bindValue(":ref_plan_old", $plan->getRef_plan_old());
        
        $q->execute();
        
        $plan->setRef_plan($this->_db->lastInsertId());
        
        return($plan);
        
    }

}