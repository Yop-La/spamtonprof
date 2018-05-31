<?php
namespace spamtonprof\gmailManager;

use Assetic\Exception\Exception;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use spamtonprof\stp_api\GmailLabelManager;
use spamtonprof;

class GmailManager
{

    private $client, $service, $userId;

    public function __construct($gmail_adress)
    {
        $this->client = $this->getClient($gmail_adress);
        $this->service = new Google_Service_Gmail($this->client);
        $this->userId = 'me';
    }

    /**
     * Returns an authorized API client.
     *
     * @return Google_Client the authorized client object
     */
    public function getClient($gmailAdress)
    {
        $profMg = new \spamtonprof\stp_api\ProfManager();
        
        $keyMg = new \spamtonprof\stp_api\KeyManager();
        
        $key = $keyMg->get($keyMg::GMAIL_KEY);
        
        $prof = $profMg->get($gmailAdress);
        
        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes(Google_Service_Gmail::GMAIL_MODIFY);
        
        $authConfig = json_decode($key->getKey(), true);
        
        $client->setAuthConfig($authConfig);
        $client->setAccessType('offline');
        
        $accessToken;
        
        if (! $prof) {
            echo ("ajouté : " . $gmailAdress . " à la table prof <br><br><br>");
        }
        
        if (! is_null($prof->getGmail_credential())) {
            
            $accessToken = json_decode($prof->getGmail_credential(), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            
            $authCode = "4/AABq19cDx-_lepdcUjYUjS7yqo2_KyjbIFYEqqK7soLNLlr6QHdxE9E"; // à remplir par ce qui sera donné par $authUrl
            
            if ($authCode == "") {
                header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            }
            
            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            
            $prof->setGmail_credential(json_encode($accessToken));
            $profMg->update($prof);
        }
        
        $client->setAccessToken($accessToken);
        
        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $prof->setGmail_credential(json_encode($client->getAccessToken()));
            $profMg->update($prof);
        }
        return $client;
    }

    /**
     * Get list of Messages in user's mailbox.
     *
     * @param Google_Service_Gmail $service
     *            Authorized Gmail API instance.
     * @param string $userId
     *            User's email address. The special value 'me'
     *            can be used to indicate the authenticated user.
     * @return array Array of Messages.
     */
    public function listMessages($searchOperator)
    {
        $pageToken = NULL;
        $messages = array();
        $opt_param = array(
            "q" => $searchOperator,
            "maxResults" => 10
        );
        do {
            try {
                if ($pageToken) {
                    $opt_param['pageToken'] = $pageToken;
                }
                $messagesResponse = $this->service->users_messages->listUsersMessages($this->userId, $opt_param);
                if ($messagesResponse->getMessages()) {
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    $pageToken = $messagesResponse->getNextPageToken();
                }
            } catch (Exception $e) {
                print 'An error occurred: ' . $e->getMessage();
            }
        } while ($pageToken);
        
        return $messages;
    }

    public function getMessage($messageId, $format = ['format' => 'metadata', 'metadataHeaders' => ['From','Date']])
    {
        try {
            
            $message = $this->service->users_messages->get($this->userId, $messageId, $format);
            return $message;
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
    }

    function modifyMessage($messageId, $labelsToAdd, $labelsToRemove)
    {
        $mods = new \Google_Service_Gmail_ModifyMessageRequest();
        if (empty($labelsToAdd) && empty($labelsToRemove)) {
            return;
        }
        $mods->setAddLabelIds($labelsToAdd);
        $mods->setRemoveLabelIds($labelsToRemove);
        try {
            $message = $this->service->users_messages->modify($this->userId, $messageId, $mods);
            return $message;
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
    }

    /**
     * Add a new Label to user's mailbox.
     *
     * @param \Google_Service_Gmail $service
     *            Authorized Gmail API instance.
     * @param string $userId
     *            User's email address. The special value 'me'
     *            can be used to indicate the authenticated user.
     * @param string $new_label_name
     *            Name of the new Label.
     * @return \Google_Service_Gmail_Label Created Label.
     */
    function createLabel($new_label_name, $bgColor)
    {
        $label = new \Google_Service_Gmail_Label();
        $label->setName($new_label_name);
        
        $color = new \Google_Service_Gmail_LabelColor();
        
        $color->setBackgroundColor($bgColor);
        $color->setTextColor("#434343");
        
        $label->setColor($color);
        
        try {
            $label = $this->service->users_labels->create($this->userId, $label);
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
        return $label;
    }

    function createStpLabels()
    {
        $gmailLabelMg = new GmailLabelManager();
        
        $labels = $gmailLabelMg->getAll();
        
        foreach ($labels as $label) {
            
            echo ($label->getNom_label() . "de couleur : " . $label->getColor_label() . "<br>");
            
            $this->createLabel($label->getNom_label(), $label->getColor_label());
        }
    }

    function resetStpLabels()
    {
        $this->deleteStpLabels();
        
        $this->createStpLabels();
    }

    /**
     * Delete Label with given ID.
     *
     * @param Google_Service_Gmail $service
     *            Authorized Gmail API instance.
     * @param string $userId
     *            User's email address. The special value 'me'
     *            can be used to indicate the authenticated user.
     * @param string $labelId
     *            Id of Label to be updated.
     */
    function deleteLabel($labelId)
    {
        try {
            $this->service->users_labels->delete($this->userId, $labelId);
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
    }

    function deleteStpLabels()
    {
        $gmailLabelMg = new GmailLabelManager();
        
        $stpLabels = $gmailLabelMg->getAllLabelName();
        
        $existingLabels = $this->getLabelsList();
        
        foreach ($existingLabels as $existingLabel) {
            
            if (in_array($existingLabel->getName(), $stpLabels)) {
                
                $this->deleteLabel($existingLabel->getId());
            }
        }
    }

    /**
     * Get all the Labels in the user's mailbox.
     *
     * @param Google_Service_Gmail $service
     *            Authorized Gmail API instance.
     * @param string $userId
     *            User's email address. The special value 'me'
     *            can be used to indicate the authenticated user.
     * @return array Array of Labels.
     */
    function getLabelsList()
    {
        $labels = array();
        
        try {
            $labelsResponse = $this->service->users_labels->listUsersLabels($this->userId);
            
            if ($labelsResponse->getLabels()) {
                $labels = array_merge($labels, $labelsResponse->getLabels());
            }
            
            return ($labels);
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
        
        return $labels;
    }

    public function getLabelsIds(array $labelNames)
    {
        $labels = $this->getLabelsList();
        
        foreach ($labels as $label) {
            
            if (in_array($label->getName(), $labelNames)) {
                $labelsIds[] = $label->getId();
            }
        }
        
        return ($labelsIds);
    }

    public function getCustomLabelsToAdd(spamtonprof\stp_api\Account $account)
    {
        $labelsNameToAdd = [];
        $labelsIdToAdd = [];
        
        $labelsNameToAdd[] = $account->statut();
        
        $labelsNameToAdd[] = $account->eleve()->classe();
        
        $labels = $this->getLabelsList();
        
        foreach ($labels as $label) {
            
            if (in_array($label->getName(), $labelsNameToAdd)) {
                $labelsIdToAdd[] = $label->getId();
            }
        }
        
        return ($labelsIdToAdd);
    }

    function listHistory($startHistoryId, $historyTypes = "messageAdded")
    {
        $userId = $this->userId;
        $service = $this->service;
        $opt_param = array(
            'startHistoryId' => $startHistoryId,
            'historyTypes' => $historyTypes,
            'maxResults' => '100'
        );
        $pageToken = NULL;
        $histories = array();
        
        do {
            try {
                if ($pageToken) {
                    $opt_param['pageToken'] = $pageToken;
                }
                $historyResponse = $service->users_history->listUsersHistory($userId, $opt_param);
                $pageToken = false; // à enlever si on veut itérer sur plusieurs pages et décommenter en dessous aussi
                                    // if ($historyResponse->getHistory()) {
                $histories = array_merge($histories, $historyResponse->getHistory());
                // $pageToken = $historyResponse->getNextPageToken();
                // }
            } catch (Exception $e) {
                print 'An error occurred: ' . $e->getMessage();
            }
        } while ($pageToken);
        
        return $histories;
    }
}

