<?php
namespace spamtonprof\stp_api;

class PrenomLbc implements \JsonSerializable
{

    protected $ref_prenom, $prenom, $nb_use, $cat;

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
    public function getCat()
    {
        return $this->cat;
    }

    /**
     *
     * @param mixed $cat
     */
    public function setCat($cat)
    {
        $this->cat = $cat;
    }

    public function getRef_prenom()
    {
        return $this->ref_prenom;
    }

    public function setRef_prenom($ref_prenom)
    {
        $this->ref_prenom = $ref_prenom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    public function getNb_use()
    {
        return $this->nb_use;
    }

    public function setNb_use($nb_use)
    {
        $this->nb_use = $nb_use;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}