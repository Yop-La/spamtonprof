<?php
namespace spamtonprof\stp_api;

class StpAbonnement implements \JsonSerializable
{

    const ACTIF = 1, ESSAI = 2, TERMINE = 3, DESACTIVE = 4, ATTENTE_DEMARRAGE = 5, REFUSE = 6;

    protected $ref_eleve, $ref_formule, $ref_statut_abonnement, $ref_abonnement, $date_creation, $remarque_inscription, $ref_plan, $eleve, $ref_prof, $formule, $prof, $date_attribution_prof, $first_prof_assigned, $ref_proche, $proche, $plan, $ref_compte, $debut_essai, $fin_essai, $subs_Id, $statut, $dateDernierStatut, $dernier_contact, $nb_message, $remarquesMatieres, $nbJourSansMessage, $objectID, $teleprospection, $compte, $interruption, $ref_coupon, $coupon, $relance_date, $test, $to_relaunch, $nb_relance_since_no_news, $utm_source_stp, $utm_medium_stp, $utm_campaign_stp, $sent_to_affiliate;

    
    
    
    /**
     * @return mixed
     */
    public function getSent_to_affiliate()
    {
        return $this->sent_to_affiliate;
    }

    /**
     * @param mixed $sent_to_affiliate
     */
    public function setSent_to_affiliate($sent_to_affiliate)
    {
        $this->sent_to_affiliate = $sent_to_affiliate;
    }

    /**
     *
     * @return mixed
     */
    public function getUtm_source_stp()
    {
        return $this->utm_source_stp;
    }

    /**
     *
     * @return mixed
     */
    public function getUtm_medium_stp()
    {
        return $this->utm_medium_stp;
    }

    /**
     *
     * @return mixed
     */
    public function getUtm_campaign_stp()
    {
        return $this->utm_campaign_stp;
    }

    /**
     *
     * @param mixed $utm_source_stp
     */
    public function setUtm_source_stp($utm_source_stp)
    {
        $this->utm_source_stp = $utm_source_stp;
    }

    /**
     *
     * @param mixed $utm_medium_stp
     */
    public function setUtm_medium_stp($utm_medium_stp)
    {
        $this->utm_medium_stp = $utm_medium_stp;
    }

    /**
     *
     * @param mixed $utm_campaign_stp
     */
    public function setUtm_campaign_stp($utm_campaign_stp)
    {
        $this->utm_campaign_stp = $utm_campaign_stp;
    }

    /**
     *
     * @return mixed
     */
    public function getTo_relaunch()
    {
        return $this->to_relaunch;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_relance_since_no_news()
    {
        return $this->nb_relance_since_no_news;
    }

    /**
     *
     * @param mixed $to_relaunch
     */
    public function setTo_relaunch($to_relaunch)
    {
        $this->to_relaunch = $to_relaunch;
    }

    /**
     *
     * @param mixed $nb_relance_since_no_news
     */
    public function setNb_relance_since_no_news($nb_relance_since_no_news)
    {
        $this->nb_relance_since_no_news = $nb_relance_since_no_news;
    }

    /**
     *
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     *
     * @param mixed $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_coupon()
    {
        return $this->ref_coupon;
    }

    /**
     *
     * @return mixed
     */
    public function getRelance_date()
    {
        return $this->relance_date;
    }

    /**
     *
     * @param mixed $relance_date
     */
    public function setRelance_date($relance_date)
    {
        $this->relance_date = $relance_date;
    }

    /**
     *
     * @return mixed
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     *
     * @param mixed $coupon
     */
    public function setCoupon($coupon)
    {
        $this->coupon = $coupon;
    }

    /**
     *
     * @param mixed $ref_coupon
     */
    public function setRef_coupon($ref_coupon)
    {
        $this->ref_coupon = $ref_coupon;
    }

    /**
     *
     * @return mixed
     */
    public function getInterruption()
    {
        return $this->interruption;
    }

    /**
     *
     * @param mixed $interruption
     */
    public function setInterruption($interruption)
    {
        $this->interruption = $interruption;
    }

    /**
     *
     * @return mixed
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     *
     * @param mixed $compte
     */
    public function setCompte($compte)
    {
        $this->compte = $compte;
    }

    /**
     *
     * @return mixed
     */
    public function getObjectID()
    {
        return $this->objectID;
    }

    /**
     *
     * @param mixed $objectID
     */
    public function setObjectID($objectID)
    {
        $this->objectID = $objectID;
    }

    /**
     *
     * @return mixed
     */
    public function getTeleprospection()
    {
        return $this->teleprospection;
    }

    /**
     *
     * @param mixed $teleprospection
     */
    public function setTeleprospection($teleprospection)
    {
        $this->teleprospection = $teleprospection;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_message()
    {
        return $this->nb_message;
    }

    /**
     *
     * @param mixed $nb_message
     */
    public function setNb_message($nb_message)
    {
        $this->nb_message = $nb_message;
    }

    /**
     *
     * @return boolean
     */
    public function getDateDernierStatut()
    {
        return $this->dateDernierStatut;
    }

    /**
     *
     * @return mixed
     */
    public function getRemarquesMatieres()
    {
        return $this->remarquesMatieres;
    }

    /**
     *
     * @param boolean $dateDernierStatut
     */
    public function setDateDernierStatut($dateDernierStatut)
    {
        $this->dateDernierStatut = $dateDernierStatut;
    }

    /**
     *
     * @param mixed $remarquesMatieres
     */
    public function setRemarquesMatieres($remarquesMatieres)
    {
        $this->remarquesMatieres = $remarquesMatieres;
    }

    /**
     *
     * @return mixed
     */
    public function getDernier_contact()
    {
        return $this->dernier_contact;
    }

    /**
     *
     * @param mixed $dernier_contact
     */
    public function setDernier_contact($dernier_contact)
    {
        $this->dernier_contact = $dernier_contact;

        if ($this->dernier_contact) {

            $dernierContact = \DateTime::createFromFormat(PG_DATETIME_FORMAT, $this->dernier_contact);
            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

            $interval = date_diff($dernierContact, $now);

            $this->setNbJourSansMessage(intval($interval->format('%a')));
        } else {

            $this->setNbJourSansMessage(100);
        }
    }

    /**
     *
     * @return mixed
     */
    public function getNbJourSansMessage()
    {
        return $this->nbJourSansMessage;
    }

    /**
     *
     * @param mixed $nbJourSansMessage
     */
    public function setNbJourSansMessage($nbJourSansMessage)
    {
        $this->nbJourSansMessage = $nbJourSansMessage;
    }

    /**
     *
     * @return mixed
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     *
     * @param mixed $statut
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    /**
     *
     * @return mixed
     */
    public function getFirst_prof_assigned()
    {
        return $this->first_prof_assigned;
    }

    /**
     *
     * @param mixed $first_prof_assigned
     */
    public function setFirst_prof_assigned($first_prof_assigned)
    {
        $this->first_prof_assigned = $first_prof_assigned;
    }

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);

        $stpLogAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();

        if ($this->getRef_abonnement()) {

            $this->dateDernierStatut = $stpLogAboMg->getDateDernierStatut($this->getRef_abonnement());
        }
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function getRef_eleve()
    {
        return $this->ref_eleve;
    }

    public function setRef_eleve($ref_eleve)
    {
        $this->ref_eleve = $ref_eleve;
    }

    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    /**
     *
     * @return mixed
     */
    public function getProf()
    {
        return $this->prof;
    }

    /**
     *
     * @param mixed $prof
     */
    public function setProf($prof)
    {
        $this->prof = $prof;
    }

    public function getRef_statut_abonnement()
    {
        return $this->ref_statut_abonnement;
    }

    public function setRef_statut_abonnement($ref_statut_abonnement)
    {
        $this->ref_statut_abonnement = $ref_statut_abonnement;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
        $this->setObjectID($ref_abonnement);
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation($date_creation)
    {
        $this->date_creation = $date_creation;
    }

    public function getRemarque_inscription()
    {
        return $this->remarque_inscription;
    }

    public function setRemarque_inscription($remarque_inscription)
    {
        $this->remarque_inscription = $remarque_inscription;
    }

    public function getRef_plan()
    {
        return $this->ref_plan;
    }

    public function setRef_plan($ref_plan)
    {
        $this->ref_plan = $ref_plan;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }

    /**
     *
     * @return mixed
     */
    public function getEleve()
    {
        return $this->eleve;
    }

    /**
     *
     * @param mixed $eleve
     */
    public function setEleve($eleve)
    {
        $this->eleve = $eleve;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    /**
     *
     * @param mixed $ref_prof
     */
    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     *
     * @param mixed $formule
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_attribution_prof()
    {
        return $this->date_attribution_prof;
    }

    /**
     *
     * @param mixed $date_attribution_prof
     */
    public function setDate_attribution_prof($date_attribution_prof)
    {
        $this->date_attribution_prof = $date_attribution_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_proche()
    {
        return $this->ref_proche;
    }

    /**
     *
     * @param mixed $ref_proche
     */
    public function setRef_proche($ref_proche)
    {
        $this->ref_proche = $ref_proche;
    }

    /**
     *
     * @return mixed
     */
    public function getProche()
    {
        return $this->proche;
    }

    /**
     *
     * @param mixed $proche
     */
    public function setProche($proche)
    {
        $this->proche = $proche;
    }

    /**
     *
     * @return mixed
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     *
     * @param mixed $plan
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     *
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    /**
     *
     * @return mixed
     */
    public function getDebut_essai()
    {
        return $this->debut_essai;
    }

    /**
     *
     * @return mixed
     */
    public function getFin_essai()
    {
        return $this->fin_essai;
    }

    /**
     *
     * @param mixed $debut_essai
     */
    public function setDebut_essai($debut_essai)
    {
        $this->debut_essai = $debut_essai;
    }

    /**
     *
     * @param mixed $fin_essai
     */
    public function setFin_essai($fin_essai)
    {
        $this->fin_essai = $fin_essai;
    }

    /**
     *
     * @return mixed
     */
    public function getSubs_Id()
    {
        return $this->subs_Id;
    }

    /**
     *
     * @param mixed $subs_Id
     */
    public function setSubs_Id($subs_Id)
    {
        $this->subs_Id = $subs_Id;
    }

    public function isTrialOver()
    {
        $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));

        $finEssai = $this->getFin_essai();

        if (! $finEssai) {
            return (false);
        }

        $trialEnd = date_create_from_format(PG_DATE_FORMAT, $finEssai);

        if ($now > $trialEnd) {
            return (true);
        } else {
            return (false);
        }
    }

    public function __toString()
    {
        $return = "Abonnement " . strval($this->ref_abonnement) . "\n--\n";

        $eleve = $this->eleve;
        if ($eleve) {
            $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
            $return = $return . "<div class='eleve'>" . $eleve->__toString() . "--\n</div>";
        }

        $proche = $this->proche;
        if ($proche) {
            $proche = \spamtonprof\stp_api\StpProche::cast($proche);
            $return = $return . "<div class='proche'>" . $proche->__toString() . "--\n</div>";
        }

        $formule = $this->formule;
        if ($formule) {
            $formule = \spamtonprof\stp_api\StpFormule::cast($formule);
            $return = $return . "<div class='proche'>" . $formule->__toString() . "</div>";
        }

        $plan = $this->plan;
        if ($plan) {
            $plan = \spamtonprof\stp_api\StpPlan::cast($plan);
            $return = $return . "<div class='plan'>" . $plan->__toString() . "</div>";
        }
        return ($return);
    }
}