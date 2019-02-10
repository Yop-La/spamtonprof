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

    public function update_all_last_used($info)
    {
        if (array_key_exists("ref_client", $info) && array_key_exists("last_used", $info)) {

            $ref_client = $info["ref_client"];
            $last_used = $info["last_used"];

            $q = $this->_db->prepare("update has_text_type set last_used = :last_used where ref_client = :ref_client");

            $q->bindValue(":ref_client", $ref_client);
            $q->bindValue(":last_used", $last_used, \PDO::PARAM_BOOL);
        }
        $q->execute();
    }

    /*
     * retourne la prochaine cat de texte à utilisé ( ie celle situté après celle utilisé )
     */
    
    public function get_next($ref_client)
    {
        $hasTypeTextes = $this->getAll(array(
            'ref_client' => $ref_client
        ));

        $hasTypeTexte = $hasTypeTextes[0];

        $nbCatTexts = count($hasTypeTextes);

        for ($i = 0; $i < $nbCatTexts; $i ++) {

            $hasTypeTexteCourant = $hasTypeTextes[$i];
            $last_used = $hasTypeTexteCourant->getLast_used();

            if ($i != $nbCatTexts - 1 && $last_used) {
                $hasTypeTexte = $hasTypeTextes[$i + 1];
            }
        }

        $this->update_all_last_used(array(
            "ref_client" => $ref_client,
            'last_used' => false
        ));

        $hasTypeTexte->setLast_used(true);
        $this->update_last_used($hasTypeTexte);
        
        return($hasTypeTexte);
    }

    public function update_last_used(\spamtonprof\stp_api\HasTextType $hasTextType)
    {
        $q = $this->_db->prepare("update has_text_type set last_used = :last_used where ref_has_text_type = :ref_has_text_type");

        $q->bindValue(":last_used", $hasTextType->getLast_used(), \PDO::PARAM_BOOL);
        $q->bindValue(":ref_has_text_type", $hasTextType->getRef_has_text_type());
        $q->execute();
    }

    public function getAll($info)
    {
        $hasTextsType = [];
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("ref_client", $info)) {

                $ref_client = $info["ref_client"];
                $q = $this->_db->prepare("select * from has_text_type where ref_client = :ref_client order by ref_type ");
                $q->bindValue(":ref_client", $ref_client);
            }
        }
        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $hasTextsType[] = new \spamtonprof\stp_api\HasTextType($data);
        }

        return ($hasTextsType);
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
