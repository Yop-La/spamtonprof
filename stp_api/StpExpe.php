<?php
namespace spamtonprof\stp_api;

class StpExpe implements \JsonSerializable
{

    protected $ref_expe, $email, $from_name;

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

    public function getRef_expe()
    {
        return $this->ref_expe;
    }

    public function setRef_expe($ref_expe)
    {
        $this->ref_expe = $ref_expe;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getFrom_name()
    {
        return $this->from_name;
    }

    public function setFrom_name($from_name)
    {
        $this->from_name = $from_name;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}