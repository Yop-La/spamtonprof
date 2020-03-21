<?php
namespace spamtonprof\stp_api;

class StpDomain implements \JsonSerializable
{

    protected $name, $ref_domain, $mail_provider, $mx_ok, $in_black_list, $root, $subdomain, $disabled, $nb_use;

    /**
     *
     * @return mixed
     */
    public function getNb_use()
    {
        return $this->nb_use;
    }

    /**
     *
     * @param mixed $nb_use
     */
    public function setNb_use($nb_use)
    {
        $this->nb_use = $nb_use;
    }

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);

        if (! is_null($this->name)) {
            $domain_part = explode('.', $this->name);
            $nbPart = count($domain_part);

            $this->root = implode('.', array(
                $domain_part[$nbPart - 2],
                $domain_part[$nbPart - 1]
            ));

            unset($domain_part[$nbPart - 2]);
            unset($domain_part[$nbPart - 1]);

            $this->subdomain = implode('.', $domain_part);
        }
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
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     *
     * @param mixed $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     *
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     *
     * @return mixed
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     *
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     *
     * @param mixed $subdomain
     */
    public function setSubdomain($subdomain)
    {
        $this->subdomain = $subdomain;
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