<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class LbcAccount implements \JsonSerializable
{

    protected $ref_compte, $mail, $password, $nb_annonces_online, $date_derniere_activite, $disabled, $date_of_disabling, $ref_client, $ref_expe, $expe, $code_promo, $prenom_client, $nom_client, $controle_date, $objectID, $date_creation, $telephone, $cookie, $user_id, $prenom, $date_publication, $open, $nb_successful_campaigns, $nb_failed_campaigns, $uncheckable, $cookie_expired, $cookie_update;

    
    
    /**
     * @return mixed
     */
    public function getCookie_update()
    {
        return $this->cookie_update;
    }

    /**
     * @param mixed $cookie_update
     */
    public function setCookie_update($cookie_update)
    {
        $this->cookie_update = $cookie_update;
    }

    /**
     *
     * @return mixed
     */
    public function getCookie_expired()
    {
        return $this->cookie_expired;
    }

    /**
     *
     * @param mixed $cookie_expired
     */
    public function setCookie_expired($cookie_expired)
    {
        $this->cookie_expired = $cookie_expired;
    }

    /**
     *
     * @return mixed
     */
    public function getUncheckable()
    {
        return $this->uncheckable;
    }

    /**
     *
     * @param mixed $uncheckable
     */
    public function setUncheckable($uncheckable)
    {
        $this->uncheckable = $uncheckable;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_successful_campaigns()
    {
        return $this->nb_successful_campaigns;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_failed_campaigns()
    {
        return $this->nb_failed_campaigns;
    }

    /**
     *
     * @param mixed $nb_successful_campaigns
     */
    public function setNb_successful_campaigns($nb_successful_campaigns)
    {
        $this->nb_successful_campaigns = $nb_successful_campaigns;
    }

    /**
     *
     * @param mixed $nb_failed_campaigns
     */
    public function setNb_failed_campaigns($nb_failed_campaigns)
    {
        $this->nb_failed_campaigns = $nb_failed_campaigns;
    }

    /**
     *
     * @return mixed
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     *
     * @param mixed $open
     */
    public function setOpen($open)
    {
        $this->open = $open;
    }

    public function __construct(array $donnees = array())

    {
        $this->hydrate($donnees);
    }

    /**
     *
     * @return mixed
     */
    public function getDate_publication()
    {
        return $this->date_publication;
    }

    /**
     *
     * @param mixed $date_publication
     */
    public function setDate_publication($date_publication)
    {
        $this->date_publication = $date_publication;
    }

    /**
     *
     * @return mixed
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     *
     * @param mixed $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     *
     * @return mixed
     */
    public function getUser_id()
    {
        return $this->user_id;
    }

    /**
     *
     * @param mixed $user_id
     */
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     *
     * @return mixed
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     *
     * @param mixed $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     *
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     *
     * @param mixed $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
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

    /**
     *
     * @return mixed
     */
    public function getDate_creation()
    {
        return $this->date_creation;
    }

    /**
     *
     * @param mixed $date_creation
     */
    public function setDate_creation($date_creation)
    {
        $this->date_creation = $date_creation;
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
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     *
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_annonces_online()
    {
        return $this->nb_annonces_online;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_derniere_activite()
    {
        return $this->date_derniere_activite;
    }

    /**
     *
     * @return mixed
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_of_disabling()
    {
        return $this->date_of_disabling;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_client()
    {
        return $this->ref_client;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_expe()
    {
        return $this->ref_expe;
    }

    /**
     *
     * @return mixed
     */
    public function getExpe()
    {
        return $this->expe;
    }

    /**
     *
     * @return mixed
     */
    public function getCode_promo()
    {
        return $this->code_promo;
    }

    /**
     *
     * @return mixed
     */
    public function getPrenom_client()
    {
        return $this->prenom_client;
    }

    /**
     *
     * @return mixed
     */
    public function getNom_client()
    {
        return $this->nom_client;
    }

    /**
     *
     * @return mixed
     */
    public function getControle_date()
    {
        return $this->controle_date;
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
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    /**
     *
     * @param mixed $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     *
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     *
     * @param mixed $nb_annonces_online
     */
    public function setNb_annonces_online($nb_annonces_online)
    {
        $this->nb_annonces_online = $nb_annonces_online;
    }

    /**
     *
     * @param mixed $date_derniere_activite
     */
    public function setDate_derniere_activite($date_derniere_activite)
    {
        $this->date_derniere_activite = $date_derniere_activite;
    }

    /**
     *
     * @param mixed $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     *
     * @param mixed $date_of_disabling
     */
    public function setDate_of_disabling($date_of_disabling)
    {
        $this->date_of_disabling = $date_of_disabling;
    }

    /**
     *
     * @param mixed $ref_client
     */
    public function setRef_client($ref_client)
    {
        $this->ref_client = $ref_client;
    }

    /**
     *
     * @param mixed $ref_expe
     */
    public function setRef_expe($ref_expe)
    {
        $this->ref_expe = $ref_expe;
    }

    /**
     *
     * @param mixed $expe
     */
    public function setExpe($expe)
    {
        $this->expe = $expe;
    }

    /**
     *
     * @param mixed $code_promo
     */
    public function setCode_promo($code_promo)
    {
        $this->code_promo = $code_promo;
    }

    /**
     *
     * @param mixed $prenom_client
     */
    public function setPrenom_client($prenom_client)
    {
        $this->prenom_client = $prenom_client;
    }

    /**
     *
     * @param mixed $nom_client
     */
    public function setNom_client($nom_client)
    {
        $this->nom_client = $nom_client;
    }

    /**
     *
     * @param mixed $controle_date
     */
    public function setControle_date($controle_date)
    {
        $this->controle_date = $controle_date;
    }

    /**
     *
     * @param mixed $objectID
     */
    public function setObjectID($objectID)
    {
        $this->objectID = $objectID;
    }
}

