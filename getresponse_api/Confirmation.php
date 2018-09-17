<?php

namespace spamtonprof\getresponse_api;

class Confirmation implements \JsonSerializable
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

    public $fromField; //FromField
    public $mimeType; //String
    public $redirectType; //String
    public $redirectUrl; //Object
    public $replyTo; //ReplyTo
    public $subscriptionConfirmationBodyId; //String
    public $subscriptionConfirmationSubjectId; //String
    
    public function getFromField() { 
         return $this->fromField; 
    }
    public function setFromField($fromField) { 
         $this->fromField = $fromField; 
    }    
    public function getMimeType() { 
         return $this->mimeType; 
    }
    public function setMimeType($mimeType) { 
         $this->mimeType = $mimeType; 
    }    
    public function getRedirectType() { 
         return $this->redirectType; 
    }
    public function setRedirectType($redirectType) { 
         $this->redirectType = $redirectType; 
    }    
    public function getRedirectUrl() { 
         return $this->redirectUrl; 
    }
    public function setRedirectUrl($redirectUrl) { 
         $this->redirectUrl = $redirectUrl; 
    }    
    public function getReplyTo() { 
         return $this->replyTo; 
    }
    public function setReplyTo($replyTo) { 
         $this->replyTo = $replyTo; 
    }    
    public function getSubscriptionConfirmationBodyId() { 
         return $this->subscriptionConfirmationBodyId; 
    }
    public function setSubscriptionConfirmationBodyId($subscriptionConfirmationBodyId) { 
         $this->subscriptionConfirmationBodyId = $subscriptionConfirmationBodyId; 
    }    
    public function getSubscriptionConfirmationSubjectId() { 
         return $this->subscriptionConfirmationSubjectId; 
    }
    public function setSubscriptionConfirmationSubjectId($subscriptionConfirmationSubjectId) { 
         $this->subscriptionConfirmationSubjectId = $subscriptionConfirmationSubjectId; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}