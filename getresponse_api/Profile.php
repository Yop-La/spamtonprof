<?php

namespace spamtonprof\getresponse_api;

class Profile implements \JsonSerializable
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

    public $description; //String
    public $industryTagId; //Object
    public $logo; //String
    public $logoLinkUrl; //String
    public $title; //String
    
    public function getDescription() { 
         return $this->description; 
    }
    public function setDescription($description) { 
         $this->description = $description; 
    }    
    public function getIndustryTagId() { 
         return $this->industryTagId; 
    }
    public function setIndustryTagId($industryTagId) { 
         $this->industryTagId = $industryTagId; 
    }    
    public function getLogo() { 
         return $this->logo; 
    }
    public function setLogo($logo) { 
         $this->logo = $logo; 
    }    
    public function getLogoLinkUrl() { 
         return $this->logoLinkUrl; 
    }
    public function setLogoLinkUrl($logoLinkUrl) { 
         $this->logoLinkUrl = $logoLinkUrl; 
    }    
    public function getTitle() { 
         return $this->title; 
    }
    public function setTitle($title) { 
         $this->title = $title; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}