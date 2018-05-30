<?php
namespace spamtonprof\stp_api;

class Facture implements \JsonSerializable

{

    protected $ref_facture, 
    $mois, 
    $tarif_base,

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

    {
        return $this->ref_facture;

    {
        return $this->mois;

    {
        return $this->annee;

    {
        return $this->tarif_base;

    {
        return $this->remise_interruption;

    {
        return $this->remise_arret;

    {
        return $this->tarif_final;

    {
        return $this->paiement_recu;

    {
        return $this->a_payer;

    {
        return $this->ref_compte;

    {
        $this->ref_facture = $ref_facture;

    {
        $this->mois = $mois;

    {
        $this->annee = $annee;

    {
        $this->tarif_base = $tarif_base;

    {
        $this->remise_interruption = $remise_interruption;

    {
        $this->remise_arret = $remise_arret;

    {
        $this->tarif_final = $tarif_final;

    {
        $this->paiement_recu = $paiement_recu;

    {
        $this->a_payer = $a_payer;

    {
        $this->ref_compte = $ref_compte;

    {
        return $this->remise_demarrage;

    {
        $this->remise_demarrage = $remise_demarrage;

    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}