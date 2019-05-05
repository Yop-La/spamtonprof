<?php
namespace spamtonprof\stp_api;

/**
 *
 * @author alexg
 *        
 */
class UrlParser implements \JsonSerializable
{

    public function __construct()
    
    {}


 
    public function parseUrls(array $urls, $limit = 20)
    {
        $nodes = $urls;
        $node_count = $limit;
        
        $curl_arr = array();
        $master = curl_multi_init();
        
        for ($i = 0; $i < $node_count; $i ++) {
            $url = $nodes[$i];
            
            $curl_arr[$i] = curl_init("https://lexper.p.rapidapi.com/v1.1/extract?media=1&url=" . urlencode($url));
            
            curl_setopt($curl_arr[$i], CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl_arr[$i], CURLOPT_TIMEOUT, 3);
            curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_arr[$i], CURLOPT_HTTPHEADER, array(
                "X-RapidAPI-Host: lexper.p.rapidapi.com",
                "X-RapidAPI-Key: " . RAPID_API_KEY
            ));
            
            curl_multi_add_handle($master, $curl_arr[$i]);
        }
        
        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);
        
        $results = [];
        
        for ($i = 0; $i < $node_count; $i ++) {
            
            $result = curl_multi_getcontent($curl_arr[$i]);
            
            $result = json_decode($result);
            
            if (is_object($result)) {
                $results[] = $result;
            }
        }
        
        return ($this->parseResults($results));
    }

    private function parseResults($results)
    {
        $texts = [];
        $titles = [];
        $images = [];
        
        foreach ($results as $result) {
            
            if (property_exists($result, 'article')) {
                
                $article = $result->article;
                
                $text = $article->text;
                $image = $article->image;
                $title = $article->title;
                
                $texts[] = $text;
                $titles[] = $title;
                $images[] = $image;
                
                if (is_array($article->images)) {
                    $images = array_merge($images, $article->images);
                }
            } else {
                
                $message = $result->message;
                
                if (! ($message == "Invalid status code 403" || $message == "Invalid status code 401")) {
                    $slack = new \spamtonprof\slack\Slack();
                    $slack->sendMessages('log-spam-google', array(
                        $result->message
                    ));
                }
            }
        }
        
        $result = [];
        $result['texts'] = $texts;
        $result['titles'] = $titles;
        $images = array_filter($images);
        $result['images'] = $images;
        
        return ($result);
    }

    // public function parseUrls(array $urls, $limit = 20)
    // {
    // $nodes = $urls;
    // $node_count = $limit;
    
    // $curl_arr = array();
    // $master = curl_multi_init();
    
    // for ($i = 0; $i < $node_count; $i ++) {
    // $url = $nodes[$i];
    
    // $curl_arr[$i] = curl_init("https://document-parser-api.lateral.io/?url=" . $url);
    
    // curl_setopt($curl_arr[$i], CURLOPT_MAXREDIRS, 10);
    // curl_setopt($curl_arr[$i], CURLOPT_TIMEOUT, 30);
    // curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($curl_arr[$i], CURLOPT_HTTPHEADER, array(
    // "content-type: application/json",
    // "subscription-key: " . LATERAL_IO_KEY
    // ));
    
    // curl_multi_add_handle($master, $curl_arr[$i]);
    // }
    
    // do {
    // curl_multi_exec($master, $running);
    // } while ($running > 0);
    
    // $results = [];
    
    // for ($i = 0; $i < $node_count; $i ++) {
    // $result = curl_multi_getcontent($curl_arr[$i]);
    
    // prettyPrint($result);
    
    // $result = json_decode($result);
    
    // if (is_object($result)) {
    // $results[] = $result;
    // }
    // }
    // return ($results);
    // }
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}

