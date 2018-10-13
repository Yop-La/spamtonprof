<?php
namespace spamtonprof\stp_api;

class HasTextType implements \JsonSerializable
{

    protected $ref_has_text_type, $ref_type, $ref_client, $defaut;

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

    public function getRef_has_text_type()
    {
        return $this->ref_has_text_type;
    }

    public function setRef_has_text_type($ref_has_text_type)
    {
        $this->ref_has_text_type = $ref_has_text_type;
    }

    public function getRef_type()
    {
        return $this->ref_type;
    }

    public function setRef_type($ref_type)
    {
        $this->ref_type = $ref_type;
    }

    public function getRef_client()
    {
        return $this->ref_client;
    }

    public function setRef_client($ref_client)
    {
        $this->ref_client = $ref_client;
    }

    public function getDefaut()
    {
        return $this->defaut;
    }

    public function setDefaut($defaut)
    {
        $this->defaut = $defaut;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}