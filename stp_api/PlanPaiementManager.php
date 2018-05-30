<?php
namespace spamtonprof\stp_api;

use PDO;

class PlanPaiementManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->prepare('SELECT ref_plan_paiement, ref_plan_stripe, ref_plan_stripe_test,  tarif, nom_plan, ref_formule, ref_paypal_test, ref_paypal_prod FROM plan_paiement WHERE ref_plan_paiement = :ref_plan_paiement');
            $q->execute([
                ':ref_plan_paiement' => $info
            ]);
            
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $data = $q->fetch(PDO::FETCH_ASSOC);
                $planPaiement = $this->get($data);
                return ($planPaiement);
            }
        } else if (is_array($info) and in_array("query_param", $info)) {
            $q = $this->_db->prepare('SELECT ref_plan_paiement, ref_plan_stripe, ref_plan_stripe_test, tarif, nom_plan, ref_formule, ref_paypal_test, ref_paypal_prod FROM plan_paiement WHERE ref_formule = :ref_formule and nom_plan = :nom_plan ');
            $q->execute([
                ':ref_formule' => $info["ref_formule"],
                ':nom_plan' => $info["nom_plan"]
            ]);
            
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $data = $q->fetch(PDO::FETCH_ASSOC);
                $planPaiement = $this->get($data);
                return ($planPaiement);
            }
        } else if (is_array($info)) {
            $planPaiement = new PlanPaiement($info);
            if (array_key_exists('ref_formule', $info)) {
                $formuleManager = new FormuleManager($this->_db);
                $formule = $formuleManager->get($info["ref_formule"]);
                $planPaiement->setFormule($formule);
                return ($planPaiement);
            }
        }
    }
    
    /**
     * sert à récupérer un plan de paiement à partir d'une ref paypal
     * 
     * @param array $info avec comme clé ref_paypal_test ou ref_paypal prod
     * @return boolean|boolean|\spamtonprof\stp_api\PlanPaiement
     */

    public function getWithRefPaypal(array $info)
    {
        $q;
        if (array_key_exists("ref_paypal_test", $info)) {
            $q = $this->_db->prepare('SELECT ref_plan_paiement, ref_plan_stripe, ref_plan_stripe_test,  tarif, nom_plan, ref_formule, ref_paypal_test, ref_paypal_prod FROM plan_paiement
                                    WHERE ref_paypal_test = :ref_paypal_test');
            $q->execute([
                ':ref_paypal_test' => $info["ref_paypal_test"]
            ]);
        }else if(array_key_exists("ref_paypal_prod", $info)){
            $q = $this->_db->prepare('SELECT ref_plan_paiement, ref_plan_stripe, ref_plan_stripe_test,  tarif, nom_plan, ref_formule, ref_paypal_test, ref_paypal_prod FROM plan_paiement
                                    WHERE ref_paypal_prod = :ref_paypal_prod');
            $q->execute([
                ':ref_paypal_prod' => $info["ref_paypal_prod"]
            ]);
        }
        
        if ($q->rowCount() <= 0) {
            return (false);
        } else {
            $data = $q->fetch(PDO::FETCH_ASSOC);
            $planPaiement = $this->get($data);
            return ($planPaiement);
        }
    }

    public function getAll($ref_formule = 0)
    {
        $selector = "";
        if ($ref_formule > 0) {
            $selector = "where ref_formule = :ref_formule";
        }
        $planPaiements = [];
        $q = $this->_db->prepare('SELECT ref_plan_paiement, ref_plan_stripe, ref_plan_stripe_test, tarif, nom_plan, ref_formule, ref_paypal_test, ref_paypal_prod FROM plan_paiement ' . $selector);
        if ($ref_formule > 0) {
            $q->execute(array(
                ':ref_formule' => $ref_formule
            ));
        } else {
            $q->execute();
        }
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $planPaiement = $this->get($donnees);
            $planPaiements[] = $planPaiement;
        }
        
        return $planPaiements;
    }

    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }

    public function update(PlanPaiement $planPaiement)
    
    {
        $q = $this->_db->prepare('
      UPDATE plan_paiement 
        SET ref_plan_stripe = :ref_plan_stripe, 
            ref_plan_stripe_test = :ref_plan_stripe_test, 
            tarif = :tarif, 
            nom_plan = :nom_plan,
            ref_formule = :ref_formule,
            ref_plan_paiement = :ref_plan_paiement,
            ref_paypal_test = :ref_paypal_test, 
            ref_paypal_prod = :ref_paypal_prod 
       WHERE ref_plan_paiement = :ref_plan_paiement');
        
        $q->bindValue(':ref_paypal_test', $planPaiement->ref_paypal_test());
        
        $q->bindValue(':ref_plan_stripe_test', $planPaiement->ref_plan_stripe_test());
        
        $q->bindValue(':ref_paypal_prod', $planPaiement->ref_paypal_prod());
        
        $q->bindValue(':ref_plan_stripe', $planPaiement->ref_plan_stripe());
        
        $q->bindValue(':nom_plan', $planPaiement->nom_plan());
        
        $q->bindValue(':tarif', $planPaiement->tarif());
        
        $q->bindValue(':ref_formule', $planPaiement->ref_formule(), PDO::PARAM_INT);
        
        $q->bindValue(':ref_plan_paiement', $planPaiement->ref_plan_paiement(), PDO::PARAM_INT);
        
        $q->execute();
    }
}