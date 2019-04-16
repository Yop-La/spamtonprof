<?php
namespace spamtonprof\stp_api;

use PDO;

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

    public function getAll($info)
    {
        $q = false;
        $urls = [];
        
        if ($info == "to_renew") {
            $q = $this->_db->prepare("select * from lbc_renewal_url where ref_compte_lbc in  
                (select ref_compte_lbc from lbc_renewal_url where statut = 1 order by date_reception desc limit 1);");
        }
        if ($q) {
            
            $q->execute();
            
            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
                
                $urls[] = new \spamtonprof\stp_api\LbcRenewalUrl($data);
                
            }
        }
        return ($urls);
    }
}
