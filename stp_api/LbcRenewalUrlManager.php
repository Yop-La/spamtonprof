<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcRenewalUrlManager
{

    private $_db;

    const TO_RENEW = 1,FAIL = 2,DONE = 3;

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
        
        if (array_key_exists('to_renew', $info)) {
            $ref_compte = $info['to_renew'];
            $q = $this->_db->prepare("select * from lbc_renewal_url where ref_compte_lbc in
                (select ref_compte_lbc from lbc_renewal_url where statut = 1 and ref_compte_lbc not in (:ref_compte) order by date_reception desc limit 1);");
            $q->bindValue(':ref_compte', $ref_compte);
        }
        if ($q) {
            
            $q->execute();
            
            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
                
                $urls[] = new \spamtonprof\stp_api\LbcRenewalUrl($data);
            }
        }
        return ($urls);
    }

    public function get($info)
    {
        $q = false;
        
        if (array_key_exists('ref_url', $info)) {
            $ref_url = $info['ref_url'];
            $q = $this->_db->prepare("select * from lbc_renewal_url where ref_url = :ref_url;");
            $q->bindValue(':ref_url', $ref_url);
        }
        if ($q) {
            
            $q->execute();
            $data = $q->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                return (new \spamtonprof\stp_api\LbcRenewalUrl($data));
            }
        }
        return (false);
    }
    
    
    public function updateStatut(\spamtonprof\stp_api\LbcRenewalUrl $url)
    {
        $q = $this->_db->prepare('update lbc_renewal_url set statut = :statut where ref_url = :ref_url');
        
        $q->bindValue(':statut', $url->getStatut());
        
        $q->bindValue(':ref_url', $url->getRef_url());
        
        $q->execute();
        
        return ($url);
    }
    
}
