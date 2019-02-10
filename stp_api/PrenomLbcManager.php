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

    public function updateNbUse(\spamtonprof\stp_api\PrenomLbc $prenomLbc)
    {
        $q = $this->_db->prepare("update prenom_lbc set nb_use = :nb_use where ref_prenom = :ref_prenom");

        $q->bindValue(":nb_use", $prenomLbc->getNb_use());
        $q->bindValue(":ref_prenom", $prenomLbc->getRef_prenom());
        $q->execute();
    }

    public function get($info)
    {
        $q = null;
        if (array_key_exists("moins_utilise", $info)) {
            $q = $this->_db->prepare("select * from prenom_lbc order by nb_use");
        }

        $q->execute();

        $donnees = $q->fetch(\PDO::FETCH_ASSOC);
        if (! $donnees) {
            return false;
        }

        $prenom = new \spamtonprof\stp_api\PrenomLbc($donnees);

        return $prenom;
    }
}
