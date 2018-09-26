<?php
namespace spamtonprof\stp_api;


/**
 *
 * @author alexg
 *         
 */
class Toky implements \JsonSerializable
{


    public function sendSms($from, $to, $message){
        
        
        // create a new cURL resource
        $ch = curl_init();
        $api_key = TOKY_KEY;
        $headers = array();
        $headers[] = "X-Toky-Key: {$api_key}";

        $data = array("from" => $from,
            "to" => $to,
            "text" => $message);
        
        $json_data = json_encode($data);
        
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, "https://api.toky.co/v1/sms/send");
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch,CURLOPT_POSTFIELDS, $json_data);
        
        $curl_response = curl_exec($ch); // Send request
        curl_close($ch); // close cURL resource
        
        $decoded = json_decode($curl_response,true);
        return($decoded);
        
    }
    
    
    
      public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

  
}

