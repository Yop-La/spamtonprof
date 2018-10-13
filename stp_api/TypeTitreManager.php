<?php
namespace spamtonprof\stp_api;

class TypeTitreManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(typeTitre $typeTitre)
    {
        $q = $this->_db->prepare('insert into type_titre(type) values(:type)');
        $q->bindValue(':type', $typeTitre->getType());
        $q->execute();
        $typeTitre->setRef_type($this->_db->lastInsertId());

        return ($typeTitre);
    }
}
