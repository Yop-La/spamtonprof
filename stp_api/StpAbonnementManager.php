<?php
namespace spamtonprof\stp_api;

use PDO;

class StpAbonnementManager
{

    private $_db, $eleveMg;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpAbonnement $StpAbonnement)
    {
        $q = $this->_db->prepare('insert into stp_abonnement(ref_eleve, ref_formule, ref_statut_abonnement, date_creation, remarque_inscription, ref_plan) values( :ref_eleve,:ref_formule,:ref_statut_abonnement,:date_creation,:remarque_inscription,:ref_plan)');
        $q->bindValue(':ref_eleve', $StpAbonnement->getRef_eleve());
        $q->bindValue(':ref_formule', $StpAbonnement->getRef_formule());
        $q->bindValue(':ref_statut_abonnement', $StpAbonnement->getRef_statut_abonnement());
        $q->bindValue(':date_creation', $StpAbonnement->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':remarque_inscription', $StpAbonnement->getRemarque_inscription());
        $q->bindValue(':ref_plan', $StpAbonnement->getRef_plan());
        $q->execute();
        
        $StpAbonnement->setRef_abonnement($this->_db->lastInsertId());
        
        return ($StpAbonnement);
    }

    /*
     * pour remonter les abonnements sans prof
     */
    public function getAbonnementsSansProf()
    {
        $abonnements = [];
        
        $q = $this->_db->prepare("select * from stp_abonnement where ref_prof is null order by date_creation ");
        
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);
            
            $this->construct(array(
                "objet" => $abonnement,
                "construct" => array(
                    'ref_eleve',
                    'ref_formule',
                    'ref_prof'
                ),
                "ref_eleve" => array(
                    "construct" => array(
                        'ref_classe',
                        'ref_profil'
                    )
                )
            
            ));
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }

    // pour remonter les abonnements qui viennent de se voir attribuer un prof pour la première fois après l'inscription
    public function getHasNotFirstProfAssignement()
    {
        $abonnements = [];
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $q = $this->_db->prepare("select * from stp_abonnement where first_prof_assigned = false and date_attribution_prof <= :now");
        $q->bindValue(":now", $now->format(PG_DATETIME_FORMAT));
        
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);
            
            $this->construct(array(
                "objet" => $abonnement,
                "construct" => array(
                    'ref_eleve',
                    'ref_formule',
                    'ref_prof',
                    'ref_parent',
                    'ref_plan'
                ),
                "ref_eleve" => array(
                    "construct" => array(
                        'ref_classe',
                        'ref_profil'
                    )
                )
            
            ));
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }

    public function updateRefProf(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_prof = :ref_prof where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_prof", $abonnement->getRef_prof());
        $q->execute();
    }
    
    public function updateRefStatutAbonnement(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_statut_abonnement = :ref_statut_abonnement where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_statut_abonnement", $abonnement->getRef_statut_abonnement());
        $q->execute();
    }
    
    public function updateSubsId(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set subs_id = :subs_id where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":subs_id", $abonnement->getSubs_Id());
        $q->execute();
    }

    public function updateDateAttributionProf(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set date_attribution_prof = :date_attribution_prof where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":date_attribution_prof", $abonnement->getDate_attribution_prof()
            ->format(PG_DATETIME_FORMAT));
        $q->execute();
    }

    public function updateRefProche(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_proche = :ref_proche where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_proche", $abonnement->getRef_proche());
        $q->execute();
    }

    public function updateFirstProfAssigned(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set first_prof_assigned = :first_prof_assigned where ref_abonnement = :ref_abonnement");
        $q->bindValue(":first_prof_assigned", $abonnement->getFirst_prof_assigned(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateRefCompte(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_compte = :ref_compte where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_compte", $abonnement->getRef_compte());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateDebutEssai(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set debut_essai = :debut_essai where ref_abonnement = :ref_abonnement");
        $q->bindValue(":debut_essai", $abonnement->getDebut_essai());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateFinEssai(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set fin_essai = :fin_essai where ref_abonnement = :ref_abonnement");
        $q->bindValue(":fin_essai", $abonnement->getFin_essai());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function cast(\spamtonprof\stp_api\StpAbonnement $object)
    {
        return ($object);
    }

    public function construct($constructor)
    {
        $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        $profMg = new \spamtonprof\stp_api\StpProfManager();
        $procheMg = new \spamtonprof\stp_api\StpProcheManager();
        
        $abonnement = $this->cast($constructor["objet"]);
        
        $constructOrders = $constructor["construct"];
        
        foreach ($constructOrders as $constructOrder) {
            
            switch ($constructOrder) {
                case "ref_eleve":
                    $eleve = $eleveMg->get(array(
                        'ref_eleve' => $abonnement->getRef_eleve()
                    ));
                    
                    if (array_key_exists("ref_eleve", $constructor)) {
                        
                        $constructorEleve = $constructor["ref_eleve"];
                        $constructorEleve["objet"] = $eleve;
                        
                        $eleveMg->construct($constructorEleve);
                    }
                    $abonnement->setEleve($eleve);
                    break;
                case "ref_formule":
                    $formule = $formuleMg->get(array(
                        'ref_formule' => $abonnement->getRef_formule()
                    ));
                    
                    $abonnement->setFormule($formule);
                    break;
                case "ref_prof":
                    $prof = $profMg->get(array(
                        'ref_prof' => $abonnement->getRef_prof()
                    ));
                    
                    $abonnement->setProf($prof);
                    break;
                case "ref_parent":
                    if (! is_null($abonnement->getRef_proche())) {
                        $procheMg = new \spamtonprof\stp_api\StpProcheManager();
                        $proche = $procheMg->get(array(
                            'ref_proche' => $abonnement->getRef_proche()
                        ));
                        
                        $abonnement->setProche($proche);
                    }
                    break;
                case "ref_plan":
                    $planMg = new \spamtonprof\stp_api\StpPlanManager();
                    $plan = $planMg->get(array(
                        'ref_plan' => $abonnement->getRef_plan()
                    ));
                    
                    $abonnement->setPlan($plan);
                    break;
            }
        }
    }

    public function get($info, $constructor = false)
    {
        $q = null;

        if (array_key_exists("ref_abonnement", $info)) {

            $refAbonnement = $info["ref_abonnement"];
            $q = $this->_db->prepare("select * from stp_abonnement where ref_abonnement =:ref_abonnement");
            $q->bindValue(":ref_abonnement", $refAbonnement);
            $q->execute();
        }
        
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);
            
            if ($constructor) {
                $constructor["objet"] = $abonnement;
                $this->construct($constructor);
            }
            
            return ($abonnement);
        }
        return (false);
    }

    public function getAll($info, $constructor = false)
    {
        $abonnements = [];
        $q = null;
        
        if (is_array($info)) {
            
            if (array_key_exists("ref_eleve", $info)) {
                
                $refEleve = $info["ref_eleve"];
                $q = $this->_db->prepare('select * from stp_abonnement where ref_eleve = :ref_eleve');
                $q->bindValue(":ref_eleve", $refEleve);
                $q->execute();
            }
            if (array_key_exists("ref_compte", $info)) {
                
                $refCompte = $info["ref_compte"];
                $q = $this->_db->prepare('select * from stp_abonnement where ref_compte = :ref_compte');
                $q->bindValue(":ref_compte", $refCompte);
                $q->execute();
            }
        }
        
        if ($info == "all") {
            $q = $this->_db->prepare('select * from stp_abonnement');
            $q->execute();
        }
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);
            
            if ($constructor) {
                $constructor["objet"] = $abonnement;
                $this->construct($constructor);
            }
            
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }
}
