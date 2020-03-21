<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class ExFaceApi
{

    private $api_key;

    // Instance de PDO
    public function __construct()

    {
        $this->api_key = '';
    }

    public function getFaceUrl($gender = 'male',$emotion = 'joy')
    {
        $ch = curl_init();

        $params['emotion'] = 'joy'; /* Valid values: joy, neutral, surprise */
        $params['gender'] = $gender; /* Valid values: male, female */
        $params['age'] = 'young-adult'; /* Valid values: infant, child, young-adult, adult, elderly */
        $params['ethnicity'] = 'white'; /* Valid values: white, latino, asian, black */
        $params['page'] = 1;
        $params['per_page'] = 100;

        curl_setopt($ch, CURLOPT_URL, 'https://api.generated.photos/api/v1/faces?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers = array();
        $headers[] = 'Authorization: API-Key xgDY_RV5075gVDX5fuyaRw';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($result);
        
        
//         prettyPrint($result);
        return ($result->faces[random_int(0,99)]->urls[4]->{'512'});
    }

}

