<?php
namespace spamtonprof\stp_api;

use Exception;
use Gregwar;

/**
 *
 * @author alexg
 *        
 */
class LbcApi implements \JsonSerializable
{

    private $slack;

    const cat_deco = 39, cat_cours_particuliers = 36, ameublement = 19, electromenager = 20, art_table = 45, chaussure = 53, vetements = 22;

    function __construct()
    {
        $this->slack = new \spamtonprof\slack\Slack();
    }

    function get_maths_ads($offset = 0)
    {

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.leboncoin.fr/finder/search');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"filters\":{\"category\":{\"id\":\"36\"},\"enums\":{\"ad_type\":[\"offer\"]},\"keywords\":{\"text\":\"maths\"},\"location\":{},\"ranges\":{}},\"limit\":35,\"limit_alu\":3,\"offset\":" . $offset . "}");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:67.0) Gecko/20100101 Firefox/67.0';
        $headers[] = 'Accept: */*';
        $headers[] = 'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3';
        $headers[] = 'Api_key: ba0c2dad52b3ec';
        $headers[] = 'Content-Type: text/plain;charset=UTF-8';
        $headers[] = 'Origin: https://www.leboncoin.fr';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Referer: https://www.leboncoin.fr/recherche/?category=36&text=maths&page=2';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return (false);
        }
        curl_close($ch);

        $ads = json_decode($result);

        return ($ads);
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

    function get_nike_ad($print = false)
    {
        
        
        $category = $this::chaussure;
        $offset = random_int(0, 1000);
        
        $ads = $this->get_ads($category, 'nike neuf', $offset, 100);
        
        $ads = $ads->ads;
        
        
        $ads_valid = [];
        
        foreach ($ads as $ad) {
            
            $ad_valid = $this->format_ad($ad);
            
            if (! $ad_valid) {
                continue;
            }
            
            $body_len = strlen($ad_valid->body);
            
            if ($body_len > 50) {
                continue;
            }
            
            $attributes = $ad->attributes;
            
            foreach ($attributes as $attribute) {
                
                if (!property_exists($attribute, 'key_label')) {
                    continue;
                }
                
                $key_label = $attribute->key_label;
                $value_label = $attribute->value_label;
                
                if ($key_label == "Univers") {
                    $ad_valid->univers = $value_label;
                }
            }
            
            $ads_valid[] = $ad_valid;
        }
        //         prettyPrint($ads_valid);
        
        shuffle($ads_valid);
        $ad = array_pop($ads_valid);
        
        
        $ad = $this->import_ad_image($ad);
        
        if ($print) {
            
            echo ($ad->subject . '<br>');
            echo ($ad->body . '<br>');
            echo ($ad->price . ' € <br>');
            echo($ad->univers. '<br>');
            echo ('<img src="' . $ad->image . '" >');
        }
        
        return ($ad);
        
        
    }

    function get_random_ad($print = false)
    {
        ini_set('allow_url_fopen', 1);

        $ads = $this->getAdds(array(
            'key' => 'random_cat'
        ));

        $ads = $ads->ads;

        // prettyPrint($ads);

        $ads_valid = [];

        foreach ($ads as $ad) {

            $ad_valid = new \stdClass();

            $ad_valid->category = $ad->category_name;

            if (property_exists($ad, "price")) {
                $ad_valid->price = $ad->price[0];
            } else {
                continue;
            }

            $ad_valid->body = $ad->body;
            $ad_valid->subject = $ad->subject;

            $images = $ad->images;
            if (property_exists($images, "urls")) {
                $ad_valid->image = $images->urls[0];
            } else {
                continue;
            }

            $body_len = strlen($ad_valid->body);

            // echo($body_len . "<br>");

            if ($body_len <= 50) {

                $ads_valid[] = $ad_valid;
            }
        }

        $ad = $ads_valid[random_int(0, count($ads_valid) - 1)];

        $url = $ad->image;

        $img_name = explode('/', $url);

        $img_name = $img_name[count($img_name) - 1];

        $rel_path = 'wp-content/uploads/lbc_images/random/' . $img_name;

        $img_path = ABSPATH . $rel_path;

        $ch = curl_init($url);
        $fp = fopen($img_path, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $ad->image = 'http://' . DOMAIN . $rel_path;

        list ($width, $height) = getimagesize($img_path);

        Gregwar\Image\Image::open($img_path)->crop(0.1 * $width, 0.1 * $height, 0.8 * $width, 0.8 * $height)->save($img_path);

        if ($print) {

            echo ($ad->subject . '<br>');
            echo ($ad->body . '<br>');
            echo ($ad->price . ' € <br>');
            echo ('<img src="' . $ad->image . '" >');
        }

        return ($ad);
    }

    // met une ad leboncoin à un format adapté à la republication par le robot ( extrait body subject price et lien image )
    function format_ad($ad)
    {
        $ad_valid = new \stdClass();

        $ad_valid->category = $ad->category_name;

        if (property_exists($ad, "price")) {
            $ad_valid->price = $ad->price[0];
        } else {
            return (false);
        }

        $ad_valid->body = $ad->body;
        $ad_valid->subject = $ad->subject;

        $images = $ad->images;
        if (property_exists($images, "urls")) {
            $ad_valid->image = $images->urls[0];
        } else {
            return (false);
        }

        return ($ad_valid);
    }
    
    function import_ad_image($ad){
        
        $url = $ad->image;
        
        $img_name = explode('/', $url);
        
        $img_name = $img_name[count($img_name) - 1];
        
        $rel_path = 'wp-content/uploads/lbc_images/random/' . $img_name;
        
        $img_path = ABSPATH . $rel_path;
        
        $ch = curl_init($url);
        $fp = fopen($img_path, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        $ad->image = 'http://' . DOMAIN . $rel_path;
        
        list ($width, $height) = getimagesize($img_path);
        
        Gregwar\Image\Image::open($img_path)->crop(0.1 * $width, 0.1 * $height, 0.8 * $width, 0.8 * $height)->save($img_path);
        
        return($ad);
        
        
    }

    function get_ads_clothes($print = false)
    {
        $category = $this::vetements;
        $offset = random_int(0, 1000);

        $ads = $this->get_ads($category, 'zara neuf', $offset, 100);

        $ads = $ads->ads;

        // prettyPrint($ads);

        $ads_valid = [];

        foreach ($ads as $ad) {

            $ad_valid = $this->format_ad($ad);

            if (! $ad_valid) {
                continue;
            }

            $body_len = strlen($ad_valid->body);

            if ($body_len > 50) {
                continue;
            }

            $attributes = $ad->attributes;

            foreach ($attributes as $attribute) {

                if (!property_exists($attribute, 'key_label')) {
                    continue;
                }

                $key_label = $attribute->key_label;
                $value_label = $attribute->value_label;

                if ($key_label == "Univers") {
                    $ad_valid->univers = $value_label;
                }
            }

            $ads_valid[] = $ad_valid;
        }
//         prettyPrint($ads_valid);

        shuffle($ads_valid);
        $ad = array_pop($ads_valid);

   
        $ad = $this->import_ad_image($ad);
        
        if ($print) {

            echo ($ad->subject . '<br>');
            echo ($ad->body . '<br>');
            echo ($ad->price . ' € <br>');
            echo($ad->univers. '<br>');
            echo ('<img src="' . $ad->image . '" >');
        }

        return ($ad);
    }

    function get_ads($category, $keyword = "", $offset = 0, $limit = 100, $ranges = false)
    {
        $response = false;
        $err = false;
        $curl = curl_init();

        $location = new \stdClass();

        if (! $ranges) {
            $ranges = new \stdClass();
        }

        $params = [
            "limit" => $limit,
            "offset" => $offset,
            "limit_alu" => 3,
            "filters" => [
                "category" => array(
                    "id" => strval($category)
                ),
                "enums " => array(
                    "ad_type" => array(
                        "offer"
                    )
                ),
                "location" => $location,
                "keywords" => array(
                    "text" => $keyword
                ),
                "ranges" => $ranges
            ]
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($params),
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

    function getAdds($info, $offset = 0)
    {
        $response = false;
        $err = false;
        $curl = curl_init();

        if (array_key_exists('key', $info)) {

            $key = $info['key'];

            if ($key == 'random_cat') {

                $cats = [
                    $this::ameublement,
                    $this::cat_deco,
                    $this::art_table,
                    $this::electromenager
                ];

                $category = $cats[array_rand($cats)];

                $code_promo = "";

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\"limit\":100, \"offset\":" . $offset . ", \"limit_alu\":3,\"filters\":{\"category\":{\"id\":\"" . $category . "\"},\"enums\":{\"ad_type\":[\"offer\"]},\"location\":{},\"keywords\":{\"text\":\"" . $code_promo . "\"},\"ranges\":{}}}",
                    CURLOPT_HTTPHEADER => array(
                        "Postman-Token: c417730d-59a2-4781-86e1-2edba9de02ee",
                        "api_key: ba0c2dad52b3ec",
                        "cache-control: no-cache"
                    )
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
            }
        }

        if (array_key_exists('nike', $info)) {

            $category = $this::chaussure;

            $code_promo = $info['nike'];

            $offset = random_int(0, 5000);

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\"limit\":100, \"offset\":" . $offset . ", \"limit_alu\":3,\"filters\":{\"category\":{\"id\":\"" . $category . "\"},\"enums\":{\"ad_type\":[\"offer\"]},\"location\":{},\"keywords\":{\"text\":\"" . $code_promo . "\"},\"ranges\":{\"price\":{\"min\":50,\"max\":250}}}}",
                CURLOPT_HTTPHEADER => array(
                    "Postman-Token: c417730d-59a2-4781-86e1-2edba9de02ee",
                    "api_key: ba0c2dad52b3ec",
                    "cache-control: no-cache"
                )
            ));

            $response = curl_exec($curl);

            $err = curl_error($curl);
        }

        if (array_key_exists('code_promo', $info) && array_key_exists('category', $info)) {

            $code_promo = $info['code_promo'];
            $category = $info['category'];

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.leboncoin.fr/finder/search",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\"limit\":100, \"offset\":" . $offset . ", \"limit_alu\":3,\"filters\":{\"category\":{\"id\":\"" . $category . "\"},\"enums\":{\"ad_type\":[\"offer\"]},\"location\":{},\"keywords\":{\"text\":\"" . $code_promo . "\"},\"ranges\":{}}}",
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
                CURLOPT_POSTFIELDS => "{\"limit\":100, \"offset\":" . $offset . ", \"filters\":{\"owner\":{\"user_id\":\"" . $user_id . "\"},\"enums\":{\"ad_type\":[\"offer\"]}},\"limit\":100}",
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
                " impossible de r�cup�rer l'annonce"
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

