<?php
namespace spamtonprof\stp_api;

class ExTadabaseMg

{

    private $_db, $headers;

    // Instance de PDO
    public function __construct()

    {
        $this->headers = array(
            "X-Tadabase-App-id: " . TADABASE_APP_ID,
            "X-Tadabase-App-Key: Y5ZJu7r0Bp13",
            "X-Tadabase-App-Secret: " . TADABASE_APP_SECRET,
            "Content-Type: application/x-www-form-urlencoded"
        );
    }
    

    public function saveAd($subject, $body, $category = 'cours_online')
    {
        $fields = array(
            'field_36' => $subject,
            'field_37' => $body,
            'field_43' => $category
        );
        $fields_string = http_build_query($fields);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tadabase.io/api/v1/data-tables/lGArg7rmR6/records",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $this->headers
        ));

        $response = curl_exec($curl);

        return ($response);
    }

    public function getDoneAds()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tadabase.io/api/v1/data-tables/lGArg7rmR6/records?filters%5Bitems%5D%5B0%5D%5Bfield_id%5D=field_47&filters%5Bitems%5D%5B0%5D%5Boperator%5D=is&filters%5Bitems%5D%5B0%5D%5Bval%5D=oui",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $this->headers
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $ads = json_decode($response);
        $ads = $ads->items;
        
        return ($ads);
    }

    public function deleteAd($adId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tadabase.io/api/v1/data-tables/lGArg7rmR6/records/" . $adId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => $this->headers
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return ($response);
    }
    
    public function pushAdsFromLbc(){
        
        $lbcApi = new \spamtonprof\stp_api\LbcApi();
        
        $nb_recup = 5;
        
        for ($i = 0; $i < $nb_recup; $i ++) {
            
            $ads = $lbcApi->getAdds(array(
                'key' => 'cours_particuliers'
            ), $i * 100);
            $ads = $ads->ads;
            
            foreach ($ads as $ad) {
                
                $this->saveAd($ad->subject, $ad->body);
            }
        }
        
        
        
    }
    
    
}