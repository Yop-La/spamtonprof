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
            } elseif (strpos(strtolower($subject), "renouvelez gratuitement") !== false && false) {

                try {

                    $to = extractFirstMail($to);

                    $urls = extract_url($body);
                    $url = $urls[0][2];

                    $url = htmlspecialchars_decode($url);

                    $act = $this->lbcAccountMg->get(array(
                        "mail" => $to
                    ));

                    $renewalUrl = $this->renewalUrlMg->add(new \spamtonprof\stp_api\LbcRenewalUrl(array(
                        "url" => $url,
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
    public function checkAds($nbCompte)
    {
        $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $lbcApi = new \spamtonprof\stp_api\LbcApi();
        $adTempoMg = new \spamtonprof\stp_api\AddsTempoManager();
        $slack = new \spamtonprof\slack\Slack();

        // step 1 :recuperer les comptes ages d'au moins 2h.
        $lbcAccounts = $lbcAccountMg->getAccountToScrap($nbCompte);

        $i = 0;
        $msgs = [];
        $nb_acts = count($lbcAccounts);
        foreach ($lbcAccounts as $lbcAccount) {

            $msgs[] = "Controle de " . $lbcAccount->getRef_compte();

            $codePromo = $lbcAccount->getCode_promo();
            $user_id = $lbcAccount->getUser_id();

            // step 2 : suppression des annonces dans la base
            $adTempoMg->deleteAll(array(
                "ref_compte" => $lbcAccount->getRef_compte()
            ));

            // step 3 : recuperation des annonces via api leboncoin

            $ads = false;

            if ($user_id) {
                $ads = $lbcApi->getAdds(array(
                    'user_id' => $user_id
                ));
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

                foreach ($ads as $ad) {

                    $firstPublicationDate = $ad->first_publication_date;
                    $zipcode = $ad->location->zipcode;
                    $city = $ad->location->city;
                    $id = $ad->list_id;
                    $hasPhone = $ad->has_phone;

                    // 4-1-1 : on ajoute ces annonces a adds_tempo
                    $adTempo = new \spamtonprof\stp_api\AddsTempo(array(
                        "first_publication_date" => $firstPublicationDate,
                        "zipcode" => $zipcode,
                        "city" => $city,
                        "id" => $id,
                        "has_phone" => $hasPhone,
                        "ref_compte" => $lbcAccount->getRef_compte()
                    ));
                    $adTempoMg->add($adTempo);
                    $nbAnnonce ++;
                }

                // 4-1-2 : on va mettre a jour la ref_commune de adds_tempo
                $adsTemp = $adTempoMg->getAll(array(
                    "ref_compte" => $lbcAccount->getRef_compte()
                ));

                $adTempoMg->updateAllRefCommune($adsTemp);
            } else {
                $disabled = true;
                $nbAnnonce = 0;
            }
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

            if ($i % 20 == 0 || $i == ($nb_acts - 1)) {
                $slack->sendMessages("log-lbc", $msgs);
                $msgs = [];
            }
            $i ++;
        }
    }

    // pour generer et retourner les annonces avant publication par zenno
    public function generateAds($refClient, $nbAds, $phone, $lock = false, $ref_compte = false)
    {
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();
        // on recupere le client
        $client = $clientMg->get(array(
            'ref_client' => $refClient
        ));

        // si il y a une seule annonce c'est que c'est une premire annonce sur un compte vierge. On doit mettre une annonce qui passe ( celle de Valentin )
        $ref_client_content = $refClient;
        $client_content = $client;
        if (false && $nbAds == 1) {

            $ref_client_content = 25;
            $client_content = $clientMg->get(array(
                'ref_client' => $ref_client_content
            ));
        }

        // on recupere les titres
        $hasTypeTitleMg = new \spamtonprof\stp_api\HasTitleTypeManager();
        $lbcTitleMg = new \spamtonprof\stp_api\LbcTitleManager();
        $communeMg = new \spamtonprof\stp_api\LbcCommuneManager();
        $adMg = new \spamtonprof\stp_api\AddsTempoManager();
        $actMg = new \spamtonprof\stp_api\LbcAccountManager();

        $hasTypeTitle = $hasTypeTitleMg->get(array(
            "ref_client_defaut" => $ref_client_content
        ));
        $titles = $lbcTitleMg->getAll(array(
            "ref_type_titre" => $hasTypeTitle->getRef_type_titre()
        ));
        shuffle($titles);

        // on recupere les textes
        $hasTypeTexteMg = new \spamtonprof\stp_api\HasTextTypeManager();
        $hasTypeTexte = $hasTypeTexteMg->get_next($ref_client_content);

        $lbcTexteMg = new \spamtonprof\stp_api\LbcTexteManager();
        $textes = $lbcTexteMg->getAll(array(
            "ref_type_texte.valid" => $hasTypeTexte->getRef_type()
        ));
        shuffle($textes);

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
        $communes = $communeMg->getAll(array(
            "ref_client" => $refClient
        ));

        // on constitue les annonces ( en verouillant les communes de ces annonces)
        $nbTitles = count($titles);
        $nbTextes = count($textes);
        $nbCommunes = count($communes);

        // recuperation des images
        $images = scandir(ABSPATH . 'wp-content/uploads/lbc_images/' . $client_content->getImg_folder());

        unset($images[0]);
        unset($images[1]);

        shuffle($images);

        $nbImages = count($images);

        $ads = [];
        for ($i = 0; $i < $nbAds; $i ++) {

            // recuperation du titre
            $title = $titles[$i % $nbTitles];
            $title = $title->getTitre();

            // recuperation du texte
            $texte = $textes[$i % $nbTextes];

            serializeTemp($texte);

            $texte->setTexte(str_replace(array(
                'Alexandre',
                'alexandre',
                'Anahyse',
                'anahyse'
            ), $prenom, $texte->getTexte()));

            // recuperation de l'image
            $image = 'https://spamtonprof.com/wp-content/uploads/lbc_images/' . $client_content->getImg_folder() . '/' . $images[($i % $nbImages) + 2];

            // recuperation de la commune
            $commune = $communes[$i % $nbCommunes];
            $nomCommune = $commune->getLibelle() . " " . $commune->getCode_postal();

            if ($lock) {
                // verouillage des communes prises dans les annonces
                $adTempo = new \spamtonprof\stp_api\AddsTempo(array(
                    "ref_compte" => $ref_compte,
                    "ref_commune" => $commune->getRef_commune()
                ));
                $adMg->add($adTempo);
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

            $ad = new \stdClass();
            $ad->title = $title;
            $ad->text = $texte;
            $ad->image = $image;
            $ad->commune = $nomCommune;
            $ad->commune = $nomCommune;
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