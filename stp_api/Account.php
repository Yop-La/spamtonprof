<?php
namespace spamtonprof\stp_api;

class Account implements \JsonSerializable

{

    private $matieres = array(), $test_account, $long_pay_plan, $pour_romain, $autre_solution_proposee, $nb_message_last_week, $compte_associe, $nb_jour_inactivite, $statut_relance, $mail_hebdo, $mail_relance, $statut, $proche, $eleve, $maths, $same_email, $physique, $francais, $tarif, $planPaiement, $date_creation, $date_dernier_statut, $attente_paiement, $ref_compte, $last_contact_eleve;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
        
        if (is_null($this->autre_solution_proposee) && ! is_null($this->eleve)) {
            $this->autre_solution_proposee = $this->isAccountDeclined();
        }
    }

    public function hydrate(array $donnees)
    
    {
        foreach ($donnees as $key => $value) {
            
            $method = 'set' . ucfirst($key);
            
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function ref_compte()
    
    {
        return $this->ref_compte;
    }

    public function tarif()
    
    {
        return $this->tarif;
    }

    public function francais()
    
    {
        return $this->francais;
    }

    public function physique()
    
    {
        return $this->physique;
    }

    public function maths()
    
    {
        return $this->maths;
    }

    public function statut()
    
    {
        return $this->statut;
    }

    public function eleve()
    
    {
        return $this->eleve;
    }

    public function proche()
    
    {
        return $this->proche;
    }

    public function date_creation()
    
    {
        return $this->date_creation;
    }

    public function planPaiement()
    
    {
        return $this->planPaiement;
    }

    public function attente_paiement()
    
    {
        return $this->attente_paiement;
    }

    public function setRef_compte($ref_compte)
    
    {
        $this->ref_compte = $ref_compte;
    }

    public function setStatut($statut)
    
    {
        $this->statut = $statut;
    }

    public function setTarif($tarif)
    
    {
        $this->tarif = $tarif;
    }

    public function setPhysique($physique)
    
    {
        $this->physique = boolval($physique);
        
        if ($this->physique) {
            $this->matieres[] = "physique";
        } else {
            $this->removeMatiere("physique");
        }
        
    }

    public function setMaths($maths)
    
    {
        $this->maths = boolval($maths);
        
        if ($this->maths) {
            $this->matieres[] = "maths";
        } else {
            $this->removeMatiere("maths");
        }
        
    }

    public function setFrancais($francais)
    
    {
        $this->francais = boolval($francais);
        
        if ($this->francais) {
            $this->matieres[] = "francais";
        } else {
            $this->removeMatiere("francais");
        }
    }
    
   private function removeMatiere($matiere){
        for($i = 0;$i<count($this->matieres) ;$i++){
            if($this->matieres[$i] == $matiere){
                unset($this->matieres[$i]);
            }
        }
    }

    public function setParent(Proche $parent)
    
    {
        $this->proche = $parent;
    }

    public function setPlanPaiement($planPaiement)
    
    {
        $this->planPaiement = $planPaiement;
    }

    public function setEleve(Eleve $eleve)
    
    {
        $this->eleve = $eleve;
    }

    public function setAttente_paiement($attente_paiement)
    
    {
        $this->attente_paiement = boolval($attente_paiement);
    }

    public function setDate_creation($date_creation)
    
    {
        $this->date_creation = new \DateTime($date_creation, new \DateTimeZone('Europe/Paris'));
    }

    /**
     *
     * @return mixed
     */
    public function getDate_dernier_statut()
    {
        return $this->date_dernier_statut;
    }

    /**
     *
     * @param mixed $date_dernier_statut
     */
    public function setDate_dernier_statut($date_dernier_statut)
    {
        $this->date_dernier_statut = new \DateTime($date_dernier_statut, new \DateTimeZone("Europe/Paris"));
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    // pour mettre à jour le compte après abonnement
    function updateAfterSubscription()
    {
        $this->setAttente_paiement(false);
        $this->setStatut("inscrit");
        return ($account);
    }

    /**
     *
     * @param $month :
     *            5 (mois sans les zéros initiaux )
     * @param $year :
     *            1998 (année sur quatre chiffres )
     * @return double : retourne la remise de démarrage (si démarrage en cours de mois )
     */
    function getRemiseDemarrage($month, $year)
    {
        $dateLastStatut = $this->getDateLastStatut("inscrit");
        
        $dayLastInscription = $dateLastStatut->format("j");
        $monthLastInscription = $dateLastStatut->format("n");
        $yearLastInscription = $dateLastStatut->format("Y");
        
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        if ($monthLastInscription == $month && $yearLastInscription == $year) {
            
            return ($dayLastInscription * $this->tarif / $totalDaysInMonth);
        } else {
            
            return 0;
        }
    }

    /**
     *
     * @param $month :
     *            5 (mois sans les zéros initiaux )
     * @param $year :
     *            1998 (année sur quatre chiffres )
     * @return double : retourne la remise d'arrêt (si arrêt en cours de mois )
     */
    function getRemiseArret($month, $year)
    {
        if ($this->statut == "desinscrit_soutien") {
            
            $dateLastStatut = $this->getDateLastStatut("desinscrit_soutien");
            
            $dayLastInscription = $dateLastStatut->format("j");
            $monthLastInscription = $dateLastStatut->format("n");
            $yearLastInscription = $dateLastStatut->format("Y");
            
            $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            
            return (($totalDaysInMonth - $dayLastInscription) * $this->tarif / $totalDaysInMonth);
        } else {
            return (0);
        }
    }

    function getDateLastStatut($statut)
    {
        $historiqueManager = new HistoriqueManager();
        
        $dateLastStatut = $historiqueManager->getDateLastStatut($statut, $this->ref_compte);
        
        return ($dateLastStatut);
    }

    /**
     *
     * @return mixed
     */
    public function getLast_contact_eleve()
    {
        return $this->last_contact_eleve;
    }

    /**
     *
     * @param mixed $last_contact_eleve
     */
    public function setLast_contact_eleve($last_contact_eleve)
    {
        if (gettype($last_contact_eleve) == "string") {
            
            $last_contact_eleve = new \DateTime($last_contact_eleve, new \DateTimeZone("Europe/Paris"));
        }
        
        $this->last_contact_eleve = $last_contact_eleve;
    }

    public function setMatieres(array $matieres)
    {
        $this->matieres = $matieres;
        $this->maths = false;
        $this->francais = false;
        $this->physique = false;
        foreach ($matieres as $matiere) {
            if ($matiere == "maths") {
                $this->maths = true;
            } else if ($matiere == "physique") {
                $this->physique = true;
            } else if ($matiere == "francais") {
                $this->francais = true;
            }
        }
    }

    /**
     *
     * @return mixed
     */
    public function getSame_email()
    {
        return $this->same_email;
    }

    /**
     *
     * @param mixed $same_email
     */
    public function setSame_email($same_email)
    {
        $this->same_email = $same_email;
    }

    /**
     *
     * @return mixed
     */
    public function getPour_romain()
    {
        return $this->pour_romain;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_message_last_week()
    {
        return $this->nb_message_last_week;
    }

    /**
     *
     * @return mixed
     */
    public function getCompte_associe()
    {
        return $this->compte_associe;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_jour_inactivite()
    {
        return $this->nb_jour_inactivite;
    }

    /**
     *
     * @return mixed
     */
    public function getStatut_relance()
    {
        return $this->statut_relance;
    }

    /**
     *
     * @return mixed
     */
    public function getMail_hebdo()
    {
        return $this->mail_hebdo;
    }

    /**
     *
     * @return mixed
     */
    public function getMail_relance()
    {
        return $this->mail_relance;
    }

    /**
     *
     * @return mixed
     */
    public function getMatieres()
    {
        return $this->matieres;
    }

    /**
     *
     * @param mixed $pour_romain
     */
    public function setPour_romain($pour_romain)
    {
        $this->pour_romain = $pour_romain;
    }

    /**
     *
     * @param mixed $nb_message_last_week
     */
    public function setNb_message_last_week($nb_message_last_week)
    {
        $this->nb_message_last_week = $nb_message_last_week;
    }

    /**
     *
     * @param mixed $compte_associe
     */
    public function setCompte_associe($compte_associe)
    {
        $this->compte_associe = $compte_associe;
    }

    /**
     *
     * @param mixed $nb_jour_inactivite
     */
    public function setNb_jour_inactivite($nb_jour_inactivite)
    {
        $this->nb_jour_inactivite = $nb_jour_inactivite;
    }

    /**
     *
     * @param mixed $statut_relance
     */
    public function setStatut_relance($statut_relance)
    {
        $this->statut_relance = $statut_relance;
    }

    /**
     *
     * @param mixed $mail_hebdo
     */
    public function setMail_hebdo($mail_hebdo)
    {
        $this->mail_hebdo = $mail_hebdo;
    }

    /**
     *
     * @param mixed $mail_relance
     */
    public function setMail_relance($mail_relance)
    {
        $this->mail_relance = $mail_relance;
    }

    /**
     *
     * @return mixed
     */
    public function getAutre_solution_proposee()
    {
        return $this->autre_solution_proposee;
    }

    /**
     *
     * @param mixed $autre_solution_proposee
     */
    public function setAutre_solution_proposee($autre_solution_proposee)
    {
        $this->autre_solution_proposee = $autre_solution_proposee;
    }

    /**
     * pour savoir on doit proposer ou non une autre solution au détenteur du compte
     */
    public function isAccountDeclined()
    {
        if ($this->eleve()->classe() == 'ts') {
            return true;
        } else {
            return false;
        }
    }

    public function getEleveCampaign()
    {
        // @todostp deux types de campaigne : les onetime campaign et les long campaigns
        // une onetime campaign ne contient qu'un seul message et se termine par une suppression du contact dans la campagne
        // faire un cron pour virer des one time campagnes tous les contacts
        
        // input
        $statut = $this->statut;
        $same_email = $this->same_email;
        $francais = $this->francais;
        $maths = $this->maths;
        $physique = $this->physique;
        $autre_solution_proposee = $this->autre_solution_proposee;
        
        $campaignMg = new \spamtonprof\stp_api\CampaignManager();
        
        if (! is_null($statut) && ! is_null($same_email) && ! is_null($francais) && ! is_null($physique) && ! is_null($maths) && ! is_null($autre_solution_proposee)) {
            
            if ($same_email) {
                return (false);
            }
            
            if ($autre_solution_proposee) {
                return ($campaignMg::autre_solution_eleve);
            }
            
            if ($statut == "desinscrit_essai") {
                return ($campaignMg::eleve_desins_essai);
            }
            
            if ($statut == "desinscrit_soutien") {
                return ($campaignMg::eleve_desins_soutien);
            }
            
            if ($statut == "essai") {
                if ($maths || $physique) {
                    return ($campaignMg::eleve_en_essai);
                }
                
                if ($francais) {
                    return ($campaignMg::fr_eleve_essai);
                }
            }
            
            if ($statut == "inscrit") {
                if ($maths || $physique) {
                    return ($campaignMg::eleve_client);
                }
                
                if ($francais) {
                    return ($campaignMg::elisabeth_migne_eleve_client);
                }
            }
        }
        
        return (null);
    }

    public function getParentCampaign()
    {
        // input
        $statut = $this->statut;
        $francais = $this->francais;
        $maths = $this->maths;
        $physique = $this->physique;
        $autre_solution_proposee = $this->autre_solution_proposee;
        $nb_account = $this->getCompte_associe();
        if ($nb_account == 1) {
            $nb_account = "";
        } else {
            $nb_account = "_2";
        }
        
        $campaignMg = new \spamtonprof\stp_api\CampaignManager();
        
        if (! is_null($statut) && ! is_null($nb_account) && ! is_null($francais) && ! is_null($physique) && ! is_null($maths) && ! is_null($autre_solution_proposee)) {
            
            if ($autre_solution_proposee) {
                return ($campaignMg::autre_solution_parent . $nb_account);
            }
            
            if ($statut == "desinscrit_essai") {
                return ($campaignMg::parent_desins_essai . $nb_account);
            }
            
            if ($statut == "desinscrit_soutien") {
                return ($campaignMg::parent_desins_soutien . $nb_account);
            }
            
            if ($statut == "essai") {
                if ($maths || $physique) {
                    return ($campaignMg::parent_en_essai . $nb_account);
                }
                
                if ($francais) {
                    return ($campaignMg::fr_parent_essai . $nb_account);
                }
            }
            
            if ($statut == "inscrit") {
                if ($maths || $physique) {
                    return ($campaignMg::parent_client . $nb_account);
                }
                
                if ($francais) {
                    return ($campaignMg::elisabeth_migne_parent_client . $nb_account);
                }
            }
        }
        
        return (null);
    }
    /**
     * @return mixed
     */
    public function getTest_account()
    {
        return $this->test_account;
    }

    /**
     * @return mixed
     */
    public function getLong_pay_plan()
    {
        return $this->long_pay_plan;
    }

    /**
     * @param mixed $test_account
     */
    public function setTest_account($test_account)
    {
        $this->test_account = $test_account;
    }

    /**
     * @param mixed $long_pay_plan
     */
    public function setLong_pay_plan($long_pay_plan)
    {
        $this->long_pay_plan = $long_pay_plan;
    }

    
    
}