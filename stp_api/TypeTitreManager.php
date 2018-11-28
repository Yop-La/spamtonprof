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

    public function get($info)
    {
        $q = null;

        if (array_key_exists("ref_type", $info)) {

            $refType = $info["ref_type"];
            $q = $this->_db->prepare("select * from type_titre where ref_type =:ref_type");
            $q->bindValue(":ref_type", $refType);
            $q->execute();
        }else if (array_key_exists("type", $info)) {
            
            $type = $info["type"];
            $q = $this->_db->prepare("select * from type_titre where type =:type");
            $q->bindValue(":type", $type);
            $q->execute();
        }

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $typeTitre = new \spamtonprof\stp_api\TypeTitre($data);

            return ($typeTitre);
        }
        return (false);
    }

    public function getAll($info)
    {
        $q = null;
        $typeTitres = [];

        if (in_array("all", $info)) {

            $q = $this->_db->prepare("select * from type_titre");
        }

        $q->execute();
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $typeTitre = new \spamtonprof\stp_api\TypeTitre($data);
            $typeTitres[] = $typeTitre;
        }
        return ($typeTitres);
    }
}
