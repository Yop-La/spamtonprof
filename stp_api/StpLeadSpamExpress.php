<?php
namespace spamtonprof\stp_api;

class StpLeadSpamExpress implements \JsonSerializable
{

    protected $ref_lead, $name, $email;

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

    public function getRef_lead()
    {
        return $this->ref_lead;
    }

    public function setRef_lead($ref_lead)
    {
        $this->ref_lead = $ref_lead;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}