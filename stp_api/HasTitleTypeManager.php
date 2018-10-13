<?php
namespace spamtonprof\stp_api;

class HasTitleTypeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(HasTitleType $hasTitleType)
    {
        $q = $this->_db->prepare('insert into has_title_type(ref_client, ref_type_titre) values( :ref_client,:ref_type_titre)');
        $q->bindValue(':ref_client', $hasTitleType->getRef_client());
        $q->bindValue(':ref_type_titre', $hasTitleType->getRef_type_titre());
        $q->execute();

        $hasTitleType->setRef_has_title_type($this->_db->lastInsertId());
        return ($hasTitleType);
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("ref_client_defaut", $info)) {
                $refClient = $info["ref_client_defaut"];

                $q = $this->_db->prepare("select * from has_title_type where ref_client = :ref_client and defaut = true");
                $q->bindValue(":ref_client", $refClient);
            }
        }
        $q->execute();
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            return new \spamtonprof\stp_api\HasTitleType($data);
        }
    }
}
