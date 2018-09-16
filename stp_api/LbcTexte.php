<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class LbcTexte implements \JsonSerializable
{

    protected $ref_texte, $texte, $type;

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
    public function getRef_texte()
    {
        return $this->ref_texte;
    }

    /**
     * @return mixed
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $ref_texte
     */
    public function setRef_texte($ref_texte)
    {
        $this->ref_texte = $ref_texte;
    }

    /**
     * @param mixed $texte
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
    
    public function __toString(){
        
        return($this->texte);
        
    }

  
}

