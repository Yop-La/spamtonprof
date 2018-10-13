<?php
namespace spamtonprof\stp_api;

class HasTitleType implements \JsonSerializable
{

    protected $ref_has_title_type, $ref_client, $ref_type_titre;

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

    public function getRef_has_title_type()
    {
        return $this->ref_has_title_type;
    }

    public function setRef_has_title_type($ref_has_title_type)
    {
        $this->ref_has_title_type = $ref_has_title_type;
    }

    public function getRef_client()
    {
        return $this->ref_client;
    }

    public function setRef_client($ref_client)
    {
        $this->ref_client = $ref_client;
    }

    public function getRef_type_titre()
    {
        return $this->ref_type_titre;
    }

    public function setRef_type_titre($ref_type_titre)
    {
        $this->ref_type_titre = $ref_type_titre;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}