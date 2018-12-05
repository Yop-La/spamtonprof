<?php
namespace spamtonprof\stp_api;

class LbcClient implements \JsonSerializable
{

    protected $ref_client, $nom_client, $prenom_client, $domain, $img_folder, $ref_mail_for_lead;

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