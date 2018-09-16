<?php
namespace spamtonprof\stp_api;

class StpStatutPremiumManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpStatutPremium $StpStatutPremium)
    {
        $q = $this->_db->prepare('insert into stp_statut_premium(statut_premium, ref_statut_premium) values( :statut_premium,:ref_statut_premium)');
        $q->bindValue(':statut_premium', $StpStatutPremium->getStatut_premium());
        $q->bindValue(':ref_statut_premium', $StpStatutPremium->getRef_statut_premium());
        $q->execute();
        
        $StpStatutPremium->setRef_statut_premium($this->_db->lastInsertId());
        
        return ($StpStatutPremium);
    }
}
