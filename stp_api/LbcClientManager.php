<?php
namespace spamtonprof\stp_api;

class LbcClientManager
{

    private $_db;

    const CANNELLE = 19, LUCAS = 20, THOMAS = 12, CAMILLA = 17, SEB = 11;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(LbcClient $client)
    {
        $q = $this->_db->prepare('insert into client(nom_client, prenom_client, domain, img_folder) values(:nom_client, :prenom_client, :domain, :img_folder)');

        $q->bindValue(':nom_client', $client->getNom_client());
        $q->bindValue(':prenom_client', $client->getPrenom_client());
        $q->bindValue(':domain', $client->getDomain());
        $q->bindValue(':img_folder', $client->getImg_folder());
        $q->execute();

        $client->setRef_client($this->_db->lastInsertId());

        return ($client);
    }

    public function get($info)
    {
        $q = null;
        if (array_key_exists("ref_client", $info)) {
            $refClient = $info["ref_client"];
            $q = $this->_db->prepare("select * from client where ref_client = :ref_client");
            $q->execute(array(
                "ref_client" => $refClient
            ));
        }

        $donnees = $q->fetch(\PDO::FETCH_ASSOC);
        if (! $donnees) {
            return false;
        }

        $client = new \spamtonprof\stp_api\LbcClient($donnees);

        return $client;
    }

    public function getAll($info)
    {
        $clients = [];
        $q = null;
        if (in_array('all', $info)) {

            $q = $this->_db->prepare("select * from client");
        } else if (in_array('with_ref_cat_prenom', $info)) {

            $q = $this->_db->prepare("select * from client where ref_cat_prenom is not null");
        }

        $q->execute();

        while ($donnees = $q->fetch(\PDO::FETCH_ASSOC)) {

            $client = new \spamtonprof\stp_api\LbcClient($donnees);
            $clients[] = $client;
        }
        return ($clients);
    }

    public function deleteAll($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("ref_client", $info)) {
                $refClient = $info["ref_client"];

                $q = $this->_db->prepare("delete from client where ref_client = :ref_client;");
                $q->bindValue(":ref_client", $refClient);
            }
        }
        $q->execute();
    }

    public function updateNom(\spamtonprof\stp_api\LbcClient $client)
    {
        $q = $this->_db->prepare("update client set nom_client = :nom_client where ref_client = :ref_client");
        $q->bindValue(":nom_client", $client->getNom_client());
        $q->bindValue(":ref_client", $client->getRef_client());
        $q->execute();
    }

    public function updateDomain(\spamtonprof\stp_api\LbcClient $client)
    {
        $q = $this->_db->prepare("update client set domain = :domain where ref_client = :ref_client");
        $q->bindValue(":domain", $client->getDomain());
        $q->bindValue(":ref_client", $client->getRef_client());
        $q->execute();
    }

    public function updateImgFolder(\spamtonprof\stp_api\LbcClient $client)
    {
        $q = $this->_db->prepare("update client set img_folder = :img_folder where ref_client = :ref_client");
        $q->bindValue(":img_folder", $client->getImg_folder());
        $q->bindValue(":ref_client", $client->getRef_client());
        $q->execute();
    }

    public function updateRefReponseLbc(\spamtonprof\stp_api\LbcClient $client)
    {
        $q = $this->_db->prepare("update client set ref_reponse_lbc = :ref_reponse_lbc where ref_client = :ref_client");
        $q->bindValue(":ref_reponse_lbc", $client->getRef_reponse_lbc());
        $q->bindValue(":ref_client", $client->getRef_client());
        $q->execute();
    }
}
