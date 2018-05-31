<?php
namespace spamtonprof\stp_api;

use PDO;

class EmailManager

{
    
    const lbcType2 = "lbcType2", lbcType1 = "lbcType1" ; // constante pour identifier les différentes types d'email de prospect

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    /**
     *
     * @param mixed $info
     *            : ref_campagne ou nom campagne
     * @return boolean|\spamtonprof\stp_api\Campaign
     */
    public function add(\spamtonprof\stp_api\Email $mail)
    {
        $q = $this->_db->prepare('insert into mail_eleve(ref_gmail, date_reception, mail_expe, ref_compte, history_id)
                                      values(:ref_gmail, :date_reception, :mail_expe, :ref_compte, :history_id)');
        $q->bindValue(':ref_gmail', $mail->getRef_gmail());
        $q->bindValue(':mail_expe', $mail->getMail_expe());
        $q->bindValue(':date_reception', $mail->getDate_reception()
            ->format(PG_DATETIME_FORMAT));
        
        $q->bindValue(':ref_compte', $mail->getRef_compte());
        $q->bindValue(':history_id', $mail->getHistory_id(),PDO::PARAM_INT);
        
        $q->execute();
        
        $mail->setRef_mail($this->_db->lastInsertId());
        
        return ($mail);
    }

    public function getLastEmail()
    {
        
        $q = $this->_db->prepare("SELECT * FROM mail_eleve where history_id is not null order by history_id desc limit 1 ");
        $q->execute();
        
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
        $mailEleve = new Email($donnees);
        
        return $mailEleve;
        
    }

    public function getList($info)
    {
        $mailEleves = [];
        $q;
        
        if (array_key_exists("week", $info)) {
            
            $week = $info["week"];
            
            $q = $this->_db->prepare("SELECT * FROM mail_eleve where EXTRACT(WEEK FROM date_reception ) = :week");
            $q->bindValue(':week', $week, PDO::PARAM_INT);
            $q->execute();
        }
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $mailEleve = new Email($donnees);
            $mailEleves[] = $mailEleve;
        }
        
        return $mailEleves;
    }
    
    public function get($info)
    {
        
        $mailEleve = false;
        $q;
        
        if(array_key_exists("ref_gmail", $info)){
            
            $refGmail = $info["ref_gmail"];
            
            $q = $this->_db->prepare("SELECT * FROM mail_eleve where ref_gmail = :ref_gmail");
            $q->bindValue(':ref_gmail', $refGmail, PDO::PARAM_STR);
            $q->execute();
        }
        
        if($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $mailEleve = new Email($donnees);
            return $mailEleve;
        }else{
            return(false);
        }
        
    }
    
    public function updateHistoryId(\spamtonprof\stp_api\Email $email){
                

        $q = $this->_db->prepare("update mail_eleve set history_id = :history_id where ref_gmail =:ref_gmail");
        $q->bindValue(':history_id', $email->getHistory_id(), PDO::PARAM_INT);
        $q->bindValue(':ref_gmail', $email->getRef_gmail() ,PDO::PARAM_STR);
        $q->execute();
        
    }
    
    
    
}