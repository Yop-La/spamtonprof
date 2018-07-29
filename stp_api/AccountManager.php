<?php
namespace spamtonprof\stp_api;

use PDO;
use Stripe\Account;
use spamtonprof;

class AccountManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    /**
     *
     * @param mixed $info
     *            exemples : array("query" => array("adresse_mail_eleve" => email@bi.don) ) ou array("query" => array("phone_eleve" => "+62585258") ) ou "45" ou 125
     * @return \spamtonprof\stp_api\Account|boolean|boolean|\spamtonprof\stp_api\Account|\spamtonprof\stp_api\Account|boolean
     */
    public function get($info)
    {
        if (is_array($info) && ! array_key_exists("query", $info)) {
            $donnees = $info;
            $account = new \spamtonprof\stp_api\Account($donnees);
            if (array_key_exists('ref_parent', $donnees)) {
                $parentManager = new ParentManager();
                $account->setParent($parentManager->get($donnees['ref_parent']));
            }
            if (array_key_exists('ref_eleve', $donnees)) {
                $eleveManager = new EleveManager();
                $account->setEleve($eleveManager->get($donnees['ref_eleve']));
            }
            if (array_key_exists('ref_plan_paiement', $donnees)) {
                
                $planPaiementManager = new PlanPaiementManager();
                $account->setPlanPaiement($planPaiementManager->get($donnees['ref_plan_paiement']));
            }
            return $account;
        } else if (is_array($info) && array_key_exists("query", $info)) {
            $query = $info["query"];
            $account;
            if (array_key_exists('adresse_mail_eleve', $query)) {
                $q = $this->_db->prepare('SELECT * FROM compte_eleve,eleve
                    WHERE eleve.ref_eleve = compte_eleve.ref_eleve and lower(adresse_mail) = lower(:adresse_mail)');
                $q->execute([
                    ':adresse_mail' => $query["adresse_mail_eleve"]
                ]);
                if ($data = $q->fetch(PDO::FETCH_ASSOC)) {
                    $account = $this->get($data);
                } else {
                    return (false);
                }
            } else if (array_key_exists('phone_eleve', $query)) {
                $q = $this->_db->prepare('SELECT * FROM compte_eleve,eleve
                    WHERE eleve.ref_eleve = compte_eleve.ref_eleve and lower(telephone) like lower(:phone_eleve)');
                $q->execute([
                    ':phone_eleve' => '%' . $query["phone_eleve"] . '%'
                ]);
                if ($data = $q->fetch(PDO::FETCH_ASSOC)) {
                    $account = $this->get($data);
                } else {
                    return (false);
                }
            }
            return $account;
        } else if (is_int($info) or is_string($info)) {
            $info = intval($info);
            $q = $this->_db->prepare('SELECT * FROM compte_eleve WHERE ref_compte = :ref_compte');
            $q->execute([
                ':ref_compte' => $info
            ]);
            
            $account = $this->get($q->fetch(PDO::FETCH_ASSOC));
            return $account;
        }
    }

    // retournera soit un tab avec un √©l√®ve ou pas du tout car il est impossible qu'un mail √©l√®ve soit associ√© √† plusieurs comptes
    public function getListEleve($mailEleve)
    {
        $accounts = [];
        
        $eleveManager = new EleveManager();
        $eleve = $eleveManager->get($mailEleve);
        if ($eleve) {
            $q = $this->_db->prepare('SELECT * from compte_eleve WHERE ref_eleve = :ref_eleve');
            $q->execute([
                ':ref_eleve' => $eleve->ref_eleve()
            ]);
            
            while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
                $account = $this->get($donnees);
                $account->setEleve($eleve);
                $accounts[] = $account;
            }
        }
        return $accounts;
    }

    public function getListParent($mailParent)
    {
        $accounts = [];
        
        $parentManager = new ParentManager();
        $parent = $parentManager->get($mailParent);
        
        if ($parent) {
            
            $q = $this->_db->prepare('SELECT * from compte_eleve WHERE ref_parent = :ref_parent');
            $q->execute([
                ':ref_parent' => $parent->ref_parent()
            ]);
            
            while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
                $account = $this->get($donnees);
                $account->setParent($parent);
                $accounts[] = $account;
            }
        }
        return $accounts;
    }

    public function getList($mail)
    {
        $accounts_eleve = $this->getListEleve($mail);
        $accounts_parent = $this->getListParent($mail);
        $accounts = array_merge($accounts_parent, $accounts_eleve);
        
        $accounts = array_unique($accounts, SORT_REGULAR);
        
        return $accounts;
    }

    public function filterByStatut($accounts, $statut)
    {
        $retour = [];
        foreach ($accounts as $account) {
            if (in_array($account->statut(), $statut)) {
                $retour[] = $account;
            }
        }
        return ($retour);
    }

    public function filterByAttentePaiement($accounts, $attentePaiement)
    {
        $retour = [];
        foreach ($accounts as $account) {
            if ($account->attente_paiement() == $attentePaiement) {
                $retour[] = $account;
            }
        }
        return ($retour);
    }

    /**
     *
     * @param string $statuts
     *            . exemple d'arguments : array("essai", "inscrit") ou "essai" ou "tous"
     *            boolean | int $limit
     *            boolean | null $attentePaiement
     *            boolean | null $test
     * @return \spamtonprof\stp_api\Account[]
     */
    public function getAllRefCompte($statuts, $limit = null, $attentePaiement = null, $test = null)
    {
        if (is_null($limit)) {
            $limit = "";
        } else {
            $limit = " limit " . $limit;
        }
        
        if (is_null($attentePaiement)) {
            $attentePaiement = "";
        } else {
            $attentePaiement = ($attentePaiement) ? 'true' : 'false';
            $attentePaiement = " and attente_paiement = " . $attentePaiement . " ";
        }
        
        if (is_null($test)) {
            $test = "";
        } else {
            $test = ($test) ? 'true' : 'false';
            $test = " and test_account = " . $test . " ";
        }
        
        $q;
        if (is_array($statuts)) {
            $q = $this->_db->prepare("SELECT ref_compte FROM compte_eleve where statut IN ('" . implode("','", $statuts) . "')" . $attentePaiement . $test . $limit);
            $q->execute();
        } else if ($statuts == "tous") {
            $q = $this->_db->prepare('SELECT ref_compte FROM compte_eleve where true' . $attentePaiement . $test . $limit);
            $q->execute();
        } else {
            $q = $this->_db->prepare('SELECT ref_compte FROM compte_eleve where statut = :statut' . $attentePaiement . $test . $limit);
            $q->execute([
                ':statut' => $statuts
            ]);
        }
        
        $refComptes = array();
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $refComptes[] = $donnees['ref_compte'];
        }
        
        return $refComptes;
    }

    public function getAll(array $refComptes)
    {
        $accounts = [];
        
        $q = $this->_db->prepare("SELECT * FROM compte_eleve where ref_compte IN ('" . implode("','", $refComptes) . "')");
        $q->execute();
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $account = $this->get($donnees);
            $accounts[] = $account;
        }
        
        return $accounts;
    }

    public function getAllWithoutPlan($statuts)
    {
        $accounts = [];
        $q;
        if (is_array($statuts)) {
            $q = $this->_db->prepare("SELECT * FROM compte_eleve where statut IN ('" . implode("','", $statuts) . "') and ref_plan_paiement is NULL");
            $q->execute();
        } else if ($statuts == "tous") {
            $q = $this->_db->prepare('SELECT * FROM compte_eleve where ref_plan_paiement is NULL');
            $q->execute();
        } else {
            $q = $this->_db->prepare('SELECT * FROM compte_eleve where statut = :statut and ref_plan_paiement is NULL');
            $q->execute([
                ':statut' => $statuts
            ]);
        }
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $account = $this->get($donnees);
            $accounts[] = $account;
        }
        
        return $accounts;
    }

    public function updateRefPlanPaiement(spamtonprof\stp_api\Account $account)
    
    {
        $q = $this->_db->prepare('UPDATE compte_eleve SET ref_plan_paiement =:ref_plan_paiement WHERE ref_compte = :ref_compte');
        
        $q->bindValue(':ref_plan_paiement', $account->planPaiement()
            ->ref_plan_paiement());
        
        $q->bindValue(':ref_compte', $account->ref_compte(), PDO::PARAM_INT);
        
        $q->execute();
    }

    public function updateLastContactEleve(\spamtonprof\stp_api\Account $account)
    
    {
        $q = $this->_db->prepare('UPDATE compte_eleve SET last_contact_eleve =:last_contact_eleve WHERE ref_compte = :ref_compte');
        
        $q->bindValue(':last_contact_eleve', $account->getLast_contact_eleve()
            ->format(PG_DATETIME_FORMAT));
        
        $q->bindValue(':ref_compte', $account->ref_compte(), PDO::PARAM_INT);
        
        $q->execute();
    }

    // pour mettre ‡ jour attente paiement et statut
    public function updateAfterSubsCreated(spamtonprof\stp_api\Account $account)
    
    {
        $q = $this->_db->prepare('UPDATE compte_eleve SET attente_paiement =:attente_paiement, statut =:statut WHERE ref_compte = :ref_compte');
        
        $q->bindValue(':attente_paiement', $account->attente_paiement(), PDO::PARAM_BOOL);
        
        $q->bindValue(':statut', $account->statut());
        
        $q->bindValue(':ref_compte', $account->ref_compte(), PDO::PARAM_INT);
        
        $q->execute();
    }

    // cette fonction trouve les comptes sans plan de paiement pour en attribuer un en fonction de la classe, des mati√®res et de la date d'inscription
    // elle est √©x√©cut√© par un cron tous les jours pour attribuer un plan aux trial accounts
    public function attribuerPlanPlaiement()
    {
        $accounts = $this->getAllWithoutPlan("tous");
        
        $formuleManager = new FormuleManager();
        $planPaiementManager = new PlanPaiementManager();
        
        $nomPlan = "defaut";
        
        $date1 = new \DateTime("2018-02-01", new \DateTimeZone('Europe/Paris'));
        
        $date2 = new \DateTime("2018-04-08", new \DateTimeZone('Europe/Paris'));
        
        foreach ($accounts as $account) {
            
            $dateCre = $account->date_creation();
            
            // d√©terminer le type de plan ( defaut, avant ou apr√®s 01/02/2018)
            if ($dateCre < $date1) {
                $nomPlan = "before 01/02/2018";
            } elseif ($dateCre < $date2) {
                $nomPlan = "after 01/02/2018";
            } else {
                $nomPlan = "defaut";
            }
            
            $formule = $formuleManager->get(array(
                "classe" => $account->eleve()
                    ->classe(),
                "maths" => $account->maths(),
                "physique" => $account->physique(),
                "francais" => $account->francais()
            ));
            
            $planPaiement = $planPaiementManager->get(array(
                "ref_formule" => $formule->ref_formule(),
                "nom_plan" => $nomPlan,
                "query_param"
            ));
            
            $planPaiement->setFormule($formule);
            
            $account->setPlanPaiement($planPaiement);
            
            $this->updateRefPlanPaiement($account);
            
            echo (json_encode($planPaiement) . "<br><br>");
        }
    }

    /**
     *
     * @param int $month
     *            : numero de mois sans les zÈros initiaux
     * @param int $year
     *            : numÈro d'annÈe sur chiffres
     * @param int $limit
     *            : nombre max d'accounts ‡ retourner
     * @return Account[] : un tableau de comptes ‡ facturer
     */
    public function getAccountToBill(int $month, int $year, int $limit)
    {
        $accounts = [];
        
        if (is_null($limit)) {
            $limit = "";
        } else {
            $limit = " limit " . $limit;
        }
        
        $q = $this->_db->prepare("SELECT * from compte_eleve where 
                statut = 'inscrit' 
                or 
                (statut = 'desinscrit_soutien' 
                    and extract(year from date_dernier_statut ) =  :year
                    and extract(month from date_dernier_statut ) =  :month)" . $limit);
        
        $q->execute(array(
            "year" => $year,
            "month" => $month
        ));
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $account = new \spamtonprof\stp_api\Account($donnees);
            
            $accounts[] = $account;
        }
        
        return $accounts;
    }

    public function updateNbJourInactivite()
    {
        $q = $this->_db->prepare("update compte_eleve set nb_jour_inactivite = extract(day from NOW() AT TIME ZONE 'Europe/Paris' - last_contact_eleve)");
        
        $q->execute();
    }

    public function resetNbMessageLastWeek()
    {
        $q = $this->_db->prepare("update compte_eleve set nb_message_last_week = 0");
        
        $q->execute();
    }

    public function updateNbMessageLastWeek($week, $year)
    {
        $q = $this->_db->prepare("select nb_message, ref_compte 
            from nb_email
            where week = :week and year = :year");
        
        $q->bindValue(":week", $week, PDO::PARAM_INT);
        $q->bindValue(":year", $year, PDO::PARAM_INT);
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $q2 = $this->_db->prepare("update compte_eleve set nb_message_last_week = :nb_message_last_week
                where ref_compte = :ref_compte");
            $q2->bindValue(':nb_message_last_week', $data["nb_message"], PDO::FETCH_ASSOC);
            $q2->bindValue(':ref_compte', $data["ref_compte"], PDO::FETCH_ASSOC);
            $q2->execute();
        }
    }

    /**
     * est utilisÈ uniquement lors l'inscription ‡ l'essai ( plien de paramËtres par dÈfaut )
     *
     * @param \spamtonprof\stp_api\Account $account
     */
    public function add(\spamtonprof\stp_api\Account $account)
    {
        $q = $this->_db->prepare("
        insert into compte_eleve
        (
            ref_parent, 
            same_email, 
            statut, 
            ref_eleve, 
            update_date, 
            date_creation, 
            tarif, 
            last_contact_eleve, 
            autre_solution_proposee, 
            nb_jour_inactivite,
        	statut_relance,
        	mail_hebdo,
        	mail_relance,
        	parrainage_set,
        	compte_associe,
        	nb_message_last_week,
        	maths,
        	physique,
        	francais,
        	pour_romain
        ) 
        values
        (
            :ref_parent, 
            :same_email, 
            :statut, 
            :ref_eleve, 
            :update_date, 
            :date_creation, 
            :tarif, 
            :last_contact_eleve, 
            :autre_solution_proposee, 
            :nb_jour_inactivite,
        	:statut_relance,
        	:mail_hebdo,
        	:mail_relance,
        	:parrainage_set,
        	:compte_associe,
        	:nb_message_last_week,
        	:maths,
        	:physique,
        	:francais,
        	:pour_romain
        );");
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $q->bindValue(':ref_parent', $account->proche()
            ->ref_parent(), PDO::PARAM_INT);
        $q->bindValue(':same_email', $account->getSame_email(), PDO::PARAM_BOOL);
        $q->bindValue(':statut', $account->statut());
        $q->bindValue(':ref_eleve', $account->eleve()
            ->ref_eleve(), PDO::PARAM_INT);
        $q->bindValue(':update_date', $now->format(PG_DATETIME_FORMAT));
        $q->bindValue(':date_creation', $now->format(PG_DATETIME_FORMAT));
        $q->bindValue(':tarif', $account->tarif());
        $q->bindValue(':last_contact_eleve', $now->format(PG_DATETIME_FORMAT));
        $q->bindValue(':autre_solution_proposee', $account->getAutre_solution_proposee(), PDO::PARAM_INT);
        $q->bindValue(':nb_jour_inactivite', 0);
        $q->bindValue(':statut_relance', "aucune");
        $q->bindValue(':mail_hebdo', true, PDO::PARAM_BOOL);
        $q->bindValue(':mail_relance', true, PDO::PARAM_BOOL);
        $q->bindValue(':parrainage_set', true, PDO::PARAM_BOOL);
        $q->bindValue(':compte_associe', $account->getCompte_associe());
        $q->bindValue(':nb_message_last_week', 0, PDO::PARAM_INT);
        $q->bindValue(':maths', $account->maths(), PDO::PARAM_BOOL);
        $q->bindValue(':physique', $account->physique(), PDO::PARAM_BOOL);
        $q->bindValue(':francais', $account->francais(), PDO::PARAM_BOOL);
        $q->bindValue(':pour_romain', $account->getPour_romain(), PDO::PARAM_BOOL);
        
        $q->execute();
        
        $account->ref_compte($this->_db->lastInsertId());
        
        return ($account);
    }

    public function delete($info)
    {
        if (is_string($info)) {
            $account = $this->get(array(
                "query" => array(
                    "adresse_mail_eleve" => $info
                )
            ));
            if ($account) {
                $info = $account->ref_compte();
            } else {
                return;
            }
        }
        
        $tables_with_ref_compte = array(
            "nb_email",
            "mail_eleve",
            "code_coupon_saisi",
            "historique_eleve",
            "compte_eleve"
        );
        
        foreach ($tables_with_ref_compte as $table) {
            
            $q = $this->_db->prepare("
                    delete from $table where ref_compte = :ref_compte
                ");
            
            $q->bindValue(":ref_compte", $info, PDO::PARAM_INT);
            $q->execute();
        }
    }

    // un compte inactif est un compte en essai ‚gÈ de 10 jours avec 5 jours d'inactivitÈ
    public function getInactiveAccounts()
    {
        $refComptes = [];
        
        $q = $this->_db->prepare("select ref_compte from compte_eleve where date_creation + interval '10 days' < now()  
            and statut = 'essai' and nb_jour_inactivite >= 5
            order by date_creation desc;");
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $refComptes[] = $data['ref_compte'];
        }
        
        return ($refComptes);
    }

    // pour retourner les comptes en fin d'essai ( ie avec 7 jours d'anciennetÈ ).
    public function getTrialEndAccounts()
    {
        $refComptes = [];
        
        $q = $this->_db->prepare("select * from compte_eleve where extract(day from now() - date_creation ) = 7
            and statut = 'essai' 
            order by date_creation desc;");
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            $refComptes[] = $data['ref_compte'];
        }
        
        return ($refComptes);
    }

    public function unsubInactiveAccounts()
    {
        $refComptes = $this->getInactiveAccounts();
        
        $accounts = $this->getAll($refComptes);
        
        foreach ($accounts as $account) {
            
            $account->setStatut('desinscrit_essai');
            $account->setAttente_paiement(true);
            
            $this->updateAfterSubsCreated($account);
        }
        
        return ($accounts);
    }

    public function getNbMessage($refAccount)
    {
        $refComptes = [];
        
        $q = $this->_db->prepare("select count(*) as nb_message from mail_eleve where ref_compte = :ref_compte");
        $q->bindValue(":ref_compte", $refAccount);
        $q->execute();
        
        if ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            
            return ($data['nb_message']);
        } else {
            
            return (0);
        }
    }

    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}

