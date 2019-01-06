<?php
namespace spamtonprof\stp_api;

class TypeTexteManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(typeTexte $typeTexte)
    {
        $q = $this->_db->prepare('insert into type_texte( type) values( :type)');
        $q->bindValue(':type', $typeTexte->getType());
        $q->execute();

        $typeTexte->setRef_type($this->_db->lastInsertId());

        return ($typeTexte);
    }

    public function get($info)
    {
        $q = null;

        if (array_key_exists("ref_type", $info)) {

            $refType = $info["ref_type"];
            $q = $this->_db->prepare("select * from type_texte where ref_type =:ref_type");
            $q->bindValue(":ref_type", $refType);
            $q->execute();
        } else if (array_key_exists("type", $info)) {

            $type = $info["type"];
            $q = $this->_db->prepare("select * from type_texte where type =:type");
            $q->bindValue(":type", $type);
            $q->execute();
        }

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $typeTexte = new \spamtonprof\stp_api\TypeTexte($data);

            return ($typeTexte);
        }
        return (false);
    }

    public function getAll($info)
    {
        $q = null;
        $typeTextes = [];

        if (in_array("all", $info)) {

            $q = $this->_db->prepare("select * from type_texte");
        }

        $q->execute();
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $typeTexte = new \spamtonprof\stp_api\TypeTexte($data);
            $typeTextes[] = $typeTexte;
        }
        return ($typeTextes);
    }
}
