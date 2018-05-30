<?php

namespace spamtonprof\getresponse_api;

class TriggerSettings implements \JsonSerializable
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

    public $action; //Object
    public $autoresponder; //Object
    public $clickTrackId; //Object
    public $custom; //Object
    public $dayOfCycle; //String
    public $goal; //Object
    public $newCustomValue; //Object
    public $newsletter; //Object
    public $selectedCampaigns; //String
    public $selectedSegments; //Object
    public $subscribedCampaign; //SubscribedCampaign
    public $type; //String
    
    public function getAction() { 
         return $this->action; 
    }
    public function setAction($action) { 
         $this->action = $action; 
    }    
    public function getAutoresponder() { 
         return $this->autoresponder; 
    }
    public function setAutoresponder($autoresponder) { 
         $this->autoresponder = $autoresponder; 
    }    
    public function getClickTrackId() { 
         return $this->clickTrackId; 
    }
    public function setClickTrackId($clickTrackId) { 
         $this->clickTrackId = $clickTrackId; 
    }    
    public function getCustom() { 
         return $this->custom; 
    }
    public function setCustom($custom) { 
         $this->custom = $custom; 
    }    
    public function getDayOfCycle() { 
         return $this->dayOfCycle; 
    }
    public function setDayOfCycle($dayOfCycle) { 
         $this->dayOfCycle = $dayOfCycle; 
    }    
    public function getGoal() { 
         return $this->goal; 
    }
    public function setGoal($goal) { 
         $this->goal = $goal; 
    }    
    public function getNewCustomValue() { 
         return $this->newCustomValue; 
    }
    public function setNewCustomValue($newCustomValue) { 
         $this->newCustomValue = $newCustomValue; 
    }    
    public function getNewsletter() { 
         return $this->newsletter; 
    }
    public function setNewsletter($newsletter) { 
         $this->newsletter = $newsletter; 
    }    
    public function getSelectedCampaigns() { 
         return $this->selectedCampaigns; 
    }
    public function setSelectedCampaigns($selectedCampaigns) { 
         $this->selectedCampaigns = $selectedCampaigns; 
    }    
    public function getSelectedSegments() { 
         return $this->selectedSegments; 
    }
    public function setSelectedSegments($selectedSegments) { 
         $this->selectedSegments = $selectedSegments; 
    }    
    public function getSubscribedCampaign() { 
         return $this->subscribedCampaign; 
    }
    public function setSubscribedCampaign($subscribedCampaign) { 
         $this->subscribedCampaign = $subscribedCampaign; 
    }    
    public function getType() { 
         return $this->type; 
    }
    public function setType($type) { 
         $this->type = $type; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}