<?php
namespace spamtonprof\stp_api;

use PDO;

class StpProcheManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpProche $StpProche)
    {
        $q = $this->_db->prepare('insert into stp_proche(email, prenom, nom, telephone, statut_proche) values( :email,:prenom,:nom,:telephone, :statut_proche)');
        $q->bindValue(':email', $StpProche->getEmail());
        $q->bindValue(':prenom', $StpProche->getPrenom());
        $q->bindValue(':nom', $StpProche->getNom());
        $q->bindValue(':telephone', $StpProche->getTelephone());
        $q->bindValue(':statut_proche', $StpProche->getStatut_proche());
        $q->execute();

        $StpProche->setRef_proche($this->_db->lastInsertId());

        return ($StpProche);
    }

    public function updateRefCompteWp(StpProche $proche)
    {
        $q = null;
        if ($_SESSION["prod"]) {

            $q = $this->_db->prepare('update stp_proche set ref_compte_wp = :ref_compte_wp where ref_proche = :ref_proche');
            $q->bindValue(':ref_compte_wp', $proche->getRef_compte_wp());
        }

        $q = $this->_db->prepare('update stp_proche set ref_compte_wp_test = :ref_compte_wp_test where ref_proche = :ref_proche');
        $q->bindValue(':ref_compte_wp_test', $proche->getRef_compte_wp());

        $q->bindValue(':ref_proche', $proche->getRef_proche());
        $q->execute();

        return ($proche);
    }

    public function get($info)
    {
        $data = false;
        if (array_key_exists("email", $info)) {

            $email = $info["email"];

            $q = $this->_db->prepare('select * from stp_proche where lower(email) like lower(:email)');
            $q->bindValue(':email', $email);
            $q->execute();
        } else if (array_key_exists("ref_proche", $info)) {

            $refProche = $info["ref_proche"];

            $q = $this->_db->prepare('select * from stp_proche where ref_proche = :ref_proche');
            $q->bindValue(':ref_proche', $refProche);
            $q->execute();
        } else if (array_key_exists("ref_compte_wp", $info)) {
            $refCompteWp = $info["ref_compte_wp"];

            $q = null;
            if (! $_SESSION["prod"]) {
                $q = $this->_db->prepare('select * from stp_proche where ref_compte_wp_test = :ref_compte_wp_test');
                $q->bindValue(':ref_compte_wp_test', $refCompteWp);
            } else {
                $q = $this->_db->prepare('select * from stp_proche where ref_compte_wp = :ref_compte_wp');
                $q->bindValue(':ref_compte_wp', $refCompteWp);
            }

            $q->execute();
        } else if (array_key_exists("telephone", $info)) {

            $telephone = $info["telephone"];

            $q = $this->_db->prepare("select * from stp_proche where regexp_replace(telephone, '[^01234536789]', '','g') like :telephone");
            $q->bindValue(':telephone', '%' . $telephone . '%');
            $q->execute();
        }

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\StpProche($data));
        } else {
            return (false);
        }
    }

    public function getAll($info)
    {
        $proches = [];
        $q = null;

        if (is_array($info)) {

            if (array_key_exists("email", $info)) {

                $email = $info["email"];

                $q = $this->_db->prepare('select * from stp_proche where email like :email ');
                $q->bindValue(":email", '%' . $email . '%');
                $q->execute();
            } else if (array_key_exists("telephones", $info)) {

                $nums = formatNums($info["telephones"]);

                $nums = toSimilarTo($nums);

                $q = $this->_db->prepare("select * from stp_proche where regexp_replace(telephone, '[^01234536789]', '','g') SIMILAR TO '" . $nums . "'");
                $q->execute();
            }
        }

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $proche = new \spamtonprof\stp_api\StpProche($data);

            $proches[] = $proche;
        }
        return ($proches);
    }
}
