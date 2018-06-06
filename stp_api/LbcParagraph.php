<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class LbcParagraph implements \JsonSerializable
{

    protected $ref_para,
    $ref_texte,
    $position,
    $paragraph;

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
    public function getRef_para()
    {
        return $this->ref_para;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function getParagraph()
    {
        return $this->paragraph;
    }

    /**
     * @param mixed $ref_para
     */
    public function setRef_para($ref_para)
    {
        $this->ref_para = $ref_para;
    }

    

    /**
     * @return mixed
     */
    public function getRef_texte()
    {
        return $this->ref_texte;
    }

    /**
     * @param mixed $ref_texte
     */
    public function setRef_texte($ref_texte)
    {
        $this->ref_texte = $ref_texte;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param mixed $paragraph
     */
    public function setParagraph($paragraph)
    {
        $this->paragraph = $paragraph;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

  
}

