<?php
namespace spamtonprof\stp_api;

class stpCompteManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpCompte $stpCompte)
    {
        $q = $this->_db->prepare('insert into stp_compte(date_creation, ref_proche) values( :date_creation,:ref_proche)');
        $q->bindValue(':date_creation', $stpCompte->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_proche', $stpCompte->getRef_proche());
        $q->execute();
        
        $stpCompte->setRef_compte($this->_db->lastInsertId());
        
        return ($stpCompte);
    }

    

    
}
