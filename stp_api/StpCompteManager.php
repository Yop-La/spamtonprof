<?php
namespace spamtonprof\stp_api;

class StpCompteManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpCompte $StpCompte)
    {
        $q = $this->_db->prepare('insert into stp_compte(date_creation, ref_proche) values( :date_creation,:ref_proche)');
        $q->bindValue(':date_creation', $StpCompte->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_proche', $StpCompte->getRef_proche());
        $q->execute();
        
        $StpCompte->setRef_compte($this->_db->lastInsertId());
        
        return ($StpCompte);
    }

    /*
     * retourne le num�ro de list d'essai parent occup� ( ie dont l'abonnement associ� est en essai ) du compte $refCompte
     * ou 0 si il n'y aucun abonnement en essai
     *
     */
    public function getNotFreeParentTrialList($refCompte)
    {
        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
        $statutEssai = new \spamtonprof\stp_api\StpStatutEssai();
        
        $abonnementsCompte = $abonnementMg->getAll(array(
            "ref_compte" => $refCompte
        ));
        
        foreach ($abonnementsCompte as $abonnementCompte) {
            
            if ($abonnementCompte->getRef_statut_abonnement() == \spamtonprof\stp_api\StpStatutAbonnementManager::ESSAI) {
                
                $eleve = $eleveMg->get(array(
                    "ref_eleve" => $abonnementCompte->getRef_eleve()
                ));
                return ($eleve->getSeq_email_parent_essai());
            }
        }
        return (0);
    }

    public function get($info)
    {
        $q = null;
        if (array_key_exists('ref_compte_wp', $info)) {
            
            $refCompteWp = $info["ref_compte_wp"];
            
            $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
            $procheMg = new \spamtonprof\stp_api\StpProcheManager();
            
            $eleve = $eleveMg->get(array(
                "ref_compte_wp" => $refCompteWp
            ));
            
            if ($eleve) {
                $compte = $this->get(array(
                    "ref_compte" => $eleve->getRef_compte()
                ));
                return ($compte);
            }
            
            $proche = $procheMg->get(array(
                "ref_compte_wp" => $refCompteWp
            ));
            
            if ($proche) {
                $compte = $this->get(array(
                    "ref_proche" => $proche->getRef_proche()
                ));
                return ($compte);
            }
            return(false);
        }
        
        if (array_key_exists("ref_compte", $info)) {
            
            $refCompte = $info["ref_compte"];
            
            $q = $this->_db->prepare('select * from stp_compte where ref_compte = :ref_compte');
            $q->bindValue(':ref_compte', $refCompte);
            $q->execute();
        }
        
        if (array_key_exists("ref_proche", $info)) {
            
            $refProche = $info["ref_proche"];
            
            $q = $this->_db->prepare('select * from stp_compte where ref_proche = :ref_proche');
            $q->bindValue(':ref_proche', $refProche);
            $q->execute();
        }
        
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            return (new \spamtonprof\stp_api\StpCompte($data));
        } else {
            return (false);
        }
    }
}
