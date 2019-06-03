<?php
namespace spamtonprof\stp_api;

class StpPlan implements \JsonSerializable
{

    protected $ref_plan, $nom, $tarif, $ref_formule, $ref_plan_stripe, $ref_plan_stripe_test, $ref_plan_old, $formule, $defaut, $installments;

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param mixed $installments
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;
    }

    public function __construct(array $donnees = array())
    
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    
    {
        foreach ($donnees as $key => $value) {
            
            $method = 'set' . ucfirst($key);
            
            if (method_exists($this, $method)) {
                
                $this->$method($value);
            }
        }
    }
    
    

    /**
     * @return mixed
     */
    public function getDefaut()
    {
        return $this->defaut;
    }

    /**
     * @param mixed $defaut
     */
    public function setDefaut($defaut)
    {
        $this->defaut = $defaut;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_plan()
    {
        return $this->ref_plan;
    }

    /**
     *
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     *
     * @return mixed
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_plan_stripe()
    {
        return $this->ref_plan_stripe;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_plan_stripe_test()
    {
        return $this->ref_plan_stripe_test;
    }

    /**
     *
     * @param mixed $ref_plan
     */
    public function setRef_plan($ref_plan)
    {
        $this->ref_plan = $ref_plan;
    }

    /**
     *
     * @param mixed $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     *
     * @param mixed $tarif
     */
    public function setTarif($tarif)
    {
        $this->tarif = $tarif;
    }

    /**
     *
     * @param mixed $ref_formule
     */
    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    /**
     *
     * @param mixed $ref_plan_stripe
     */
    public function setRef_plan_stripe($ref_plan_stripe)
    {
        $this->ref_plan_stripe = $ref_plan_stripe;
    }

    /**
     *
     * @param mixed $ref_plan_stripe_test
     */
    public function setRef_plan_stripe_test($ref_plan_stripe_test)
    {
        $this->ref_plan_stripe_test = $ref_plan_stripe_test;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_plan_old()
    {
        return $this->ref_plan_old;
    }

    /**
     *
     * @param mixed $ref_plan_old
     */
    public function setRef_plan_old($ref_plan_old)
    {
        $this->ref_plan_old = $ref_plan_old;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    public static function cast($plan): \spamtonprof\stp_api\StpPlan
    {
        return ($plan);
    }

    /**
     *
     * @return mixed
     */
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     *
     * @param mixed $formule
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;
    }
    
    public function __toString()
    {
        $return = "Plan: " . $this->tarif  . ' € par semaine ' . "\n";
        return($return);
    }
}

