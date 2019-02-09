<?php
namespace spamtonprof\stp_api;

class PrenomLbcManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(PrenomLbc $prenomLbc)
    {
        $q = $this->_db->prepare('insert into prenom_lbc(prenom, nb_use) values(:prenom,0)');

        $q->bindValue(':prenom', $prenomLbc->getPrenom());
        $q->execute();

        $prenomLbc->setRef_prenom($this->_db->lastInsertId());

        return ($prenomLbc);
    }
}
