<?php
namespace spamtonprof\stp_api;

class LbcCommuneManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(LbcCommune $lbcCommune)
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

    // pour chercher dans la base de donn�es code-insee-postaux-geoflar de OpenDataSoft
    public function getAllFromODS($text)
    {
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
    // $communes est un records retourn� par getAllFromODS
    // Elle sert � lier un nom de commune du bon coin � une commune de la base open data soft
    public function findClosest($communes, $nomCommune)
    {
        $nbRecord = count($communes);

        if ($nbRecord == 0) {
            return (false);
        }

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

    // pour retourner les communes qui ont le m�me code postal que celui pass� en argument
    public function matchByZipCode($communes, $zipCode)
    {
        $matched = [];

        foreach ($communes as $commune) {

            if ($commune->fields->code_postal == $zipCode) {
                $matched[] = $commune;
            }
        }

        return ($matched);
    }

    public function getAll($info)
    {
        $q = null;
        $communes = [];
        if (is_array($info)) {
            if (array_key_exists('ref_client', $info) && array_key_exists('target_big_city', $info)) {
                $refClient = $info['ref_client'];
                $target_big_city = $info['target_big_city'];

                $req = "select * from (
                  select ref_commune,
                		libelle,
                		code_postal,
                		population,
                        row_number() over
                          (partition by libelle ) row_num
                		       from lbc_commune
                                    where ref_commune not in(
                                        select ref_commune from adds_tempo
                                            where ref_compte in (select ref_compte from compte_lbc where ref_client = :ref_client)  and statut in ('online','publie') and ref_commune is not null
                                    )
                                and (lbc is not true )
                                [pop]
                                    order by population  desc limit 500) t
                				where row_num = 1 ";

                $pop = '';
                if($target_big_city){
                    $pop = "and population <= 70 and population >= 20";
                }
                
                
                $req = str_replace("[pop]", $pop, $req);
                

                $q = $this->_db->prepare($req);
                $q->bindValue(":ref_client", $refClient);
            }
        }
        //
        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $communes[] = new \spamtonprof\stp_api\LbcCommune($data);
        }
        return ($communes);
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists('libelle', $info) && array_key_exists('code_postal', $info)) {
                $libelle = $info['libelle'];
                $codePostal = $info['code_postal'];

                $q = $this->_db->prepare('select * from lbc_commune where libelle like :libelle and
code_postal like :code_postal');
                $q->bindValue(":libelle", $libelle);
                $q->bindValue(":code_postal", $codePostal);
            }
        }
        $q->execute();

        if ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            return (new \spamtonprof\stp_api\LbcCommune($data));
        } else {
            return (false);
        }
    }

    public function updateLbc(\spamtonprof\stp_api\LbcCommune $commune)
    {
        $q1 = $this->_db->prepare("update lbc_commune set lbc = :lbc where ref_commune = :ref_commune");
        $q1->bindValue(":lbc", $commune->getLbc());
        $q1->bindValue(":ref_commune", $commune->getRef_commune());
        $q1->execute();
    }
}
