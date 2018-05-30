<?php
namespace spamtonprof\stp_api;

class PlanPaiement implements \JsonSerializable

{

    protected $formule, 
    $tarif, 
    $ref_plan_paiement, 
    $ref_formule, 
    $nom_plan, 
    $ref_plan_stripe, 
    $ref_plan_stripe_test, 
    $ref_paypal_test, 
    $ref_paypal_prod;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
    }



    public function hydrate(array $donnees)
    
    {
        foreach ($donnees as $key => $value) 
        {
            
            $method = 'set' . ucfirst($key);
            
            if (method_exists($this, $method)) 
            {
                
                $this->$method($value);
            }
        }
    }

    public function ref_plan_paiement()
    
    {
        return $this->ref_plan_paiement;
    }

    public function ref_plan_stripe()
    
    {
        return $this->ref_plan_stripe;
    }

    public function tarif()
    
    {
        return $this->tarif;
    }

    public function nom_plan()
    
    {
        return $this->nom_plan;
    }

    public function ref_formule()
    
    {
        return $this->ref_formule;
    }

    public function formule()
    
    {
        return $this->formule;
    }

    public function setRef_plan_paiement($ref_plan_paiement)
    
    {
        $this->ref_plan_paiement = $ref_plan_paiement;
    }

    public function setRef_plan_stripe($ref_plan_stripe)
    
    {
        $this->ref_plan_stripe = $ref_plan_stripe;
    }

    public function setTarif($tarif)
    
    {
        $this->tarif = $tarif;
    }

    public function setNom_plan($nom_plan)
    
    {
        $this->nom_plan = $nom_plan;
    }

    public function setRef_formule($ref_formule)
    
    {
        $this->ref_formule = $ref_formule;
    }

    public function setFormule(Formule $formule)
    
    {
        $this->formule = $formule;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    public function setRef_paypal_test($ref_paypal_test)
    {
        $this->ref_paypal_test = $ref_paypal_test;
    }

    public function ref_paypal_test()
    
    {
        return $this->ref_paypal_test;
    }

    public function setRef_paypal_prod($ref_paypal_prod)
    {
        $this->ref_paypal_prod = $ref_paypal_prod;
    }

    public function ref_paypal_prod()
    
    {
        return $this->ref_paypal_prod;
    }
    
    /**
     *
     * @return string
     */
    public function ref_plan_stripe_test()
    {
        return $this->ref_plan_stripe_test;
    }
    
    /**
     *
     * @param string $ref_plan_stripe_test
     */
    public function setRef_plan_stripe_test($ref_plan_stripe_test)
    {
        $this->ref_plan_stripe_test = $ref_plan_stripe_test;
    }
}