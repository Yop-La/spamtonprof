<?php
namespace spamtonprof\stp_api;

use PDO;

class ProspectLbcManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function get($info)
    {
        $donnees = false;
        
        if (array_key_exists("adresse_mail", $info)) {
            
            $mail = $info["adresse_mail"];
            $mail = trim($mail);
            $q = $this->_db->prepare("select * from prospect_lbc where lower(adresse_mail) = lower(:adresse_mail)");
            $q->execute(array(
                "adresse_mail" => $mail
            ));
            
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        if (array_key_exists("ref_prospect_lbc", $info)) {
            
            $refProspectLbc = $info["ref_prospect_lbc"];

            $q = $this->_db->prepare("select * from prospect_lbc where ref_prospect_lbc =:ref_prospect_lbc");
            $q->execute(array(
                "ref_prospect_lbc" => $refProspectLbc
            ));
            
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        if (! $donnees) {
            return false;
        }
        
        $prospect = new \spamtonprof\stp_api\ProspectLbc($donnees);
        
        return $prospect;
    }

    public function add(ProspectLbc $prospect)
    {
        $q = $this->_db->prepare('INSERT INTO prospect_lbc(adresse_mail) VALUES(lower(:adresse_mail))');
        $q->bindValue(':adresse_mail', $prospect->getAdresse_mail());
        $q->execute();
        
        $prospect->setRef_prospect_lbc($this->_db->lastInsertId());
        return ($prospect);
    }
    



}