<?php

namespace spamtonprof\getresponse_api;

class Campaign implements \JsonSerializable
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

    public $campaignId; //String
    public $confirmation; //Confirmation
    public $createdOn; //String
    public $href; //String
    public $isDefault; //Boolean
    public $languageCode; //String
    public $name; //String
    public $optinTypes; //OptinType
    public $postal; //Postal
    public $profile; //Profile
    public $subscriptionNotifications; //SubscriptionNotification
    
    public function getCampaignId() { 
         return $this->campaignId; 
    }
    public function setCampaignId($campaignId) { 
         $this->campaignId = $campaignId; 
    }    
    public function getConfirmation() { 
         return $this->confirmation; 
    }
    public function setConfirmation( $confirmation) { 
         $this->confirmation = $confirmation; 
    }    
    public function getCreatedOn() { 
         return $this->createdOn; 
    }
    public function setCreatedOn($createdOn) { 
         $this->createdOn = $createdOn; 
    }    
    public function getHref() { 
         return $this->href; 
    }
    public function setHref($href) { 
         $this->href = $href; 
    }    
    public function getIsDefault() { 
         return $this->isDefault; 
    }
    public function setIsDefault($isDefault) { 
         $this->isDefault = $isDefault; 
    }    
    public function getLanguageCode() { 
         return $this->languageCode; 
    }
    public function setLanguageCode($languageCode) { 
         $this->languageCode = $languageCode; 
    }    
    public function getName() { 
         return $this->name; 
    }
    public function setName($name) { 
         $this->name = $name; 
    }    
    public function getOptinTypes() { 
         return $this->optinTypes; 
    }
    public function setOptinTypes($optinTypes) { 
         $this->optinTypes = $optinTypes; 
    }    
    public function getPostal() { 
         return $this->postal; 
    }
    public function setPostal($postal) { 
         $this->postal = $postal; 
    }    
    public function getProfile() { 
         return $this->profile; 
    }
    public function setProfile($profile) { 
         $this->profile = $profile; 
    }    
    public function getSubscriptionNotifications() { 
         return $this->subscriptionNotifications; 
    }
    public function setSubscriptionNotifications($subscriptionNotifications) { 
         $this->subscriptionNotifications = $subscriptionNotifications; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}