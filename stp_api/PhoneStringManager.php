<?php
namespace spamtonprof\stp_api;

class PhoneStringManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(PhoneString $phoneString)
    {
        $q = $this->_db->prepare('insert into phone_string(phone_string) values( :phone_string)');
        $q->bindValue(':phone_string', $phoneString->getPhone_string());
        $q->execute();

        $phoneString->setRef_phone_string($this->_db->lastInsertId());

        return ($phoneString);
    }

    public function getAll()
    {
        $q = $this->_db->prepare('select * from phone_string');
        $q->execute();

        $phoneStrings = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $phoneStrings[] = new \spamtonprof\stp_api\PhoneString($data);
        }
        return ($phoneStrings);
    }
}
