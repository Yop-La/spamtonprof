<?php
namespace spamtonprof\stp_api;

class StpGmailAccount implements \JsonSerializable
{

    protected $ref_gmail_account, $email, $credential, $last_history_id, $date_url_sent, $last_timestamp;

    /**
     *
     * @return mixed
     */
    public function getLast_timestamp()
    {
        return $this->last_timestamp;
    }

    /**
     *
     * @param mixed $last_timestamp
     */
    public function setLast_timestamp($last_timestamp)
    {
        $this->last_timestamp = $last_timestamp;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_url_sent()
    {
        return $this->date_url_sent;
    }

    /**
     *
     * @param mixed $date_url_sent
     */
    public function setDate_url_sent($date_url_sent)
    {
        $this->date_url_sent = $date_url_sent;
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

    public function getRef_gmail_account()
    {
        return $this->ref_gmail_account;
    }

    public function setRef_gmail_account($ref_gmail_account)
    {
        $this->ref_gmail_account = $ref_gmail_account;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getCredential()
    {
        return $this->credential;
    }

    public function setCredential($credential)
    {
        $this->credential = $credential;
    }

    /**
     *
     * @return mixed
     */
    public function getLast_history_id()
    {
        return $this->last_history_id;
    }

    /**
     *
     * @param mixed $last_history_id
     */
    public function setLast_history_id($last_history_id)
    {
        $this->last_history_id = $last_history_id;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}