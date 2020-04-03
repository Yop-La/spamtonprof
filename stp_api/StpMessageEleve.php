<?php
namespace spamtonprof\stp_api;

class StpMessageEleve implements \JsonSerializable
{

    protected $ref_abonnement, $date_message, $ref_message, $ref_gmail, $mail_expe, $ref_prof, $ref_gmail_account;

    /**
     *
     * @return mixed
     */
    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_gmail_account()
    {
        return $this->ref_gmail_account;
    }

    /**
     *
     * @param mixed $ref_prof
     */
    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    /**
     *
     * @param mixed $ref_gmail_account
     */
    public function setRef_gmail_account($ref_gmail_account)
    {
        $this->ref_gmail_account = $ref_gmail_account;
    }

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
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
    public function getMail_expe()
    {
        return $this->mail_expe;
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
     * @param mixed $mail_expe
     */
    public function setMail_expe($mail_expe)
    {
        $this->mail_expe = $mail_expe;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function getDate_message()
    {
        return $this->date_message;
    }

    public function setDate_message($date_message)
    {
        $this->date_message = $date_message;
    }

    public function getRef_message()
    {
        return $this->ref_message;
    }

    public function setRef_message($ref_message)
    {
        $this->ref_message = $ref_message;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}