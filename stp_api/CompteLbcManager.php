<?php
namespace spamtonprof\stp_api;

class CompteLbcManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(compteLbc $compteLbc)
    {
        $q = $this->_db->prepare('insert into compte_lbc(ref_client, mail, password) values(:ref_client, :mail,:password)');
        $q->bindValue(':ref_client', $compteLbc->getRef_client());
        $q->bindValue(':mail', $compteLbc->getMail());
        $q->bindValue(':password', $compteLbc->getPassword());
        $q->execute();
        
        $compteLbc->setRef_compte($this->_db->lastInsertId());
        
        return ($compteLbc);
    }
}
