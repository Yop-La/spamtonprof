<?php
namespace spamtonprof\stp_api;

use PDO;

class ProfManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->query('SELECT ref_prof, gmail_adress, gmail_credential FROM prof WHERE ref_prof = ' . $info);
            
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                return new Prof($q->fetch(PDO::FETCH_ASSOC));
            }
        } else {
            $q = $this->_db->prepare('SELECT ref_prof, gmail_adress, gmail_credential FROM prof WHERE gmail_adress like :mail');
            $q->execute([
                ':mail' => '%' . $info . '%'
            ]);
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $res = $q->fetch(PDO::FETCH_ASSOC);
                return new Prof($res);
            }
        }
    }

    public function update(Prof $prof)
    
    {
        $q = $this->_db->prepare('UPDATE prof SET gmail_adress =:gmail_adress, gmail_credential=:gmail_credential
            WHERE ref_prof = :ref_prof');
        
        $q->bindValue(':gmail_adress', $prof->getGmail_adress());
        
        $q->bindValue(':gmail_credential', $prof->getGmail_credential());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
    }

    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}