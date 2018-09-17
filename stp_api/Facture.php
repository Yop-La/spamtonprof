<?php
namespace spamtonprof\stp_api; /** *  *  * @author alexg * pour faire la facturation manuelle de seb ( rien à voir avec la factu stripe ou paypal ) */

class Facture implements \JsonSerializable

{

    protected $ref_facture, 
    $mois,         $annee,
    $tarif_base,        $remise_interruption,        $remise_arret,    $remise_demarrage,        $tarif_final,        $paiement_recu,        $a_payer,        $ref_compte;

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
    /**     * @return mixed     */    public function getRef_facture()
    {
        return $this->ref_facture;    }
    /**     * @return mixed     */    public function getMois()
    {
        return $this->mois;    }
    /**     * @return mixed     */    public function getAnnee()
    {
        return $this->annee;    }
    /**     * @return mixed     */    public function getTarif_base()
    {
        return $this->tarif_base;    }
    /**     * @return mixed     */    public function getRemise_interruption()
    {
        return $this->remise_interruption;    }
    /**     * @return mixed     */    public function getRemise_arret()
    {
        return $this->remise_arret;    }
    /**     * @return mixed     */    public function getTarif_final()
    {
        return $this->tarif_final;    }
    /**     * @return mixed     */    public function getPaiement_recu()
    {
        return $this->paiement_recu;    }
    /**     * @return mixed     */    public function getA_payer()
    {
        return $this->a_payer;    }
    /**     * @return mixed     */    public function getRef_compte()
    {
        return $this->ref_compte;    }
    /**     * @param mixed $ref_facture     */    public function setRef_facture($ref_facture)
    {
        $this->ref_facture = $ref_facture;    }
    /**     * @param mixed $mois     */    public function setMois($mois)
    {
        $this->mois = $mois;    }
    /**     * @param mixed $annee     */    public function setAnnee($annee)
    {
        $this->annee = $annee;    }
    /**     * @param mixed $tarif_base     */    public function setTarif_base($tarif_base)
    {
        $this->tarif_base = $tarif_base;    }
    /**     * @param mixed $remise_interruption     */    public function setRemise_interruption($remise_interruption)
    {
        $this->remise_interruption = $remise_interruption;    }
    /**     * @param mixed $remise_arret     */    public function setRemise_arret($remise_arret)
    {
        $this->remise_arret = $remise_arret;    }
    /**     * @param mixed $tarif_final     */    public function setTarif_final($tarif_final)
    {
        $this->tarif_final = $tarif_final;    }
    /**     * @param mixed $paiement_recu     */    public function setPaiement_recu($paiement_recu)
    {
        $this->paiement_recu = $paiement_recu;    }
    /**     * @param mixed $a_payer     */    public function setA_payer($a_payer)
    {
        $this->a_payer = $a_payer;    }
    /**     * @param mixed $ref_compte     */    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;    }
            /**     * @return mixed     */    public function getRemise_demarrage()
    {
        return $this->remise_demarrage;    }
    /**     * @param mixed $remise_demarrage     */    public function setRemise_demarrage($remise_demarrage)
    {
        $this->remise_demarrage = $remise_demarrage;    }
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}