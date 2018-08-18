<?php
namespace spamtonprof\stp_api;

class StpRemarqueInscriptionManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpRemarqueInscription $StpRemarqueInscription)
    {
        $q = $this->_db->prepare('insert into stp_remarque_inscription(ref_abonnement, chapitre, difficulte, note, ref_matiere) values( :ref_abonnement,:chapitre,:difficulte,:note,:ref_matiere)');
        $q->bindValue(':ref_abonnement', $StpRemarqueInscription->getRef_abonnement());
        $q->bindValue(':chapitre', $StpRemarqueInscription->getChapitre());
        $q->bindValue(':difficulte', $StpRemarqueInscription->getDifficulte());
        $q->bindValue(':note', $StpRemarqueInscription->getNote());
        $q->bindValue(':ref_matiere', $StpRemarqueInscription->getRef_matiere());
        $q->execute();
        
        $StpRemarqueInscription->setRef_remarque($this->_db->lastInsertId());
        
        return ($StpRemarqueInscription);
    }
    
    public function getAll($info){
        
        $q=null;
        $remarques = [];
        
        if(array_key_exists("ref_abonnement", $info)){
            
            $refAbonnement = $info["ref_abonnement"];
            
            $q = $this->_db->prepare("select * from stp_remarque_inscription where ref_abonnement = :ref_abonnement");
            $q->bindValue(":ref_abonnement", $refAbonnement);
            $q->execute();
            
        }
        
        while($data = $q->fetch(\PDO::FETCH_ASSOC)){
            
            $remarques[] = new \spamtonprof\stp_api\StpRemarqueInscription($data);
            
            
        }
        return($remarques);
        
    }
}
