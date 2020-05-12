<?php
namespace spamtonprof\googleMg;

use PHPMailer\PHPMailer\PHPMailer;
use Exception;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Sheets;
use spamtonprof\stp_api\GmailLabelManager;

class GoogleManager
{

    private $client, $service, $userId, $authUrl = false, $slack, $adress, $account, $accountMg, $service_webamster, $site_verfication;

    public function __construct($gmail_adress, $code = false)
    {

        // $this->getClient2();
        // exit();
        $this->adress = $gmail_adress;

        $this->accountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
        $this->account = $this->accountMg->get($this->adress);

        // $this->account->setCredential(null);

        $this->client = $this->getClient2($gmail_adress, $code);

        // $this->client = $this->getClientV0($gmail_adress);

        // exit();

        $this->slack = new \spamtonprof\slack\Slack();

        if (! $this->client) {

            echo ("Authentification impossible ...<br>");

            $this->slack->sendMessages('google-log', array(
                "Authentification google de : " . $gmail_adress . " à faire ..."
            ));

            $this->sendAuthUrl();

            exit();
        }

        $this->service = new Google_Service_Gmail($this->client);

        $this->service_webamster = new \Google_Service_Webmasters($this->client);

        $this->site_verfication = new \Google_Service_SiteVerification($this->client);

        $this->userId = 'me';
    }

//     public function webResourceGetToken()
//     {
//         try {
//             $ret = $this->site_verfication->webResource->(, array(
//                 "site" => array(
//                     'type' => 'SITE',
//                     "identifier" => 'maitrepain.fr'
//                 ),
//                 "verificationMethod" => "DNS_TXT"
//             ));
//             prettyPrint($ret);
//         } catch (Exception $e) {

//             echo ($e->getMessage());
//         }
//     }

    public function webResourceList()
    {
        try {
            $ret = $this->site_verfication->webResource->listWebResource();
            prettyPrint($ret);
        } catch (Exception $e) {

            echo ($e->getMessage());
        }
    }

    public function testSearchConsole($siteUrl, $feedpath = false)
    {
        try {
            $ret = $this->service_webamster->sites->prettyPrint($ret);
        } catch (Exception $e) {

            echo ($e->getMessage());
        }
    }

    public function addSiteMap($siteUrl, $feedpath)
    {
        try {
            $this->service_webamster->sitemaps->submit($siteUrl, $feedpath);
        } catch (Exception $e) {

            echo ($e->getMessage());
        }
    }

    /**
     * Returns an authorized API client.
     *
     * @return Google_Client the authorized client object
     */
    public function getClient2($gmailAdress, $code = false)
    {
        $client = new Google_Client();
        $client->setApplicationName('Stp Tracker');

        $client->setClientId(GOOGLE_APP_CLIENT_ID);
        $client->setClientSecret(GOOGLE_APP_CLIENT_SECRET);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // $params_url = http_build_query(array('email_prof' => $gmailAdress));

        $client->setState($gmailAdress);

        $client->setRedirectUri(domain_to_url());

        $client->setScopes(array(
            Google_Service_Sheets::SPREADSHEETS,
            Google_Service_Gmail::GMAIL_MODIFY,
            \Google_Service_Webmasters::WEBMASTERS,
            \Google_Service_SiteVerification::SITEVERIFICATION
        ));

        if (! $this->account) {
            echo ("ajouté : " . $gmailAdress . " à la table prof <br><br><br>");
            exit(0);
        }

        if ($code) {

            $credentials = $client->fetchAccessTokenWithAuthCode($code);

            $this->account->setCredential(json_encode($client->getAccessToken()));
            $this->accountMg->updateCredential($this->account);

            $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
        }

        $need_new_authen = true;
        $credentials_in_db = json_decode($this->account->getCredential(), true);
        $credentials = false;
        if (($credentials_in_db != "" && ! is_null($credentials_in_db) && ! array_key_exists('error', $credentials_in_db))) {

            $credentials = $credentials_in_db;
        }

        if ($credentials) {

            try {

                $ret = $client->setAccessToken($credentials);

                // prettyPrint(array($credentials,$ret,gettype($credentials)));

                // If there is no previous token or it's expired.
                $need_new_authen = false;

                // prettyPrint($client->isAccessTokenExpired());

                if ($client->isAccessTokenExpired()) {

                    // Refresh the token if possible, else fetch a new one.
                    $need_new_authen = true;

                    if ($client->getRefreshToken()) {

                        $credentials = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                        if (! (array_key_exists('error', $credentials) && strlen($credentials['error']) > 0)) {

                            $this->account->setCredential(json_encode($client->getAccessToken()));
                            $this->accountMg->updateCredential($this->account);
                            $need_new_authen = false;
                        }
                    }
                }
            } catch (Exception $e) {

                echo ($e->getMessage() . '<br>');
            }
        }

        if ($need_new_authen) {

            // Request authorization from the user.

            $this->authUrl = $client->createAuthUrl();

            return (false);
        }

        return ($client);
    }

    public function getClientV0($gmailAdress)
    {
        $accountMg = new \spamtonprof\stp_api\StpGmailAccountManager();

        $keyMg = new \spamtonprof\stp_api\KeyManager();

        $key = $keyMg->get($keyMg::GMAIL_KEY);

        $account = $accountMg->get($gmailAdress);

        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');

        $client->setApplicationName('Stp Tracker');

        $client->setClientId(GOOGLE_APP_CLIENT_ID);
        $client->setClientSecret(GOOGLE_APP_CLIENT_SECRET);
        $client->setAccessType('offline');
        $client->setRedirectUri(domain_to_url());

        $client->setScopes(array(
            Google_Service_Sheets::SPREADSHEETS,
            Google_Service_Gmail::GMAIL_MODIFY
        ));

        // $authConfig = json_decode($key->getKey(), true);

        // $client->setAuthConfig($authConfig);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $accessToken;

        if (! $account) {
            echo ("ajouté : " . $gmailAdress . " à la table prof <br><br><br>");
            exit(0);
        }

        if (false && $account->getCredential() != "" && ! is_null($account->getCredential())) {

            $accessToken = json_decode($account->getCredential(), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();

            // echo($authUrl);

            // exit();

            $authCode = ""; // a remplir par ce qui sera donne par $authUrl

            if ($authCode == "") {
                echo ("la2");
                header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            }

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            echo (json_encode($accessToken));

            echo ('<br><br><br>');

            echo ($account->getRef_gmail_account());

            echo ('<br><br><br>');

            // $account->setCredential(json_encode($accessToken));
            $account->setCredential(json_encode($accessToken));
            $accountMg->updateCredential($account);
        }

        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $account->setCredential(json_encode($client->getAccessToken()));
            $accountMg->updateCredential($account);
        }
        return $client;
    }

    public function sendAuthUrl()
    {
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        $now = $now->format(PG_DATE_FORMAT);

        $dateUrlSent = $this->account->getDate_url_sent();

        echo ("Envoi de l'email d'authentification en cours...<br>");

        if ($dateUrlSent) {

            $dateUrlSent = \DateTime::createFromFormat(PG_DATETIME_FORMAT, $dateUrlSent);
            $dateUrlSent = $dateUrlSent->format(PG_DATE_FORMAT);

            if ($dateUrlSent == $now) {
                $this->slack->sendMessages('google-log', array(
                    "Déjà un mail d'authentification envoyé dans la journée à : " . $this->adress
                ));

                exit();
            }
        }

        $profMg = new \spamtonprof\stp_api\StpProfManager();
        $prof = $profMg->get(array(
            'ref_gmail_account' => $this->account->getRef_gmail_account()
        ));

        $name = 'Monsieur/Madame';
        if ($prof) {
            $name = ucfirst($prof->getPrenom());
        }

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

        $email->addTo($this->adress, $name, [

            "name" => $name,
            "name_lower" => strtolower($name),
            "lien" => $this->authUrl
        ], 0);

        $email->addCc('alexandre@spamtonprof.com');

        $email->setTemplateId("d-bd9ea3e114814f03892b8fd1e4992d1d");
        $sendgrid = new \SendGrid(SEND_GRID_API_KEY);
        try {
            $response = $sendgrid->send($email);

            $this->slack->sendMessages('google-log', array(
                "Url d'authentification envoyé à: " . $this->adress
            ));

            echo ($response->body());
        } catch (\Exception $e) {

            $this->slack->sendMessages('google-log', array(
                'Caught exception: ' . $e->getMessage()
            ));
        }

        $this->account->setDate_url_sent($now);
        $this->accountMg->updateDateUrlSent($this->account);

        print('email envoyé à ' . $this->adress);
    }

    public function getClient($gmailAdress)
    {
        $accountMg = new \spamtonprof\stp_api\StpGmailAccountManager();

        $keyMg = new \spamtonprof\stp_api\KeyManager();

        $key = $keyMg->get($keyMg::GMAIL_KEY);

        $account = $accountMg->get($gmailAdress);

        $client = new Google_Client();

        $authConfig = json_decode($key->getKey(), true);

        $client = new Google_Client();
        $client->setApplicationName('Stp Tracker');

        $client->setClientId(GOOGLE_APP_CLIENT_ID);
        $client->setClientSecret(GOOGLE_APP_CLIENT_SECRET);
        $client->setAccessType('offline');

        $client->setAccessType('offline');

        $client->setRedirectUri('https://spamtonprof.com');

        // $client->setAuthConfig($authConfig);

        $client->setScopes(array(
            Google_Service_Sheets::SPREADSHEETS,
            Google_Service_Gmail::GMAIL_MODIFY
        ));
        $url = $client->createAuthUrl();

        // echo ($url);
        // exit();

        $accessToken;

        if ($account->getCredential() != "" && ! is_null($account->getCredential())) {

            $accessToken = json_decode($account->getCredential(), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();

            $authCode = ""; // a remplir par ce qui sera donne par $authUrl

            if ($authCode == "") {
                echo ("la2");
                header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            }

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            echo (json_encode($accessToken));

            echo ('<br><br><br>');

            echo ($account->getRef_gmail_account());

            echo ('<br><br><br>');

            // $account->setCredential(json_encode($accessToken));
            $account->setCredential(json_encode($accessToken));
            $accountMg->updateCredential($account);
        }

        $client->setAccessToken($accessToken);

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.

            $need_new_authen = true;
            if ($client->getRefreshToken()) {

                $ret = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                if (array_key_exists('error', $ret) && $ret['error'] == 'invalid_grant') {

                    $need_new_authen = true;
                } else {
                    $need_new_authen = false;
                    $account->setCredential(json_encode($client->getAccessToken()));
                    $accountMg->updateCredential($account);
                }
            }

            if ($need_new_authen) {

                // Request authorization from the user.

                $authUrl = $client->createAuthUrl();
                echo ($authUrl);

                exit();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);
            }
        }

        return $client;
    }

    public function getLastMessage($historyId, $batchSize = 10)
    {
        $histories = [];
        if (! $historyId) {
            $nbDay = 20;
            do {
                ;
                $msg = $this->findSingleMessageInPast($nbDay);
                $historyId = $msg->historyId;
                $histories = $this->listHistory(strval($historyId), "messageadded", false, 100);
                $nbDay = $nbDay - 1;
            } while (! $histories);
        } else {
            $histories = $this->listHistory(strval($historyId), "messageadded", false, 100);
        }

        $msgs = [];

        $lastHistoryId = false;

        foreach ($histories as $historie) {
            $lastHistoryId = $historie->id;

            $messagesAdded = $historie->messagesAdded;
            $messageAdded = $messagesAdded[0];
            $msg = $messageAdded->message;

            $labelIds = $msg->labelIds;

            if (isset($labelIds)) {

                if (in_array("SENT", $labelIds)) {
                    continue;
                }

                if (in_array("DRAFT", $labelIds)) {
                    continue;
                }
            }

            $msg = $this->getMessage($msg->id);

            $msgs[] = $msg;

            if (count($msgs) == $batchSize) {
                break;
            }
        }
        return (array(
            'msgs' => $msgs,
            'historyId' => $lastHistoryId
        ));
    }

    public function findSingleMessageInPast($nbDaysInPaste = 20, $nbPage = 1, $maxResults = 50)
    {
        $pastDate = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
        $pastDate->sub(new \DateInterval('P' . $nbDaysInPaste . 'D'));

        $messages = $this->listMessages("before:" . $pastDate->format(GMAIL_DATE_FORMAT), $nbPage, $maxResults);

        $winner_msg = false;

        foreach ($messages as $msg) {

            $msgId = $msg->id;

            if ($msgId == $msg->threadId) {

                $msg = $this->getMessage($msgId);

                if (($msg->internalDate / 1000) <= $pastDate->getTimestamp()) {

                    if (! $winner_msg) {
                        $winner_msg = $msg;
                    }

                    if ($winner_msg->internalDate > $msg->internalDate) {
                        $winner_msg = $msg;
                    }
                }
            }
        }

        return ($winner_msg);
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
    public function listMessages($searchOperator, $nbPage = 1, $maxResults = 10)
    {
        $pageToken = NULL;
        $messages = array();
        $opt_param = array(
            "q" => utf8_encode($searchOperator),
            "maxResults" => $maxResults
        );

        $indexPage = 0;

        do {
            try {
                if ($pageToken) {
                    $opt_param['pageToken'] = $pageToken;
                }
                $messagesResponse = $this->service->users_messages->listUsersMessages($this->userId, $opt_param);
                if ($messagesResponse->getMessages()) {
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    $pageToken = $messagesResponse->getNextPageToken();

                    $indexPage = $indexPage + 1;

                    if ($indexPage == $nbPage) {
                        return ($messages);
                    }
                }
            } catch (\Exception $e) {
                print 'An error occurred: ' . $e->getMessage();
            }
        } while ($pageToken);

        return $messages;
    }

    // ['format' => 'full']
    public function getMessage($messageId, $format = ['format' => 'metadata', 'metadataHeaders' => ['From','Date']])
    {
        try {

            $message = $this->service->users_messages->get($this->userId, $messageId, $format);
            return $message;
        } catch (\Exception $e) {
            return (false);
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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

    public function getCustomLabelsToAdd($labelsNameToAdd)
    {
        $labelsIdToAdd = [];

        $labels = $this->getLabelsList();

        foreach ($labels as $label) {

            if (in_array($label->getName(), $labelsNameToAdd)) {
                $labelsIdToAdd[] = $label->getId();
            }
        }

        return ($labelsIdToAdd);
    }

    function listHistory($startHistoryId, $historyTypes = "messageAdded", $labelId = "INBOX", $maxResults = 1000)
    {
        $userId = $this->userId;
        $service = $this->service;
        $opt_param = array(
            'startHistoryId' => $startHistoryId,
            'maxResults' => $maxResults
        );

        if ($historyTypes) {
            $opt_param['historyTypes'] = $historyTypes;
        }

        if ($labelId) {
            $opt_param['labelId'] = $labelId;
        }

        $pageToken = NULL;
        $histories = array();

        do {
            try {
                if ($pageToken) {
                    $opt_param['pageToken'] = $pageToken;
                }

                $historyResponse = $service->users_history->listUsersHistory($userId, $opt_param);
                $pageToken = false; // a enlever si on veut iterer sur plusieurs pages et decommenter en dessous aussi
                                    // if ($historyResponse->getHistory()) {
                $histories = array_merge($histories, $historyResponse->getHistory());
                // $pageToken = $historyResponse->getNextPageToken();
                // }
            } catch (Exception $e) {
                print 'An error occurred in list history function : ' . $e->getMessage();
                return (false);
            }
        } while ($pageToken);

        return $histories;
    }

    function getMessageIdsInHistory($histories)
    {
        $gmailIds = [];
        foreach ($histories as $history) {
            $messages = $history->messages;
            foreach ($messages as $message) {
                $gmailIds[] = $message->id;
            }
        }

        return ($gmailIds);
    }

    /*
     *
     * permet de retourner le header d'un email. Pour retourner le from il suffit de saisir "From" comme headerName par exemple.
     *
     */
    function getHeader(\Google_Service_Gmail_Message $message, $headerName)
    {
        $headers = $message->getPayload()->getHeaders();

        foreach ($headers as $header) {

            if ($header->name == $headerName) {

                return ($header->value);
            }
        }
        return (null);
    }

    function hasLabel($message, $label)
    {
        $labelIds = $message->labelIds;

        if (in_array($label, $labelIds)) {
            return (true);
        } else {
            return (false);
        }
    }

    function getNewMessages($lastHistoryId, $label = "INBOX", $format = ['format' => 'full'], $historyTypes = "messageAdded")
    {
        $histories = $this->listHistory($lastHistoryId, $historyTypes, $label);

        $indexMessage = 0;
        $messageLimit = 20;

        $messages = [];

        foreach ($histories as $history) {

            $message = $history->messages[0];

            $message = $this->getMessage($message->id, $format);

            if ($message) {

                if ($this->hasLabel($message, "INBOX")) {

                    $messages[] = $message;

                    $indexMessage ++;
                }
            }

            $lastHistoryId = $history->id;

            if ($indexMessage == $messageLimit) {
                break;
            }
        }

        return (array(
            "messages" => $messages,
            "lastHistoryId" => $lastHistoryId
        ));
    }

    /*
     * sert a creer un message pour l'envoyer a partir de gmail
     *
     */
    private function createMessage($body, $subject, $to, $replyTo, $from, $fromName)
    {
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";

        $mail->From = $from;
        $mail->FromName = $fromName;

        $mail->AddAddress($to);
        $mail->addReplyTo($replyTo);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->preSend();
        $mime = $mail->getSentMIMEMessage();
        $gMessage = new Google_Service_Gmail_Message();
        $data = base64_encode($mime);
        $data = str_replace(array(
            '+',
            '/',
            '='
        ), array(
            '-',
            '_',
            ''
        ), $data); // url safe
        $gMessage->setRaw($data);

        return $gMessage;
    }

    /**
     * Send Message.
     *
     * @param Google_Service_Gmail $service
     *            Authorized Gmail API instance.
     * @param string $userId
     *            User's email address. The special value 'me'
     *            can be used to indicate the authenticated user.
     * @param Google_Service_Gmail_Message $message
     *            Message to send.
     * @return Google_Service_Gmail_Message sent Message.
     */
    function sendMessage($body, $subject, $to, $replyTo, $from, $fromName, $threadId = false)
    {
        $gMessage = $this->createMessage($body, $subject, $to, $replyTo, $from, $fromName);

        if ($threadId) {
            $gMessage->threadId = $threadId;
        }

        try {
            $gMessage = $this->service->users_messages->send($this->userId, $gMessage);
            print 'Message with ID: ' . $gMessage->getId() . ' sent.<br>';
            return $gMessage;
        } catch (Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
    }

    function decodeBody($body)
    {
        $rawData = $body;
        $sanitizedData = strtr($rawData, '-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);
        if (! $decodedMessage) {
            $decodedMessage = FALSE;
        }
        return $decodedMessage;
    }

    function getBody($message, $type = "html")
    {
        $body = "";
        $message_array = json_decode(json_encode($message), True);
        $datas = $this->getDatas($message_array);

        if (array_key_exists("text/html", $datas) && $type == "html") {
            return ($this->decodeBody($datas["text/html"]));
        } else {
            return ($this->decodeBody($datas["text/plain"]));
        }
    }

    function getDatas(array $message_part, $mimeType = "text/html")
    {
        $values = [];

        foreach ($message_part as $key => $value) {

            if ($key === "data") {
                if ($value) {
                    $values[$mimeType] = $value;
                }
            } elseif ($key === "mimeType") {

                if ($value === "text/html") {
                    $mimeType = "text/html";
                } elseif ($value === "text/plain") {
                    $mimeType = "text/plain";
                }
            } elseif (is_array($value)) {
                $values = array_merge($values, $this->getDatas($value, $mimeType));
            }
        }
        return ($values);
    }

    function readSheet($sheetId = '1dUtoN7GsgfPtWJcoanlwYn1o83i9ABaxZeefz6aOfts', $sheetName = 'prog-new-act')
    {
        $service = new \Google_Service_Sheets($this->client);

        $response = $service->spreadsheets_values->get($sheetId, $sheetName);
        $values = $response->getValues();

        if (empty($values)) {
            print "No data found.\n";
        } else {
            return ($values);
        }
    }

    function test()
    {
        $service = new \Google_Service_Sheets($this->client);

        // Prints the names and majors of students in a sample spreadsheet:
        // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        $spreadsheetId = '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms';
        $range = 'Class Data';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            print "No data found.\n";
        } else {
            print "Name, Major:\n";
            foreach ($values as $row) {
                // Print columns A and E, which correspond to indices 0 and 4.
                printf("%s, %s\n", $row[0], $row[4]);
            }
        }
    }

    public function forwardMessage($gmailId, $to, $replyTo)
    {
        $gMessage = $this->getMessage($gmailId, [
            "format" => "full"
        ]);

        $subject = $this->getHeader($gMessage, "Subject");
        $body = $this->getBody($gMessage);
        $this->sendMessage($body, $subject, $to, $replyTo, "mailsfromlbc@gmail.com", "lbcBot");
    }

    public function forwardMessages($query, $nbPage = 1, $to, $replyTo)
    {
        $msgs = $this->listMessages($query, $nbPage);

        foreach ($msgs as $msg) {

            $this->forwardMessage($msg->id, $to, $replyTo);
        }
    }
}

