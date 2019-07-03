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

    public function add(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare("insert into stp_plan_paiement( nom, tarif, ref_formule, defaut) 
                        values( :nom, :tarif, :ref_formule, false);");

        $q->bindValue(":nom", $plan->getNom());
        $q->bindValue(":tarif", $plan->getTarif());
        $q->bindValue(":ref_formule", $plan->getRef_formule());

        $q->execute();

        $plan->setRef_plan($this->_db->lastInsertId());

        return ($plan);
    }

    public function getDefault($info)
    {
        if (is_array($info)) {

            if (array_key_exists("ref_formule", $info)) {
                $refFormule = $info["ref_formule"];
                $q = $this->_db->prepare("select * from stp_plan_paiement where ref_formule = :ref_formule and defaut is true");
                $q->bindValue(":ref_formule", $refFormule);
                $q->execute();
            }

            $data = $q->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $plan = new \spamtonprof\stp_api\StpPlan($data);
                return ($plan);
            }
            return (false);
        }
    }

    public function update_defaut_plan($ref_formule, $nom_plan)
    {
        $plans = $this->getAll(array(
            'ref_formule' => $ref_formule
        ));

        foreach ($plans as $plan) {

            if ($plan->getNom() == $nom_plan) {
                $plan->setDefaut(true);
            } else {
                $plan->setDefaut(false);
            }
            $this->update_defaut($plan);
        }
    }

    public function getAll($info, $constructor = false)
    {
        $plans = [];
        $q = null;

        if (is_array($info)) {

            if (array_key_exists("ref_formule", $info)) {
                $refFormule = $info["ref_formule"];
                $q = $this->_db->prepare("select * from stp_plan_paiement where ref_formule = :ref_formule");
                $q->bindValue(":ref_formule", $refFormule);
            }
        }

        if ($info == "all") {

            $q = $this->_db->prepare("select * from stp_plan_paiement");
        }

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $plan = new \spamtonprof\stp_api\StpPlan($data);

            if ($constructor) {
                $constructor["objet"] = $plan;
                $this->construct($constructor);
            }

            $plans[] = $plan;
        }
        return ($plans);
    }

    public function update_defaut(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set defaut = :defaut where ref_plan = :ref_plan');
        $q->bindValue(':defaut', $plan->getDefaut(), PDO::PARAM_BOOL);
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function update_label_installment(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set label_installment = :label_installment where ref_plan = :ref_plan');
        $q->bindValue(':label_installment', $plan->getLabel_installment());
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function update_nb_days(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set nb_days = :nb_days where ref_plan = :ref_plan');
        $q->bindValue(':nb_days', $plan->getNb_days());
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function update_installments(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set installments = :installments where ref_plan = :ref_plan');
        $q->bindValue(':installments', $plan->getInstallments());
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function updateRefPlanOld(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set ref_plan_old = :ref_plan_old where ref_plan = :ref_plan');
        $q->bindValue(':ref_plan_old', $plan->getRef_plan_old());
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function updateRefPlanStripe(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set ref_plan_stripe = :ref_plan_stripe where ref_plan = :ref_plan');
        $q->bindValue(':ref_plan_stripe', $plan->getRef_plan_stripe());
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function updateRefPlanStripeTest(\spamtonprof\stp_api\StpPlan $plan)
    {
        $q = $this->_db->prepare('update stp_plan_paiement set ref_plan_stripe_test = :ref_plan_stripe_test where ref_plan = :ref_plan');
        $q->bindValue(':ref_plan_stripe_test', $plan->getRef_plan_stripe_test());
        $q->bindValue(':ref_plan', $plan->getRef_plan());
        $q->execute();

        return ($plan);
    }

    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->prepare('SELECT * FROM stp_plan_paiement WHERE ref_plan = :ref_plan');
            $q->execute([
                ':ref_plan' => $info
            ]);

            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $data = $q->fetch(PDO::FETCH_ASSOC);
                return new \spamtonprof\stp_api\StpFormule($data);
            }
        } else if (is_array($info)) {
            $data = false;
            if (array_key_exists('ref_formule', $info) && array_key_exists('nom', $info)) {
                $refFormule = $info['ref_formule'];
                $nom = $info['nom'];
                $q = $this->_db->prepare('SELECT * FROM stp_plan_paiement WHERE ref_formule =:ref_formule and nom = :nom');
                $q->bindValue(':ref_formule', $refFormule);
                $q->bindValue(':nom', $nom);
                $q->execute();
            }
            if (array_key_exists('ref_plan', $info)) {
                $refPlan = $info['ref_plan'];

                $q = $this->_db->prepare('SELECT * FROM stp_plan_paiement WHERE ref_plan =:ref_plan');
                $q->bindValue(':ref_plan', $refPlan);
                $q->execute();
            }

            $data = $q->fetch(PDO::FETCH_ASSOC);

            if (! $data) {
                return (false);
            } else {
                return new \spamtonprof\stp_api\StpPlan($data);
            }
        }
    }

    public function cast(\spamtonprof\stp_api\StpPlan $plan)
    {
        return ($plan);
    }

    public function construct($constructor)
    {
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

        $plan = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {
                case "ref_formule":
                    $formule = $formuleMg->get(array(
                        'ref_formule' => $plan->getRef_formule()
                    ));

                    $plan->setFormule($formule);
                    break;
            }
        }
    }
}
    
