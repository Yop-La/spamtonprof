<?php
namespace spamtonprof\stp_api;

class stpAbonnementManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpAbonnement $stpAbonnement)
    {
        $q = $this->_db->prepare('insert into stp_abonnement(ref_eleve, ref_formule, ref_statut_abonnement, ref_abonnement, date_creation, prof_referent, date_maj, interrompu, probleme_paiement, remarque_inscription, ref_plan) values( :ref_eleve,:ref_formule,:ref_statut_abonnement,:ref_abonnement,:date_creation,:prof_referent,:date_maj,:interrompu,:probleme_paiement,:remarque_inscription,:ref_plan)');
        $q->bindValue(':ref_eleve', $stpAbonnement->getRef_eleve());
        $q->bindValue(':ref_formule', $stpAbonnement->getRef_formule());
        $q->bindValue(':ref_statut_abonnement', $stpAbonnement->getRef_statut_abonnement());
        $q->bindValue(':ref_abonnement', $stpAbonnement->getRef_abonnement());
        $q->bindValue(':date_creation', $stpAbonnement->getDate_creation());
        $q->bindValue(':prof_referent', $stpAbonnement->getProf_referent());
        $q->bindValue(':date_maj', $stpAbonnement->getDate_maj());
        $q->bindValue(':interrompu', $stpAbonnement->getInterrompu());
        $q->bindValue(':probleme_paiement', $stpAbonnement->getProbleme_paiement());
        $q->bindValue(':remarque_inscription', $stpAbonnement->getRemarque_inscription());
        $q->bindValue(':ref_plan', $stpAbonnement->getRef_plan());
        $q->execute();
        $stpAbonnement->setRef_abonnement($this->_db->lastInsertId());
        return ($stpAbonnement);
    }
}
