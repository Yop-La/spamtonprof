<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcParagraphMg

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    public function add(\spamtonprof\stp_api\LbcParagraph $para){
        
        $q = $this->_db->prepare("insert into lbc_paragraph( position, ref_text ,  paragraph  ) values ( :position, :ref_text ,  :paragraph  )");
        $q -> bindValue(':position', $para->getPosition());
        $q -> bindValue(':ref_text', $para->getRef_texte());
        $q -> bindValue(':paragraph', $para->getParagraph());
        $q->execute();
        
        $para->setRef_para($this->_db->lastInsertId());
        
        return($para);
        
    }
    
    public function getAll($info){
        $paras = [];
        $q=null;
        if(array_key_exists('ref_text', $info)){
            $refText = $info['ref_text'];
            $q = $this->_db->prepare("select * from lbc_paragraph where ref_text = :ref_text order by position");
            $q->bindValue(':ref_text', $refText);

            
        }
        $q->execute();
        while($data = $q->fetch(PDO::FETCH_ASSOC)){
            
            $para = new \spamtonprof\stp_api\LbcParagraph($data);
            $paras[] = $para;
        }
        if(count($paras) == 0){
            return false;
        }
        return($paras);

    }
    
    public function updateParagraph(\spamtonprof\stp_api\LbcParagraph $para){

            $q = $this->_db->prepare("update lbc_paragraph set paragraph = :paragraph where ref_para = :ref_para");
            $q->bindValue(':paragraph', $para->getParagraph());
            $q->bindValue(':ref_para', $para->getRef_para());
            $q->execute();
        
    }
    
    

}