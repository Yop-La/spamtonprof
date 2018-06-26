<?php
namespace spamtonprof\stp_api;

class stpCompteFamilleManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpCompteFamille $stpCompteFamille)
    {
        $refProche = $stpCompteFamille->getRef_proche();
        if (is_null($refProche)) {
            $refProche = null;
        }
        $q = $this->_db->prepare('insert into stp_compte_famille(date_creation, ref_proche) values( :date_creation,:ref_proche)');
        $q->bindValue(':date_creation', $stpCompteFamille->getDate_creation()->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_proche', $stpCompteFamille->getRef_proche());
        $q->execute();
        
        $stpCompteFamille->setRef_compte_famille($this->_db->lastInsertId());
        
        return ($stpCompteFamille);
    }
}
