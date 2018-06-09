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
    
    

    protected 
    $slack,
    $host,
    $port,
    $password,
    $username,
    $from,
    $replyTo,
    $name;

    public function __construct(array $donnees = array())

{
    $this->hydrate($donnees);
    
    $this->from = $this->username;
    
    $this->replyTo = $this->replyTo;
    
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
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }


    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @param mixed $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
    }



    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
    
    public function sendEmail($subject, $to, $textBody, $from){
        
//         $subject = utf8_encode($subject);
//         $textBody = utf8_encode($textBody);
        
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('Etc/UTC');
        
        
        //Create a new PHPMailer instance
        $mail = new PHPMailer;
        
        $mail->CharSet = 'UTF-8';
        
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 2;
        //Set the hostname of the mail server
        $mail->Host = $this->host;
        $mail->SMTPSecure = 'tls';
        //Set the SMTP port number - likely to be 25, 465 or 587
        $mail->Port = 587;
        
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication
        $mail->Username = $this->username;
        //Password to use for SMTP authentication
        $mail->Password = $this->password;
        //Set who the message is to be sent from
        $mail->addReplyTo($from, $this->name);
        $mail->setFrom($from, $this->name);

        //Set who the message is to be sent to
        $mail->addAddress($to);
        //Set the subject line
        $mail->Subject = $subject;

        //Replace the plain text body with one created manually
        $mail->Body = $textBody;
        
        //send the message, check for errors
        if (!$mail->send()) {
            $this->slack->sendMessages($this->slack::Log, array($mail->ErrorInfo));
        } else {
            return(true);
        }
        
    }

}

