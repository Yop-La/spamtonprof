<?php
namespace spamtonprof\stp_api;

use Assetic\Exception\Exception;
use spamtonprof;

/*
 *
 * Cette classe sert commmuniquer avec GetResponse
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
class GetResponseManager

{

    private $getresponse, $bdd, $campaignManager;

    public function __construct()
    
    {
        $bdd = \spamtonprof\stp_api\PdoManager::getBdd();
        
        $this->getresponse = new \GetResponse(GR_API);
        
        $this->campaignManager = new CampaignManager();
    }

    /**
     * exemple de $params :
     *
     *
     *
     * $params = array(
     *
     * "email" => "alex@gmx.fr",
     *
     * "name" => "alex",
     *
     * "note" => "test",
     *
     * "dayOfCycle" => "10",
     *
     * "campaign" => array(
     *
     * "campaignId" => "pv2e",
     *
     * "ae5g"
     *
     * ),
     *
     * "tags" => array(
     *
     * array(
     *
     * "tagId" => "Xw"
     *
     * ),
     *
     * array(
     *
     * "tagId" => "Nn"
     *
     * )
     *
     * ),
     *
     * "customFieldValues" => array(
     *
     * array(
     *
     * "customFieldId" => "n",
     *
     * "value" => array(
     *
     * "white"
     *
     * )
     *
     * )
     *
     * )
     *
     * );
     */
    public function addContact(Personne $personne, string $nomCampaign)
    
    {
        $campaign = $this->campaignManager->get($nomCampaign);
        
        $params = array(
            
            "email" => $personne->adresse_mail(),
            
            "name" => $personne->prenom(),
            
            "dayOfCycle" => "0",
            
            "campaign" => array(
                
                "campaignId" => $campaign->ref_campaign_get_response()
            
            )
        
        );
        
        $res = $this->getresponse->addContact($params);
    }

    /**
     * pour ajouter un contact à plusieurs campagnes
     */
    public function addContactToCampaigns(Personne $personne, array $nomsCampaigns)
    
    {
        $campaigns = [];
        foreach ($nomsCampaigns as $nomCampaign) {
            $campaigns[] = $this->campaignManager->get($nomCampaign);
        }
        $
        
        $params = array(
            
            "email" => $personne->adresse_mail(),
            
            "name" => $personne->prenom(),
            
            "dayOfCycle" => "0",
            
            "campaign" => array(
                
                "campaignId" => $campaign->ref_campaign_get_response()
            
            )
        
        );
        
        $res = $this->getresponse->addContact($params);
    }

    /**
     * seul les paramèteres spécifiés sont mise à jour
     *
     *
     *
     * exemple de $paramsToUpdate pour tous les champs :
     *
     *
     *
     * $params = array(
     *
     * "name" => "alex",
     *
     * "note" => "test",
     *
     * "dayOfCycle" => "10",
     *
     * "campaign" => array(
     *
     * "campaignId" => "pv2e",
     *
     * "ae5g"
     *
     * ),
     *
     * "tags" => array(
     *
     * array(
     *
     * "tagId" => "Xw"
     *
     * ),
     *
     * array(
     *
     * "tagId" => "Nn"
     *
     * )
     *
     * ),
     *
     * "customFieldValues" => array(
     *
     * array(
     *
     * "customFieldId" => "n",
     *
     * "value" => array(
     *
     * "white"
     *
     * )
     *
     * )
     *
     * )
     *
     * );
     */
    public function updateContactInCampaign(Personne $personne, string $nomCampaign, array $paramsToUpdate)
    
    {
        $contact = $this->getContactInCampaign($personne, $nomCampaign);
        
        $res = $this->getresponse->updateContact($contact->contactId, $paramsToUpdate);
    }

    public function updatePrenomProcheContact(Personne $personne, string $nomCampaign, $prenomProche)
    
    {
        $params = array(
            
            "customFieldValues" => array(
                
                array(
                    
                    "customFieldId" => "3ytt8",
                    
                    "value" => array(
                        
                        $prenomProche
                    
                    )
                
                )
            
            )
        
        );
        
        $this->updateContactInCampaign($personne, $nomCampaign, $params);
    }

    /**
     * pour mettre à jour les infos d'un contact élève comme :
     *
     * - prenom
     *
     * - nom
     *
     * - nb de messages
     *
     * - tags
     *
     * - custom fields
     *
     * - prenom proche
     *
     *
     *
     * (pas de changement de campagne ou de jour de cyle)
     *
     * todostp terminer cette méthode pour faire la mise à jour des tags et customfields , à intégrer après swicthCampaigns
     */
    public function updateAccountInGr(Account $account, string $nomCampaign)
    
    {
        $eleve = $account->eleve();
        
        $proche = $account->proche();
        
        $params = array(
            
            "name" => $eleve->prenom(),
            
            "tags" => array(
                
                array(
                    
                    "tagId" => "Xw"
                
                ),
                
                array(
                    
                    "tagId" => "Nn"
                
                )
            
            ),
            
            "customFieldValues" => array(
                
                array(
                    
                    "customFieldId" => "n",
                    
                    "value" => array(
                        
                        "white"
                    
                    )
                
                )
            
            )
        
        );
        
        $this->updateContactInCampaign($eleve, $nomCampaign, $params);
    }

    /**
     * pour mettre à jour les infos d'un parent (pas de changement de campagne ou de jour de cyle)
     *
     *
     *
     *
     *
     * todostp terminer cette méthode pour faire la mise à jour des tags et customfields
     */
    public function synchroniseEleveParent(Account $account)
    
    {
        $parent = $account->parent();
        
        $this->updateContactInCampaign($parent, $params);
    }

    public function getContact(Personne $personne)
    {
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $personne->adresse_mail()
        
        ));
        
        $contacts = (array) $contacts;
        
        $number_contact = count($contacts);
        
        if ($number_contact == 0) {
            
            return (false);
        } else {
            
            $contact = $contacts[0];
            
            return ($contact);
        }
    }

    /**
     *
     * @param string $adresseMail
     * @return mixed|\StdClass pour avoir tous les contacts correspondants à une adresse mail
     */
    public function getContacts($adresseMail)
    {
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $adresseMail
        
        ));
        
        return ($contacts);
    }
    

    
    public function removeAll($adresseMail){
    
        $contacts = $this->getContacts($adresseMail);
        foreach ($contacts as $contact){
            
            $campaignName = $contact->campaign->name;
            
            $this->getresponse->updateContact($contact->contactId, $params = array("campaign" => array("campaignId" => '47CmU')));

        }

        
    }

    public function getContactInCampaign(Personne $personne, string $nomCampaign)
    
    {
        $campaign = $this->campaignManager->get($nomCampaign);
        
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $personne->adresse_mail(),
            
            "query[campaignId]" => $campaign->ref_campaign_get_response()
        
        ));
        
        $contacts = (array) $contacts;
        
        $number_contact = count($contacts);
        
        if ($number_contact == 0) {
            
            return (false);
        } else {
            
            $contact = $contacts[0];
            
            return ($contact);
        }
    }

    public function removeAllContactsInCampaigns(string $email, array $nomsCampaigns)
    {
        $contacts = $this->removeAllContactsInCampaigns($email, $nomsCampaigns);
        
        $nbContacts = count($contacts);
        
        $contactToReturn = $contacts[0];
        
        for ($i = 1; $i < $nbContacts; $i ++) {
            
            $contact = $contacts[$i];
            
            $this->removeFromCampaign(new Personne(array(
                "adresse_mail" => $email
            )), $campaign);
        }
        
        return ($contactToReturn);
    }

    public function getCampaigns(array $nomsCampaigns)
    {
        $campaigns = $this->getresponse->getCampaigns();
        
        $campaigns = (array) $campaigns;
        
        $i = 0;
        foreach ($campaigns as $campaign) {
            
            $campaign = new \spamtonprof\getresponse_api\Campaign($campaign);
            if (in_array($campaign->getName(), $nomsCampaigns)) {
                $campaigns[$i];
            } else {
                unset($campaigns[$i]);
            }
            $i ++;
        }
        return ($campaigns);
    }

    public function removeFromCampaign(Personne $personne, $campaign)
    
    {
        if ($this->contains($campaign, $personne)) {
            
            $this->switchCampaign($personne, $campaign, CampaignManager::remove_me);
        }
    }

    /**
     * permet de migrer un contact d'une campagne à l'autre
     *
     * peut créer le contact si il n'existe pas
     *
     * ( attention gère simplement prénom et adresse mail )
     */
    public function switchCampaign(Personne $personne, $oldCampaign, $newCampaign)
    
    {
        $newCampaign = $this->campaignManager->get($newCampaign);
        
        $exitedInOld = $this->contains($oldCampaign, $personne);
        
        $exitedInNew = $this->contains($newCampaign->nom_campaign(), $personne);
        
        if ($exitedInOld && $exitedInNew) {
            
            $this->removeFromCampaign($personne, $oldCampaign);
        } elseif ($exitedInOld && ! $exitedInNew) {
            
            $paramsToUpdate = array(
                
                "name" => $personne->prenom(),
                
                "dayOfCycle" => "0",
                
                "campaign" => array(
                    
                    "campaignId" => $newCampaign->ref_campaign_get_response()
                
                )
            
            );
            
            $this->updateContactInCampaign($personne, $oldCampaign, $paramsToUpdate);
        } elseif (! $exitedInOld && $exitedInNew) {
            
            // -> dans new : rien à faire
        } elseif (! $exitedInOld && ! $exitedInNew) {
            
            $this->addContact($personne, $newCampaign->nom_campaign());
        }
    }

    public function contains(string $nomCampaign, Personne $personne)
    
    {
        $contact = $this->getContactInCampaign($personne, $nomCampaign);
        
        if ($contact == false) {
            
            return (false);
        } else {
            
            return (true);
        }
    }

    public function changeListAfterSubEleve(\spamtonprof\stp_api\Account $account)
    
    {
        $email_eleve = $account->eleve()->adresse_mail();
        
        $email_parent = $account->proche()->adresse_mail();
        
        $prenom_eleve = $account->eleve()->prenom();
        
        $prenom_parent = $account->proche()->prenom();
        
        // faire les changements de liste
        
        // dÃ©terminer les ref de campagne
        
        $campaignIdEleve;
        
        $campaignIdEleveOld;
        
        if ($account->francais()) {
            
            $campaignIdEleve = "4b4vi";
            
            $campaignIdEleveOld = "4t7ut";
        } else if ($account->maths() or $account->physique()) {
            
            $campaignIdEleve = "45X2f";
            
            $campaignIdEleveOld = "4TP5I";
        }
        
        // supprimer les doublons d'emails Ã©lÃ¨ves
        
        $contact_eleve;
        
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $email_eleve,
            
            "query[campaignId]" => $campaignIdEleveOld
        
        ));
        
        $number_contact = count((array) $contacts);
        
        if ($number_contact == 1) {
            
            $contacts = (array) $contacts;
            
            $contact_eleve = $contacts[0];
            
            if ($email_eleve != $email_parent) {
                
                // changement de campagne Ã©lÃ¨ve
                
                $params = '{
			    "campaign": {
			    	"campaignId": "' . $campaignIdEleve . '"
			    },
			    "dayOfCycle": "0"
			}';
                
                $params = json_decode($params);
                
                $res = $this->getresponse->updateContact($contact_eleve->contactId, $params);
                
                // update du contact proche
                
                $params = '{
			    "name": "' . $prenom_eleve . '",
			    "customFieldValues": [
			        {
			            "customFieldId": "3ytt8",
			            "value": [
			                "' . $prenom_parent . '"
			            ]
			        }
			    ]
			}';
                
                $params = json_decode($params);
                
                $res = $this->getresponse->updateContact($contact_eleve->contactId, $params);
            } else {
                
                $ret = $this->getresponse->deleteContact($contact_eleve->contactId);
            }
        } else if ($number_contact == 0) {
            
            if ($email_eleve != $email_parent) {
                
                $params = '{
			    "name": "' . $prenom_eleve . '",
			    "email": "' . $email_eleve . '",
			    "campaign": {
			    	"campaignId": "' . $campaignIdEleve . '"
			    },
			    "dayOfCycle": "0",
			    "customFieldValues": [
			        {
			            "customFieldId": "3ytt8",
			            "value": [
			                "' . $prenom_parent . '"
			            ]
			        }
			    ]
			}';
                
                $params = json_decode($params);
                
                $res = $this->getresponse->addContact($params);
            }
        }
    }

    public function changeListAfterSubParent(\spamtonprof\stp_api\Account $account)
    
    {
        
        // dÃ©terminer les ref de campagne
        $campaignIdProche;
        
        $campaignIdProcheOld;
        
        if ($account->francais()) {
            
            $campaignIdProche = "4b4hs";
            
            $campaignIdProcheOld = "4t7kQ";
        } else if ($account->maths() or $account->physique()) {
            
            $campaignIdProche = "45XJl";
            
            $campaignIdProcheOld = "4TPZW";
        }
        
        // supprimer les doublons d'emails parents
        
        $contact_parent;
        
        $contacts = $this->getresponse->getContacts(array(
            
            "query[email]" => $email_parent,
            
            "query[campaignId]" => $campaignIdProcheOld
        
        ));
        
        $number_contact = count((array) $contacts);
        
        if ($number_contact == 1) {
            
            $contacts = (array) $contacts;
            
            $contact_parent = $contacts[0];
            
            // changement de campagne parent
            
            $params = '{
		    "campaign": {
		    	"campaignId": "' . $campaignIdProche . '"
		    },
		    "dayOfCycle": "0"
		}';
            
            $params = json_decode($params);
            
            $res = $this->getresponse->updateContact($contact_parent->contactId, $params);
            
            // update du contact proche
            
            $params = '{
		    "name": "' . $prenom_parent . '",
		    "customFieldValues": [
		        {
		            "customFieldId": "3ytt8",
		            "value": [
		                "' . $prenom_eleve . '"
		            ]
		        }
		    ]
		}';
            
            $params = json_decode($params);
            
            $res = $this->getresponse->updateContact($contact_parent->contactId, $params);
        } else if ($number_contact == 0) {
            
            // update du contact proche
            
            $params = '{
		    "name": "' . $prenom_parent . '",
		    "email": "' . $email_parent . '",
		    "campaign": {
		    	"campaignId": "' . $campaignIdProche . '"
		    },
		    "dayOfCycle": "0",
		    "customFieldValues": [
		        {
		            "customFieldId": "3ytt8",
		            "value": [
		                "' . $prenom_eleve . '"
		            ]
		        }
		    ]
		}';
            
            $params = json_decode($params);
            
            $res = $this->getresponse->addContact($params);
        }
    }

    function changeListAfterSubAccount(\spamtonprof\stp_api\Account $account)
    
    {
        $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();
        
        $proche = $account->proche();
        
        $eleve = $account->eleve();
        
        if ($account->francais()) {
            
            $getResponseManager->switchCampaign($proche, CampaignManager::fr_parent_essai, CampaignManager::elisabeth_migne_parent_client);
            
            $getResponseManager->updatePrenomProcheContact($proche, CampaignManager::elisabeth_migne_parent_client, $eleve->prenom());
            
            if ($proche->adresse_mail() != $eleve->adresse_mail()) {
                
                $getResponseManager->switchCampaign($eleve, CampaignManager::fr_eleve_essai, CampaignManager::elisabeth_migne_eleve_client);
                
                $getResponseManager->updatePrenomProcheContact($eleve, CampaignManager::elisabeth_migne_eleve_client, $proche->prenom());
            } else {
                
                $getResponseManager->removeFromCampaign($eleve, CampaignManager::fr_eleve_essai);
            }
        } elseif ($account->maths() || $account->physique()) {
            
            $getResponseManager->switchCampaign($proche, CampaignManager::parent_en_essai, CampaignManager::parent_client);
            
            $getResponseManager->updatePrenomProcheContact($proche, CampaignManager::parent_client, $eleve->prenom());
            
            if ($proche->adresse_mail() != $eleve->adresse_mail()) {
                
                $getResponseManager->switchCampaign($eleve, CampaignManager::eleve_en_essai, CampaignManager::eleve_client);
                
                $getResponseManager->updatePrenomProcheContact($eleve, CampaignManager::eleve_client, $proche->prenom());
            } else {
                
                $getResponseManager->removeFromCampaign($eleve, CampaignManager::eleve_en_essai);
            }
        }
    }

    function resetToEssai(\spamtonprof\stp_api\Account $account)
    
    {
        $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();
        
        $proche = $account->proche();
        
        $eleve = $account->eleve();
        
        if ($account->francais()) {
            
            $getResponseManager->switchCampaign($proche, CampaignManager::elisabeth_migne_parent_client, CampaignManager::fr_parent_essai);
            
            $getResponseManager->updatePrenomProcheContact($proche, CampaignManager::fr_parent_essai, $eleve->prenom());
            
            if ($proche->adresse_mail() != $eleve->adresse_mail()) {
                
                $getResponseManager->switchCampaign($eleve, CampaignManager::elisabeth_migne_eleve_client, CampaignManager::fr_eleve_essai);
                
                $getResponseManager->updatePrenomProcheContact($eleve, CampaignManager::fr_eleve_essai, $proche->prenom());
            } else {
                
                $getResponseManager->removeFromCampaign($eleve, CampaignManager::elisabeth_migne_eleve_client);
            }
        } elseif ($account->maths() || $account->physique()) {
            
            $getResponseManager->switchCampaign($proche, CampaignManager::parent_client, CampaignManager::parent_en_essai);
            
            $getResponseManager->updatePrenomProcheContact($proche, CampaignManager::parent_en_essai, $eleve->prenom());
            
            if ($proche->adresse_mail() != $eleve->adresse_mail()) {
                
                $getResponseManager->switchCampaign($eleve, CampaignManager::eleve_client, CampaignManager::eleve_en_essai);
                
                $getResponseManager->updatePrenomProcheContact($eleve, CampaignManager::eleve_en_essai, $proche->prenom());
            } else {
                
                $getResponseManager->removeFromCampaign($eleve, CampaignManager::eleve_client);
            }
        }
    }
}
