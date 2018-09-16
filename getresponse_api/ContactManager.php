<?php
namespace spamtonprof\getresponse_api;

use spamtonprof;

class ContactManager

{

    private $getresponse;

    public function __construct()
    
    {
        $this->getresponse = new \GetResponse(GR_API);
    }

    /**
     * retourne le seul contact avec cette adresse mail dans la campagne spécifié
     *
     * @param string $nameCampaign
     * @param string $email
     * @return boolean|\spamtonprof\getresponse_api\Contact
     */
    public function get($nameCampaign, $email, $allContact = false)
    
    {
        $campaignMg = new spamtonprof\getresponse_api\CampaignManager();
        
        $campaign = $campaignMg->get($nameCampaign);
        
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $email,
            
            "query[campaignId]" => $campaign->campaignId
        
        ));
        
        $contacts = (array) $contacts;
        
        $number_contact = count($contacts);
        
        if ($number_contact == 0) {
            
            return (false);
        } else {
            
            $contact = new \spamtonprof\getresponse_api\Contact($contacts[0]);
            
            if($allContact){
                
                $contact = $this->getresponse->getContact($contact->contactId);
                
            }
            
            prettyPrint($contact);
            
            return ($contact);
        }
    }

    /**
     * retourne tous les contacts avec cet adresse mail
     *
     * @param string $email
     * @return boolean|\spamtonprof\getresponse_api\Contact
     */
    public function getList($email, $campaignsNames)
    
    {
        if(is_string($campaignsNames)){
            $campaignsNames = array($campaignsNames);
        }
        
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $email
        
        ));
        
        $contacts = (array) $contacts;
        
        $i = 0;
        foreach ($contacts as $contact) {
            
            prettyPrint($contact);
            
            return;
            
            $contact = new \spamtonprof\getresponse_api\Contact($contact);
            
            if (in_array($contact->getCampaign()->name, $campaignsNames)) {
                
                $contacts[$i] = $contact;
            } else {
                unset($contacts[$i]);
            }
            $i ++;
            

        }
        
        
        $number_contact = count($contacts);
        
        if ($number_contact == 0) {
            return(false);
        }else{
            return ($contacts);
        }
            
    }

    public function keepOnlyOne($email, array $campaignsNames)
    {
        $contacts = $this->getList($email, $campaignsNames);
        
        if ($contacts) {
            $contactToKeep = $contacts[0];
            $nbContacts = count($contacts);
            for ($i = 1; $i < $nbContacts; $i ++) {
                
                $contact = $contacts[$i];
                $this->removeFromCampaign($contact);
            }
            return($contactToKeep);
        } else {
            
            return (false);
        }
    }

    public function switchCampaign(\spamtonprof\getresponse_api\Contact $contact, String $newCampaign)
    
    {
        $campaignMg = new \spamtonprof\getresponse_api\CampaignManager();
        
        $newCampaign = $campaignMg->get($newCampaign);
        
        $paramsToUpdate = array(
            
            "dayOfCycle" => "0",
            
            "campaign" => array(
                
                "campaignId" => $newCampaign->getCampaignId()
            
            )
        
        );
        
        $this->getresponse->updateContact($contact->getContactId(), $paramsToUpdate);
    }

    public function removeFromCampaign(\spamtonprof\getresponse_api\Contact $contact)
    {
        $this->switchCampaign($contact, spamtonprof\getresponse_api\CampaignManager::remove_me);
    }
    
    public function addContact(String $email, 
        string $nomCampaign, 
        String $prenom, 
        array $customFields = array(),
        array $tags = array())
    
    {
        
        
        $campaignMg = new \spamtonprof\getresponse_api\CampaignManager();
        
        $campaign = $campaignMg->get($nomCampaign);
        
        $params = array(
            
            "email" => $email,
            
            "name" => $prenom,
            
            "dayOfCycle" => "0",
            
            "campaign" => array(
                
                "campaignId" => $campaign->getCampaignId()
                
            )
            
        );
        
        $res = $this->getresponse->addContact($params);
        
        echo("res : " . json_encode($res). "<br>". "<br>". "<br>");
    }
    
    
    
    
}