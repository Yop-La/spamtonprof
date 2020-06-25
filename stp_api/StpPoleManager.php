<?php
namespace spamtonprof\stp_api;

class StpPoleManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpPole $stpPole)
    {
        $q = $this->_db->prepare('insert into stp_pole(name) values( :name)');

        $q->bindValue(':name', $stpPole->getName());
        $q->execute();

        $stpPole->setRef_pole($this->_db->lastInsertId());

        return ($stpPole);
    }

    public function get($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stp_pole where ref_pole = :ref_pole");
        $q->bindValue(":ref_pole", $info);

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $pole = new \spamtonprof\stp_api\StpPole($data);

        return ($pole);
    }

    public function getAll($info = false, $constructor = false)
    {
        $poles = [];

        $q = $this->_db->prepare("select * from stp_pole");

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];
            }
        }

        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $pole = new \spamtonprof\stp_api\StpPole($data);

            if ($constructor) {
                $constructor["objet"] = $pole;
                $this->construct($constructor);
            }
            $poles[] = $pole;
        }
        return ($poles);
    }

    public function updateName(StpPole $pole)
    {
        $q = $this->_db->prepare("update stp_pole set name = :name where ref_pole = :ref_pole");
        $q->bindValue(":name", $pole->getName());
        $q->bindValue(":ref_pole", $pole->getRef_pole());
        $q->execute();
    }
}
