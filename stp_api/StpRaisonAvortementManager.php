<?php
namespace spamtonprof\stp_api;

class StpRaisonAvortementManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpRaisonAvortement $StpRaisonAvortement)
    {
        $q = $this->_db->prepare('insert into stp_raison_avortement(ref_raison_avortement, raison_avortement) values( :ref_raison_avortement,:raison_avortement)');
        $q->bindValue(':ref_raison_avortement', $StpRaisonAvortement->getRef_raison_avortement());
        $q->bindValue(':raison_avortement', $StpRaisonAvortement->getRaison_avortement());
        $q->execute();
        
        $StpRaisonAvortement->setRef_raison_avortement($this->_db->lastInsertId());
        
        return ($StpRaisonAvortement);
    }
}
