<?php
namespace spamtonprof\cnl;

use PDO;

class GmailAccountManager 

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function getPasCree()
    {
        
        $q = $this->_db->prepare("SELECT ref_compte_gmail, prenom, nom, date_naissance, adresse_mail, cree, password 
            from cnl_compte_gmail
            where cree = false");
        $q->execute();
        
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
        if($donnees){
            return( new GmailAccount($donnees));
        }else{
            return false;
        }
        
    }
}