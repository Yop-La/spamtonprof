<?php

/**
 * GetResponse API v3 client library
 *
 * @author Pawel Maslak <pawel.maslak@getresponse.com>
 * @author Grzegorz Struczynski <grzegorz.struczynski@implix.com>
 *
 * @see http://apidocs.getresponse.com/en/v3/resources
 * @see https://github.com/GetResponse/getresponse-api-php
 */
class GetResponse
{

    private $api_key;

    private $api_url = 'https://api.getresponse.com/v3';

    private $timeout = 8;

    private $campaigns = null;

    private $customFields = null;

    private $profNameId, $mailProfId, $sexeProfId, $matieresId, $nameProcheId, $nameProche2Id, $StpEleveEssaiId, $stpParentEssaiId1, $stpParentEssaiId2, $profName2Id, $sexeProf2Id, $mailProf2Id, $matieres2Id;

    public $http_status;

    /**
     * X-Domain header value if empty header will be not provided
     *
     * @var string|null
     */
    private $enterprise_domain = null;

    /**
     * X-APP-ID header value if empty header will be not provided
     *
     * @var string|null
     */
    private $app_id = null;

    /**
     * Set api key and optionally API endpoint
     *
     * @param
     *            $api_key
     * @param null $api_url
     */
    public function __construct($api_url = null)
    {
        $this->api_key = GR_API;
        if (! empty($api_url)) {
            $this->api_url = $api_url;
        }
        
        $this->profNameId = $this->getCustomFieldId("prof_name");
        $this->profName2Id = $this->getCustomFieldId("prof_name_2");
        $this->mailProfId = $this->getCustomFieldId("mail_prof");
        $this->mailProf2Id = $this->getCustomFieldId("mail_prof_2");
        $this->sexeProfId = $this->getCustomFieldId("sexe_prof");
        $this->sexeProf2Id = $this->getCustomFieldId("sexe_prof_2");
        $this->matieresId = $this->getCustomFieldId("matieres");
        $this->matieres2Id = $this->getCustomFieldId("matieres_2");
        $this->nameProcheId = $this->getCustomFieldId("name_proche");
        $this->nameProche2Id = $this->getCustomFieldId("name_proche_2");
        
        $this->StpEleveEssaiId = $this->getCampagnId('stp_eleve_essai');
        $this->stpParentEssaiId1 = $this->getCampagnId('stp_parent_essai');
        $this->stpParentEssaiId2 = $this->getCampagnId('stp_parent_essai_2');
    }

    /**
     * We can modify internal settings
     *
     * @param
     *            $key
     * @param
     *            $value
     */
    function __set($key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * get account details
     *
     * @return mixed
     */
    public function accounts()
    {
        return $this->call('accounts');
    }

    /**
     *
     * @return mixed
     */
    public function ping()
    {
        return $this->accounts();
    }

    /**
     * Return all campaigns
     *
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->call('campaigns');
    }

    /**
     * get single campaign
     *
     * @param string $campaign_id
     *            retrieved using API
     * @return mixed
     */
    public function getCampaign($campaign_id)
    {
        return $this->call('campaigns/' . $campaign_id);
    }

    /**
     * adding campaign
     *
     * @param
     *            $params
     * @return mixed
     */
    public function createCampaign($params)
    {
        return $this->call('campaigns', 'POST', $params);
    }

    /**
     * retrieving autoresponders
     *
     * @param array $params
     *            - params to retrieve autoresponders
     * @return mixed
     */
    public function getAutoresponders($params = array())
    {
        return $this->call('autoresponders?' . $this->setParams($params));
    }

    /**
     * retrieving autoresponder
     *
     * @param
     *            string autoresponder_id - to retrieve autoresponder
     * @return mixed
     */
    public function getAutoresponder($autoresponder_id)
    {
        return $this->call('autoresponders/' . $autoresponder_id);
    }

    /**
     * add single autoresponder into your campaign
     *
     * @param
     *            $params
     * @return mixed
     */
    public function addAutoresponder($params)
    {
        return $this->call('autoresponders', 'POST', $params);
    }

    /**
     * list all RSS newsletters
     *
     * @return mixed
     */
    public function getRSSNewsletters()
    {
        $this->call('rss-newsletters', 'GET', null);
    }

    /**
     * send one newsletter
     *
     * @param
     *            $params
     * @return mixed
     */
    public function sendNewsletter($params)
    {
        return $this->call('newsletters', 'POST', $params);
    }

    /**
     *
     * @param
     *            $params
     * @return mixed
     */
    public function sendDraftNewsletter($params)
    {
        return $this->call('newsletters/send-draft', 'POST', $params);
    }

    /**
     * add single contact into your campaign
     *
     * @param
     *            $params
     * @return mixed
     */
    public function addContact($params)
    {
        return $this->call('contacts', 'POST', $params);
    }

    /**
     * retrieving contact by id
     *
     * @param string $contact_id
     *            - contact id obtained by API
     * @return mixed
     */
    public function getContact($contact_id)
    {
        return $this->call('contacts/' . $contact_id);
    }

    /**
     * search contacts
     *
     * @param
     *            $params
     * @return mixed
     */
    public function searchContacts($params = null)
    {
        return $this->call('search-contacts?' . $this->setParams($params));
    }

    /**
     * get from fields
     *
     * @param
     *            $params
     * @return mixed
     */
    public function getFromFields($params = array())
    {
        return $this->call('from-fields?' . $this->setParams($params));
    }

    /**
     * searchCampaigns
     *
     * @param
     *            $params
     * @return mixed
     */
    public function searchCampaigns($params = array())
    {
        return $this->call('campaigns?' . $this->setParams($params));
    }

    /**
     * mettre Ã  jour le champ personnalisÃ© d'un contact
     *
     * @param array $params
     * @param
     *            $id
     * @return mixed
     */
    public function updateCustomFieldContact($params, $id)
    {
        echo ('contacts/' . $id . '/custom-fields');
        return $this->call('contacts/' . $id . '/custom-fields', 'POST', $params);
    }

    /**
     * retrieve segment
     *
     * @param
     *            $id
     * @return mixed
     */
    public function getContactsSearchContacts($id)
    {
        return $this->call('search-contacts/' . $id . '/contacts?perPage=500');
    }

    /**
     * retrieve segment
     *
     * @param
     *            $id
     * @return mixed
     */
    public function getContactsSearch($id)
    {
        return $this->call('search-contacts/' . $id);
    }

    /**
     * add contacts search
     *
     * @param
     *            $params
     * @return mixed
     */
    public function addContactsSearch($params)
    {
        return $this->call('search-contacts/', 'POST', $params);
    }

    /**
     * add contacts search
     *
     * @param
     *            $id
     * @return mixed
     */
    public function deleteContactsSearch($id)
    {
        return $this->call('search-contacts/' . $id, 'DELETE');
    }

    /**
     * get contact activities
     *
     * @param
     *            $contact_id
     * @return mixed
     */
    public function getContactActivities($contact_id)
    {
        return $this->call('contacts/' . $contact_id . '/activities');
    }

    /**
     * retrieving contact by params
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getContacts($params = array())
    {
        return $this->call('contacts?' . $this->setParams($params));
    }

    /**
     * updating any fields of your subscriber (without email of course)
     *
     * @param
     *            $contact_id
     * @param array $params
     *
     * @return mixed
     */
    public function updateContact($contact_id, $params = array())
    {
        return $this->call('contacts/' . $contact_id, 'POST', $params);
    }

    /**
     * drop single user by ID
     *
     * @param string $contact_id
     *            - obtained by API
     * @return mixed
     */
    public function deleteContact($contact_id)
    {
        return $this->call('contacts/' . $contact_id, 'DELETE');
    }

    /**
     * adding tag
     *
     * @param
     *            $params
     * @return mixed
     */
    public function createTag($params)
    {
        return $this->call('tags', 'POST', $params);
    }

    /**
     * get all tags
     *
     * @return mixed
     */
    public function getTags()
    {
        return $this->call('tags');
    }

    /**
     * adding tag to contact
     *
     * @param
     *            $params
     * @param
     *            $contact_id
     * @return mixed
     */
    public function addTags($contact_id, $params)
    {
        return $this->call('contacts/' . $contact_id . '/tags', 'POST', $params);
    }

    /**
     * retrieve account custom fields
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getCustomFields($params = array())
    {
        return $this->call('custom-fields?' . $this->setParams($params));
    }

    /**
     * add custom field
     *
     * @param
     *            $params
     * @return mixed
     */
    public function setCustomField($params)
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * retrieve single custom field
     *
     * @param string $cs_id
     *            obtained by API
     * @return mixed
     */
    public function getCustomField($custom_id)
    {
        return $this->call('custom-fields/' . $custom_id, 'GET');
    }

    /**
     * retrieving billing information
     *
     * @return mixed
     */
    public function getBillingInfo()
    {
        return $this->call('accounts/billing');
    }

    /**
     * get single web form
     *
     * @param int $w_id
     * @return mixed
     */
    public function getWebForm($w_id)
    {
        return $this->call('webforms/' . $w_id);
    }

    /**
     * retrieve all webforms
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getWebForms($params = array())
    {
        return $this->call('webforms?' . $this->setParams($params));
    }

    /**
     * get single form
     *
     * @param int $form_id
     * @return mixed
     */
    public function getForm($form_id)
    {
        return $this->call('forms/' . $form_id);
    }

    /**
     * retrieve all forms
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getForms($params = array())
    {
        return $this->call('forms?' . $this->setParams($params));
    }

    /**
     * Curl run request
     *
     * @param null $api_method
     * @param string $http_method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    function getCustomFieldId($name)
    {
        if (is_null($this->customFields)) {
            $this->customFields = $this->getCustomFields();
        }
        
        foreach ($this->customFields as $customField) {
            
            if ($customField->name == $name)
                return ($customField->customFieldId);
        }
    }

    function getCampagnId($name)
    {
        if (is_null($this->campaigns)) {
            $this->campaigns = $this->getCampaigns();
        }
        
        foreach ($this->campaigns as $campaign) {
            
            if ($campaign->name == $name)
                return ($campaign->campaignId);
        }
    }

    /*
     * pour savoir si une adresse email dans une sequence email est après un nombre jour ($limitDay ).
     * return false si le contact n'est pas après la deadline
     */
    function isAfterDeadline($contact, $limitDay)
    {
        if (! is_null($contact->dayOfCycle)) {
            
            $dayOfCycle = intval($contact->dayOfCycle);
            
            if ($dayOfCycle >= $limitDay) {
                
                return ($contact);
            } else {
                
                return (false);
            }
        }
    }

    /*
     *
     *
     *
     */
    function moveContact($sequenceName, $contactId)
    {
        $campaignId = $this->getCampagnId($sequenceName);
        
        $params = '
        {
            "dayOfCycle": "0",
            "campaign": {
                "campaignId": "' . $campaignId . '"
            }
        }
        ';
        
        $params = json_decode($params);
        
        $ret = $this->updateContact($contactId, $params);
        return ($ret);
    }

    function getContactInList($email, $listName)
    {
        $campaignId = $this->getCampagnId($listName);
        
        $params = array(
            "query" => array(
                "email" => $email,
                "campaignId" => $campaignId
            )
        );
        
        $contacts = $this->getContacts($params);
        
        if (empty((array) $contacts)) {
            return (false);
        }
        
        foreach ($contacts as $contact) { // on boucle sur un contact uniquement car il ne peut y avoir qu'un
            
            return ($contact);
        }
    }

    function getFreeParentEssaiList($emailParent)
    {
        $contact = $this->getContactInList($emailParent, "stp_parent_essai");
        
        if ($contact) {
            
            $contact = $this->isAfterDeadline("stp_parent_essai", 7, $proche->getEmail());
            
            if ($contact) {
                
                return ("stp_parent_essai");
            }
        } else {
            return ("stp_parent_essai");
        }
        $contact = $this->getContactInList($emailParent, "stp_parent_essai_2");
        
        if ($contact) {
            
            $contact = $this->isAfterDeadline("stp_parent_essai_2", 7, $proche->getEmail());
            
            if ($contact) {
                
                return ("stp_parent_essai_2");
            }
        } else {
            return ("stp_parent_essai_2");
        }
    }

    private function call($api_method = null, $http_method = 'GET', $params = array())
    {
        if (empty($api_method)) {
            return (object) array(
                'httpStatus' => '400',
                'code' => '1010',
                'codeDescription' => 'Error in external resources',
                'message' => 'Invalid api method'
            );
        }
        $params = json_encode($params);
        $url = $this->api_url . '/' . $api_method;
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => 'PHP GetResponse client 0.0.2',
            CURLOPT_HTTPHEADER => array(
                'X-Auth-Token: api-key ' . $this->api_key,
                'Content-Type: application/json'
            )
        );
        if (! empty($this->enterprise_domain)) {
            $options[CURLOPT_HTTPHEADER][] = 'X-Domain: ' . $this->enterprise_domain;
        }
        if (! empty($this->app_id)) {
            $options[CURLOPT_HTTPHEADER][] = 'X-APP-ID: ' . $this->app_id;
        }
        if ($http_method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params;
        } else if ($http_method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = json_decode(curl_exec($curl));
        $this->http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return (object) $response;
    }

    /**
     *
     * @param array $params
     *
     * @return string
     */
    private function setParams($params = array())
    {
        $result = array();
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $result[$key] = $value;
            }
        }
        return http_build_query($result);
    }

    public function addEleveInTrialSequence(\spamtonprof\stp_api\StpEleve $eleve, \spamtonprof\stp_api\StpProf $prof, \spamtonprof\stp_api\StpFormule $formule, $dayOfCycle = 0)
    {
        $params = '{
            "name": "' . $eleve->getPrenom() . '",
            "email": "' . $eleve->getEmail() . '",
            "dayOfCycle": ' . $dayOfCycle . ',
            "campaign": {
                "campaignId": "' . $this->StpEleveEssaiId . '"
            },
            "customFieldValues": [
                {
                    "customFieldId": "' . $this->profNameId . '",
                    "value": [
                        "' . $prof->getPrenom() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->mailProfId . '",
                    "value": [
                        "' . $prof->getEmail_stp() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->sexeProfId . '",
                    "value": [
                        "' . $prof->getSexe() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->matieresId . '",
                    "value": [
                        "' . $formule->toGetResponse() . '"
                    ]
                }
            ]
        }';
        
        $params = json_decode($params);
        
        $rep = $this->addContact($params);
        
        return ($rep);
    }

    public function addParentInTrialSequence1(\spamtonprof\stp_api\StpEleve $eleve, \spamtonprof\stp_api\StpProf $prof, \spamtonprof\stp_api\StpFormule $formule, \spamtonprof\stp_api\StpProche $proche, $dayOfCycle = 0)
    {
        $params = '{
            "name": "' . $proche->getPrenom() . '",
            "email": "' . $proche->getEmail() . '",
            "dayOfCycle": ' . $dayOfCycle . ',
            "dayOfCycle": "0",
            "campaign": {
                "campaignId": "' . $this->stpParentEssaiId1 . '"
            },
            "customFieldValues": [
                {
                    "customFieldId": "' . $this->profNameId . '",
                    "value": [
                        "' . $prof->getPrenom() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->mailProfId . '",
                    "value": [
                        "' . $prof->getEmail_stp() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->sexeProfId . '",
                    "value": [
                        "' . $prof->getSexe() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->matieresId . '",
                    "value": [
                        "' . $formule->toGetResponse() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->nameProcheId . '",
                    "value": [
                        "' . $eleve->getPrenom() . '"
                    ]
                }
            ]
        }';
        
        $params = json_decode($params);
        
        $rep = $this->addContact($params);
        
        return ($rep);
    }

    public function addParentInTrialSequence2(\spamtonprof\stp_api\StpEleve $eleve, \spamtonprof\stp_api\StpProf $prof, \spamtonprof\stp_api\StpFormule $formule, \spamtonprof\stp_api\StpProche $proche)
    {
        $params = '{
            "name": "' . $proche->getPrenom() . '",
            "email": "' . $proche->getEmail() . '",
            "dayOfCycle": "0",
            "campaign": {
                "campaignId": "' . $this->stpParentEssaiId2 . '"
            },
            "customFieldValues": [
                {
                    "customFieldId": "' . $this->profName2Id . '",
                    "value": [
                        "' . $prof->getPrenom() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->mailProf2Id . '",
                    "value": [
                        "' . $prof->getEmail_stp() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->sexeProf2Id . '",
                    "value": [
                        "' . $prof->getSexe() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->matieres2Id . '",
                    "value": [
                        "' . $formule->toGetResponse() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->nameProche2Id . '",
                    "value": [
                        "' . $eleve->getPrenom() . '"
                    ]
                }
            ]
        }';
        
        $params = json_decode($params);
        
        $rep = $this->addContact($params);
        
        return ($rep);
    }

    function updateEleveInTrialList(\spamtonprof\stp_api\StpAbonnement $abo)
    {
        $eleve = $abo->getEleve();
        $formule = $abo->getFormule();
        $prof = $abo->getProf();
        
        $contact = $this->getContactInList($eleve->getEmail(), $this->StpEleveEssaiId);
        
        if ($contact) {
            
            $params = '{
            "name": "' . $eleve->getPrenom() . '",
            "customFieldValues": [
                {
                    "customFieldId": "' . $this->profNameId . '",
                    "value": [
                        "' . $prof->getPrenom() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->mailProfId . '",
                    "value": [
                        "' . $prof->getEmail_stp() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->sexeProfId . '",
                    "value": [
                        "' . $prof->getSexe() . '"
                    ]
                },
                {
                    "customFieldId": "' . $this->matieresId . '",
                    "value": [
                        "' . $formule->toGetResponse() . '"
                    ]
                }
            ]
        }';
            
            $params = json_decode($params);
            
            $this->updateContact($contact->contactId, $params);
        }
    }

    function updateParentInTrialList(\spamtonprof\stp_api\StpAbonnement $abo)
    {
        $eleve = $abo->getEleve();
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
        $formule = $abo->getFormule();
        $prof = $abo->getProf();
        $proche = $abo->getProche();
        
        $stpParentEssai = $this->stpParentEssaiId1;
        $profNameId = $this->profNameId;
        $mailProfId = $this->mailProfId;
        $sexeProfId = $this->sexeProfId;
        $matieresId = $this->matieresId;
        $nameProcheId = $this->nameProcheId;
        if ($eleve->getSeq_email_parent_essai() == 1) {
            $stpParentEssai = $this->stpParentEssaiId1;
            $profNameId = $this->profNameId;
            $mailProfId = $this->mailProfId;
            $sexeProfId = $this->sexeProfId;
            $matieresId = $this->matieresId;
            $nameProcheId = $this->nameProcheId;
        } else if ($eleve->getSeq_email_parent_essai() == 2) {
            $stpParentEssai = $this->stpParentEssaiId2;
            $profNameId = $this->profName2Id;
            $mailProfId = $this->mailProf2Id;
            $sexeProfId = $this->sexeProf2Id;
            $matieresId = $this->matieres2Id;
            $nameProcheId = $this->nameProche2Id;
        } else {
            exit(0);
        }
        
        $contact = $this->getContactInList($proche->getEmail(), $stpParentEssai);
        
        if ($contact) {
            
            $params = '{
                "name": "' . $proche->getPrenom() . '",
                "customFieldValues": [
                    {
                        "customFieldId": "' . $profNameId . '",
                        "value": [
                            "' . $prof->getPrenom() . '"
                        ]
                    },
                    {
                        "customFieldId": "' . $mailProfId . '",
                        "value": [
                            "' . $prof->getEmail_stp() . '"
                        ]
                    },
                    {
                        "customFieldId": "' . $sexeProfId . '",
                        "value": [
                            "' . $prof->getSexe() . '"
                        ]
                    },
                    {
                        "customFieldId": "' . $matieresId . '",
                        "value": [
                            "' . $formule->toGetResponse() . '"
                        ]
                    },
                    {
                        "customFieldId": "' . $nameProcheId . '",
                        "value": [
                            "' . $eleve->getPrenom() . '"
                        ]
                    }
                ]
            }';
            
            $params = json_decode($params);
            $this->updateContact($contact->contactId, $params);
        }
    }

    function updateTrialList($refAbo)
    {
        $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
        
        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_formule',
                'ref_prof',
                'ref_parent'
            )
        );
        
        $abo = $aboMg->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);
        
        $eleve = $abo->getEleve();
        
        if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ESSAI) {
            
            $eleve = $abo->getEleve();
            $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
            
            if ($eleve->hasToSendToEleve()) {
                $this->updateEleveInTrialList($abo);
            }
            if ($eleve->hasToSendToParent()) {
                $this->updateParentInTrialList($abo);
            }
        }
    }
    
    public function updateDayOfCycle($contact_id, $dayOfCycle)
    {
        $params = '{
                "dayOfCycle": "' . $dayOfCycle . '"
            }';
        
        $params = json_decode($params);
        $this->updateContact($contact_id, $params);
    }
}

?>