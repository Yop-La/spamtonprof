<?php
namespace spamtonprof\stp_api;

class Key implements \JsonSerializable

{

    private $ref_key, $name, $key;
    
    public function __construct(array $donnees)
    
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
    public function getRef_key()
    {
        return $this->ref_key;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $ref_key
     */
    public function setRef_key($ref_key)
    {
        $this->ref_key = $ref_key;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

}