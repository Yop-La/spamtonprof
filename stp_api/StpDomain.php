<?php
namespace spamtonprof\stp_api;

class StpDomain implements \JsonSerializable
{

    protected $name, $ref_domain, $mail_provider, $mx_ok, $in_black_list;

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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getRef_domain()
    {
        return $this->ref_domain;
    }

    public function setRef_domain($ref_domain)
    {
        $this->ref_domain = $ref_domain;
    }

    public function getMail_provider()
    {
        return $this->mail_provider;
    }

    public function setMail_provider($mail_provider)
    {
        $this->mail_provider = $mail_provider;
    }

    public function getMx_ok()
    {
        return $this->mx_ok;
    }

    public function setMx_ok($mx_ok)
    {
        $this->mx_ok = $mx_ok;
    }

    public function getIn_black_list()
    {
        return $this->in_black_list;
    }

    public function setIn_black_list($in_black_list)
    {
        $this->in_black_list = $in_black_list;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}