<?php
namespace spamtonprof\stp_api;

class LbcRenewalUrl implements \JsonSerializable
{

    protected $url, $ref_url, $statut, $date_ajout, $ref_compte_lbc, $date_reception;

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

    /**
     *
     * @return mixed
     */
    public function getDate_reception()
    {
        return $this->date_reception;
    }

    /**
     *
     * @param mixed $date_reception
     */
    public function setDate_reception($date_reception)
    {
        $this->date_reception = $date_reception;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getRef_url()
    {
        return $this->ref_url;
    }

    public function setRef_url($ref_url)
    {
        $this->ref_url = $ref_url;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    public function getDate_ajout()
    {
        return $this->date_ajout;
    }

    public function setDate_ajout($date_ajout)
    {
        $this->date_ajout = $date_ajout;
    }

    public function getRef_compte_lbc()
    {
        return $this->ref_compte_lbc;
    }

    public function setRef_compte_lbc($ref_compte_lbc)
    {
        $this->ref_compte_lbc = $ref_compte_lbc;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}