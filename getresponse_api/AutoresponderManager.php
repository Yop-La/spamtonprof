<?php
namespace spamtonprof\getresponse_api;

use PDO;
use spamtonprof\stp_api\CampaignManager;

class AutoresponderManager

{

    private $getresponse;

    public function __construct()
    
    {
        $this->getresponse = new \GetResponse(GR_API); //todostp faire une classe qui instancie une fois getresponse pour les manager de getresponse_api
    }

    public function get($name)
    
    {}
    
    public function deleteAll(string $nomCampaign){
        
        $campaignMg = new \spamtonprof\getresponse_api\CampaignManager();
        
        $campaign = $campaignMg -> get($nomCampaign);
        
        $params = array(
            "query" => array(
                "campaignId" => $campaign ->campaignId
            )
        );
        
        $autoresponders = $this->getresponse->getAutoresponders($params);
        
        foreach ($autoresponders as $autoresponder) {
            
            $autoresponder = new \spamtonprof\getresponse_api\Autoresponder($autoresponder);
            
            $this->getresponse->deleteAutoresponder($autoresponder->autoresponderId);
        }
            
        
    }

    public function copyAll(string $nomCampaignSource, string $nomCampaignCible, string  $adresseExpe, string $adresseReplyTo)
    {
        $arrayNomCible = explode("_", $nomCampaignCible);
        
        $numero_campaign_cible = $arrayNomCible [count($arrayNomCible) - 1];
        
        $campaignMg = new \spamtonprof\getresponse_api\CampaignManager();
        
        $fromFieldMg = new FromFieldManager();
        
        $campaignSource = $campaignMg -> get($nomCampaignSource);
        
        $campaignCible = $campaignMg -> get($nomCampaignCible);
     
        $fromField = $fromFieldMg -> get($adresseExpe);
        
        $replyTo = $fromFieldMg -> get($adresseReplyTo);
        
        $params = array(
            "query" => array(
                "campaignId" => $campaignSource->campaignId
            )
        );
        
        $autoresponders = $this->getresponse->getAutoresponders($params);
        
        echo('la');
        prettyPrint($autoresponders);
        
        foreach ($autoresponders as $autoresponder) {
            
            $autoresponder = $this->getresponse->getAutoresponder($autoresponder->autoresponderId);
            $autoresponder = new \spamtonprof\getresponse_api\Autoresponder($autoresponder);
            
            unset($autoresponder->href);
            unset($autoresponder->autoresponderId);
            unset($autoresponder->campaignId);
            unset($autoresponder->fromField->href);
            unset($autoresponder->replyTo->href);
            unset($autoresponder->createdOn);
            unset($autoresponder->editor);
            unset($autoresponder->statistics);
            unset($autoresponder->clickTracks);
            
            
            $autoresponder-> setFromField($fromField->fromFieldId);
            
            $autoresponder-> setReplyTo($replyTo->fromFieldId);
            
            $autoresponder -> name = $autoresponder-> name . " - " . $numero_campaign_cible;
            
            $triggerSettings = new TriggerSettings($autoresponder->triggerSettings);
            
            unset ($triggerSettings -> autoresponder );
            
            unset ($triggerSettings -> newsletter );
            
            unset ($triggerSettings -> clickTrackId );
            
            unset ($triggerSettings -> goal );
            
            unset ($triggerSettings -> custom );

            unset ($triggerSettings -> action );

            unset ($triggerSettings -> newCustomValue );
            
            unset ($triggerSettings -> subscribedCampaign );
            
            unset ($triggerSettings -> selectedSegments );
            
            $triggerSettings -> selectedCampaigns = array($campaignCible->campaignId);
     
            $autoresponder->setTriggerSettings($triggerSettings);
            
            $ret = $this->getresponse->setAutoresponder($autoresponder);
            
            
        }
    }
}