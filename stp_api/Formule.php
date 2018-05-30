<?php
namespace spamtonprof\stp_api;

class Formule implements \JsonSerializable

{

    protected $formule, 
    $ref_formule, 
    $classes, 
    $maths, 
    $physique, 
    $id_stripe, 
    $id_stripe_test, 
    $francais;

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
    }

    public function formule()
    
    {
        return $this->formule;
    }

    public function ref_formule()
    
    {
        return $this->ref_formule;
    }

    public function classes()
    
    {
        return $this->classes;
    }

    public function maths()
    
    {
        return $this->maths;
    }

    public function physique()
    
    {
        return $this->physique;
    }

    public function francais()
    
    {
        return $this->francais;
    }

    public function id_stripe()
    
    {
        return $this->id_stripe;
    }

    public function setRef_formule($ref_formule)
    
    {
        $this->ref_formule = $ref_formule;
    }

    public function setFormule($formule)
    
    {
        $this->formule = $formule;
    }

    public function setClasses($classes)
    
    {
        $this->classes = $classes;
    }

    public function setMaths($maths)
    
    {
        $this->maths = boolval($maths);
    }

    public function setPhysique($physique)
    
    {
        $this->physique = boolval($physique);
    }

    public function setFrancais($francais)
    
    {
        $this->francais = boolval($francais);
    }

    public function setId_stripe($id_stripe)
    
    {
        $this->id_stripe = $id_stripe;
    }

    /**
     *
     * @return string
     */
    public function id_stripe_test()
    {
        return $this->id_stripe_test;
    }

    /**
     *
     * @param string $id_stripe_test
     */
    public function setId_stripe_test($id_stripe_test)
    {
        $this->id_stripe_test = $id_stripe_test;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}