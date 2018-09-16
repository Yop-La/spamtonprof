<?php
namespace spamtonprof\stp_api;

class Classe implements \JsonSerializable

{

    protected $ref_classe, $classe, $classe_complet;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
    }
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
    }    /**     * @return mixed     */    public function getRef_classe()
    {
        return $this->ref_classe;    }
    /**     * @return mixed     */    public function getClasse()
    {
        return $this->classe;    }
    /**     * @return mixed     */    public function getClasse_complet()
    {
        return $this->classe_complet;    }
    /**     * @param mixed $ref_classe     */    public function setRef_classe($ref_classe)
    {
        $this->ref_classe = $ref_classe;    }
    /**     * @param mixed $classe     */    public function setClasse($classe)
    {
        $this->classe = $classe;    }
    /**     * @param mixed $classe_complet     */    public function setClasse_complet($classe_complet)
    {
        $this->classe_complet = $classe_complet;    }
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}