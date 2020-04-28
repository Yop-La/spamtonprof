<?php
namespace spamtonprof\stp_api;

use spamtonprof;

class LbcProcessManager
{

    public $slack, $gmailManager, $prospectLbcMg, $messProspectMg, $lbcAccountMg, $msgs, $errors, $gmailAccountMg, $gmailAccount, $renewalUrlMg;

    public function __construct($email = "mailsfromlbc@gmail.com")
    {
        $this->slack = new \spamtonprof\slack\Slack();

        $this->clientMg = new \spamtonprof\stp_api\LbcClientManager();
        $this->prospectLbcMg = new \spamtonprof\stp_api\ProspectLbcManager();
        $this->messProspectMg = new \spamtonprof\stp_api\MessageProspectLbcManager();
        $this->lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $this->gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
        $this->messageTypeMg = new \spamtonprof\stp_api\LeadMessageTypeManager();
        $this->renewalUrlMg = new \spamtonprof\stp_api\LbcRenewalUrlManager();

        $this->gmailManager = new \spamtonprof\googleMg\GoogleManager($email);
        $this->gmailAccount = $this->gmailAccountMg->get($email);
        $this->lbcAdValidationEmailMg = new \spamtonprof\stp_api\LbcAdValidationEmailManager();

        $this->msgs = [];
        $this->errors = [];
    }

    /*
     * cette function lit les nouveaux messages de mailsfromlbc ( ie les messages dont l'history id est plus grand que le dernier history id ).
     *
     */
    public function read_messages_mailfromlbc()
    {
        // on récupère le dernier history id
        $lastHistoryId = $this->gmailAccount->getLast_history_id();

        echo ("Dernier history id ( avant process ) : " . $lastHistoryId . '<br>');

        $retour = $this->gmailManager->getNewMessages($lastHistoryId);
        $messages = $retour["messages"];
        $lastHistoryId = $retour["lastHistoryId"];

        $this->gmailAccount->setLast_history_id($lastHistoryId);
        $this->gmailAccountMg->updateHistoryId($this->gmailAccount);

        echo ("------  nb messages : " . count($messages) . " ----- <br>");

        $nbMessageToProcess = 100;
        $indexMessageProcessed = 0;

        foreach ($messages as $message) {

            $gmailId = $message->id;

            $from = $this->gmailManager->getHeader($message, "From");
            $snippet = $message->snippet;
            $subject = $this->gmailManager->getHeader($message, "Subject");
            $to = $this->gmailManager->getHeader($message, "To");
            $messageId = $this->gmailManager->getHeader($message, "Message-Id");
            $body = $this->gmailManager->getBody($message);

            $timeStamp = $message->internalDate / 1000;
            $dateReception = new \DateTime();
            $dateReception->setTimestamp($timeStamp);
            $dateReception->setTimezone(new \DateTimeZone('Europe/Paris'));

            echo ($subject . '   --- date reception : ' . $dateReception->format(PG_DATETIME_FORMAT) . '<br>');

            $messageType = 0;

            if (strpos($from, 'messagerie.leboncoin.fr') !== false) { // message du bon coin a priori via mailgun ou via gmx

                // est ce le début d'une conversation ?
                if (strpos($body, 'Messages précédents') !== false) { // si le message contient "Messages précédents" alors ce n'est pas le début d'une conversation
                    $messageType = $this->messageTypeMg::CONVERSATION_MESSAGERIE_LEBONCOIN;
                } else {
                    $messageType = $this->messageTypeMg::DEBUT_MESSAGERIE_LEBONCOIN;
                }

                $to = extractFirstMail($to);
                $from = extractFirstMail($from);

                if ($messageType != 0) {

                    echo ("from " . $from . " Date de réception: " . $dateReception->format(FR_DATETIME_FORMAT) . '<br><br><br>');

                    $pseudo = extract_text_from_node("user", $body);
                    $pseudo = trim(str_replace("NOUVEAU", "", $pseudo));

                    $txt_msg = extract_text_from_node("msg", $body);
                    $txt_msg = trim($txt_msg);

                    $lbcProfil = $to;
                    $leadEmail = $from;

                    // on fait les enregistrements en base

                    // determination du compte leboncoin associe au messsage
                    $compteLbc = $this->lbcAccountMg->get(array(
                        'mail' => $lbcProfil
                    ));

                    if (! $compteLbc) {
                        return;
                    }

                    // enregistrement du prospect si il n'existe pas
                    $prospectLbc = $this->prospectLbcMg->get(array(
                        "adresse_mail" => $leadEmail
                    ));

                    if (! $prospectLbc) {

                        $prospectLbc = new \spamtonprof\stp_api\ProspectLbc();
                        $prospectLbc->setAdresse_mail($leadEmail);
                        $prospectLbc = $this->prospectLbcMg->add($prospectLbc);
                    }

                    // enregistrement du messge du prospect
                    $mess = new \spamtonprof\stp_api\MessageProspectLbc();

                    $mess->setDate_reception($dateReception);
                    $mess->setRef_compte_lbc($compteLbc->getRef_compte());
                    $mess->setRef_prospect_lbc($prospectLbc->getRef_prospect_lbc());
                    $mess->setGmail_id($gmailId);
                    $mess->setSubject($subject);
                    $mess->setType($messageType);
                    $mess->setPseudo($pseudo);
                    $mess->setBody($txt_msg);
                    $mess = $this->messProspectMg->add($mess);

                    // on attribue le libelle client
                    // on attribue le libelle coresspondant au type de conv
                    // on attribue le libelle correpondant au type de fournisseur email ( mailgun, gmx )
                    // attribuer le libelle pour dire si il doit y avoir réponse au nom
                }
            } elseif (strpos($from, 'le.bureau.des.profs@gmail.com') !== false) {

                if (strpos($subject, "|--|") !== false) {

                    $matches = [];
                    preg_match('/\|--\|(\d*)\|--\|/', $subject, $matches);

                    $refMessage = $matches[1];

                    $stpMessage = $this->messProspectMg->get(array(
                        "ref_message" => $refMessage
                    ));

                    if ($stpMessage) {

                        $body = $this->gmailManager->getBody($message);

                        $stpMessage->setReply($body);
                        $this->messProspectMg->updateReply($stpMessage);

                        $stpMessage->setAnswer_gmail_id($gmailId);
                        $this->messProspectMg->updateAnswerGmailId($stpMessage);

                        $stpMessage->setTo_send(true);
                        $this->messProspectMg->update_to_send($stpMessage);

                        // // attribuer un libelle pour dire que le message a ete lu
                        // $labelId = $this->gmailManager->getLabelsIds(array(
                        // "bot_read_it"
                        // ));

                        // $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    }
                }
            } elseif (strpos(strtolower($subject), "renouvelez gratuitement") !== false) {

                try {

                    $to = extractFirstMail($to);

                    $urls = extract_url($body);

                    $urls = $urls[0];

                    $renewal_url = $urls[2];

                    foreach ($urls as $url) {

                        $url = htmlspecialchars_decode($url);

                        if (strpos($url, 'https://www.leboncoin.fr/ai') !== false) {
                            $renewal_url = $url;
                            break;
                        }
                    }

                    $url = htmlspecialchars_decode($url);

                    $act = $this->lbcAccountMg->get(array(
                        "mail" => $to
                    ));

                    $renewalUrl = $this->renewalUrlMg->add(new \spamtonprof\stp_api\LbcRenewalUrl(array(
                        "url" => $renewal_url,
                        "statut" => $this->renewalUrlMg::TO_RENEW,
                        "ref_compte_lbc" => $act->getRef_compte(),
                        "date_reception" => $dateReception->format(PG_DATETIME_FORMAT)
                    )));

                    // attribuer un libelle pour dire que le message a ete lu
                    $labelId = $this->gmailManager->getLabelsIds(array(
                        "bot_read_it"
                    ));

                    $this->gmailManager->modifyMessage($gmailId, $labelId, array());

                    $this->slack->sendMessages('log-lbc', array(
                        "Récupération du lien de renouvellement d'annonce :" . $url,
                        "Avec pour compte : " . $act->getMail()
                    ));
                } catch (\Exception $e) {

                    $this->slack->sendMessages('log-lbc', array(
                        "Erreur lors de la récupération du lien de renouvellement d'annonce :" . $url,
                        "Avec pour compte : " . $act->getMail()
                    ));
                }
            } elseif (strpos(strtolower($subject), "est en ligne") !== false) {

                $to = extractFirstMail($to);

                $act = $this->lbcAccountMg->get(array(
                    "mail" => $to
                ));

                $this->lbcAdValidationEmailMg->add(new \spamtonprof\stp_api\LbcAdValidationEmail(array(
                    'gmail_id' => $gmailId,
                    'date_reception' => $dateReception->format(PG_DATETIME_FORMAT),
                    'ref_compte_lbc' => $act->getRef_compte()
                )));

                $client = $this->clientMg->get(array(
                    'ref_client' => $act->getRef_client()
                ));

                $labels = [];
                $labels[] = $client->getLabel();

                if (count($labels) > 0) {
                    $labelIds = $this->gmailManager->getLabelsIds($labels);
                    $this->gmailManager->modifyMessage($gmailId, $labelIds, array());
                }
            }

            $indexMessageProcessed ++;
            if ($nbMessageToProcess == $indexMessageProcessed) {
                break;
            }
        }
    }

    // lit et stocke les mails du bureau des profs
    public function read_messages_lebureaudesprofs()
    {
        $lastHistoryId = $this->gmailAccount->getLast_history_id();

        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        $now->sub(new \DateInterval("PT2H"));
        $now = $now->format('Y/m/d');

        $retour = $this->gmailManager->getNewMessages($lastHistoryId);

        $messages = $retour["messages"];

        $lastHistoryId = $retour["lastHistoryId"];

        $this->gmailAccount->setLast_history_id($lastHistoryId);
        $this->gmailAccountMg->updateHistoryId($this->gmailAccount);

        echo ("------ nb messages : " . count($messages) . " ----- <br><br><br>");

        foreach ($messages as $message) {

            // on regarde le titre du message si il commence par |--|ref_|--| c'est bon
            $subject = $this->gmailManager->getHeader($message, "Subject");

            echo ($subject . ' -- <br>');

            $matches = [];
            preg_match('/^\|--\|(\d*)\|--\|/', $subject, $matches);

            if (count($matches) != 2) {
                continue;
            }

            // on extrait la ref_message
            $refMessage = $matches[1];

            // on recupere le message dans la table message_prospect_lbc
            $messProspectMg = new \spamtonprof\stp_api\MessageProspectLbcManager();
            $msg = $messProspectMg->get(array(
                "ref_message" => $refMessage
            ));

            if ($msg) {

                // pour signaler que l'email est bien reçu
                $msg->setIn_agent_box(true);
                $messProspectMg->update_in_agent_box($msg);

                $msg->setGmail_id_bureau_prof($message->id);
                $messProspectMg->update_gmail_id_bureau_prof($msg);
            } else {
                echo ('le message de ref : ' . $refMessage . " n'existe pas.<br>");
            }
        }
    }

    /*
     * attribue les étiquettes : prospect_reconnu, pseudo_reconnu, body_reconnu à chaque nouveau message de prospect que ce soit dans la bdd ou dans gmail
     *
     * Elle n'est exécuté qu'une seule fois par message
     * processed = True à la fin
     *
     */
    public function categoriser_lead_message()
    {
        $message = $this->messProspectMg->get_new_lead_messages();

        if ($message) {
            $date_limit = \DateTime::createFromFormat(PG_DATETIME_FORMAT, $message->getDate_reception()->format(PG_DATETIME_FORMAT), new \DateTimeZone('Europe/Paris'));
            $date_limit->sub(new \DateInterval('PT2H'));

            $labels = [];

            $msg = $this->messProspectMg->get(array(
                "prospect_existe" => array(
                    'ref_prospect_lbc' => $message->getRef_prospect_lbc(),
                    'ref_message' => $message->getRef_message()
                )
            ));
            $ancien_prospect = false;
            if ($msg) {
                $labels[] = 'prospect_reconnu';
                $ancien_prospect = true;
                $message->setAncien_prospect($ancien_prospect);
                $this->messProspectMg->update_ancien_prospect($message);
                $msg = false;
            }

            // voir si ce pseudo a déjà été utilisé pendant les deux dernières heures
            $msg = $this->messProspectMg->get(array(
                "pseudo_existe" => array(
                    'pseudo' => $message->getPseudo(),
                    'ref_message' => $message->getRef_message(),
                    'date_limite' => $date_limit->format(PG_DATETIME_FORMAT)
                )
            ));
            $pseudo_reconnu = false;
            if ($msg) {
                $labels[] = 'pseudo_reconnu';
                $pseudo_reconnu = true;
                $message->setPseudo_reconnu($pseudo_reconnu);
                $this->messProspectMg->update_pseudo_reconnu($message);
                $msg = false;
            }

            // voir si ce message a déjà été utilisé pendant les deux dernières heures
            $msg = $this->messProspectMg->get(array(
                "body_existe" => array(
                    'body' => $message->getBody(),
                    'ref_message' => $message->getRef_message(),
                    'date_limite' => $date_limit->format(PG_DATETIME_FORMAT)
                )
            ));
            $body_reconnu = false;
            if ($msg) {
                $labels[] = 'body_reconnu';
                $body_reconnu = true;
                $message->setMessage_reconnu($body_reconnu);
                $this->messProspectMg->update_message_reconnu($message);
                $msg = false;
            }

            // attribution des labels
            if ($message->getType() == LeadMessageTypeManager::DEBUT_MESSAGERIE_LEBONCOIN) {
                $labels[] = "debut-messagerie-leboncoin";
            } else if ($message->getType() == LeadMessageTypeManager::CONVERSATION_MESSAGERIE_LEBONCOIN) {
                $labels[] = "conversation-messagerie-leboncoin";
            }

            // on récupère le compte et le client
            $compteMg = new \spamtonprof\stp_api\LbcAccountManager();
            $compte = $compteMg->get(array(
                'ref_compte' => $message->getRef_compte_lbc()
            ));

            $client = $this->clientMg->get(array(
                'ref_client' => $compte->getRef_client()
            ));

            $labels[] = $client->getLabel();

            if (count($labels) > 0) {
                $labelIds = $this->gmailManager->getLabelsIds($labels);
                $this->gmailManager->modifyMessage($message->getGmail_id(), $labelIds, array());
            }

            $message->setLabelled(true);
            $this->messProspectMg->update_labelled($message);
        }
    }

    public function process_new_lead_messages()
    {
        // pour traiter les messages de leads si il y en a et les transferer au bureau des profs
        for ($i = 0; $i < 5; $i ++) {

            $this->categoriser_lead_message();
            $this->forward_lead_message();
        }
    }

    /*
     * pour étiquetter les messages venant du bureau des profs
     *
     *
     */
    public function label_forwarded_messages()
    {
        $messages = $this->messProspectMg->getAll(array(
            'forwarded_messages'
        ));

        foreach ($messages as $message) {

            $labels = [];

            if ($message->getAncien_prospect()) {
                $labels[] = 'prospect_reconnu';
            }

            if ($message->getPseudo_reconnu()) {
                $labels[] = 'pseudo_reconnu';
            }

            if ($message->getMessage_reconnu()) {
                $labels[] = 'body_reconnu';
            }

            // attribution des labels
            if ($message->getType() == LeadMessageTypeManager::DEBUT_MESSAGERIE_LEBONCOIN) {
                $labels[] = "debut-messagerie-leboncoin";

                $this->slack->sendMessages('log-lbc', array(
                    "Nouvelle conversation sur leboncoin"
                ));
            } else if ($message->getType() == LeadMessageTypeManager::CONVERSATION_MESSAGERIE_LEBONCOIN) {
                $labels[] = "conversation-messagerie-leboncoin";
            }

            // on récupère le compte et le client
            $compteMg = new \spamtonprof\stp_api\LbcAccountManager();
            $compte = $compteMg->get(array(
                'ref_compte' => $message->getRef_compte_lbc()
            ));

            $client = $this->clientMg->get(array(
                'ref_client' => $compte->getRef_client()
            ));

            $labels[] = $client->getLabel();

            if (count($labels) > 0) {
                $labelIds = $this->gmailManager->getLabelsIds($labels);
                $this->gmailManager->modifyMessage($message->getGmail_id_bureau_prof(), $labelIds, array());
            }

            $message->setReady_to_answer(true);
            $this->messProspectMg->update_ready_to_answer($message);
        }
    }

    public function forward_lead_message()
    {
        $message = $this->messProspectMg->getMessageToForward();

        if ($message) {
            $gmailId = $message->getGmail_id();
            $subject = $message->getSubject();
            $refMessage = $message->getRef_message();

            $gMessage = $this->gmailManager->getMessage($gmailId, [
                "format" => "full"
            ]);

            $body = $this->gmailManager->getBody($gMessage);

            $subject = "|--|" . $refMessage . "|--| " . $subject;
            $replyTo = "mailsfromlbc@gmail.com";

            $this->gmailManager->sendMessage($body, $subject, "le.bureau.des.profs@gmail.com", $replyTo, "mailsfromlbc@gmail.com", "lbcBot");

            $labelIds = $this->gmailManager->getLabelsIds(array(
                'forwarded'
            ));
            $this->gmailManager->modifyMessage($message->getGmail_id(), $labelIds, array());

            $message->setForwarded(true);
            $this->messProspectMg->update_forwarded($message);
        }
    }

    public function label_message($gmail_id, array $labels)
    {
        $labelId = $this->gmailManager->getLabelsIds($labels);
        $this->gmailManager->modifyMessage($gmail_id, $labelId, array());
    }

    public function send_reply_to_lead()
    {
        $message = $this->messProspectMg->get_message_to_send();

        if ($message) {

            $compteLbc = $this->lbcAccountMg->get(array(
                "ref_compte" => $message->getRef_compte_lbc()
            ));

            $lead = $this->prospectLbcMg->get(array(
                "ref_prospect_lbc" => $message->getRef_prospect_lbc()
            ));

            $subject = 'Re: ' . str_replace('leboncoin', 'lebonc...', $message->getSubject());
            $body = $message->getReply();

            $to = $lead->getAdresse_mail();

            $send = $this->sendLeadReply($compteLbc, $subject, $to, $body, $message);

            if ($send) {

                $message->setTo_send(false);
                $this->messProspectMg->update_to_send($message);

                // on attribue le libellé répondu au message du prospect
                $this->label_message($message->getGmail_id(), array(
                    "Repondu"
                ));

                // on attribue le libellé envoyé à la réponse envoyé par l'agent depuis le bureaudesprofs
                $this->label_message($message->getAnswer_gmail_id(), array(
                    "envoyé"
                ));

                return ($message->getGmail_id_bureau_prof());
            } else {
                $slack = new \spamtonprof\slack\Slack();
                $slack->sendMessages("log-lbc", array(
                    "La reponse au lead de ref " . $message->getRef_message() . " n'a pas pu etre envoye ... "
                ));
            }
            return (false);
        }
    }

    public function sendLeadReply(\spamtonprof\stp_api\LbcAccount $compteLbc, $subject, $to, $body, $message)
    {
        if (strpos($compteLbc->getMail(), 'gmx') !== false) {

            $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
            $gmxAct = $gmxActMg->get(array(
                'ref_compte_lbc' => $compteLbc->getRef_compte()
            ));

            $smtpServer = new \spamtonprof\stp_api\SmtpServer(array(
                'host' => 'mail.gmx.com',
                'port' => 587,
                'password' => $gmxAct->getPassword(),
                'username' => $gmxAct->getMail()
            ));
        } else {
            $smtpServerMg = new \spamtonprof\stp_api\SmtpServerManager();
            $smtpServer = $smtpServerMg->get(array(
                "ref_smtp_server" => $smtpServerMg::smtp2Go
            ));
        }

        $send1 = $smtpServer->sendEmail($subject, $to, $body, $compteLbc->getMail(), $compteLbc->getPrenom(), false);
        $send2 = $smtpServer->sendEmail("Stp Reply : |--|" . $message->getRef_message() . "|--|" . $subject, "lebureaudesprofs@gmail.com", $body, $compteLbc->getMail(), $compteLbc->getPrenom(), false);

        return ($send1 && $send2);
    }

    // cette fonction permet de controler les annonces en ligne des nbCompte derniers comptes actifs (ie qui n'a pas desactive par leboncoin)
    // --- step 1 : recuperation des nb derniers comptes actifs ( on pourrait specifier un autre critere de recuperation des comptes )
    // --- step 2 : on supprime toutes les annonces dans la table adds_tempo comme le compte va de nouveau etre controle
    // --- step 3 : on recupere les potentiels annonces en ligne de ces comptes avec l'api du bon coin

    // --- step 4-1 (si il y a des annonces en ligne)
    // --- step 4-1-1 : on les ajoute a la table adds_tempo
    // --- step 4-1-2 : on met a jour la ref_commune des annonces ajoutes a adds_tempo
    // --- step 5 : on desactive ou on active le compte
    // --- step 6 : on met a jour le nb d'annonce du compte lbc
    // --- step 7 : on met a jour la de controle
    public function check_account(\spamtonprof\stp_api\LbcAccount $lbcAccount, $send_msg = false)
    {
        $msgs = [];

        $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $lbcApi = new \spamtonprof\stp_api\LbcApi();
        $adTempoMg = new \spamtonprof\stp_api\AddsTempoManager();

        $msgs[] = "Controle de " . $lbcAccount->getRef_compte();

        $codePromo = $lbcAccount->getCode_promo();
        $user_id = $lbcAccount->getUser_id();
        $cookie = $lbcAccount->getCookie();

        // step 2 : suppression des annonces dans la base sans ref_titre et ref_texte ( vestige du robot qui ne mémoriser pas les textes et les titres d'annonces )
        $adTempoMg->deleteAll(array(
            "key" => $adTempoMg::no_ref_texte_or_no_ref_titre,
            "ref_compte" => $lbcAccount->getRef_compte()
        ));
        // on ne supprime pas les annonces avec ref_texte et ref_titre (elles seront mise à jour grâce à une jointure sur le titre)

        // step 3 : recuperation des annonces via api leboncoin
        $ads = false;

        if ($cookie) {

            if (! $user_id) {

                $lbcApi = new \spamtonprof\stp_api\LbcApi();
                $user_id = $lbcApi->getUserId($cookie);

                if (! $user_id) {

                    $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
                    $lbcAccount->setControle_date($now);
                    $lbcAccountMg->updateControleDate($lbcAccount);

                    $lbcAccount->setUncheckable(true);
                    $lbcAccountMg->update_uncheckable($lbcAccount);

                    $msgs[] = 'impossible de récupérer le user_id';
                    // on va libérer les annonces d'un compte impossible à checker
                }

                $lbcAccount->setUser_id($user_id);
                $lbcAccountMg->updateUserId($lbcAccount);
            }

            if ($user_id) {

                $ads = $lbcApi->getAdds(array(
                    'key' => 'by_user_id',
                    'params' => [
                        'user_id' => $user_id
                    ]
                ));
            }

            $msgs[] = "User_id : " . $user_id;
        } else if (! is_null($codePromo)) {
            $ads = $lbcApi->getAdds(array(
                "code_promo" => $codePromo
            ));
            $msgs[] = "Pas de user_id : ";
        }

        // step 4-1 : si il y a des annonces en ligne sur leboncoin
        $disabled = false;
        $nbAnnonce = 0;
        if ($ads) {

            $ads = $ads->ads;

            foreach ($ads as $ad_lbc) {

                $firstPublicationDate = $ad_lbc->first_publication_date;
                $zipcode = $ad_lbc->location->zipcode;
                $city = $ad_lbc->location->city;
                $id = $ad_lbc->list_id;
                $hasPhone = $ad_lbc->has_phone;
                $subject = $ad_lbc->subject;

                // on essaye de trouver une correspondance entre l'annonce en ligne et les annonces en lignes grâce au titre
                // si il y a correspondance, on met à jour l'annonce en base
                $ad = $adTempoMg->get(array(
                    "key" => $adTempoMg::nearest_title_ad,
                    'ref_compte' => $lbcAccount->getRef_compte(),
                    'title' => $subject
                ));

                if ($ad) {
                    $ad->setFirst_publication_date($firstPublicationDate);
                    $adTempoMg->update_first_publication_date($ad);

                    $ad->setZipcode($zipcode);
                    $adTempoMg->update_zipcode($ad);

                    $ad->setCity($city);
                    $adTempoMg->update_city($ad);

                    $ad->setId($id);
                    $adTempoMg->update_id($ad);

                    $ad->setHas_phone($hasPhone);
                    $adTempoMg->update_has_phone($ad);

                    $ad->setStatut($adTempoMg::online);
                    $adTempoMg->update_statut($ad);
                } else {
                    // si il n'y a pas de correspondance alors on ajoute cette annonce à la base
                    // c'est le cas des annonces publiés avec la version qui ne mémorisait pas les textes et les titres
                    $adTempo = new \spamtonprof\stp_api\AddsTempo(array(
                        "first_publication_date" => $firstPublicationDate,
                        "zipcode" => $zipcode,
                        "city" => $city,
                        "id" => $id,
                        "has_phone" => $hasPhone,
                        "statut" => $adTempoMg::online,
                        "ref_compte" => $lbcAccount->getRef_compte()
                    ));
                    $adTempoMg->add($adTempo);
                }

                $nbAnnonce ++;
            }

            // 4-1-2: on va virer ( statut = bloque ), les annonces sans correspondance. On part du principe qu'il n'y pas d'annonces en attente de modération
            // ( ie le contrôle se fait au moins deux heures après la publication )
            $ads_to_block = $adTempoMg->getAll(array(
                "key" => $adTempoMg::get_ads_to_block_during_check,
                "ref_compte" => $lbcAccount->getRef_compte()
            ));

            foreach ($ads_to_block as $ad_to_block) {

                $ad_to_block->setStatut($adTempoMg::bloque);
                $adTempoMg->update_statut($ad_to_block);
            }

            // 4-1-3 : on va mettre a jour la ref_commune de adds_tempo car la commune saisi ne correspond pas forcément à la commune en ligne
            $adsTemp = $adTempoMg->getAll(array(
                "key" => $adTempoMg::get_ads_online,
                "ref_compte" => $lbcAccount->getRef_compte()
            ));

            $adTempoMg->updateAllRefCommune($adsTemp);
        } else {
            $disabled = true;
            $nbAnnonce = 0;

            // toutes les annonces de ce compte se voient attribuer le statut bloqué
            $adTempoMg->update_all(array(
                'key' => $adTempoMg::block_ads_of_act,
                'ref_compte' => $lbcAccount->getRef_compte()
            ));
        }

        // les annonces avec le statut publié se voit attribuer le statut refusé
        $adTempoMg->update_all(array(
            'key' => $adTempoMg::update_statut_ad_refuse,
            'ref_compte' => $lbcAccount->getRef_compte()
        ));

        // --- step 5 : on desactive ou on active le compte
        $lbcAccount->setDisabled($disabled);
        $lbcAccountMg->updateDisabled($lbcAccount);

        // --- step 6 : on met a jour le nb d'annonce du compte lbc
        $lbcAccount->setNb_annonces_online($nbAnnonce);
        $lbcAccountMg->updateNbAnnonceOnline($lbcAccount);

        // --- step 7 : on met a jour la date de controle
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        $lbcAccount->setControle_date($now);
        $lbcAccountMg->updateControleDate($lbcAccount);

        $msgs[] = $nbAnnonce . "en ligne";

        if ($send_msg) {
            $slack = new \spamtonprof\slack\Slack();
            $slack->sendMessages("log-lbc-check", $msgs);
        }

        return ($msgs);
    }

    // à faire tourner après check_ads
    // se charge de mesurer les performances des camapagnes . Elle récole les résultats de campagne
    // elle complète le nb d'annonces en ligne d'une campagne
    // une campagne ne peut être analysé qu'une fois par cette méthode
    public function analyse_campaigns()
    {
        $ads_mg = new \spamtonprof\stp_api\AddsTempoManager();

        $lbc_campaign_mg = new \spamtonprof\stp_api\LbcCampaignManager();

        $lbc_campaigns = $lbc_campaign_mg->getAll(array(
            'key' => $lbc_campaign_mg::campaign_to_analyse
        ));

        $slack = new \spamtonprof\slack\Slack();

        $i = 0;
        $msgs = [];
        $nb_campaigns = count($lbc_campaigns);

        foreach ($lbc_campaigns as $campaign) {

            $ads = $ads_mg->getAll(array(
                'key' => $ads_mg::get_ads_online_in_campaign,
                'ref_campaign' => $campaign->getRef_campaign()
            ));

            $nb_ads = count($ads);

            $campaign->setNb_ad_online($nb_ads);
            $lbc_campaign_mg->update_nb_ad_online($campaign);

            $msgs_inter = array(
                '----',
                'Analyse de la campagne ' . $campaign->getRef_campaign(),
                $campaign->getNb_ad_online() . '/' . $campaign->getNb_ad_publie() . ' en ligne(s)'
            );

            $msgs = array_merge($msgs, $msgs_inter);

            $lbc_act_mg = new \spamtonprof\stp_api\LbcAccountManager();
            $lbc_act = $lbc_act_mg->get(array(
                'ref_compte' => $campaign->getRef_compte()
            ));

            if ($nb_ads == 0) {
                $campaign->setFail(true);
                $lbc_campaign_mg->update_fail($campaign);

                $nb_fail = $lbc_act->getNb_failed_campaigns();
                $nb_fail = $nb_fail + 1;
                $lbc_act->setNb_failed_campaigns($nb_fail);
                $lbc_act_mg->update_nb_failed_campaigns($lbc_act);
            } else {
                $nb_success = $lbc_act->getNb_successful_campaigns();
                $nb_success = $nb_success + 1;
                $lbc_act->setNb_successful_campaigns($nb_success);
                $lbc_act_mg->update_nb_successful_campaigns($lbc_act);
            }

            $campaign->setChecked(true);
            $lbc_campaign_mg->update_checked($campaign);

            if ($i % 20 == 0 || $i == ($nb_campaigns - 1)) {
                $slack->sendMessages("log-lbc", $msgs);
                $msgs = [];
            }
            $i ++;
        }

        prettyPrint($lbc_campaigns);
    }

    // elle donne les résultats des campagnes des 7 derniers jours des clients des 5 derniers jours
    // dans le channel campaign_lbc
    public function publish_campaigns_reporting()
    {
        $campaign_lbc = new \spamtonprof\stp_api\LbcCampaignManager();

        $client_mg = new \spamtonprof\stp_api\LbcClientManager();

        $clients = $client_mg->getAll(array(
            'key' => $client_mg::client_last_5_days_campaigns
        ));

        $slack = new \spamtonprof\slack\Slack();

        // $campaigns = [];

        $key = "";
        $reportings = [];

        foreach ($clients as $client) {

            $campaigns = $campaign_lbc->getAll(array(
                'key' => $campaign_lbc::clients_campaigns_to_analyse,
                'ref_client' => $client->getRef_client()
            ));

            foreach ($campaigns as $campaign) {

                $campaign_datetime = \DateTime::createFromFormat(PG_DATETIME_FORMAT, $campaign->getDate(), new \DateTimeZone("Europe/Paris"));

                $campaign_date = $campaign_datetime->format(FR_DATE_FORMAT);
                $campaign_hour = intval($campaign_datetime->format('G'));

                $period = 'moitie_1';
                if ($campaign_hour > 12) {
                    $period = 'moitie_2';
                }

                $campaign_type = 'cultivable';
                if ($campaign->getNb_ad_publie() == 1) {
                    $campaign_type = 'vierge';
                }

                $key = $campaign_date . '__' . $period . '__' . $campaign_type . '__' . $client->getPrenom_client() . '_' . $client->getNom_client();

                if (! array_key_exists($key, $reportings)) {
                    $reportings[$key] = [];
                }
                $reportings[$key][] = $campaign;
            }
        }

        ksort($reportings);

        $campaign_date = "";
        $period = "";
        $campaign_type = "";

        foreach ($reportings as $key => $reporting) {

            $nb_campaign = count($reporting);
            $nb_fail = 0;
            $nb_sucess = 0;
            $nb_total_sucess = 0;

            foreach ($reporting as $campaign) {

                if ($campaign->getNb_ad_online() == $campaign->getNb_ad_publie()) {
                    $nb_total_sucess = $nb_total_sucess + 1;
                } else if ($campaign->getNb_ad_online() == 0) {
                    $nb_fail = $nb_fail + 1;
                } else {
                    $nb_sucess = $nb_sucess + 1;
                }
            }

            $key = explode('__', $key);

            $new_campaign_date = $key[0];
            $new_period = $key[1];
            $new_campaing_type = $key[2];
            $client = $key[3];

            $msgs = [];

            $date_changed = false;
            $period_changed = false;

            if ($campaign_date != $new_campaign_date) {
                $date_changed = true;
                $campaign_date = $new_campaign_date;
                $msgs[] = "----------------      Campagne du $campaign_date      ----------------";
            }

            if ($period != $new_period || $date_changed) {
                $period = $new_period;
                $period_changed = true;
                if ($period == "moitie_2") {
                    $msgs[] = "------------      Dans l'après midi      ------------";
                } else {
                    $msgs[] = "------------      Dans la matinée      ------------";
                }
            }

            if ($campaign_type != $new_campaing_type || $period_changed) {

                $campaign_type = $new_campaing_type;
                if ($campaign_type == "vierge") {
                    $msgs[] = "---------      Nouveau compte      ---------";
                } else {
                    $msgs[] = "---------      Compte existant      ---------";
                }
            }

            $msg = array(
                "------      $client      ------",
                "                nb campaign : $nb_campaign",
                "                % total sucess : " . (round($nb_total_sucess / $nb_campaign * 100, 2) . "% ($nb_total_sucess)"),
                "                % sucess : " . (round($nb_sucess / $nb_campaign * 100, 2) . "% ($nb_sucess)"),
                "                % fail : " . (round($nb_fail / $nb_campaign * 100, 2) . "% ($nb_fail)")
            );

            $msgs = array_merge($msgs, $msg);

            $slack->sendMessages('campaign_lbc', $msgs);
        }

        prettyPrint($reportings);
    }

    // elle donne les comptes ouverts à publication avec plus de deux campagnes échoués
    public function publish_failed_campaigns()
    {
        $slack = new \spamtonprof\slack\Slack();

        $lbc_act_mg = new \spamtonprof\stp_api\LbcAccountManager();

        $acts = $lbc_act_mg->getAll(array(
            'key' => $lbc_act_mg::act_with_fail_campaigns
        ));

        $msgs = [];

        $msgs[] = "----------    Rapport des campagnes échouées    ----------";

        $i = 0;
        $nb_acts = count($acts);

        foreach ($acts as $act) {

            if ($act->getNb_failed_campaigns() >= 2) {

                $msgs[] = "---- Compte: " . $act->getMail() . " ----";
                $msgs[] = "Nb campagnes échouées : " . $act->getNb_failed_campaigns();
                $msgs[] = "Nb campagnes réussies : " . $act->getNb_successful_campaigns();
            }

            if ($i % 20 == 0 || $i == ($nb_acts - 1)) {
                $slack->sendMessages("campaign_lbc", $msgs);
                $msgs = [];
            }
            $i ++;
        }

        prettyPrint($acts);
    }

    public function checkAds($nbCompte)
    {
        $slack = new \spamtonprof\slack\Slack();
        $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $msgs = [];

        // step 1 :recuperer les comptes ages d'au moins 2h.
        $lbcAccounts = $lbcAccountMg->getAccountToScrap($nbCompte);

        $i = 0;
        $msgs = [];
        $nb_acts = count($lbcAccounts);
        foreach ($lbcAccounts as $lbcAccount) {

            echo ($lbcAccount->getRef_compte() . '<br>');

            try {
                $msgs_inter = $this->check_account($lbcAccount, true);
            } catch (\Exception $e) {
                echo ($e->getMessage());
            }
            $msgs = array_merge($msgs_inter, $msgs);

            if ($i % 20 == 0 || $i == ($nb_acts - 1)) {
                $slack->sendMessages("log-lbc-check", $msgs);
                $msgs = [];
            }
            $i ++;
        }
    }

    // pour generer et retourner les annonces avant publication par zenno
    public function generateAds($refClient, $nbAds, $phone, $lock = false, $ref_compte = 0, \spamtonprof\stp_api\LbcCampaign $campaign = null, $category = false)
    {
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();
        $hasTypeTitleMg = new \spamtonprof\stp_api\HasTitleTypeManager();
        $lbcTitleMg = new \spamtonprof\stp_api\LbcTitleManager();
        $communeMg = new \spamtonprof\stp_api\LbcCommuneManager();
        $adMg = new \spamtonprof\stp_api\AddsTempoManager();
        $actMg = new \spamtonprof\stp_api\LbcAccountManager();

        // on recupere le client
        $client = $clientMg->get(array(
            'ref_client' => $refClient
        ));

        $nbTitles = 0;
        $nbTextes = 0;
        $lbcAdsMg = new \spamtonprof\stp_api\LbcAdManager();

        if (false && $nbAds == 1) {
            $client->setAds_from_lbc_ad(true);
        }

        $ads_from_lbc = false;
        if ($client->getAds_from_lbc_ad()) {

            $ads_from_lbc = $lbcAdsMg->getAll(array(
                'key' => 'is_ready'
            ));

            shuffle($ads_from_lbc);

            $nbTitles = count($ads_from_lbc);
            $nbTextes = count($ads_from_lbc);
        }

        $random_ad = false;

        $ref_client_content = $refClient;
        $client_content = $client;
        // pas besoin de récupérer des textes à nouveau
        if (! $client->getAds_from_lbc_ad()) {

            // si il y a une seule annonce c'est que c'est une premire annonce sur un compte vierge. On doit mettre une annonce qui passe ( celle de Valentin )
            if (false && $nbAds == 1) {

                // $ref_client_content = 12;
                // $client_content = $clientMg->get(array(
                // 'ref_client' => $ref_client_content
                // ));
                $random_ad = true;
            }

            // on recupere les titres non déjà utilisés sur ce compte
            $hasTypeTitle = $hasTypeTitleMg->get(array(
                "ref_client_defaut" => $ref_client_content
            ));

            $titles = $lbcTitleMg->getAll(array(
                "ref_type_titre" => $hasTypeTitle->getRef_type_titre(),
                "not_that_title" => $ref_compte
            ));

            shuffle($titles);

            $nbTitles = count($titles);

            // on recupere les textes non déjà potentiellement en ligne ou en ligne sur ce compte
            $hasTypeTexteMg = new \spamtonprof\stp_api\HasTextTypeManager();
            $hasTypeTexte = $hasTypeTexteMg->get_next($ref_client_content);

            $lbcTexteMg = new \spamtonprof\stp_api\LbcTexteManager();
            $textes = $lbcTexteMg->getAll(array(
                "key" => $lbcTexteMg::texte_not_in_that_act,
                "ref_type_texte" => $hasTypeTexte->getRef_type(),
                "ref_compte" => $ref_compte
            ));
            shuffle($textes);

            $nbTextes = count($textes);
        }

        // recuperation des images
        $images = scandir(ABSPATH . 'wp-content/uploads/lbc_images/' . $client_content->getImg_folder());

        unset($images[0]);
        unset($images[1]);

        shuffle($images);

        $nbImages = count($images);

        // on recupere le compte lbc pour avoir le prenom a mettre dans les annonces
        $prenom = '[prenom]';
        if ($ref_compte) {
            $act = $actMg->get(array(
                'ref_compte' => $ref_compte
            ));
            $prenom = $act->getPrenom();

            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

            $act->setDate_publication($now);
            $actMg->update_date_publication($act);
        }

        // on ajoute le num tel aux textes si demande
        if ($phone != 'pas-de-num') {
            $textes = $lbcTexteMg->addPhoneLine($textes, $phone);
        }

        // on recupere les communes

        $target_big_city = true;
        if ($nbAds == 1) {
            $target_big_city = false;
        }

        $communes = $communeMg->getAll(array(
            "ref_client" => $refClient,
            'target_big_city' => $target_big_city
        ));

        if (count($communes) == 0) {
            $communes = $communeMg->getAll(array(
                "ref_client" => $refClient,
                'target_big_city' => false
            ));
        }

        $nbCommunes = count($communes);

        $nbAds = min(array(
            $nbTitles,
            $nbTextes,
            $nbCommunes,
            $nbAds
        ));

        if ($nbAds == 0) {
            $ret = new \stdClass();
            $ret->msg = 'annonces_epuises';
            prettyPrint($ret);
        }

        // on constitue les annonces ( en verouillant les communes de ces annonces)
        $ads = [];
        for ($i = 0; $i < $nbAds; $i ++) {

            $univers = false;

            if (! $client->getAds_from_lbc_ad()) {
                // recuperation du titre
                $title = $titles[$i % $nbTitles];
                $title_str = $title->getTitre();

                // recuperation du texte
                $texte = $textes[$i % $nbTextes];

                $texte->setTexte(str_replace(array(
                    'Alexandre',
                    'alexandre',
                    'Anahyse',
                    'anahyse',
                    'Martin',
                    'martin'
                ), $prenom, $texte->getTexte()));

                $texte->setTexte(str_replace(array(
                    'spamtonprof',
                    'Spamtonprof',
                    'SpamTonProf'
                ), 'spamprof', $texte->getTexte()));

                // prettyPrint($nbImages);

                // recuperation de l'image
                $image = 'https://spamtonprof.com/wp-content/uploads/lbc_images/' . $client_content->getImg_folder() . '/' . $images[($i % $nbImages)];
            }

            // recuperation de la commune
            $commune = $communes[$i % $nbCommunes];
            $nomCommune = $commune->getLibelle() . " " . $commune->getCode_postal();

            if ($lock) {

                $ref_campaign = null;
                if ($campaign) {
                    $ref_campaign = $campaign->getRef_campaign();
                }

                // verouillage des communes prises dans les annonces
                $params = array(
                    "ref_compte" => $ref_compte,
                    "ref_commune" => $commune->getRef_commune(),
                    "ref_titre" => null,
                    "ref_texte" => null,
                    "statut" => $adMg::publie,
                    "ref_campaign" => $ref_campaign
                );

                if (! $client->getAds_from_lbc_ad()) {

                    $params = array(
                        "ref_compte" => $ref_compte,
                        "ref_commune" => $commune->getRef_commune(),
                        "ref_titre" => $title->getRef_titre(),
                        "ref_texte" => $texte->getRef_texte(),
                        "statut" => $adMg::publie,
                        "ref_campaign" => $ref_campaign
                    );
                }

                $adTempo = new \spamtonprof\stp_api\AddsTempo($params);
                $adMg->add($adTempo);
            }

            $ad = new \stdClass();

            if ($random_ad) {
                $lbcApi = new \spamtonprof\stp_api\LbcApi();
                $rd_ad = $lbcApi->get_random_ad();

                $title_str = $rd_ad->subject;
                $texte->setTexte($rd_ad->body);
                $image = $rd_ad->image;
            }

            if ($refClient == 31) {

                $lbcApi = new \spamtonprof\stp_api\LbcApi();
                $ad = $lbcApi->get_local_nike_ad();

                $title_str = $ad->subject;
                $texte->setTexte($ad->body);
                $image = 'http://' . DOMAIN . $ad->image;
            }

            if ($refClient >= 33 && $refClient < 47) {

                $lbcApi = new \spamtonprof\stp_api\LbcApi();
                $ad = $lbcApi->get_local_ad($client->getImg_folder());

                
                $title_str = $ad->subject;
                $texte->setTexte($ad->body);

                
                $image = 'http://' . DOMAIN . $ad->image;
            }

            if ($client->getAds_from_lbc_ad()) {

                $texte = new \spamtonprof\stp_api\LbcTexte();

                $ad_from_lbc = array_pop($ads_from_lbc);

                $ad_from_lbc = $lbcAdsMg->cast($ad_from_lbc);

                $title_str = $ad_from_lbc->getSubject();
                $texte->setTexte($ad_from_lbc->getBody());
                // $image = 'no-picture';
                $image = 'https://spamtonprof.com/wp-content/uploads/lbc_images/' . $client_content->getImg_folder() . '/' . $images[($i % $nbImages)];
                // $image = $ad_from_lbc->getImage_url();
            }

            $symbols = [
                '-',
                '_',
                '/',
                '=',
                '.',
                '*'
            ];
            $symbol = $symbols[rand(0, count($symbols) - 1)];
            $symbols_line = str_repeat($symbol, rand(10, 50));
            $texte->setTexte($symbols_line . PHP_EOL . PHP_EOL . $texte->getTexte() . PHP_EOL . PHP_EOL . $symbols_line);

            $ad->title = $title_str;
            $ad->text = $texte;
            $ad->image = $image;
            $ad->category = $category;
            $ad->commune = $nomCommune;

            if ($univers) {
                $ad->univers = $univers;
            }

            $ads[] = $ad;
        }
        return ($ads);
    }

    // pour retouner la configuration d'un client leboncoin (le type de texte par defaut et le type de titre par defaut d'un client)
    public function getDefaultConf($refClient)
    {

        // on recupere le type titre
        $hasTypeTitleMg = new \spamtonprof\stp_api\HasTitleTypeManager();
        $typeTitreMg = new \spamtonprof\stp_api\TypeTitreManager();

        $hasTypeTitle = $hasTypeTitleMg->get(array(
            "ref_client_defaut" => $refClient
        ));

        $typeTitre = false;
        $messageTypeTitre = 'pas de ref type titre par defaut pour ce client (voir has_title_type)';
        if ($hasTypeTitle) {

            $typeTitre = $typeTitreMg->get(array(
                'ref_type' => $hasTypeTitle->getRef_type_titre()
            ));
            if (! $typeTitre) {
                $messageTypeTitre = 'pas type titre definie type_titre pour ce client (a ajouter)';
            }
        }

        // on recupere le type texte
        $hasTypeTexteMg = new \spamtonprof\stp_api\HasTextTypeManager();

        $typeTexteMg = new \spamtonprof\stp_api\TypeTexteManager();

        $hasTypeTexte = $hasTypeTexteMg->get(array(
            "ref_client_defaut" => $refClient
        ));

        $typeTexte = false;
        $messageTypeTexte = 'pas de ref type texte par defaut pour ce client (voir has_texte_type)';
        if ($hasTypeTexte) {

            $typeTexte = $typeTexteMg->get(array(
                'ref_type' => $hasTypeTexte->getRef_type()
            ));
            if (! $typeTexte) {
                $messageTypeTexte = 'pas type texte definie type_texte pour ce client (a ajouter)';
            }
        }

        // on recupere le client
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();

        $client = $clientMg->get(array(
            'ref_client' => $refClient
        ));

        if ($typeTexte) {
            $messageTypeTexte = 'tout est ok pour le texte';
        }

        if ($typeTitre) {
            $messageTypeTitre = 'tout est ok pour le titre';
        }

        $conf = new \stdClass();
        $conf->typeTexte = $typeTexte;
        $conf->typeTitre = $typeTitre;
        $conf->messagetypeTexte = $messageTypeTexte;
        $conf->messagetypeTitre = $messageTypeTitre;

        $conf->client = $client;

        return ($conf);
    }

    // pour ajouter les titres lors de l'arrive d'un nouveau prof par exemple
    // ajouter type titre a la table type_titre
    // ajouter les titres a la table titres
    function addLbcTitle($fileName = 'titles.csv', $type)
    {
        $csv_path = ABSPATH . $fileName;

        // ajout du type titre si il existe pas
        $typeTitreMg = new \spamtonprof\stp_api\TypeTitreManager();

        $typeTitre = $typeTitreMg->get(array(
            "type" => $type
        ));

        if (! $typeTitre) {

            $typeTitre = $typeTitreMg->add(new \spamtonprof\stp_api\TypeTitre(array(
                'type' => $type
            )));
        }

        // ajout des titres
        $titleMg = new \spamtonprof\stp_api\LbcTitleManager();

        $rows = readCsv($csv_path);

        foreach ($rows as $row) {

            $titleMg->add(new \spamtonprof\stp_api\LbcTitle(array(
                "titre" => $row[0],
                "type_titre" => $typeTitre->getType(),
                "ref_type_titre" => $typeTitre->getRef_type()
            )));
        }
    }

    function addLbcEmails($ref_client, $fileName = 'emails.csv')
    {
        $csv_path = ABSPATH . $fileName;

        // ajout des titres
        $actMg = new \spamtonprof\stp_api\LbcAccountManager();

        $rows = readCsv($csv_path);

        foreach ($rows as $row) {

            echo ($row[0] . '<br>');

            $newAccount = new \spamtonprof\stp_api\LbcAccount();

            $newAccount->setRef_client($ref_client);
            $newAccount->setMail($row[0]);

            $newAccount = $actMg->add($newAccount);
        }
    }

    function send_automatic_reply()
    {
        $msg = $this->messProspectMg->get_message_to_reply();

        if ($msg) {

            // on récupère le message du lead transmis dans le bureau des profs
            $message = $this->gmailManager->getMessage($msg->getGmail_id_bureau_prof(), [
                'format' => 'full'
            ]);

            // on récupère le titre, le threadID et le gmailId du message dans le bureau des profs
            $subject = $this->gmailManager->getHeader($message, "Subject");
            $threadId = $message->threadId;
            $gmailId = $message->id;

            // on recupere ref_compte_lbc a partir du message
            $refCompte = $msg->getRef_compte_lbc();

            // on recupere le compte lbc a partir de la ref_compte_lbc
            $act = $this->lbcAccountMg->get(array(
                'ref_compte' => $refCompte
            ));

            // puis on recupere le client, puis le message a envoyer
            $refClient = $act->getRef_client();

            $client = $this->clientMg->get(array(
                'ref_client' => $refClient
            ));

            if (! $client->getAuto_reply()) {
                $msg->setAutomatic_answer_done(true);
                $this->messProspectMg->update_automatic_answer_done($msg);
                return;
            }

            // recuperation du message a envoyer
            $txtMg = new spamtonprof\stp_api\LbcTexteManager();

            $typeTxtMg = new \spamtonprof\stp_api\TypeTexteManager();
            $typeTxt = $typeTxtMg->get(array(
                'ref_type' => $client->getRef_reponse_lbc()
            ));

            $typeTxt = $typeTxt->getType();

            $txt = $txtMg->get(array(
                'type_random' => $typeTxt
            ));

            $txt = $txt->getTexte();

            $spamtonprofs = array(
                'sppamtonprof',
                'spaamtonprof',
                'spammtonprof',
                'spamttonprof',
                'spamtoonprof',
                'spamtonnprof',
                'spamtonpprof',
                'spamtonprrof',
                'spamtonproof',
                'pamtonprof',
                'spmtonprof',
                'spatonprof',
                'spamonprof',
                'spamtnprof',
                'spamtoprof',
                'spamtonrof'
            );

            $spamtonprof = $spamtonprofs[array_rand($spamtonprofs, 1)];

            $txt = str_replace(array(
                'spamtonprof',
                'sppamtonprof',
                'spamtonpprof'
            ), $spamtonprof, $txt);

            $body = str_replace('[prof_name]', $act->getPrenom(), $txt);

            $body = str_replace('[lien_affilie]', $client->getLink(), $body);

            // on envoie le message
            $this->gmailManager->sendMessage($body, 'Re: ' . $subject, 'mailsfromlbc@gmail.com', 'mailsfromlbc@gmail.com', 'le.bureau.des.profs@gmail.com', 'Cannelle Gaucher', $threadId);

            // on attribue les libellés automatic_reply_done
            $this->label_message($gmailId, array(
                'reponse_auto_faite'
            ));

            // on fait les enregistrements pour éviter de refaire la même réponse automatique
            $msg->setAutomatic_answer_done(true);
            $this->messProspectMg->update_automatic_answer_done($msg);
        }
    }
}