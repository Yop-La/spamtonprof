<?php
namespace spamtonprof\stp_api;

class StpCoupon implements \JsonSerializable
{

    protected $ref_coupon, $ref_stripe, $name, $client_limit, $description, $ref_stripe_test, $pct_off;

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
    public function getPct_off()
    {
        return $this->pct_off;
    }

    /**
     *
     * @param mixed $pct_off
     */
    public function setPct_off($pct_off)
    {
        $this->pct_off = $pct_off;
    }

    /**
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_stripe_test()
    {
        return $this->ref_stripe_test;
    }

    /**
     *
     * @param mixed $ref_stripe_test
     */
    public function setRef_stripe_test($ref_stripe_test)
    {
        $this->ref_stripe_test = $ref_stripe_test;
    }

    /**
     *
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getRef_coupon()
    {
        return $this->ref_coupon;
    }

    public function setRef_coupon($ref_coupon)
    {
        $this->ref_coupon = $ref_coupon;
    }

    public function getRef_stripe()
    {
        return $this->ref_stripe;
    }

    public function setRef_stripe($ref_stripe)
    {
        $this->ref_stripe = $ref_stripe;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getClient_limit()
    {
        return $this->client_limit;
    }

    public function setClient_limit($client_limit)
    {
        $this->client_limit = $client_limit;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}