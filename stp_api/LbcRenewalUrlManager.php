<?php
namespace spamtonprof\stp_api;

class LbcRenewalUrlManager
{

    private $_db;
    
    const TO_RENEW = 1;
    

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(LbcRenewalUrl $lbcRenewalUrl)
    {
        $q = $this->_db->prepare('insert into lbc_renewal_url(url, statut, date_ajout, ref_compte_lbc, date_reception) values( :url,:statut,NOW(),:ref_compte_lbc, :date_reception)');
        $q->bindValue(':url', $lbcRenewalUrl->getUrl());
        $q->bindValue(':statut', $lbcRenewalUrl->getStatut());
        $q->bindValue(':ref_compte_lbc', $lbcRenewalUrl->getRef_compte_lbc());
        $q->bindValue(':date_reception', $lbcRenewalUrl->getDate_reception());
        $q->execute();
        
        $lbcRenewalUrl->setRef_url($this->_db->lastInsertId());
        return ($lbcRenewalUrl);
    }
}
