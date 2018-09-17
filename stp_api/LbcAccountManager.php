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

    public function getAll($info = false)
    {
        $accounts = [];
        $q = null;
        if ($info == "lastTwentyForReportingLbcIndex") {
            
            $q = $this->_db->prepare("select prenom_client, nom_client, ref_compte, code_promo, controle_date, nb_annonces_online
                from compte_lbc, client where compte_lbc.ref_client = client.ref_client  order by ref_compte desc limit 20");
            $q->execute();
        } else if (! $info) {
            
            $q = $this->_db->prepare("select * from compte_lbc");
            $q->execute();
        }else if("forReportingLbcIndex") {
            
            
            $q = $this->_db->prepare("select prenom_client, nom_client, ref_compte, code_promo, controle_date, nb_annonces_online
                from compte_lbc, client where compte_lbc.ref_client = client.ref_client");
            $q->execute();
            
            
        }
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $account = new \spamtonprof\stp_api\LbcAccount($donnees);
            $accounts[] = $account;
        }
        return ($accounts);
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

    public function getAccountToScrap($nbCompte)
    {
        $accounts = [];
        
        $q = $this->_db->prepare("select * from compte_lbc where disabled = false or disabled is null  order by ref_compte desc limit :nb_compte");
        $q->bindValue(":nb_compte", $nbCompte);
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $accounts[] = new \spamtonprof\stp_api\LbcAccount($data);
        }
        
        return ($accounts);
    }

    public function desactivateDeadAccounts()
    {
        $q1 = $this->_db->prepare("select * from compte_lbc where code_promo is null and (disabled is null or disabled = false)");
        $q1->execute();
        
        $refComptes = [];
        while ($data = $q1->fetch(PDO::FETCH_ASSOC)) {
            
            $refComptes[] = $data["ref_compte"];
        }
        
        $in = "(" . join(',', array_fill(0, count($refComptes), '?')) . ")";
        
        $q2 = $this->_db->prepare("update compte_lbc set disabled = true where ref_compte in " . $in);
        $q2->execute($refComptes);
        
        $q3 = $this->_db->prepare("delete from adds_lbc where ref_compte in " . $in);
        $q3->execute($refComptes);
    }

    public function updateAfterScraping(array $rows)
    {
        $nbTot = 0;
        $refComptes = [];
        foreach ($rows as $row) {
            
            $cols = explode(";", $row);
            $refCompte = $cols[0];
            $nbAnnonces = $cols[2];
            $nbTot = $nbTot + intval($nbAnnonces);
            
            $disabled = false;
            
            if ($nbAnnonces <= 10) {
                $disabled = true;
                $refComptes[] = $refCompte;
            }
            
            $now = new \DateTime(null, new  \DateTimeZone("Europe/Paris"));
            
            
            $q1 = $this->_db->prepare("update compte_lbc set controle_date = :controle_date, disabled = :disabled, nb_annonces_online = :nb_annonces_online where ref_compte= :ref_compte");
            $q1->bindValue(":ref_compte", $refCompte);
            $q1->bindValue(":disabled", $disabled, PDO::PARAM_BOOL);
            $q1->bindValue(":nb_annonces_online", $nbAnnonces);
            $q1->bindValue(":controle_date", $now->format(PG_DATETIME_FORMAT));
            
            $q1->execute();
        }
        
        $in = "(" . join(',', array_fill(0, count($refComptes), '?')) . ")";
        $q3 = $this->_db->prepare("delete from adds_lbc where ref_compte in " . $in);
        $q3->execute($refComptes);
        
        return ($nbTot);
    }
}