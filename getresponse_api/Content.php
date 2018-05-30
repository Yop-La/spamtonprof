<?php

namespace spamtonprof\getresponse_api;

class Content implements \JsonSerializable
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

    public $html; //String
    public $plain; //Object
    
    public function getHtml() { 
         return $this->html; 
    }
    public function setHtml($html) { 
         $this->html = $html; 
    }    
    public function getPlain() { 
         return $this->plain; 
    }
    public function setPlain($plain) {
         $this->plain = $plain; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}