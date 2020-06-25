<?php
namespace spamtonprof\stp_api;

class StpOffreSpamExpress implements \JsonSerializable
{

    protected $ref_offre, $ref_pole, $ref_categorie_scolaire, $name, $price, $main, $title, $cat, $pole, $stripe_product, $stripe_product_test, $stripe_price_test, $stripe_price, $checkout_session_id;

    /**
     *
     * @return mixed
     */
    public function getCheckout_session_id()
    {
        return $this->checkout_session_id;
    }

    /**
     *
     * @param mixed $checkout_session_id
     */
    public function setCheckout_session_id($checkout_session_id)
    {
        $this->checkout_session_id = $checkout_session_id;
    }

    /**
     *
     * @return mixed
     */
    public function getStripe_price_test()
    {
        return $this->stripe_price_test;
    }

    /**
     *
     * @return mixed
     */
    public function getStripe_price()
    {
        return $this->stripe_price;
    }

    /**
     *
     * @param mixed $stripe_price_test
     */
    public function setStripe_price_test($stripe_price_test)
    {
        $this->stripe_price_test = $stripe_price_test;
    }

    /**
     *
     * @param mixed $stripe_price
     */
    public function setStripe_price($stripe_price)
    {
        $this->stripe_price = $stripe_price;
    }

    /**
     *
     * @return mixed
     */
    public function getStripe_product()
    {
        return $this->stripe_product;
    }

    /**
     *
     * @return mixed
     */
    public function getStripe_product_test()
    {
        return $this->stripe_product_test;
    }

    /**
     *
     * @param mixed $stripe_product
     */
    public function setStripe_product($stripe_product)
    {
        $this->stripe_product = $stripe_product;
    }

    /**
     *
     * @param mixed $stripe_product_test
     */
    public function setStripe_product_test($stripe_product_test)
    {
        $this->stripe_product_test = $stripe_product_test;
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
     * @return mixed
     */
    public function getPole()
    {
        return $this->pole;
    }

    /**
     *
     * @param mixed $cat
     */
    public function setCat($cat)
    {
        $this->cat = $cat;
    }

    /**
     *
     * @param mixed $pole
     */
    public function setPole(StpPole $pole)
    {
        $this->pole = $pole;
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

    public function getRef_offre()
    {
        return $this->ref_offre;
    }

    public function setRef_offre($ref_offre)
    {
        $this->ref_offre = $ref_offre;
    }

    public function getRef_pole()
    {
        return $this->ref_pole;
    }

    public function setRef_pole($ref_pole)
    {
        $this->ref_pole = $ref_pole;
    }

    public function getRef_categorie_scolaire()
    {
        return $this->ref_categorie_scolaire;
    }

    public function setRef_categorie_scolaire($ref_categorie_scolaire)
    {
        $this->ref_categorie_scolaire = $ref_categorie_scolaire;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getMain()
    {
        return $this->main;
    }

    public function setMain($main)
    {
        $this->main = $main;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}