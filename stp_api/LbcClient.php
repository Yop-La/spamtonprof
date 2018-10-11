<?php
namespace spamtonprof\stp_api;

class LbcClient implements \JsonSerializable
{

    protected $ref_client, $nom_client, $prenom_client, $domain;

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