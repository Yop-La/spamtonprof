<?php
namespace spamtonprof\stp_api;

class GmxAct implements \JsonSerializable
{

    protected $ref_gmx_act, $password, $mail, $has_redirection, $ref_compte_lbc;

    /**
     *
     * @return mixed
     */
    public function getRef_compte_lbc()
    {
        return $this->ref_compte_lbc;
    }

    /**
     *
     * @param mixed $ref_compte_lbc
     */
    public function setRef_compte_lbc($ref_compte_lbc)
    {
        $this->ref_compte_lbc = $ref_compte_lbc;
    }

    /**
     *
     * @return mixed
     */
    public function getHas_redirection()
    {
        return $this->has_redirection;
    }

    /**
     *
     * @param mixed $has_redirection
     */
    public function setHas_redirection($has_redirection)
    {
        $this->has_redirection = $has_redirection;
    }

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

    public function getRef_gmx_act()
    {
        return $this->ref_gmx_act;
    }

    public function setRef_gmx_act($ref_gmx_act)
    {
        $this->ref_gmx_act = $ref_gmx_act;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}