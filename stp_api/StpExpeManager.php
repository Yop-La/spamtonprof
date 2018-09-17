<?php
namespace spamtonprof\stp_api;

class StpExpeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function get($info)
    {
        $email = $info;
        
        $q = $this->_db->prepare('select * from stp_expe where email like :email');
        $q->bindValue(':email', $email);
        $q->execute();
        
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            
            $expe = new \spamtonprof\stp_api\StpExpe($data);
            return ($expe);
        } else {
            return (false);
        }
    }
}
