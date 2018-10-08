<?php
namespace spamtonprof\stp_api;

class LbcClientManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    // public function add(client $client)
    // {
    // $q = $this->_db->prepare('insert into client(ref_client, nom_client, prenom_client, domain) values( :ref_client,:nom_client,:prenom_client,:domain)');
    // $q->bindValue(':ref_client', $client->getRef_client());
    // $q->bindValue(':nom_client', $client->getNom_client());
    // $q->bindValue(':prenom_client', $client->getPrenom_client());
    // $q->bindValue(':domain', $client->getDomain());
    // $q->execute();
    // // ----------------- à finir ----------------
    // // -----------------
    // $client->setRef_($this->_db->lastInsertId());
    // // ----------------- à finir ----------------
    // // -----------------
    // return ($client);
    // }
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
}
