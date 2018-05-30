<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class ExpeLbc implements \JsonSerializable
{

    protected $ref_expe, $smtpServer, $ref_smtp_server, $expe, $nb_message, $weebhook, $ref_mail_for_lead, $mailForLead, $filtre;

    public function __construct(array $donnees = array())
    
    {
        $this->hydrate($donnees);
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
     *
     * @return mixed
     */
    public function getRef_expe()
    {
        return $this->ref_expe;
    }

    /**
     *
     * @return mixed
     */
    public function getExpe()
    {
        return $this->expe;
    }

    /**
     *
     * @return mixed
     */
    public function getNb_message()
    {
        return $this->nb_message;
    }

    /**
     *
     * @return mixed
     */
    public function getWeebhook()
    {
        return $this->weebhook;
    }

    /**
     *
     * @return mixed
     */
    public function getFiltre()
    {
        return $this->filtre;
    }

    /**
     *
     * @param mixed $ref_expe
     */
    public function setRef_expe($ref_expe)
    {
        $this->ref_expe = $ref_expe;
    }

    /**
     *
     * @param mixed $expe
     */
    public function setExpe($expe)
    {
        $this->expe = $expe;
    }

    /**
     *
     * @param mixed $nb_message
     */
    public function setNb_message($nb_message)
    {
        $this->nb_message = $nb_message;
    }

    /**
     *
     * @param mixed $weebhook
     */
    public function setWeebhook($weebhook)
    {
        $this->weebhook = $weebhook;
    }

    /**
     *
     * @param mixed $filtre
     */
    public function setFiltre($filtre)
    {
        $this->filtre = $filtre;
    }

    /**
     *
     * @return mixed
     */
    public function getSmtpServer()
    {
        return $this->smtpServer;
    }

    /**
     *
     * @param mixed $smtpServer
     */
    public function setSmtpServer($smtpServer)
    {
        $this->smtpServer = $smtpServer;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_smtp_server()
    {
        return $this->ref_smtp_server;
    }

    /**
     *
     * @param mixed $ref_smtp_server
     */
    public function setRef_smtp_server($ref_smtp_server)
    {
        $this->ref_smtp_server = $ref_smtp_server;
        
        $smtpServerMg = new \spamtonprof\stp_api\SmtpServerManager();
        
        $this->smtpServer = $smtpServerMg->get(array(
            "ref_smtp_server" => $ref_smtp_server
        ));
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
    public function getRef_mail_for_lead()
    {
        return $this->ref_mail_for_lead;
    }

    /**
     *
     * @return mixed
     */
    public function getMailForLead()
    {
        return $this->mailForLead;
    }

    /**
     *
     * @param mixed $ref_mail_for_lead
     */
    public function setRef_mail_for_lead($ref_mail_for_lead)
    {
        $this->ref_mail_for_lead = $ref_mail_for_lead;
        
        $mailForLeadMg = new \spamtonprof\stp_api\MailForLeadManager();
        
        $mailForLead = $mailForLeadMg->get(array(
            "ref_mail_for_lead" => $ref_mail_for_lead
        ));
        if ($mailForLead) {
            $this->setMailForLead($mailForLead);
        }
    }

    /**
     *
     * @param mixed $mailForLead
     */
    public function setMailForLead(\spamtonprof\stp_api\MailForLead $mailForLead)
    {
        $this->mailForLead = $mailForLead;
    }
}

