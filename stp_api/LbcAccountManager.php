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
        $q = null;
        if (array_key_exists("ref_compte", $info)) {
            $refCompte = $info["ref_compte"];
            $q = $this->_db->prepare("select * from compte_lbc where ref_compte = :ref_compte");
            $q->execute(array(
                "ref_compte" => $refCompte
            ));
        }

        if (array_key_exists("mail", $info)) {

            $mail = $info["mail"];
            $mail = trim($mail);
            $q = $this->_db->prepare("select * from compte_lbc where mail = :mail");
            $q->execute(array(
                "mail" => $mail
            ));
        }

        if (array_key_exists("refClient", $info) && array_key_exists("query", $info)) {

            $refClient = $info["refClient"];
            $query = $info["query"];

            if ($query == "shortestEmail") {
                $q = $this->_db->prepare("select * from compte_lbc where ref_client = :refClient order by length(mail)*random()");
                $q->bindValue(":refClient", $refClient);
                $q->execute();
            }
        }

        $donnees = $q->fetch(PDO::FETCH_ASSOC);
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

        if (is_array($info)) {

            if (array_key_exists("refComptes", $info)) {
                $refComptes = $info["refComptes"];

                $in = "(" . str_repeat('?,', count($refComptes) - 1) . '?' . ")";

                $q = $this->_db->prepare("select prenom_client, nom_client, ref_compte, code_promo, controle_date, nb_annonces_online
                from compte_lbc, client where compte_lbc.ref_client = client.ref_client and compte_lbc.ref_compte in " . $in);
                $q->execute($refComptes);
            } else if (array_key_exists("ref_client", $info)) {

                $refClient = $info["ref_client"];

                $q = $this->_db->prepare("select * from compte_lbc where ref_client = :ref_client");
                $q->bindValue(":ref_client", $refClient);
                $q->execute();
            }
        } else {

            if ($info == "lastTwentyForReportingLbcIndex") {

                $q = $this->_db->prepare("select prenom_client, nom_client, ref_compte, code_promo, controle_date, nb_annonces_online
                from compte_lbc, client where compte_lbc.ref_client = client.ref_client  order by ref_compte desc limit 20");
                $q->execute();
            } else if (! $info) {

                $q = $this->_db->prepare("select * from compte_lbc");
                $q->execute();
            } else if ("forReportingLbcIndex") {

                $q = $this->_db->prepare("select prenom_client, nom_client, ref_compte, code_promo, controle_date, nb_annonces_online
                from compte_lbc, client where compte_lbc.ref_client = client.ref_client");
                $q->execute();
            }
        }

        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $account = new \spamtonprof\stp_api\LbcAccount($donnees);
            $accounts[] = $account;
        }
        return ($accounts);
    }

    public function getReport($info = false)
    {
        $ret = [];

        $q = null;

        if (is_array($info)) {

            if (array_key_exists("global_nb_ads", $info)) {

                $q = $this->_db->prepare("select  prenom_client,client.ref_client, sum(nb_annonces_online) as nb_adds 
                    from compte_lbc, client where  compte_lbc.ref_client = client.ref_client
                        group by client.ref_client,prenom_client order by nb_adds desc");
            }

            if (array_key_exists("ads_by_day", $info)) {

                $q = $this->_db->prepare("select  client.prenom_client as prenom, sum(nb_annonces_online) as nb_ads, date(date_creation) as date_creation from compte_lbc,client
                    where nb_annonces_online != 0 and compte_lbc.ref_client = client.ref_client
                    group by date(date_creation), client.prenom_client
                    order by date_creation desc,nb_ads desc ;");
            }

            if (array_key_exists("ads_by_domain", $info)) {

                $q = $this->_db->prepare("select client.ref_client, client.prenom_client, regexp_matches(mail, '@.*')  as domain , sum(nb_annonces_online)from compte_lbc , client
                    where compte_lbc.ref_client = client.ref_client
                    group by regexp_matches(mail, '@.*')  ,client.ref_client, client.prenom_client
                    order by ref_client desc , domain 
                ");
            }

            if (array_key_exists("domains_stats", $info)) {

                $q = $this->_db->prepare("select name, mail_provider, count(name) as nb_emails,sum(nb_annonces_online) as nb_adds,stp_domain.disabled from stp_domain, compte_lbc 
                    where    compte_lbc.mail like '%' || stp_domain.name || '%' 
                    group by name, stp_domain.disabled,mail_provider
                    order by nb_adds desc;  
                ");
            }
        }

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $ret[] = array_values($data);
        }

        return ($ret);
    }

    public function updateDisabled(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        $q = $this->_db->prepare("update compte_lbc set disabled = :disabled, date_of_disabling = :date_of_disabling where ref_compte = :ref_compte");
        $q->bindValue(":disabled", $lbcAccount->getDisabled(), PDO::PARAM_BOOL);
        $q->bindValue(":date_of_disabling", $now->format(PG_DATETIME_FORMAT));
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function update_date_publication(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set date_publication = :date_publication where ref_compte = :ref_compte");
        $q->bindValue(":date_publication", $lbcAccount->getDate_publication()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function updateControleDate(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set controle_date = :controle_date where ref_compte = :ref_compte");
        $q->bindValue(":controle_date", $lbcAccount->getControle_date()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function updateNbAnnonceOnline(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set nb_annonces_online = :nb_annonces_online where ref_compte = :ref_compte");
        $q->bindValue(":nb_annonces_online", $lbcAccount->getNb_annonces_online());
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function add(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        $q = $this->_db->prepare("insert into compte_lbc(mail, password, nb_annonces_online, disabled, ref_client, telephone, date_creation) 
            values(:mail, :password, 0, false, :ref_client, :telephone, :date_creation)");
        $q->bindValue(":mail", $lbcAccount->getMail());
        $q->bindValue(":password", $lbcAccount->getPassword());
        $q->bindValue(":ref_client", $lbcAccount->getRef_client());
        $q->bindValue(":telephone", $lbcAccount->getTelephone());
        $q->bindValue(":date_creation", $now->format(PG_DATETIME_FORMAT));
        $q->execute();

        $lbcAccount->setRef_compte($this->_db->lastInsertId());

        return ($lbcAccount);
    }

    public function updateRefExpe(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set ref_expe = :ref_expe where ref_compte = :ref_compte");
        $q->bindValue(":ref_expe", $lbcAccount->getRef_expe());
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function updateAll($info)
    {
        $q = null;

        if (is_array($info)) {

            if (array_key_exists("dumpRefClient", $info)) {
                $dumpRefClient = $info["dumpRefClient"];

                $q = $this->_db->prepare("update compte_lbc set ref_client = 1 where ref_client = :ref_client ");
                $q->bindValue(':ref_client', $dumpRefClient);
            }
        }

        $q->execute();
    }

    public function updatePrenom(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set prenom = :prenom where ref_compte = :ref_compte");
        $q->bindValue(":prenom", $lbcAccount->getPrenom());
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

    public function updateCookie(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set cookie = :cookie where ref_compte = :ref_compte");
        $q->bindValue(":cookie", $lbcAccount->getCookie());
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function updateUserId(\spamtonprof\stp_api\LbcAccount $lbcAccount)
    {
        $q = $this->_db->prepare("update compte_lbc set user_id = :user_id where ref_compte = :ref_compte");
        $q->bindValue(":user_id", $lbcAccount->getUser_id());
        $q->bindValue(":ref_compte", $lbcAccount->getRef_compte());
        $q->execute();
    }

    public function getAccountToScrap($nbCompte)
    {
        $accounts = [];

        $q = $this->_db->prepare("select * from compte_lbc 
        where now() - interval '2 hour' > date_creation and (disabled = false or disabled is null) and (uncheckable = false or uncheckable is null)
            order by nb_annonces_online, ref_compte desc limit :nb_compte");
        $q->bindValue(":nb_compte", $nbCompte);
        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $accounts[] = new \spamtonprof\stp_api\LbcAccount($data);
        }
        return ($accounts);
    }

    public function desactivateDeadAccounts($info = false)
    {
        $refComptes = [];
        if (! $info) {

            $q1 = $this->_db->prepare("select * from compte_lbc where code_promo is null and (disabled is null or disabled = false)");
            $q1->execute();

            $refComptes = [];
            while ($data = $q1->fetch(PDO::FETCH_ASSOC)) {

                $refComptes[] = $data["ref_compte"];
            }
        } else if (is_array($info)) {
            $refComptes = $info;
        }

        if (count($refComptes) != 0) {

            $in = "(" . join(',', array_fill(0, count($refComptes), '?')) . ")";

            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

            $params2 = $refComptes;
            $params3 = $refComptes;
            array_unshift($params2, $now->format(PG_DATETIME_FORMAT));

            $q2 = $this->_db->prepare("update compte_lbc set controle_date = ?, disabled = true, nb_annonces_online = 0 where ref_compte in " . $in);
            $q2->execute($params2);

            $q3 = $this->_db->prepare("delete from adds_lbc where ref_compte in " . $in);
            $q3->execute($params3);
        } else {
            return;
        }
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

            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

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