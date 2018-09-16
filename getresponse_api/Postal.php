<?php

namespace spamtonprof\getresponse_api;

class Postal implements \JsonSerializable
{
    
    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            
            if (gettype($value) == "object") {
                
                $method = "\\spamtonprof\\getresponse_api\\" . ucfirst($key);
                
                $sub = new $method($value);
                
                $method = 'set' . ucfirst($key);
                
                if (method_exists($this, $method)) {
                    
                    $this->$method($value);
                }
                
            } elseif (gettype($value) == "string" || gettype($value) == "array") {
                
                $method = 'set' . ucfirst($key);
                
                if (method_exists($this, $method)) {
                    
                    $this->$method($value);
                }
            }
        }
    }

    public $addPostalToMessages; //Boolean
    public $city; //String
    public $companyName; //String
    public $country; //String
    public $design; //String
    public $state; //String
    public $street; //String
    public $zipCode; //String
    
    public function getAddPostalToMessages() { 
         return $this->addPostalToMessages; 
    }
    public function setAddPostalToMessages($addPostalToMessages) { 
         $this->addPostalToMessages = $addPostalToMessages; 
    }    
    public function getCity() { 
         return $this->city; 
    }
    public function setCity($city) { 
         $this->city = $city; 
    }    
    public function getCompanyName() { 
         return $this->companyName; 
    }
    public function setCompanyName($companyName) { 
         $this->companyName = $companyName; 
    }    
    public function getCountry() { 
         return $this->country; 
    }
    public function setCountry($country) { 
         $this->country = $country; 
    }    
    public function getDesign() { 
         return $this->design; 
    }
    public function setDesign($design) { 
         $this->design = $design; 
    }    
    public function getState() { 
         return $this->state; 
    }
    public function setState($state) { 
         $this->state = $state; 
    }    
    public function getStreet() { 
         return $this->street; 
    }
    public function setStreet($street) { 
         $this->street = $street; 
    }    
    public function getZipCode() { 
         return $this->zipCode; 
    }
    public function setZipCode($zipCode) { 
         $this->zipCode = $zipCode; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}