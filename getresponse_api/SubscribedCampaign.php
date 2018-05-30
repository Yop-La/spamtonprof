<?php

namespace spamtonprof\getresponse_api;

class SubscribedCampaign implements \JsonSerializable
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

    public $campaignId; //String
    public $href; //String
    
    public function getCampaignId() { 
         return $this->campaignId; 
    }
    public function setCampaignId($campaignId) { 
         $this->campaignId = $campaignId; 
    }    
    public function getHref() { 
         return $this->href; 
    }
    public function setHref($href) { 
         $this->href = $href; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}