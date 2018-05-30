<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         pour gérer les mails enregistrées dans la table mail_eleve
 *         Le constructeur permet à partir d'un message de gmail de constuire un email
 *         avec des méthodes facile d'utilisation
 *         (pas besoin de se prendre la tête à regarder la structure du message envoyé par gmail
 *         - cette classe se charge de récupérer les champs utiles et de mettre ça dans un objet facile d'utilisation)
 *        
 *         utilisé par les process lbc et les process de suivi des élèves
 */
class Email implements \JsonSerializable
{

    const LABELS_TO_KEEP = array(
        "IMPORTANT",
        "INBOX",
        "SPAM",
        "TRASH",
        "UNREAD",
        "STARRED",
        "IMPORTANT",
        "SENT",
        "DRAFT",
        "CATEGORY_PERSONAL",
        "CATEGORY_SOCIAL",
        "CATEGORY_PROMOTIONS",
        "CATEGORY_UPDATES",
        "CATEGORY_FORUMS"
    );

    protected $subject, $body, $ref_mail, $ref_gmail, $date_reception, $mail_expe, $threadId, $labelIds, $ref_compte, $received, $snippet, $type, $text;

    public function __construct(array $donnees = array(), $type = null)
    
    {
        if (! is_null($type)) {
            $this->type = $type;
        }
        
        if (array_key_exists("message", $donnees)) {
            
            $message = $donnees['message'];
            
            $this->setRef_gmail($message->getId());
            $this->setThreadId($message->getThreadId());
            
            // récupération du snippet
            $this->snippet = $message->snippet;
            
            $headers = $message->getPayload()->getHeaders();
            
            $hasReceived = false;
            $received = array();
            
            foreach ($headers as $header) {
                $name = $header["name"];
                $value = $header["value"];
                if ($name == "Date") {
                    $dateReception;
                    try {
                        $dateReception = new \DateTime($value);
                    } catch (Exception $e) {
                        echo (" erreur date : " . $value . " : <br>");
                        $dateReception = new \DateTime(str_replace("UT", "", "$value"));
                    }
                    $dateReception->setTimezone(new \DateTimeZone("Europe/Paris"));
                    
                    $this->setDate_reception($dateReception);
                }
                
                if ($name == "Subject") {
                    
                    $this->setSubject($value);
                }
                
                // récupération de l'adresse expe
                if ($name == "From") {
                    $from = $value;
                    
                    if (strpos($value, '<') !== false) {
                        
                        $matches = array();
                        preg_match('/<(.*)>/', $value, $matches);
                        $from = $matches[1];
                    }
                    
                    $this->setMail_expe($from);
                }
                
                // récupération des labels
                if (! is_null($message->getLabelIds())) {
                    $this->setLabelIds($message->getLabelIds());
                }
                
                if ($name == "Received") {
                    $received[] = $value;
                    $hasReceived = true;
                }
            }
            if ($hasReceived) {
                $this->received = $received;
            }
            
            if ($type == "lbcType1") {
                $this->body = base64url_decode($message->getPayload()->getParts()[0]->getParts()[0]->getBody()->getData());
                if($this->body == ""){
                    $this->body = base64url_decode($message->getPayload()->getParts()[0]->getParts()[0]->getParts()[1]->getBody()->getData());
                }
            }
            
            if ($type == "lbcType2") {
                $this->body = base64url_decode($message->getPayload()
                    ->getBody()
                    ->getData());
            }
        } else {
            $this->hydrate($donnees);
        }
    }

    public function hydrate(array $donnees)
    
    {
        foreach ($donnees as $key => $value) {
            
            $method = 'set' . ucfirst($key);
            
            if (method_exists($this, $method)) {
                
                $this->$method($value);
            }
        }
    }

    /**
     * pour retirer tous les labels customisés
     */
    public function getCustomLabels()
    {
        $customLabels = [];
        
        for ($i = 0; $i < count($this->labelIds); $i ++) {
            
            $label = $this->labelIds[$i];
            
            if (! in_array($label, $this::LABELS_TO_KEEP)) {
                
                $customLabels[] = $label;
            }
        }
        
        return ($customLabels);
    }

    /**
     *
     * @return mixed
     */
    public function getRef_mail()
    {
        return $this->ref_mail;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_gmail()
    {
        return $this->ref_gmail;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_reception()
    {
        return $this->date_reception;
    }

    /**
     *
     * @param mixed $ref_mail
     */
    public function setRef_mail($ref_mail)
    {
        $this->ref_mail = $ref_mail;
    }

    /**
     *
     * @param mixed $ref_gmail
     */
    public function setRef_gmail($ref_gmail)
    {
        $this->ref_gmail = $ref_gmail;
    }

    /**
     *
     * @param mixed $date_reception
     *            ( string or datetime )
     */
    public function setDate_reception($date_reception)
    {
        if (gettype($date_reception) == "string") {
            
            $date_reception = new \DateTime($date_reception, new \DateTimeZone("Europe/Paris"));
        }
        
        $this->date_reception = $date_reception;
    }

    /**
     *
     * @return mixed
     */
    public function getMail_expe()
    {
        return $this->mail_expe;
    }

    /**
     *
     * @param mixed $mail_expe
     */
    public function setMail_expe($mail_expe)
    {
        $this->mail_expe = $mail_expe;
    }

    /**
     *
     * @return mixed
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     *
     * @param mixed $threadId
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;
    }

    public function __toString()
    {
        return ("gmail id : " . $this->getRef_gmail() . " - mail expe : " . $this->getMail_expe() . " - date reception : " . $this->getDate_reception()->format(PG_DATETIME_FORMAT) . " threadId : " . $this->getThreadId() . "<br> labels : " . print_r($this->getLabelIds(), true));
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    /**
     *
     * @return mixed
     */
    public function getLabelIds()
    {
        return $this->labelIds;
    }

    /**
     *
     * @param mixed $labelIds
     */
    public function setLabelIds($labelIds)
    {
        $this->labelIds = $labelIds;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     *
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    /**
     *
     * @return multitype:unknown
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     *
     * @param multitype:unknown $received
     */
    public function setReceived($received)
    {
        $this->received = $received;
    }

    /**
     *
     * @return mixed
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     *
     * @param mixed $snippet
     */
    public function setSnippet($snippet)
    {
        $this->snippet = $snippet;
    }

    /**
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     *
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * @return string
     */
    public function getText()
    {
        if(is_null($this->text) && !is_null($this->body) && !is_null($this->type)){
            
            if ($this->type == "lbcType1") {
             
                $dom = new \DOMDocument();
                $dom->loadHTML($this->body);
                $paragraphs = $dom->getElementsByTagName('p');
                $msgProspect = "";
                foreach ($paragraphs as $paragraph) {
                    $msgProspect = $msgProspect . "\r\n" . $paragraph->textContent;
                    

                }
                
                $this->text = utf8_decode($msgProspect);
            }
            
            if ($this->type == "lbcType2") {
                $matches = array();
                preg_match('#http://.*\r\n\r\n([\S\s]*Coo[\S\s]*)\r\n\r\nCet#', $this->body, $matches);
                $this->text = $matches[1];
            }
            
            
        }
        return $this->text;
    }

    /**
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}

