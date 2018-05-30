<?php

namespace spamtonprof\getresponse_api;

class OptinTypes implements \JsonSerializable
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

    public $api; //String
    public $email; //String
    public $import; //String
    public $webform; //String
    
    public function getApi() { 
         return $this->api; 
    }
    public function setApi($api) { 
         $this->api = $api; 
    }    
    public function getEmail() { 
         return $this->email; 
    }
    public function setEmail($email) { 
         $this->email = $email; 
    }    
    public function getImport() { 
         return $this->import; 
    }
    public function setImport($import) { 
         $this->import = $import; 
    }    
    public function getWebform() { 
         return $this->webform; 
    }
    public function setWebform($webform) { 
         $this->webform = $webform; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}