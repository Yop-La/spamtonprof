<?php
namespace spamtonprof\stp_api;

class stpCompteWordpressManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpCompteWordpress $stpCompteWordpress)
    {
        $q = $this->_db->prepare('insert into stp_compte_wordpress(ref_wp, ref_compte_famille) values( :ref_wp,:ref_compte_famille)');
        $q->bindValue(':ref_wp', $stpCompteWordpress->getRef_wp());
        $q->bindValue(':ref_compte_famille', $stpCompteWordpress->getRef_compte_famille());
        $q->execute();
        
        return ($stpCompteWordpress);
    }
}
