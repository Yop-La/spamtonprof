<?php
namespace spamtonprof\stp_api;

use PDO;

class stpAbonnementManager
{

    private $_db, $eleveMg;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpAbonnement $stpAbonnement)
    {
        $q = $this->_db->prepare('insert into stp_abonnement(ref_eleve, ref_formule, ref_statut_abonnement, date_creation, remarque_inscription, ref_plan) values( :ref_eleve,:ref_formule,:ref_statut_abonnement,:date_creation,:remarque_inscription,:ref_plan)');
        $q->bindValue(':ref_eleve', $stpAbonnement->getRef_eleve());
        $q->bindValue(':ref_formule', $stpAbonnement->getRef_formule());
        $q->bindValue(':ref_statut_abonnement', $stpAbonnement->getRef_statut_abonnement());
        $q->bindValue(':date_creation', $stpAbonnement->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':remarque_inscription', $stpAbonnement->getRemarque_inscription());
        $q->bindValue(':ref_plan', $stpAbonnement->getRef_plan());
        $q->execute();
        
        $stpAbonnement->setRef_abonnement($this->_db->lastInsertId());
        
        return ($stpAbonnement);
    }

    public function getAbonnementsSansProf()
    {
        $abonnements = [];
        
        $q = $this->_db->prepare("select * from stp_abonnement where ref_prof is null order by date_creation ");
        
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $abonnement = new \spamtonprof\stp_api\stpAbonnement($data);
            
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
    
    public function updateRefProf(\spamtonprof\stp_api\stpAbonnement $abonnement){
        
        $q = $this->_db->prepare("update stp_abonnement set ref_prof = :ref_prof where ref_abonnement = :ref_abonnement");
        $q -> bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q -> bindValue(":ref_prof", $abonnement->getRef_prof());
        $q->execute();
        
    }

    public function cast(\spamtonprof\stp_api\stpAbonnement $object)
    {
        return ($object);
    }

    public function construct($constructor)
    {
        $eleveMg = new \spamtonprof\stp_api\stpEleveManager();
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        $profMg = new \spamtonprof\stp_api\stpProfManager();
        
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
            }
        }
    }
}
