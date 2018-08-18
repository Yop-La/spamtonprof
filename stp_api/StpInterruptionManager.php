<?php
namespace spamtonprof\stp_api;

class StpInterruptionManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpInterruption $StpInterruption)
    {
        $q = $this->_db->prepare('insert into stp_interruption(ref_interruption, ref_abonnement, date_debut, date_fin) values( :ref_interruption,:ref_abonnement,:date_debut,:date_fin)');
        $q->bindValue(':ref_interruption', $StpInterruption->getRef_interruption());
        $q->bindValue(':ref_abonnement', $StpInterruption->getRef_abonnement());
        $q->bindValue(':date_debut', $StpInterruption->getDate_debut());
        $q->bindValue(':date_fin', $StpInterruption->getDate_fin());
        $q->execute();
        
        $StpInterruption->setRef_interruption($this->_db->lastInsertId());
        
        return ($StpInterruption);
    }
}
