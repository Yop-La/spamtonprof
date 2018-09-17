<?php

namespace spamtonprof\getresponse_api;

class Statistics implements \JsonSerializable
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

    public $clickRate; //Integer
    public $delivered; //Integer
    public $openRate; //Float
    
    public function getClickRate() { 
         return $this->clickRate; 
    }
    public function setClickRate($clickRate) { 
         $this->clickRate = $clickRate; 
    }    
    public function getDelivered() { 
         return $this->delivered; 
    }
    public function setDelivered($delivered) { 
         $this->delivered = $delivered; 
    }    
    public function getOpenRate() { 
         return $this->openRate; 
    }
    public function setOpenRate($openRate) { 
         $this->openRate = $openRate; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}