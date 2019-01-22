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

    private $slack;

    function __construct()
    {
        $this->slack = new \spamtonprof\slack\Slack();
    }

    function getUserId($luat)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.leboncoin.fr/api/accounts/v1/accounts/me/personaldata",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 81bc2fa8-dee0-43d1-a3ab-c861b219a19d",
                "authorization: Bearer " . $luat,
                "cache-control: no-cache"
            )
        ));

        $response = json_decode(curl_exec($curl));
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return (false);
        } else {

            return ($response->userId);
        }
    }

    function getTexts($info, $offset = 0)
    {
        $ads = $this->getAdds($info, $offset);

        $res = property_exists($ads, "ads");

        $txts = [];
        if ($res) {
            $ads = $ads->ads;

            foreach ($ads as $ad) {
                $txts[] = $ad->body;
            }
            return ($txts);
        }

        return (false);
    }

    function getAdds($info, $offset = 0)
    {
        $response = false;
        $err = false;
        $curl = curl_init();

        if (array_key_exists('code_promo', $info)) {

            $code_promo = $info['code_promo'];

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\"limit\":100, \"offset\":" . $offset . ", \"limit_alu\":3,\"filters\":{\"category\":{\"id\":\"36\"},\"enums\":{\"ad_type\":[\"offer\"]},\"location\":{},\"keywords\":{\"text\":\"" . $code_promo . "\"},\"ranges\":{}}}",
                CURLOPT_HTTPHEADER => array(
                    "Postman-Token: c417730d-59a2-4781-86e1-2edba9de02ee",
                    "api_key: ba0c2dad52b3ec",
                    "cache-control: no-cache"
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
        }

        if (array_key_exists('user_id', $info)) {

            $user_id = $info['user_id'];

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\"filters\":{\"owner\":{\"user_id\":\"" . $user_id . "\"},\"enums\":{\"ad_type\":[\"offer\"]}},\"limit\":100}",
                CURLOPT_HTTPHEADER => array(
                    "Postman-Token: e7ce26b5-3715-466a-940e-1e64c45b7013",
                    "api_key: ba0c2dad52b3ec",
                    "cache-control: no-cache"
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
        }

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

    function getCommuneOnLbcAdd($adId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.leboncoin.fr/cours_particuliers/" . $adId . ".htm/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
                "Accept-Encoding: gzip, deflate, br",
                "Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7",
                "Cache-Control: max-age=0",
                "Connection: keep-alive",
                "DNT: 1",
                "Host: www.leboncoin.fr",
                "Postman-Token: d729c736-33ef-4bea-a828-21aff527acf8",
                "Upgrade-Insecure-Requests: 1",
                "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/69.0.3497.81 Chrome/69.0.3497.81 Safari/537.36",
                "cache-control: no-cache"
            )
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {

            $this->slack->sendMessages("log", array(
                " impossible de récupérer l'annonce"
            ));
            return ("error error");
        } else {

            $response;

            $match = [];

            preg_match_all('#<span data-reactid="96">(.*?)</span>#', $response, $match);

            $res = $match[1];

            preg_match_all("/(?:97|98|99) -->(.*?)<!--/", $res[0], $match);

            if ($match[1][0] == "") {

                preg_match_all('#<span data-reactid="81">(.*?)</span>#', $response, $match);

                $res = $match[1];

                preg_match_all("/(?:82|83|84) -->(.*?)<!--/", $res[0], $match);
            }

            $ret = [];
            $ret[] = $match[1][0];
            $ret[] = $match[1][2];

            $this->slack->sendMessages("log", $ret);
            return ($ret);
        }
        return (false);
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

