<?php
namespace spamtonprof\stp_api;

use PDO;



class HistoriqueManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }
    
    public function getDateLastStatut($statut, $refCompte){
        
       
            $q = $this->_db->prepare('SELECT date_statut FROM historique_eleve WHERE ref_compte = :ref_compte and statut = :statut order by date_statut desc limit 1');
            $q->execute([
                ':ref_compte' => $refCompte,
                ':statut' => $statut
            ]);
            $retour = $q->fetch();
            
            $dateLastStatut = new \DateTime($retour["date_statut"], new \DateTimeZone("Europe/Paris"));
            
            return $dateLastStatut;
        
    }


}