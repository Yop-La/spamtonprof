<?php

namespace spamtonprof\getresponse_api;

class SubscriptionNotifications implements \JsonSerializable
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

    public $recipients; //Recipient
    public $status; //String
    
    public function getRecipients() { 
         return $this->recipients; 
    }
    public function setRecipients($recipients) { 
         $this->recipients = $recipients; 
    }    
    public function getStatus() { 
         return $this->status; 
    }
    public function setStatus($status) { 
         $this->status = $status; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}