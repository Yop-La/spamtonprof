<?php
namespace spamtonprof\stp_api;

use PDO;

class LbcAccountManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    /**
     * fonction qui renvoie tous les comptes actif sur lesquels des annonces ont été publiées durant
     * les 10 dernières heures
     */
    public function getAccountToCheck($nbHours)
    {
        $accounts = [];
        $nbHours = $nbHours . " hours";
        
        $q = $this->_db->prepare("select distinct(adds_lbc.ref_compte) as ref_compte from adds_lbc, compte_lbc 
            where (date_publication ) < (  NOW() - INTERVAL '" . $nbHours . "' ) and disabled is null
            and adds_lbc.ref_compte = compte_lbc.ref_compte");
        $q->execute();
        
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
        
        if (! $donnees) {
            return false;
        }
        
        while ($donnees) {
            
            $accounts[] = $this->get(array(
                "ref_compte" => $donnees["ref_compte"]
            ));
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        return $accounts;
    }

    public function get($info)
    {
        $donnees = false;
        if (array_key_exists("ref_compte", $info)) {
            $refCompte = $info["ref_compte"];
            $q = $this->_db->prepare("select * from compte_lbc where ref_compte = :ref_compte");
            $q->execute(array(
                "ref_compte" => $refCompte
            ));
            
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        if (array_key_exists("mail", $info)) {
            
            $mail = $info["mail"];
            $mail = trim($mail);
            $q = $this->_db->prepare("select * from compte_lbc where mail = :mail");
            $q->execute(array(
                "mail" => $mail
            ));
            
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        if (! $donnees) {
            return false;
        }
        
        $account = new \spamtonprof\stp_api\LbcAccount($donnees);
        
        return $account;
    }

    public function getAll()
    {
        $accounts = [];
        
        $q = $this->_db->prepare("select * from compte_lbc");
        $q->execute();
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $account = new \spamtonprof\stp_api\LbcAccount($donnees);
            $accounts[] = $account;
        }
        return($accounts);
        
    }

    public function updateDisabled(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set disabled = :disabled where ref_compte = :ref_compte");
        $q->bindValue(":disabled", $lbcAccount->getDisabled(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function updateRefExpe(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set ref_expe = :ref_expe where ref_compte = :ref_compte");
        $q->bindValue(":ref_expe", $lbcAccount->getRef_expe());
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }
    
    public function updateCodePromo(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set code_promo = :code_promo where ref_compte = :ref_compte");
        $q->bindValue(":code_promo", $lbcAccount->getCode_promo());
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }
    
    public function getAccountToScrap(){
        
        $accounts = [];
        
        $q = $this->_db->prepare("
            select distinct(ref_compte) as ref_compte
            from adds_lbc 
                where DATE_PART('day',  now() -  date_publication)  * 24 + DATE_PART('hour', now() -  date_publication ) >= 0
                    and date_publication >= '2018-05-30'
                    and etat = 'enAttenteModeration'
             order by ref_compte");

        $q->execute();
        
        while($data = $q-> fetch(PDO::FETCH_ASSOC)){
            $accounts[] = $this->get(array("ref_compte" => $data["ref_compte"]));
        }
        
        if(count($accounts) == 0){
            return(false);
        }else{
            return($accounts);
        }
        
        
    }
    


}