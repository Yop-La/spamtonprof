<?php

function printInfoCompteTest()
{
    $accountManager = new \spamtonprof\stp_api\AccountManager();
    
    $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();
    
    $comptesTest = array(
        
        868,
        
        867
    
    );
    
    foreach ($comptesTest as $compteTest) {
        
        $account = $accountManager->get($compteTest);
        
        echo ("<br>-------- compte n° " . $account->ref_compte() . "------ <br>" . "<br> ");
        
        echo ("prenom eleve : " . $account->eleve()->prenom() . "<br> ");
        
        echo ("statut : " . $account->statut() . "<br> ");
        
        echo ("attente paiement : " . $account->attente_paiement() . "<br>");
        
        echo ("maths : " . $account->maths() . "physique : " . $account->physique() . "francais : " . $account->francais() . "<br>");
        
        echo ("email eleve  : ");
        
        $contacts = $getResponseManager->getContact($account->eleve());
        
        foreach ($contacts as $contact) {
            
            echo ($contact->campaign->name . " - ");
        }
        
        echo ("<br>");
        
        // echo($contacts["campaign"]);
        
        echo ("email parent  : ");
        
        $contacts = $getResponseManager->getContact($account->proche());
        
        foreach ($contacts as $contact) {
            
            echo ($contact->campaign->name . " - ");
        }
        
        echo ("<br>");
    }
}

function resetCompteTest()

{
    $accountManager = new \spamtonprof\stp_api\AccountManager();
    
    $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();
    
    $comptesTest = array(
        
        868,
        
        867
    
    );
    
    foreach ($comptesTest as $compteTest) {
        
        $account = $accountManager->get($compteTest);
        
        $account->setStatut("essai");
        
        $account->setAttente_paiement(true);
        
        $accountManager->updateAfterSubsCreated($account);
        
        $getResponseManager->resetToEssai($account);
    }
    
    echo ("reset des comptes de test over ! ");
}

/**
 *
 * @param Object $object
 *            pour bien indenter en json l'objet à l'écran
 */
function prettyPrint($object)
{
    header('Content-Type: application/json');
    
    echo (json_encode($object, JSON_PRETTY_PRINT));
    
    exit(0);
}

function serializeTemp($object, $file = "/tempo/tempoObject")
{
    $s = serialize($object);
    file_put_contents(dirname(__FILE__) . $file, $s);
}

function unserializeTemp($file = "/tempo/tempoObject")
{
    $file = dirname(__FILE__) . $file;
    if (file_exists($file)) {
        $s = file_get_contents($file);
        $a = unserialize($s);
        return ($a);
    } else {
        return (false);
    }
}

function toUtf8(array $arr)
{
    for ($i = 0; $i < count($arr); $i ++) {
        $value = $arr[$i];
        $encoding = mb_detect_encoding($value, 'UTF-8', true);
        
        if (! $encoding) {
            $arr[$i] = utf8_encode($value);
        }
    }
    return ($arr);
}

function prettyPrintArray(array $arr)
{
    echo ("<pre>");
    
    print_r($arr);
    
    echo ("</pre>");
    
    exit(0);
}

function saveArrayAsCsv($array, $filename = "export.csv", $delimiter = ";")
{
    // open raw memory as file so no temp files needed, you might run out of memory though
    $filename = dirname(__FILE__) . "/tempo/" . $filename;
    
    $f = fopen($filename, 'w');
    
    // loop over the input array
    foreach ($array as $line) {
        
        if (is_object($line)) {
            $line = $line->__toString();
            $line = array(
                $line
            );
        }
        // generate csv lines from the inner arrays
        
        fputcsv($f, $line, $delimiter);
    }
    
    fclose($f);
}

function call($url, $http_method = 'GET', $params = array(), $async = null)
{
    if ($http_method == 'GET') {
        $url = $url . "?" . http_build_query($params);
    }
    
    $params = json_encode($params);
    
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_ENCODING => 'gzip,deflate',
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => 'SpamTonProf'
    );
    
    if ($async) {
        $options[CURLOPT_TIMEOUT_MS] = 1000;
    }
    
    if ($http_method == 'POST') {
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $params;
    } else if ($http_method == 'DELETE') {
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    }
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $response = json_decode(curl_exec($curl));
    curl_close($curl);
    return (object) $response;
}

/* pour décoder le body des messages de gmail */
function base64url_decode($base64url)
{
    $base64 = strtr($base64url, '-_', '+/');
    $plainText = base64_decode($base64);
    return ($plainText);
}

