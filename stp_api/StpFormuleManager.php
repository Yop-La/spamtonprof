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
        $q = $this->_db->prepare("insert into stp_formule(formule, from_tool) values(:formule, :from_tool);");

        $q->bindValue(":formule", $formule->getFormule());
        $q->bindValue(":from_tool", $formule->getFrom_tool(), PDO::PARAM_BOOL);
        $q->execute();

        $formule->setRef_formule($this->_db->lastInsertId());

        return ($formule);
    }

    public function getAll($info = null, $constructor = false)
    {
        $formules = [];
        $q = null;

        if (is_array($info)) {
            if (array_key_exists('from_tool', $info) && ! array_key_exists('matieres', $info) && ! array_key_exists('ref_formule', $info)) {
                $fromTool = $info['from_tool'];
                $q = $this->_db->prepare("select * from stp_formule where from_tool = :from_tool order by ref_formule");
                $q->bindValue(':from_tool', $fromTool, PDO::PARAM_BOOL);
            } else if (array_key_exists('getFormuleNotInStripe', $info)) {
                $testMode = $info['getFormuleNotInStripe'];

                if (! $testMode) {
                    $q = $this->_db->prepare("select * from stp_formule where from_tool is true and ref_product_stripe is null");
                } else {
                    $q = $this->_db->prepare("select * from stp_formule where from_tool is true and ref_product_stripe_test is null");
                }
            } else if (array_key_exists('matieres', $info) && array_key_exists('from_tool', $info) && array_key_exists('ref_formule', $info)) {

                $matieres = $info['matieres'];
                $fromTool = $info['from_tool'];
                $refFormule = $info['ref_formule'];

                $matieresParam = "'{";
                $nbMatieres = count($matieres);
                for ($i = 0; $i < $nbMatieres; $i ++) {

                    $matiere = $matieres[$i];

                    $matieresParam = $matieresParam . $matiere;
                    if ($i != $nbMatieres - 1) {
                        $matieresParam = $matieresParam . ',';
                    }
                }
                $matieresParam = $matieresParam . "}'";
                $q = $this->_db->prepare('SELECT * FROM stp_formule where matieres = ' . $matieresParam . ' and from_tool = :from_tool and ref_formule != :ref_formule order by ref_formule');
                $q->bindValue('from_tool', $fromTool, PDO::PARAM_BOOL);
                $q->bindValue('ref_formule', $refFormule);
            } else if (array_key_exists('classe', $info) && array_key_exists('matiere', $info)) {

                $matiere = $info['matiere'];
                $classe = $info['classe'];

                $q = $this->_db->prepare('SELECT * FROM stp_formule where :matiere like any (matieres) and :classe like any (classes) and from_tool is true order by array_upper(matieres,1)');
                $q->bindValue(':matiere', $matiere);
                $q->bindValue(':classe', $classe);
            } else if (array_key_exists('ref_eleve', $info)) {

                $refEleve = $info['ref_eleve'];

                $q = $this->_db->prepare('SELECT * FROM stp_formule where ref_formule in (select ref_formule from stp_abonnement where ref_eleve = :ref_eleve)');
                $q->bindValue(':ref_eleve', $refEleve);
            }else if (array_key_exists('all_ref_formule_sup', $info)) {
                
                $refFormule = $info['all_ref_formule_sup'];
                
                $q = $this->_db->prepare('SELECT * FROM stp_formule where ref_formule >= :ref_formule');
                $q->bindValue(':ref_formule', $refFormule);
                
            }
        } else {
            $q = $this->_db->prepare("select * from stp_formule");
        }
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

    public function cast(\spamtonprof\stp_api\StpFormule $formule)
    {
        return ($formule);
    }

    public function updateRefProductStripe(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set ref_product_stripe = :ref_product_stripe where ref_formule = :ref_formule');
        $q->bindValue(':ref_product_stripe', $formule->getRef_product_stripe());
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();

        return ($formule);
    }

    public function updateFormule(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set formule = :formule where ref_formule = :ref_formule');
        $q->bindValue(':formule', $formule->getFormule());
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();

        return ($formule);
    }

    public function updateMatieres(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set matieres = :matieres where ref_formule = :ref_formule');
        $q->bindValue(':matieres', arrayToPgArray($formule->getMatieres()));
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();

        return ($formule);
    }

    public function update_ref_prof(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set ref_prof = :ref_prof where ref_formule = :ref_formule');
        $q->bindValue(':ref_prof', $formule->getRef_prof());
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();
        
        return ($formule);
    }
    
    
    public function update_from_tool(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set from_tool = :from_tool where ref_formule = :ref_formule');
        $q->bindValue(':from_tool', $formule->getFrom_tool(),PDO::PARAM_BOOL);
        $q->bindValue(':ref_formule', $formule->getRef_formule());
        $q->execute();
        
        return ($formule);
    }
    
    public function updateClasses(\spamtonprof\stp_api\StpFormule $formule)
    {
        $q = $this->_db->prepare('update stp_formule set classes = :classes where ref_formule = :ref_formule');
        $q->bindValue(':classes', arrayToPgArray($formule->getClasses()));
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

        $profMg = new \spamtonprof\stp_api\StpProfManager();

        $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();

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
                case "defaultPlan":
                    $plan = $planMg->getDefault(array(
                        'ref_formule' => $formule->getRef_formule()
                    ));

                    $formule->setDefaultPlan($plan);
                    break;
                case 'ref_prof':
                    $prof = $profMg->get(array(
                        'ref_prof' => $formule->getRef_prof()
                    ));

                    $formule->setProf($prof);
                    break;
                case 'matieres':

                    $matieres = $formule->getMatieres();
                    for ($i = 0; $i < count($matieres); $i ++) {
                        $matiere = $matieres[$i];
                        $matieres[$i] = $matiereMg->get(array(
                            'matiere' => $matiere
                        ));
                    }
                    $formule->setMatieres($matieres);
                    break;
            }
        }
    }

    public function get($info, $constructor = false)
    {
        $q = null;
        if (is_array($info)) {

            if (array_key_exists("formule", $info)) {

                $nomFormule = $info["formule"];

                $q = $this->_db->prepare("select * from stp_formule where formule = :formule");

                $q->bindValue(":formule", $nomFormule);
            } else if (array_key_exists('ref_formule', $info)) {

                $refFormule = $info['ref_formule'];

                $q = $this->_db->prepare('SELECT * FROM stp_formule where ref_formule = :ref_formule');
                $q->bindValue(':ref_formule', $refFormule);
            }

            $q->execute();

            $data = $q->fetch();

            if ($data) {

                $formule = new \spamtonprof\stp_api\StpFormule($data);
                if ($constructor) {

                    $constructor["objet"] = $formule;
                    $this->construct($constructor);
                }

                return ($formule);
            } else {
                return ($data);
            }
        }
    }
}