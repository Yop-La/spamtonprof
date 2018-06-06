<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcTexteManager

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
        $titleTextes = [];
        
        $q = $this->_db->prepare("select distinct(type) as type_texte from textes");
        
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $titleTextes[] = $data['type_texte'];
        }
        
        return ($titleTextes);
    }

    public function add(\spamtonprof\stp_api\LbcTexte $texte)
    {
        $q = $this->_db->prepare("insert into textes(texte, type) values(:texte, :type)");
        
        $q->bindValue(":texte", $texte->getTexte());
        
        $q->bindValue(":type", $texte->getType());
        
        $q->execute();
        
        $texte->setRef_texte($this->_db->lastInsertId());
        
        return ($texte);
    }

    public function deleteAll($info)
    {
        if (array_key_exists("type", $info)) {
            $type = $info["type"];
            
            $q = $this->_db->prepare("delete from textes where type =:type");
            
            $q->bindValue(":type", $type);
            
            $q->execute();
        }
    }

    public function getAll($texteType)
    {
        $textes = [];
        
        $q = $this->_db->prepare("select * from textes where type = :type_texte order by ref_texte desc");
        
        $q->bindValue(":type_texte", $texteType);
        
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $textes[] = new \spamtonprof\stp_api\LbcTexte($data);
        }
        
        if (empty($textes)) {
            return (false);
        }
        
        return ($textes);
    }
}