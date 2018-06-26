<?php
namespace spamtonprof\stp_api;

class stpInterruptionManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpInterruption $stpInterruption)
    {
        $q = $this->_db->prepare('insert into stp_interruption(ref_interruption, ref_abonnement, date_debut, date_fin) values( :ref_interruption,:ref_abonnement,:date_debut,:date_fin)');
        $q->bindValue(':ref_interruption', $stpInterruption->getRef_interruption());
        $q->bindValue(':ref_abonnement', $stpInterruption->getRef_abonnement());
        $q->bindValue(':date_debut', $stpInterruption->getDate_debut());
        $q->bindValue(':date_fin', $stpInterruption->getDate_fin());
        $q->execute();
        
        $stpInterruption->setRef_interruption($this->_db->lastInsertId());
        
        return ($stpInterruption);
    }
}
