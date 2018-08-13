<?php
namespace spamtonprof\stp_api;

class stpCompteManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpCompte $stpCompte)
    {
        $q = $this->_db->prepare('insert into stp_compte(date_creation, ref_proche) values( :date_creation,:ref_proche)');
        $q->bindValue(':date_creation', $stpCompte->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_proche', $stpCompte->getRef_proche());
        $q->execute();
        
        $stpCompte->setRef_compte($this->_db->lastInsertId());
        
        return ($stpCompte);
    }

    /*
     * retourne le numéro de list d'essai parent occupé ( ie dont l'abonnement associé est en essai ) du compte $refCompte
     * ou 0 si il n'y aucun abonnement en essai
     *
     */
    public function getNotFreeParentTrialList($refCompte)
    {
        $abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
        $eleveMg = new \spamtonprof\stp_api\stpEleveManager();
        $statutEssai = new \spamtonprof\stp_api\stpStatutEssai();
        
        $abonnementsCompte = $abonnementMg->getAll(array(
            "ref_compte" => $refCompte
        ));
        
        foreach ($abonnementsCompte as $abonnementCompte) {
            
            if ($abonnementCompte->getRef_statut_abonnement() == $statutEssai::EN_COURS || $abonnementCompte->getRef_statut_abonnement() == $statutEssai::EN_ATTENTE_DEMARRAGE) {
                
                $eleve = $eleveMg->get(array(
                    "ref_eleve" => $abonnementCompte->getRef_eleve()
                ));
                return($eleve->getSeq_email_parent_essai());

            }
        }
        return(0);
    }
}
