<?php
namespace spamtonprof\stp_api;

class LbcCommuneManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(lbcCommune $lbcCommune)
    {
        $q = $this->_db->prepare('insert into lbc_commune(code_insee, nom_commune, code_postal, libelle, nom_reg, nom_dep, code_reg, code_com, code_dep, population, nom_com) values( :code_insee,:nom_commune,:code_postal,:libelle,:nom_reg,:nom_dep,:code_reg,:code_com,:code_dep,:population,:nom_com)');
        $q->bindValue(':code_insee', $lbcCommune->getCode_insee());
        $q->bindValue(':nom_commune', $lbcCommune->getNom_commune());
        $q->bindValue(':code_postal', $lbcCommune->getCode_postal());
        $q->bindValue(':libelle', $lbcCommune->getLibelle());
        $q->bindValue(':nom_reg', $lbcCommune->getNom_reg());
        $q->bindValue(':nom_dep', $lbcCommune->getNom_dep());
        $q->bindValue(':code_reg', $lbcCommune->getCode_reg());
        $q->bindValue(':code_com', $lbcCommune->getCode_com());
        $q->bindValue(':code_dep', $lbcCommune->getCode_dep());
        $q->bindValue(':population', $lbcCommune->getPopulation());
        $q->bindValue(':nom_com', $lbcCommune->getNom_com());
        $q->execute();

        return ($lbcCommune);
    }
    
    
    // pour chercher dans la base de données code-insee-postaux-geoflar de OpenDataSoft
    public function getAllFromODS($text){
        
        $params = urlencode($text);
        $url = "https://public.opendatasoft.com/api/records/1.0/search/?dataset=code-insee-postaux-geoflar&q=" . $params;
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: 04d097cd-1dd1-48be-8532-e3a855fc41f8",
                "cache-control: no-cache"
            )
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response);
            return ($response);
        }
        
    }
    
    // pour trouver la commune dans une liste de commune ayat le nom de commune le plus proche de $nomCommune
    //$communes est un records retourné par getAllFromODS
    //Elle sert à lier un nom de commune du bon coin à une commune de la base open data soft
    public function findClosest($communes, $nomCommune)
    {
        $nbRecord = count($communes);
        $record = $communes[0];
        $winner = $record;
        $min = levenshtein($record->fields->libelle_d_acheminement, $nomCommune);
        for ($i = 1; $i < $nbRecord; $i ++) {
            
            $record = $communes[$i];
            $dist = levenshtein($record->fields->libelle_d_acheminement, $nomCommune);
            if ($dist < $min) {
                $winner = $record;
                $min = $dist;
            }
        }
        return ($winner);
    }
}
