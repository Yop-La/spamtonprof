<?php

namespace spamtonprof\getresponse_api;

class SendSettings implements \JsonSerializable
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

    public $delayInHours; //Integer
    public $excludedDaysOfWeek; //Object
    public $recurrence; //Boolean
    public $sendAtHour; //String
    public $timeTravel; //Boolean
    public $type; //String
    
    public function getDelayInHours() { 
         return $this->delayInHours; 
    }
    public function setDelayInHours($delayInHours) { 
         $this->delayInHours = $delayInHours; 
    }    
    public function getExcludedDaysOfWeek() { 
         return $this->excludedDaysOfWeek; 
    }
    public function setExcludedDaysOfWeek($excludedDaysOfWeek) { 
         $this->excludedDaysOfWeek = $excludedDaysOfWeek; 
    }    
    public function getRecurrence() { 
         return $this->recurrence; 
    }
    public function setRecurrence($recurrence) { 
         $this->recurrence = $recurrence; 
    }    
    public function getSendAtHour() { 
         return $this->sendAtHour; 
    }
    public function setSendAtHour($sendAtHour) { 
         $this->sendAtHour = $sendAtHour; 
    }    
    public function getTimeTravel() { 
         return $this->timeTravel; 
    }
    public function setTimeTravel($timeTravel) { 
         $this->timeTravel = $timeTravel; 
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