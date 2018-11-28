<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcTitleManager

{

    private $_db;

    // Instance de PDO
    public function __construct()

    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function getAllType()
    {
        $titleTypes = [];

        $q = $this->_db->prepare("select distinct(type_titre) as type_titre from titres");

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $titleTypes[] = $data['type_titre'];
        }

        return ($titleTypes);
    }

    
    /* utiliser après insertion des titres dans la table titres pour raccoder titres à type_titre
     */
    public function getDistinctTypeWithoutRefType()
    {
        $types = [];
        $q = $this->_db->prepare("select distinct(type_titre) as type from titres where ref_type_titre is null;");
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $types[] = $data['type'];
        }
        return ($types);
    }
    
    public function add(LbcTitle $titre)
    {
        $q = $this->_db->prepare('insert into titres(titre, type_titre, ref_type_titre) values(:titre, :type_titre, :ref_type_titre)');
        $q->bindValue(':titre', $titre->getTitre());
        $q->bindValue(':type_titre', $titre->getType_titre());
        $q->bindValue(':ref_type_titre', $titre->getRef_type_titre());
        $q->execute();
        $titre->setRef_titre($this->_db->lastInsertId());
        
        return ($titre);
    }
    
    public function getAll($info)
    {
        $titles = [];
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("type_titre", $info)) {
                $titleType = $info["type_titre"];
                $q = $this->_db->prepare("select * from titres where type_titre = :type_titre");

                $q->bindValue(":type_titre", $titleType);
            }
            if (array_key_exists("ref_type_titre", $info)) {
;
                $refType = $info["ref_type_titre"];
                $q = $this->_db->prepare("select * from titres where ref_type_titre = :ref_type_titre");
                $q->bindValue(":ref_type_titre", $refType);
            }
        }
        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $titles[] = new \spamtonprof\stp_api\LbcTitle($data);
        }


        return ($titles);
    }
}