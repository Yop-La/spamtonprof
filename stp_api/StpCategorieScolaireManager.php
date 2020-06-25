<?php
namespace spamtonprof\stp_api;

class StpCategorieScolaireManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpCategorieScolaire $stpCategorieScolaire)
    {
        $q = $this->_db->prepare('insert into stp_categorie_scolaire(name) values( :ref_cat_scolaire,:name)');
        $q->bindValue(':name', $stpCategorieScolaire->getName());
        $q->execute();

        $stpCategorieScolaire->setRef_cat_scolaire($this->_db->lastInsertId());

        return ($stpCategorieScolaire);
    }

    public function get($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stp_categorie_scolaire where ref_cat_scolaire = :ref_cat_scolaire");
        $q->bindValue(":ref_cat_scolaire", $info);

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

        $pole = new \spamtonprof\stp_api\StpCategorieScolaire($data);

        return ($pole);
    }

    public function getAll($info = false, $constructor = false)
    {
        $cats = [];

        $q = $this->_db->prepare("select * from stp_categorie_scolaire");

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];
            }
        }

        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $cat = new \spamtonprof\stp_api\StpCategorieScolaire($data);

            if ($constructor) {
                $constructor["objet"] = $cat;
                $this->construct($constructor);
            }
            $cats[] = $cat;
        }
        return ($cats);
    }
}
