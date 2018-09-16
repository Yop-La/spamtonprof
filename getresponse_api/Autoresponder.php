<?php

namespace spamtonprof\getresponse_api;

class Autoresponder implements \JsonSerializable
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

    public $autoresponderId; //String
    public $campaignId; //String
    public $clickTracks; //ClickTrack
    public $content; //Content
    public $createdOn; //String
    public $editor; //String
    public $flags; //String
    public $fromField; //FromField
    public $href; //String
    public $name; //String
    public $replyTo; //ReplyTo
    public $sendSettings; //SendSetting
    public $statistics; //Statistic
    public $status; //String
    public $subject; //String
    public $triggerSettings; //TriggerSetting
    
    public function getAutoresponderId() { 
         return $this->autoresponderId; 
    }
    public function setAutoresponderId($autoresponderId) { 
         $this->autoresponderId = $autoresponderId; 
    }    
    public function getCampaignId() { 
         return $this->campaignId; 
    }
    public function setCampaignId($campaignId) { 
         $this->campaignId = $campaignId; 
    }    
    public function getClickTracks() { 
         return $this->clickTracks; 
    }
    public function setClickTracks($clickTracks) { 
         $this->clickTracks = $clickTracks; 
    }    
    public function getContent() { 
         return $this->content; 
    }
    public function setContent($content) { 
         $this->content = $content; 
    }    
    public function getCreatedOn() { 
         return $this->createdOn; 
    }
    public function setCreatedOn($createdOn) { 
         $this->createdOn = $createdOn; 
    }    
    public function getEditor() { 
         return $this->editor; 
    }
    public function setEditor($editor) { 
         $this->editor = $editor; 
    }    
    public function getFlags() { 
         return $this->flags; 
    }
    public function setFlags($flags) { 
         $this->flags = $flags; 
    }    
    public function getFromField() : FromField { 
         return $this->fromField; 
    }
    public function setFromField($fromField) { 
         $this->fromField = $fromField; 
    }    
    public function getHref() { 
         return $this->href; 
    }
    public function setHref($href) { 
         $this->href = $href; 
    }    
    public function getName() { 
         return $this->name; 
    }
    public function setName($name) { 
         $this->name = $name; 
    }    
    public function getReplyTo() { 
         return $this->replyTo; 
    }
    public function setReplyTo($replyTo) { 
         $this->replyTo = $replyTo; 
    }    
    public function getSendSettings() { 
         return $this->sendSettings; 
    }
    public function setSendSettings($sendSettings) { 
         $this->sendSettings = $sendSettings; 
    }    
    public function getStatistics() { 
         return $this->statistics; 
    }
    public function setStatistics($statistics) { 
         $this->statistics = $statistics; 
    }    
    public function getStatus() { 
         return $this->status; 
    }
    public function setStatus($status) { 
         $this->status = $status; 
    }    
    public function getSubject() { 
         return $this->subject; 
    }
    public function setSubject($subject) { 
         $this->subject = $subject; 
    }    
    public function getTriggerSettings() { 
         return $this->triggerSettings; 
    }
    public function setTriggerSettings($triggerSettings) { 
         $this->triggerSettings = $triggerSettings; 
    }    
    
        public function jsonSerialize()

    {

        $vars = get_object_vars($this);

        

        return $vars;

    }

}