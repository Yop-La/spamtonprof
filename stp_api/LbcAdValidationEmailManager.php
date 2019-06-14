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
        $q = $this->_db->prepare('insert into lbc_ad_validation_email( gmail_id, date_reception, destinataire) values( :gmail_id,:date_reception,:destinataire)');
        $q->bindValue(':gmail_id', $lbcAdValidationEmail->getGmail_id());
        $q->bindValue(':date_reception', $lbcAdValidationEmail->getDate_reception());
        $q->bindValue(':destinataire', $lbcAdValidationEmail->getDestinataire());
        $q->execute();
        
        $lbcAdValidationEmail->setRef_message($this->_db->lastInsertId());
        
        return ($lbcAdValidationEmail);
    }
}
