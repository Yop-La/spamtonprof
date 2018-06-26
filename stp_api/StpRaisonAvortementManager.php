<?php
namespace spamtonprof\stp_api;

class stpRaisonAvortementManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpRaisonAvortement $stpRaisonAvortement)
    {
        $q = $this->_db->prepare('insert into stp_raison_avortement(ref_raison_avortement, raison_avortement) values( :ref_raison_avortement,:raison_avortement)');
        $q->bindValue(':ref_raison_avortement', $stpRaisonAvortement->getRef_raison_avortement());
        $q->bindValue(':raison_avortement', $stpRaisonAvortement->getRaison_avortement());
        $q->execute();
        
        $stpRaisonAvortement->setRef_raison_avortement($this->_db->lastInsertId());
        
        return ($stpRaisonAvortement);
    }
}
