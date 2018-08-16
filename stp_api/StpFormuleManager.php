<?php
namespace spamtonprof\stp_api;

use PDO;

class StpFormuleManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function add(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare("insert into stp_formule(formule) values(:formule);");
        
        $q->bindValue(":formule", $formule->getFormule());
        
        $q->execute();
        
        $formule->setRef_formule($this->_db->lastInsertId());
        
        return ($formule);
    }

    public function getAll($constructor = false)
    {
        $formules = [];
        
        $q = $this->_db->prepare("select * from stp_formule");
        
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $formule = new \spamtonprof\stp_api\StpFormule($data);
            
            if ($constructor) {
                $constructor["objet"] = $formule;
                $this->construct($constructor);
            }
            
            $formules[] = $formule;
        }
        return ($formules);
    }
    
    public function cast(\spamtonprof\stp_api\StpFormule $formule){
        
        return($formule);
    }
    
    public function updateRefProductStripe(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set ref_product_stripe = :ref_product_stripe where ref_formule = :ref_formule');
        $q->bindValue(':ref_product_stripe', $formule->getRef_product_stripe());
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();
        
        return ($formule);
    }
    
    public function updateRefProductStripeTest(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set ref_product_stripe_test = :ref_product_stripe_test where ref_formule = :ref_formule');
        $q->bindValue(':ref_product_stripe_test', $formule->getRef_product_stripe_test());
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();
        
        return ($formule);
    }
    
    
    public function construct($constructor)
    {
        
        $planMg = new \spamtonprof\stp_api\StpPlanManager();
        
        $formule = $this->cast($constructor["objet"]);
        
        $constructOrders = $constructor["construct"];
        
        foreach ($constructOrders as $constructOrder) {
            
            switch ($constructOrder) {
                case "plans":
                    $plans = $planMg->getAll(array(
                    'ref_formule' => $formule->getRef_formule()
                    ));
                    
                    $formule->setPlans($plans);
                    break;
            }
        }
    }

    public function get($info)
    {
        if (is_array($info)) {
            
            if (array_key_exists("formule", $info)) {
                
                $nomFormule = $info["formule"];
                
                $q = $this->_db->prepare("select * from stp_formule where formule = :formule");
                
                $q->bindValue(":formule", $nomFormule);
                
                $q->execute();
                
                $data = $q->fetch(PDO::FETCH_ASSOC);
                
                if ($data) {
                    return (new \spamtonprof\stp_api\StpFormule($data));
                } else {
                    return (false);
                }
            } else if (array_key_exists('classe', $info) && array_key_exists('matieres', $info)) {
                
                $classe = $info['classe'];
                $matieres = $info['matieres'];
                
                $matieresParam = "'{";
                $nbMatieres = count($matieres);
                for ($i = 0; $i < $nbMatieres; $i ++) {
                    
                    $matiere = $matieres[$i];
                    
                    $matieresParam = $matieresParam . $matiere->getMatiere();
                    if ($i != $nbMatieres - 1) {
                        $matieresParam = $matieresParam . ',';
                    }
                }
                $matieresParam = $matieresParam . "}'";
                
                $q = $this->_db->prepare('SELECT * FROM stp_formule where :classe like ANY (classes) and matieres = ' . $matieresParam);
                $q->bindValue(':classe', $classe->getClasse());
                
                $q->execute();
                
                $data = $q->fetch();
                
                if ($data) {
                    return (new \spamtonprof\stp_api\StpFormule($data));
                } else {
                    return ($data);
                }
            } else if (array_key_exists('ref_formule', $info)) {
                
                $refFormule = $info['ref_formule'];
                
                $q = $this->_db->prepare('SELECT * FROM stp_formule where ref_formule = :ref_formule');
                $q->bindValue(':ref_formule', $refFormule);
                
                $q->execute();
                
                $data = $q->fetch();
                
                if ($data) {
                    return (new \spamtonprof\stp_api\StpFormule($data));
                } else {
                    return ($data);
                }
            }
        }
    }
}