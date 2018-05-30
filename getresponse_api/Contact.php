<?php

namespace spamtonprof\getresponse_api;

class Contact implements \JsonSerializable
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


    public $activities; //String
    public $campaign; //Campaign
    public $changedOn; //Object
    public $contactId; //String
    public $createdOn; //String
    public $dayOfCycle; //Object
    public $email; //String
    public $href; //String
    public $ipAddress; //String
    public $name; //String
    public $note; //Object
    public $origin; //String
    public $scoring; //Object
    public $timeZone; //String
    
    public function getActivities() { 
         return $this->activities; 
    }
    public function setActivities($activities) { 
         $this->activities = $activities; 
    }    
    public function getCampaign() { 
         return $this->campaign; 
    }
    public function setCampaign($campaign) { 
         $this->campaign = $campaign; 
    }    
    public function getChangedOn() { 
         return $this->changedOn; 
    }
    public function setChangedOn($changedOn) { 
         $this->changedOn = $changedOn; 
    }    
    public function getContactId() { 
         return $this->contactId; 
    }
    public function setContactId($contactId) { 
         $this->contactId = $contactId; 
    }    
    public function getCreatedOn() { 
         return $this->createdOn; 
    }
    public function setCreatedOn($createdOn) { 
         $this->createdOn = $createdOn; 
    }    
    public function getDayOfCycle() { 
         return $this->dayOfCycle; 
    }
    public function setDayOfCycle($dayOfCycle) { 
         $this->dayOfCycle = $dayOfCycle; 
    }    
    public function getEmail() { 
         return $this->email; 
    }
    public function setEmail($email) { 
         $this->email = $email; 
    }    
    public function getHref() { 
         return $this->href; 
    }
    public function setHref($href) { 
         $this->href = $href; 
    }    
    public function getIpAddress() { 
         return $this->ipAddress; 
    }
    public function setIpAddress($ipAddress) { 
         $this->ipAddress = $ipAddress; 
    }    
    public function getName() { 
         return $this->name; 
    }
    public function setName($name) { 
         $this->name = $name; 
    }    
    public function getNote() { 
         return $this->note; 
    }
    public function setNote($note) { 
         $this->note = $note; 
    }    
    public function getOrigin() { 
         return $this->origin; 
    }
    public function setOrigin($origin) { 
         $this->origin = $origin; 
    }    
    public function getScoring() { 
         return $this->scoring; 
    }
    public function setScoring($scoring) { 
         $this->scoring = $scoring; 
    }    
    public function getTimeZone() { 
         return $this->timeZone; 
    }
    public function setTimeZone($timeZone) { 
         $this->timeZone = $timeZone; 
    }    
    
    public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}