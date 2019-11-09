<?php
namespace spamtonprof\stp_api;

class LbcClient implements \JsonSerializable
{

    protected $ref_client, $nom_client, $prenom_client, $domain, $img_folder, $ref_reponse_lbc, $ref_cat_prenom, $label, $auto_reply, $category, $link;

    
    
    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     *
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     *
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     *
     * @return mixed
     */
    public function getAuto_reply()
    {
        return $this->auto_reply;
    }

    /**
     *
     * @param mixed $auto_reply
     */
    public function setAuto_reply($auto_reply)
    {
        $this->auto_reply = $auto_reply;
    }

    /**
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     *
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);
    }

    /**
     *
     * @return mixed
     */
    public function getRef_mail_for_lead()
    {
        return $this->ref_mail_for_lead;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_cat_prenom()
    {
        return $this->ref_cat_prenom;
    }

    /**
     *
     * @param mixed $ref_cat_prenom
     */
    public function setRef_cat_prenom($ref_cat_prenom)
    {
        $this->ref_cat_prenom = $ref_cat_prenom;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_reponse_lbc()
    {
        return $this->ref_reponse_lbc;
    }

    /**
     *
     * @param mixed $ref_reponse_lbc
     */
    public function setRef_reponse_lbc($ref_reponse_lbc)
    {
        $this->ref_reponse_lbc = $ref_reponse_lbc;
    }

    /**
     *
     * @param mixed $ref_mail_for_lead
     */
    public function setRef_mail_for_lead($ref_mail_for_lead)
    {
        $this->ref_mail_for_lead = $ref_mail_for_lead;
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

    /**
     *
     * @return mixed
     */
    public function getImg_folder()
    {
        return $this->img_folder;
    }

    /**
     *
     * @param mixed $img_folder
     */
    public function setImg_folder($img_folder)
    {
        $this->img_folder = $img_folder;
    }

    public function getRef_client()
    {
        return $this->ref_client;
    }

    public function setRef_client($ref_client)
    {
        $this->ref_client = $ref_client;
    }

    public function getNom_client()
    {
        return $this->nom_client;
    }

    public function setNom_client($nom_client)
    {
        $this->nom_client = $nom_client;
    }

    public function getPrenom_client()
    {
        return $this->prenom_client;
    }

    public function setPrenom_client($prenom_client)
    {
        $this->prenom_client = $prenom_client;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}