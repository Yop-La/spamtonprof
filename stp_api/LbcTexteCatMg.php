<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcTexteCatMg

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function add(\spamtonprof\stp_api\LbcTexteCat $texteCat)
    {
        $q = $this->_db->prepare("insert into lbc_texte_categorie ( nom_cat, nb_paragraph, nb_texte ) values (:nom_cat, :nb_paragraph, :nb_texte )");
        $q->bindValue(':nom_cat', $texteCat->getNom_cat());
        $q->bindValue(":nb_paragraph", $texteCat->getNb_paragraph());
        $q->bindValue(":nb_texte", $texteCat->getNb_texte());
        $q->execute();
        
        $texteCat->setRef_texte_cat($this->_db->lastInsertId());
        
        return ($texteCat);
    }

    public function get($info)
    {
        $data = false;
        
        if (array_key_exists("nom_cat", $info)) {
            $nomCat = $info["nom_cat"];
            $q = $this->_db->prepare("select * from lbc_texte_categorie where nom_cat = :nom_cat");
            $q->bindValue(':nom_cat', $nomCat);
            $q->execute();
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        if (array_key_exists("ref_texte_cat", $info)) {
            $ref = $info["ref_texte_cat"];
            $q = $this->_db->prepare("select * from lbc_texte_categorie where ref_texte_cat = :ref_texte_cat");
            $q->bindValue(':ref_texte_cat', $ref);
            $q->execute();
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($data) {
            $texteCat = new \spamtonprof\stp_api\LbcTexteCat($data);
            return ($texteCat);
        } else {
            return (false);
        }
    }

    public function getAll()
    {
        $texteCats = [];
        
        $q = $this->_db->prepare("select * from lbc_texte_categorie");
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $texteCats[] = new \spamtonprof\stp_api\LbcTexteCat($data);
        }
        
        if (count($texteCats) == 0) {
            return (false);
        } else {
            return ($texteCats);
        }
    }
}