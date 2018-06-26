<?php
namespace spamtonprof\stp_api;

class stpStatutPremiumManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpStatutPremium $stpStatutPremium)
    {
        $q = $this->_db->prepare('insert into stp_statut_premium(statut_premium, ref_statut_premium) values( :statut_premium,:ref_statut_premium)');
        $q->bindValue(':statut_premium', $stpStatutPremium->getStatut_premium());
        $q->bindValue(':ref_statut_premium', $stpStatutPremium->getRef_statut_premium());
        $q->execute();
        
        $stpStatutPremium->setRef_statut_premium($this->_db->lastInsertId());
        
        return ($stpStatutPremium);
    }
}
