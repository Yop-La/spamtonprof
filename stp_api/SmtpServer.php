<?php
namespace spamtonprof\stp_api;

use PHPMailer\PHPMailer\PHPMailer;

/**
 *
 * @author alexg
 *        
 */
class SmtpServer implements \JsonSerializable
{

    protected $slack, $host, $port, $password, $username;

    public function __construct(array $donnees = array())
    
    {
        $this->hydrate($donnees);
        
        $this->slack = new \spamtonprof\slack\Slack();
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
    public function getHost()
    {
        return $this->host;
    }

    /**
     *
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     *
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     *
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     *
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    public function sendEmail($subject, $to, $body, $from, $fromName = "", $html = false, $ccs = false)
    {
        $host = $this->host;
        $port = $this->port;
        $password = $this->password;
        $username = $this->username;
        
        // Create a new PHPMailer instance
        $mail = new PHPMailer();
        
        $mail->CharSet = 'UTF-8';
        
        // Tell PHPMailer to use SMTP
        $mail->isSMTP();
        // Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        // Set the hostname of the mail server
        // $mail->Host = $this->host;
        $mail->Host = $host;
        
        $mail->SMTPSecure = 'tls';
        // Set the SMTP port number - likely to be 25, 465 or 587
        $mail->Port = $port;
        
        // Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        // Username to use for SMTP authentication
        $mail->Username = $username;
        // Password to use for SMTP authentication
        $mail->Password = $password;
        // Set who the message is to be sent from
        $mail->addReplyTo($from);
        $mail->setFrom($from, $fromName);
        
        // Set who the message is to be sent to
        $mail->addAddress($to);
        // Set the subject line
        $mail->Subject = $subject;
        
        $mail->isHTML($html);
        $mail->Body = $body;
        
        if ($ccs) {
            
            foreach ($ccs as $cc) {
                $mail->addCC($cc);
            }
        }
        
        // send the message, check for errors
        if (! $mail->send()) {
            $this->slack->sendMessages("log", array(
                $mail->ErrorInfo
            ));
            return (false);
        } else {
            return (true);
        }
    }
}

