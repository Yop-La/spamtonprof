<?php
namespace spamtonprof\stp_api;

class Abonnement implements \JsonSerializable

{

    protected $ref_abonnement, 
    $ref_paypal_agreement, 
    $ref_compte;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
    }

    /**
    {
        return $this->ref_stripe_subscription;

    {
        $this->ref_stripe_subscription = $ref_stripe_subscription;

    
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


    /**
    {
        return $this->ref_abonnement;

    {
        return $this->ref_paypal_agreement;

    {
        return $this->ref_compte;

    {
        $this->ref_abonnement = $ref_abonnement;

    {
        $this->ref_paypal_agreement = $ref_paypal_agreement;

    {
        $this->ref_compte = $ref_compte;

    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}