<?php
namespace spamtonprof\stp_api;

class HasTextTypeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(HasTextType $hasTextType)
    {
        $q = $this->_db->prepare('insert into has_text_type(ref_type, ref_client, defaut) values(:ref_type,:ref_client,true)');
        $q->bindValue(':ref_type', $hasTextType->getRef_type());
        $q->bindValue(':ref_client', $hasTextType->getRef_client());
        $q->execute();

        $hasTextType->setRef_has_text_type($this->_db->lastInsertId());
        return ($hasTextType);
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("ref_client_defaut", $info)) {
                $refClient = $info["ref_client_defaut"];

                $q = $this->_db->prepare("select * from has_text_type where ref_client = :ref_client and defaut = true");
                $q->bindValue(":ref_client", $refClient);
            }
        }
        $q->execute();
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            return new \spamtonprof\stp_api\HasTextType($data);
        } else {
            return (false);
        }
    }

    public function deleteAll($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("ref_client", $info)) {
                $refClient = $info["ref_client"];

                $q = $this->_db->prepare("delete from has_text_type where ref_client = :ref_client;");
                $q->bindValue(":ref_client", $refClient);
            }
        }
        $q->execute();
    }
}
