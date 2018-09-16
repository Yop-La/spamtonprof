<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class LbcBaseText implements \JsonSerializable
{

    protected $ref_text, $ref_text_cat;

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
    public function getRef_text()
    {
        return $this->ref_text;
    }

    /**
     * @return mixed
     */
    public function getRef_text_cat()
    {
        return $this->ref_text_cat;
    }
    
    /**
     * @param mixed $ref_text
     */
    public function setRef_text($ref_text)
    {
        $this->ref_text = $ref_text;
    }

    /**
     * @param mixed $ref_text_cat
     */
    public function setRef_text_cat($ref_text_cat)
    {
        $this->ref_text_cat = $ref_text_cat;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

  
}

