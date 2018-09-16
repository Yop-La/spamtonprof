<?php
namespace spamtonprof\stp_api;

class StpProf implements \JsonSerializable
{

    protected $email_perso, $prenom, $nom, $telephone, $ref_prof, $email_stp, $code_postal, $ville, $pays, $adresse, $date_naissance, $stripe_id, $user_id_wp, $onboarding_step, $sexe, $stripe_id_test, $ref_gmail_account, $inbox_ready, $processing_date;

    /**
     * @return mixed
     */
    public function getProcessing_date()
    {
        return $this->processing_date;
    }

    /**
     * @param mixed $processing_date
     */
    public function setProcessing_date($processing_date)
    {
        $this->processing_date = $processing_date;
    }

    /**
     * @return mixed
     */
    public function getInbox_ready()
    {
        return $this->inbox_ready;
    }

    /**
     * @return mixed
     */
    public function getProcess_inbox()
    {
        return $this->process_inbox;
    }

    /**
     * @param mixed $inbox_ready
     */
    public function setInbox_ready($inbox_ready)
    {
        $this->inbox_ready = $inbox_ready;
    }

    /**
     * @param mixed $process_inbox
     */
    public function setProcess_inbox($process_inbox)
    {
        $this->process_inbox = $process_inbox;
    }

    /**
     * @return mixed
     */
    public function getRef_gmail_account()
    {
        return $this->ref_gmail_account;
    }

    /**
     * @param mixed $ref_gmail_account
     */
    public function setRef_gmail_account($ref_gmail_account)
    {
        $this->ref_gmail_account = $ref_gmail_account;
    }

    /**
     *
     * @return mixed
     */
    public function getStripe_id_test()
    {
        return $this->stripe_id_test;
    }

    /**
     *
     * @param mixed $stripe_id_test
     */
    public function setStripe_id_test($stripe_id_test)
    {
        $this->stripe_id_test = $stripe_id_test;
    }

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);
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

    public function getEmail_perso()
    {
        return $this->email_perso;
    }

    public function setEmail_perso($email_perso)
    {
        $this->email_perso = $email_perso;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    public function getEmail_stp()
    {
        return $this->email_stp;
    }

    public function setEmail_stp($email_stp)
    {
        $this->email_stp = $email_stp;
    }

    public function getCode_postal()
    {
        return $this->code_postal;
    }

    public function setCode_postal($code_postal)
    {
        $this->code_postal = $code_postal;
    }

    public function getVille()
    {
        return $this->ville;
    }

    public function setVille($ville)
    {
        $this->ville = $ville;
    }

    public function getPays()
    {
        return $this->pays;
    }

    public function setPays($pays)
    {
        $this->pays = $pays;
    }

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    public function getDate_naissance()
    {
        return $this->date_naissance;
    }

    public function setDate_naissance($date_naissance)
    {
        $this->date_naissance = $date_naissance;
    }

    public function getStripe_id()
    {
        return $this->stripe_id;
    }

    public function setStripe_id($stripe_id)
    {
        $this->stripe_id = $stripe_id;
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
    public function getUser_id_wp()
    {
        return $this->user_id_wp;
    }

    /**
     *
     * @param mixed $user_id_wp
     */
    public function setUser_id_wp($user_id_wp)
    {
        $this->user_id_wp = $user_id_wp;
    }

    /**
     *
     * @return mixed
     */
    public function getOnboarding()
    {
        return $this->onboarding;
    }

    /**
     *
     * @param mixed $onboarding
     */
    public function setOnboarding($onboarding)
    {
        $this->onboarding = $onboarding;
    }

    /**
     *
     * @return mixed
     */
    public function getOnboarding_step()
    {
        return $this->onboarding_step;
    }

    /**
     *
     * @param mixed $onboarding_step
     */
    public function setOnboarding_step($onboarding_step)
    {
        $this->onboarding_step = $onboarding_step;
    }

    public function toArray()
    {
        $retour = [];
        
        foreach ($this as $key => $value) {
            $retour[$key] = $value;
        }
        return ($retour);
    }

    public function toSlack($header)
    {
        $retour = [];
        
        $retour[] = $header;
        $retour[] = " ";
        foreach ($this as $key => $value) {
            if ($value) {
                $retour[] = $key . " : " . $value;
            }
        }
        return ($retour);
    }

    public static function cast($prof): \spamtonprof\stp_api\StpProf
    {
        return ($prof);
    }

    /**
     *
     * @return mixed
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     *
     * @param mixed $sexe
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;
    }

}