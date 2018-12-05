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
        $accountMg = new \spamtonprof\stp_api\StpGmailAccountManager();

        $keyMg = new \spamtonprof\stp_api\KeyManager();

        $key = $keyMg->get($keyMg::GMAIL_KEY);

        $account = $accountMg->get($gmailAdress);

        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');

        $client->setScopes(array(
            Google_Service_Sheets::SPREADSHEETS,
            Google_Service_Gmail::GMAIL_MODIFY
        ));

        $authConfig = json_decode($key->getKey(), true);

        $client->setAuthConfig($authConfig);
        $client->setAccessType('offline');

        $accessToken;

        if (! $account) {
            echo ("ajouté : " . $gmailAdress . " à la table prof <br><br><br>");
            exit(0);
        }

        if ($account->getCredential() != "" && ! is_null($account->getCredential())) {

            $accessToken = json_decode($account->getCredential(), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();

            $authCode = "4/qgAUI0_GtpAZ5GlWXMYCDcd2pZ2-HgLiU5EeQKCIcWV-kJrf97LNfBI"; // à remplir par ce qui sera donné par $authUrl

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
                }
            } catch (\Exception $e) {
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

    function listHistory($startHistoryId, $historyTypes = "messageAdded", $labelId = "INBOX")
    {
        $userId = $this->userId;
        $service = $this->service;
        $opt_param = array(
            'startHistoryId' => $startHistoryId,
            'historyTypes' => $historyTypes,
            'maxResults' => '1000',
            'labelId' => $labelId
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

    function getNewMessages($lastHistoryId, $label = "INBOX")
    {
        $histories = $this->listHistory($lastHistoryId, $historyTypes = "messageAdded");

        $indexMessage = 0;
        $messageLimit = 20;

        $messages = [];

        foreach ($histories as $history) {

            $message = $history->messages[0];

            $message = $this->getMessage($message->id, [
                'format' => 'full'
            ]);

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
     * sert à créer un message pour l'envoyer à partir de gmail
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

    function readSheet($sheetId = '1dUtoN7GsgfPtWJcoanlwYn1o83i9ABaxZeefz6aOfts', $sheetName = 'prog')
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
}

