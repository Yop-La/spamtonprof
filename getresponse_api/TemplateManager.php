<?php
namespace spamtonprof\stp_api;

class LbcAccountManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();

    }

}