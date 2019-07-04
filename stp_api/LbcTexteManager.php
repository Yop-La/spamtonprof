<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcTexteManager

{

    private $_db;
    
    const texte_not_in_that_act = 1;
    

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

    function addAll($categorie, $nbTextes = 50)
    {
        $typeTexteMg = new \spamtonprof\stp_api\TypeTexteManager();
        $typeTexte = $typeTexteMg->add(new \spamtonprof\stp_api\TypeTexte(array(
            'type' => $categorie
        )));

        for ($i = 0; $i < $nbTextes; $i ++) {

            $txt = $this->get(array(
                'type' => "reponse_lbc_general"
            ));

            $this->add(new \spamtonprof\stp_api\LbcTexte(array(
                "texte" => $txt->getTexte() . 'not_valid ' . $categorie . ' ' . $i,
                "type" => $typeTexte->getType(),
                "ref_type_texte" => $typeTexte->getRef_type()
            )));
        }
    }

    public function add(\spamtonprof\stp_api\LbcTexte $texte)
    {
        $q = $this->_db->prepare("insert into textes(texte, type,ref_type_texte) values(:texte, :type,:ref_type_texte)");

        $q->bindValue(":texte", $texte->getTexte());

        $q->bindValue(":type", $texte->getType());

        $q->bindValue(":ref_type_texte", $texte->getRef_type_texte());

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

    public function updateNbOnline(\spamtonprof\stp_api\LbcTexte $texte)
    {
        $q = $this->_db->prepare("update textes set nb_online = :nb_online where ref_texte =:ref_texte");

        $q->bindValue(":ref_texte", $texte->getNb_online());
        $q->bindValue(":texte", $texte->getTexte());
        $q->execute();
    }

    public function updateTexte(\spamtonprof\stp_api\LbcTexte $texte)
    {
        $q = $this->_db->prepare("update textes set texte = :texte where ref_texte =:ref_texte");

        $q->bindValue(":ref_texte", $texte->getRef_texte());
        $q->bindValue(":texte", $texte->getTexte());
        $q->execute();
    }

    /*
     * utiliser apr�s g�n�ration automatique des textes pour raccoder textes� type_texte
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

    public function count($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("type", $info)) {
                $type = $info["type"];

                $q = $this->_db->prepare("select count(*) as nb_txt from textes
                            where type like :type and texte not like '%not_valid%'");
                $q->bindValue(":type", $type);
            }
        }
        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return ($data["nb_txt"]);
        } else {
            return (false);
        }
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists("type_random", $info)) {
                $type = $info["type_random"];

                $q = $this->_db->prepare("select * from textes 
                            where type like :type and texte not like '%not_valid%' ORDER BY random() limit 1;");
                $q->bindValue(":type", $type);
            } else if (array_key_exists("type", $info)) {
                $type = $info["type"];

                $q = $this->_db->prepare("select * from textes
                            where type like :type limit 1");
                $q->bindValue(":type", $type);
            }
        }
        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\LbcTexte($data));
        } else {
            return (false);
        }
    }

    public function getAll($info)
    {
        $textes = [];
        $q = null;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                
                $key = $info["key"];
                
                if($key == $this::texte_not_in_that_act){
                    
                    $ref_type_texte = $info["ref_type_texte"];
                    $ref_compte = $info["ref_compte"];
                    $q = $this->_db->prepare("select * from textes where ref_type_texte = :ref_type_texte
                        and ref_texte not in (select ref_texte from adds_tempo where ref_compte = :ref_compte)
                        order by ref_texte desc");
                    
                    $q->bindValue(":ref_type_texte", $ref_type_texte);
                    $q->bindValue(":ref_compte", $ref_compte);
                    
                }
                
                
            } else {

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

                if (array_key_exists("ref_type_texte.valid", $info) && ! array_key_exists("limit", $info)) {
                    $refTexteType = $info["ref_type_texte.valid"];
                    $q = $this->_db->prepare("select * from textes where ref_type_texte = :ref_type_texte and texte not like '%not_valid%' order by ref_texte desc");
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