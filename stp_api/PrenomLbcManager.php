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
        $q = $this->_db->prepare('insert into prenom_lbc(cat,prenom, nb_use) values(:cat,:prenom,0)');

        $q->bindValue(':prenom', $prenomLbc->getPrenom());
        $q->bindValue(':cat', $prenomLbc->getCat());
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
        if (array_key_exists("moins_utilise", $info) && array_key_exists("ref_cat_prenom", $info)) {

            $ref_cat_prenom = $info["ref_cat_prenom"];


            $q = $this->_db->prepare("select * from prenom_lbc 
                where cat = :ref_cat_prenom
                order by nb_use ");

            $q->bindValue(":ref_cat_prenom", $ref_cat_prenom);
        }

        $q->execute();

        $donnees = $q->fetch(\PDO::FETCH_ASSOC);
        if (! $donnees) {
            return false;
        }

        $prenom = new \spamtonprof\stp_api\PrenomLbc($donnees);

        return $prenom;
    }

    public function getAllCat()
    {
        $cats = [];
        $q = null;
        $q = $this->_db->prepare("select distinct(cat) as categorie_prenom from prenom_lbc");
        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $cats[] = $data['categorie_prenom'];
        }

        return ($cats);
    }

    public function getAll($info = FALSE)
    {
        $prenoms = [];
        $q = null;
        if (is_array($info)) {

            if (array_key_exists('ref_cat_prenom', $info)) {
                $cat = $info['ref_cat_prenom'];
                $q = $this->_db->prepare("select * from prenom_lbc where cat = :cat");
                $q->bindValue(':cat', $cat);
            }
        } else {
            $q = $this->_db->prepare("select * from prenom_lbc");
        }
        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $prenoms[] = new \spamtonprof\stp_api\PrenomLbc($data);
        }

        return ($prenoms);
    }
}
