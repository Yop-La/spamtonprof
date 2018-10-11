<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class LbcApi implements \JsonSerializable
{

    function getAdds($text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"limit\":100,\"limit_alu\":3,\"filters\":{\"category\":{\"id\":\"36\"},\"enums\":{\"ad_type\":[\"offer\"]},\"location\":{},\"keywords\":{\"text\":\"" . $text . "\"},\"ranges\":{}}}",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: c417730d-59a2-4781-86e1-2edba9de02ee",
                "api_key: ba0c2dad52b3ec",
                "cache-control: no-cache"
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $lbcRep = false;
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $lbcRep = json_decode($response);
        }

        if ($lbcRep->total == 0) {
            return (false);
        }

        return ($lbcRep);
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

