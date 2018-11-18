<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcTexteManager

{

    private $_db;

    // Instance de PDO
    public function __construct()

    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function getAllType()
    {
        $titleTextes = [];

        $q = $this->_db->prepare("select distinct(type) as type_texte from textes");

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $titleTextes[] = $data['type_texte'];
        }

        return ($titleTextes);
    }

    public function add(\spamtonprof\stp_api\LbcTexte $texte)
    {
        $q = $this->_db->prepare("insert into textes(texte, type) values(:texte, :type)");

        $q->bindValue(":texte", $texte->getTexte());

        $q->bindValue(":type", $texte->getType());

        $q->execute();

        $texte->setRef_texte($this->_db->lastInsertId());

        return ($texte);
    }

    public function deleteAll($info)
    {
        if (array_key_exists("type", $info)) {
            $type = $info["type"];

            $q = $this->_db->prepare("delete from textes where type =:type");

            $q->bindValue(":type", $type);

            $q->execute();
        }
    }

    public function updateAll($info)
    {
        if (array_key_exists("type", $info) && array_key_exists("ref_type_texte", $info)) {

            $type = $info["type"];
            $refTypeTexte = $info["ref_type_texte"];

            $q = $this->_db->prepare("update textes set ref_type_texte = :ref_type_texte where type =:type");

            $q->bindValue(":type", $type);
            $q->bindValue(":ref_type_texte", $refTypeTexte);
        }

        $q->execute();
    }

    /*
     * utiliser après génération automatique des textes pour raccoder textesà type_texte
     */
    public function getDistinctTypeWithoutRefType()
    {
        $types = [];
        $q = $this->_db->prepare("select distinct (type) as type from textes where ref_type_texte is null;");
        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $types[] = $data['type'];
        }
        return ($types);
    }

    public function getAll($info)
    {
        $textes = [];
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("type_texte", $info) && ! array_key_exists("limit", $info)) {
                $texteType = $info["type_texte"];
                $q = $this->_db->prepare("select * from textes where type = :type_texte order by ref_texte desc");
                $q->bindValue(":type_texte", $texteType);
            }
            if (array_key_exists("ref_type_texte", $info) && ! array_key_exists("limit", $info)) {
                $refTexteType = $info["ref_type_texte"];
                $q = $this->_db->prepare("select * from textes where ref_type_texte = :ref_type_texte order by ref_texte desc");
                $q->bindValue(":ref_type_texte", $refTexteType);
            }

            if (array_key_exists("ref_type_texte", $info) && array_key_exists("limit", $info)) {
                $refTexteType = $info["ref_type_texte"];
                $limit = $info["limit"];
                $q = $this->_db->prepare("select * from textes where ref_type_texte = :ref_type_texte limit :limit");
                $q->bindValue(":ref_type_texte", $refTexteType);
                $q->bindValue(":limit", $limit);
            }
        }
        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $textes[] = new \spamtonprof\stp_api\LbcTexte($data);
        }

        return ($textes);
    }

    public function exist($texteType)
    {
        $q = $this->_db->prepare("select count(*) as exist from textes where type = :type_texte ");

        $q->bindValue(":type_texte", $texteType);

        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        $exist = $data['exist'];

        if ($exist == 0) {
            return (false);
        } else {
            return (true);
        }
    }

    public function addPhoneLine($textes, $phone)
    {
        $phoneStringMg = new \spamtonprof\stp_api\PhoneStringManager();
        $phoneStrings = $phoneStringMg->getAll();
        $nbPhoneStrings = count($phoneStrings);

        $indexAd = 0;
        foreach ($textes as $texte) {

            $phoneString = $phoneStrings[$indexAd % $nbPhoneStrings];
            $phoneString = str_replace("[num-tel]", $phone, $phoneString->getPhone_string());

            $texte->setTexte($phoneString . "\r\n\r\n" . $texte->getTexte());
            $indexAd ++;
        }
        return ($textes);
    }
}