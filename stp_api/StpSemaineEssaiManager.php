<?php
namespace spamtonprof\stp_api;

class StpSemaineEssaiManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpSemaineEssai $StpSemaineEssai)
    {
        $q = $this->_db->prepare('insert into stp_semaine_essai(ref_essai, ref_abonnement, debut, fin, ref_prof, ref_statut_essai, essai_rattrapage, ref_raison_avortement) values( :ref_essai,:ref_abonnement,:debut,:fin,:ref_prof,:ref_statut_essai,:essai_rattrapage,:ref_raison_avortement)');
        $q->bindValue(':ref_essai', $StpSemaineEssai->getRef_essai());
        $q->bindValue(':ref_abonnement', $StpSemaineEssai->getRef_abonnement());
        $q->bindValue(':debut', $StpSemaineEssai->getDebut());
        $q->bindValue(':fin', $StpSemaineEssai->getFin());
        $q->bindValue(':ref_prof', $StpSemaineEssai->getRef_prof());
        $q->bindValue(':ref_statut_essai', $StpSemaineEssai->getRef_statut_essai());
        $q->bindValue(':essai_rattrapage', $StpSemaineEssai->getEssai_rattrapage());
        $q->bindValue(':ref_raison_avortement', $StpSemaineEssai->getRef_raison_avortement());
        $q->execute();
        
        $StpSemaineEssai->setRef_essai($this->_db->lastInsertId());
        
        return ($StpSemaineEssai);
    }
}
