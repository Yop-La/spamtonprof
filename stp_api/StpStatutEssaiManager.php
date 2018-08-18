<?php
namespace spamtonprof\stp_api;

class StpStatutEssaiManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpStatutEssai $StpStatutEssai)
    {
        $q = $this->_db->prepare('insert into stp_statut_essai(statut_essai, ref_statut_essai) values( :statut_essai,:ref_statut_essai)');
        $q->bindValue(':statut_essai', $StpStatutEssai->getStatut_essai());
        $q->bindValue(':ref_statut_essai', $StpStatutEssai->getRef_statut_essai());
        $q->execute();
        
        $StpStatutEssai->setRef_statut_essai($this->_db->lastInsertId());
        
        return ($StpStatutEssai);
    }
}
