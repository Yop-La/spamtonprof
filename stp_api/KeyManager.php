<?php
namespace spamtonprof\stp_api;

use PDO;

class KeyManager
{
    
    const GMAIL_KEY = "GMAIL_KEY";

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function get($info)
    {
        
        
        if (is_int($info)) {
            
            $q = $this->_db->query('SELECT ref_key, name, key  FROM key WHERE ref_key = ' . $info);
            
            
            
            if ($q->rowCount() <= 0) {
                
                return (false);
                
            } else {
                
                return new Key($q->fetch(PDO::FETCH_ASSOC));
                
            }
            
        } elseif (is_string($info)) {
            
            
            
            $q = $this->_db->prepare('SELECT ref_key, name, key  FROM key WHERE name = :name');
            
            $q->execute(array(
                
                "name" => $info
                
            ));
            
            
            
            if ($q->rowCount() <= 0) {
                
                return (false);
                
            } else {
                
                return new Key($q->fetch(PDO::FETCH_ASSOC));
                
            }
            
        }
        
        
    }

    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}