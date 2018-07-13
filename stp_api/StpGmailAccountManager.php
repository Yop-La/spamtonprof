<?php
namespace spamtonprof\stp_api;

use PDO;

class stpGmailAccountManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpGmailAccount $stpGmailAccount)
    {
        $q = $this->_db->prepare('insert into stp_gmail_account(ref_gmail_account, email) values( :ref_gmail_account,:email)');
        $q->bindValue(':ref_gmail_account', $stpGmailAccount->getRef_gmail_account());
        $q->bindValue(':email', $stpGmailAccount->getEmail());

        $q->execute();

        $stpGmailAccount->setRef_gmail_account($this->_db->lastInsertId());

        return ($stpGmailAccount);
    }
    
    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->prepare('SELECT * FROM stp_gmail_account WHERE ref_gmail_account = :ref_gmail_account');
            
            $q->bindValue(":ref_gmail_account", $info);
            
            $q->execute();
            
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                return new stpGmailAccount($q->fetch(PDO::FETCH_ASSOC));
            }
        } else {
            $q = $this->_db->prepare('SELECT * FROM stp_gmail_account WHERE email like :email');
            $q->execute([
                ':email' => '%' . $info . '%'
            ]);
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $res = $q->fetch(PDO::FETCH_ASSOC);
                return new stpGmailAccount($res);
            }
        }
    }
    
    public function updateCredential(stpGmailAccount $stpGmailAccount)
    
    {
        $q = $this->_db->prepare('UPDATE stp_gmail_account credential=:credential
            WHERE ref_gmail_account = :ref_gmail_account');
        
        $q->bindValue(':credential', $stpGmailAccount->getCredential());
        
        $q->bindValue(':ref_gmail_account', $stpGmailAccount->getRef_gmail_account());
        
        $q->execute();
        
    }
    
    public function updateHistoryId(stpGmailAccount $stpGmailAccount)
    
    {
        $q = $this->_db->prepare('UPDATE stp_gmail_account SET last_history_id =:last_history_id
            WHERE ref_gmail_account = :ref_gmail_account');
        
        $q->bindValue(':last_history_id', $stpGmailAccount->getLast_history_id());
        
        $q->bindValue(':ref_gmail_account', $stpGmailAccount->getRef_gmail_account());
        
        $q->execute();
        
    }
    
    
}
