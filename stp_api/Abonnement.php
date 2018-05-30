<?php
namespace spamtonprof\stp_api;

class Abonnement implements \JsonSerializable

{

    protected $ref_abonnement, 
    $ref_paypal_agreement,         $ref_stripe_subscription,
    $ref_compte;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
    }

    /**     * @return mixed     */    public function ref_stripe_subscription()
    {
        return $this->ref_stripe_subscription;    }
    /**     * @param mixed $ref_stripe_subscription     */    public function setRef_stripe_subscription($ref_stripe_subscription)
    {
        $this->ref_stripe_subscription = $ref_stripe_subscription;    }
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


    /**     * @return mixed     */    public function ref_abonnement()
    {
        return $this->ref_abonnement;    }
    /**     * @return mixed     */    public function ref_paypal_agreement()
    {
        return $this->ref_paypal_agreement;    }
    /**     * @return mixed     */    public function ref_compte()
    {
        return $this->ref_compte;    }
    /**     * @param mixed $ref_paypal_abonnement     */    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;    }
    /**     * @param mixed $ref_paypal_agreement     */    public function setRef_paypal_agreement($ref_paypal_agreement)
    {
        $this->ref_paypal_agreement = $ref_paypal_agreement;    }
    /**     * @param mixed $ref_compte     */    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;    }
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}