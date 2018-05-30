<?php
namespace spamtonprof\stp_api;

class GmailLabel implements \JsonSerializable

{

    protected $ref_label, $nom_label, $color_label;

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
        return $this->ref_label;

    {
        return $this->nom_label;

    {
        return $this->color_label;

    {
        $this->ref_label = $ref_label;

    {
        $this->nom_label = $nom_label;

    {
        $this->color_label = $color_label;

    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}