<?php
namespace spamtonprof\stp_api;

class LbcAdValidationEmailManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(lbcAdValidationEmail $lbcAdValidationEmail)
    {
        $q = $this->_db->prepare('insert into lbc_ad_validation_email( gmail_id, date_reception, ref_compte_lbc) values( :gmail_id,:date_reception,:ref_compte_lbc)');
        $q->bindValue(':gmail_id', $lbcAdValidationEmail->getGmail_id());
        $q->bindValue(':date_reception', $lbcAdValidationEmail->getDate_reception());
        $q->bindValue(':ref_compte_lbc', $lbcAdValidationEmail->getRef_compte_lbc());
        $q->execute();

        $lbcAdValidationEmail->setRef_message($this->_db->lastInsertId());

        return ($lbcAdValidationEmail);
    }
    
    public function getAll($info)
    {
        $email_validations = [];
        $q = null;
        if (array_key_exists('day', $info)) {
            
            $day = $info['day'];
            
            $q = $this->_db->prepare("select * from lbc_ad_validation_email where date(date_reception) = :day ");
            $q->bindValue(':day', $day);
        }
        
        $q->execute();
        
        while ($donnees = $q->fetch(\PDO::FETCH_ASSOC)) {
            
            $email_validation = new \spamtonprof\stp_api\LbcAdValidationEmail($donnees);
            $email_validations[] = $email_validation;
        }
        return ($email_validations);
    }
    
    
}
