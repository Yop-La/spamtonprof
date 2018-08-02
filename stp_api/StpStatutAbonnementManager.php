<?php
namespace spamtonprof\stp_api;

class stpStatutAbonnementManager
{

    const ACTIF = 1, ESSAI = 2, TERMINE = 3;
    
    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    
}
