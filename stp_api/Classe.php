<?php
namespace spamtonprof\stp_api;

class Classe implements \JsonSerializable

{

    protected $ref_classe, $classe, $classe_complet;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
    }

    
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
        return $this->ref_classe;

    {
        return $this->classe;

    {
        return $this->classe_complet;

    {
        $this->ref_classe = $ref_classe;

    {
        $this->classe = $classe;

    {
        $this->classe_complet = $classe_complet;

    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}