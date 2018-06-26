<?php
namespace spamtonprof\stp_api;

class stpSemaineEssaiManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpSemaineEssai $stpSemaineEssai)
    {
        $q = $this->_db->prepare('insert into stp_semaine_essai(ref_essai, ref_abonnement, debut, fin, ref_prof, ref_statut_essai, essai_rattrapage, ref_raison_avortement) values( :ref_essai,:ref_abonnement,:debut,:fin,:ref_prof,:ref_statut_essai,:essai_rattrapage,:ref_raison_avortement)');
        $q->bindValue(':ref_essai', $stpSemaineEssai->getRef_essai());
        $q->bindValue(':ref_abonnement', $stpSemaineEssai->getRef_abonnement());
        $q->bindValue(':debut', $stpSemaineEssai->getDebut());
        $q->bindValue(':fin', $stpSemaineEssai->getFin());
        $q->bindValue(':ref_prof', $stpSemaineEssai->getRef_prof());
        $q->bindValue(':ref_statut_essai', $stpSemaineEssai->getRef_statut_essai());
        $q->bindValue(':essai_rattrapage', $stpSemaineEssai->getEssai_rattrapage());
        $q->bindValue(':ref_raison_avortement', $stpSemaineEssai->getRef_raison_avortement());
        $q->execute();
        
        $stpSemaineEssai->setRef_essai($this->_db->lastInsertId());
        
        return ($stpSemaineEssai);
    }
}
