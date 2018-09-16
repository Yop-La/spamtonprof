<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcBaseTextMg

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function add(\spamtonprof\stp_api\LbcBaseText $text)
    {
        $q = $this->_db->prepare("insert into lbc_text( ref_text_cat) values ( :ref_text_cat  )");
        $q->bindValue(':ref_text_cat', $text->getRef_text_cat());
        $q->execute();
        
        $text->setRef_text($this->_db->lastInsertId());
        
        return ($text);
    }

    public function getTextsByParagraphs($info)
    {
        if (array_key_exists("ref_text_cat", $info)) {
            
            $ref_text_cat = $info['ref_text_cat'];
            
            $lbcTextCatMg = new \spamtonprof\stp_api\LbcTexteCatMg();
            
            $textCat = $lbcTextCatMg -> get(array("ref_texte_cat" => $ref_text_cat));
            $nbPara = $textCat->getNb_paragraph();
            
            $q = $this->_db->prepare("select * from lbc_paragraph, lbc_text
                where lbc_paragraph.ref_text = lbc_text.ref_text
                    and ref_text_cat = :ref_text_cat
                    order by lbc_text.ref_text, lbc_paragraph.position ");
            
            $q->bindValue(':ref_text_cat', $ref_text_cat);
            $q->execute();
            $textContent = "";
            
            $textes = [];
            $paras = [];
            
            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
                
                $position = $data["position"];
                $paragraph = $data["paragraph"];
                $refText = $data["ref_text"];
                
                $paras[] = $paragraph;
                
                if($position == ($nbPara-1)){
                    
                    $textes[$refText] = $paras;
                    $paras = [];
                    
                }
                
            }
            
            if (count($textes) == 0) {
                return (false);
            }
            
            return ($textes);
        }
    }
    
    public function delete(int $refText){
        
        $q = $this->_db->prepare("delete from lbc_paragraph where ref_text = :ref_texte");
        $q->bindValue(":ref_texte", $refText);
        $q->execute();
        
        $q = $this->_db->prepare("delete from lbc_text where ref_text = :ref_texte");
        $q->bindValue(":ref_texte", $refText);
        $q->execute();
        
    }
    
    public function count(string $nomCatLoaded){
        
        $lbcCatMg = new \spamtonprof\stp_api\LbcTexteCatMg();
        
        $lbcCat = $lbcCatMg->get(array("nom_cat" => $nomCatLoaded));
        
        $q = $this->_db->prepare("select count(*) as nb from lbc_text where ref_text_cat = :ref_text_cat");
        $q->bindValue(":ref_text_cat", $lbcCat->getRef_texte_cat());
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if($data){
            return($data["nb"]);
        }else{
            return 0;
        }
        
    }
    
    
    
    
}