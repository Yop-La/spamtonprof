<?php
namespace spamtonprof\stp_api;

class StpNiveauManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function getAll($info)
    {
        $q = null;
        if (in_array('all', $info)) {
            $q = $this->_db->prepare('select * from stp_niveau order by niveau');
            $q->execute();
        }

        $niveaux = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $niveaux[] = new \spamtonprof\stp_api\StpNiveau($data);
        }
        return ($niveaux);
    }

    public function get($info)
    {
        $q = null;
        if (in_array('all', $info)) {
            $q = $this->_db->prepare('select * from stp_niveau order by niveau');
        } else if (array_key_exists('niveau', $info)) {
            $niveau = $info['niveau'];

            $q = $this->_db->prepare('select * from stp_niveau where lower(niveau) like lower(:niveau) limit 1');
            $q->bindValue(':niveau', $niveau);
        } else if (array_key_exists('ref_niveau', $info)) {
            $ref_niveau = $info['ref_niveau'];

            $q = $this->_db->prepare('select * from stp_niveau where ref_niveau = :ref_niveau');
            $q->bindValue(':ref_niveau', $ref_niveau);
        } else if (array_key_exists('sigle', $info)) {
            $sigle = $info['sigle'];
            
            $q = $this->_db->prepare('select * from stp_niveau where sigle = :sigle');
            $q->bindValue(':sigle', $sigle);
        }

        $q->execute();

        if ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $niveau = new \spamtonprof\stp_api\StpNiveau($data);
            return ($niveau);
        } else {
            return (false);
        }
    }

    public static function cast(\spamtonprof\stp_api\StpNiveau $object)
    {
        return ($object);
    }
}
