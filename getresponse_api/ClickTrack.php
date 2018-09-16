<?php

namespace spamtonprof\getresponse_api;

class ClickTrack implements \JsonSerializable
{
    
    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            
            if (gettype($value) == "object") {
                
                $method = "\\spamtonprof\\getresponse_api\\" . ucfirst($key);
                
                $sub = new $method($value);
            } elseif (gettype($value) == "string" || gettype($value) == "array") {
                
                $method = 'set' . ucfirst($key);
                
                if (method_exists($this, $method)) {
                    
                    $this->$method($value);
                }
            }
        }
    }

    public $amount; //String
    public $clickTrackId; //String
    public $name; //String
    public $url; //String
    
    public function getAmount() { 
         return $this->amount; 
    }
    public function setAmount($amount) { 
         $this->amount = $amount; 
    }    
    public function getClickTrackId() { 
         return $this->clickTrackId; 
    }
    public function setClickTrackId($clickTrackId) { 
         $this->clickTrackId = $clickTrackId; 
    }    
    public function getName() { 
         return $this->name; 
    }
    public function setName($name) { 
         $this->name = $name; 
    }    
    public function getUrl() { 
         return $this->url; 
    }
    public function setUrl($url) { 
         $this->url = $url; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}