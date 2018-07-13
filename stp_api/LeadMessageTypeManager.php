<?php
namespace spamtonprof\stp_api;

use PDO;
use spamtonprof;

class leadMessageTypeManager
{

    const MESSAGE_DIRECT = 1, DEBUT_MESSAGERIE_LEBONCOIN = 2, CONVERSATION_MESSAGERIE_LEBONCOIN = 3;

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function get($info)
    {
        $q;
        if (is_int($info)) {
            $q = $this->_db->prepare("select * from lead_message_type where ref_type = :ref_type");
            
            $q->bindValue(":ref_type", $info);
        } else {
            
            $q = $this->_db->prepare("select * from lead_message_type where type = :type");
            
            $q->bindValue(":type", $info);
        }
        
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return new spamtonprof\stp_api\leadMessageType($data);
        } else {
            return (false);
        }
    }
}
