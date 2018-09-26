<?php
namespace spamtonprof\stp_api;

class StpAppelManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpAppel $stpAppel)
    {
        $q = $this->_db->prepare('insert into stp_appel("to", "from", date) values(:to,:from,:date)');
        $q->bindValue(':to', $stpAppel->getTo());
        $q->bindValue(':from', $stpAppel->getFrom());
        $q->bindValue(':date', $stpAppel->getDate());
        $q->execute();
        $stpAppel->setRef_appel($this->_db->lastInsertId());
        return ($stpAppel);
    }
    
    public function getAll($info)
    {
        $appels = [];
        $q = null;
        if(array_key_exists("from", $info)){
            $from = $info["from"];
            $q = $this->_db->prepare('select * from stp_appel where "from" like :from');
            $q->bindValue(":from", $from);
            
        }
        $q->execute();
        
        while($data = $q->fetch(\PDO::FETCH_ASSOC)){
            
            $appels[] = new \spamtonprof\stp_api\StpAppel($data);
            
        }
        return($appels);

    }
}
