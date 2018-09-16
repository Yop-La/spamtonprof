<?php
namespace spamtonprof\stp_api;

use PDO;

class ClasseManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function getAll()
    {
        $classes = [];
        
        $q = $this->_db->prepare("SELECT ref_classe, classe, classe_complet FROM classe ");
        $q->execute();
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $classe = new Classe($donnees);
            $classes[] = $classe;
        }
        
        return $classes;
    }
}