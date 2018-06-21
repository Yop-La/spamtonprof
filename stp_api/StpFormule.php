<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class StpFormule implements \JsonSerializable
{

    protected $formule,  $ref_formule;

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
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     * @param mixed $formule
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
    /**
     * @return mixed
     */
    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    /**
     * @param mixed $ref_formule
     */
    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    
    

  
}

