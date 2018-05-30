<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class ProspectLbc implements \JsonSerializable
{

    protected $adresse_mail, $ref_prospect_lbc;

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
    public function getAdresse_mail()
    {
        return $this->adresse_mail;
    }

    /**
     * @return mixed
     */
    public function getRef_prospect_lbc()
    {
        return $this->ref_prospect_lbc;
    }

    /**
     * @param mixed $adresse_mail
     */
    public function setAdresse_mail($adresse_mail)
    {
        $this->adresse_mail = $adresse_mail;
    }

    /**
     * @param mixed $ref_prospect_lbc
     */
    public function setRef_prospect_lbc($ref_prospect_lbc)
    {
        $this->ref_prospect_lbc = $ref_prospect_lbc;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    
    
}

