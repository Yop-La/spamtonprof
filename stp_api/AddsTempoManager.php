<?php
namespace spamtonprof\stp_api;

class AddsTempoManager
{

    private $_db;

    const online = "online", publie = "publie", bloque = "bloque";

    const no_ref_texte_or_no_ref_titre = "no_ref_texte_or_no_ref_titre", nearest_title_ad = "nearest_title_ad";

    const update_statut_ad_refuse = 'update_statut_ad_refuse';
    
    const block_ads_of_act = 'block_ads_of_act';

    const get_ads_online = "get_ads_online", get_ads_online_in_campaign = "get_ads_online_in_campaign",get_ads_to_block_during_check = "get_ads_to_block_during_check";

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function deleteAll($info)
    {
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                if ($key == $this::no_ref_texte_or_no_ref_titre) {

                    $refCompte = $info["ref_compte"];

                    $q = $this->_db->prepare("delete from adds_tempo where ref_compte = :ref_compte and (ref_titre is null or ref_texte is null)");
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->execute();
                }
            } else {

                if (array_key_exists("ref_compte", $info)) {

                    $refCompte = $info["ref_compte"];

                    $q = $this->_db->prepare("delete from adds_tempo where ref_compte = :ref_compte");
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->execute();
                }
                if (array_key_exists("high_potential_city", $info)) {

                    $ref_client = $info["high_potential_city"];

                    $q = $this->_db->prepare("
                    delete from adds_tempo where
                    ref_commune in (select ref_commune from lbc_commune where population >= 20)
                    and statut = 'online' 
                    and first_publication_date < ( now() - interval '7 days' )
                    and first_publication_date is not null
                    and ref_compte in (select ref_compte from compte_lbc where ref_client = :ref_client);");
                    $q->bindValue(":ref_client", $ref_client);

                    $q->execute();
                }
            }
        }
    }

    public function add(AddsTempo $addsTempo)
    {
        $q = $this->_db->prepare('insert into adds_tempo(first_publication_date, zipcode, city, id, ref_compte, has_phone, ref_commune, ref_texte, ref_titre, statut, ref_campaign) 
            values( :first_publication_date,:zipcode,:city,:id,:ref_compte,:has_phone, :ref_commune, :ref_texte, :ref_titre, :statut, :ref_campaign)');
        $q->bindValue(':first_publication_date', $addsTempo->getFirst_publication_date());
        $q->bindValue(':zipcode', $addsTempo->getZipcode());
        $q->bindValue(':city', $addsTempo->getCity());
        $q->bindValue(':id', $addsTempo->getId());
        $q->bindValue(':ref_texte', $addsTempo->getRef_texte());
        $q->bindValue(':ref_titre', $addsTempo->getRef_titre());
        $q->bindValue(':statut', $addsTempo->getStatut());
        $q->bindValue(':ref_compte', $addsTempo->getRef_compte());
        $q->bindValue(':has_phone', $addsTempo->getHas_phone(), \PDO::PARAM_BOOL);
        $q->bindValue(':ref_commune', $addsTempo->getRef_commune());
        $q->bindValue(':ref_campaign', $addsTempo->getRef_campaign());
        $q->execute();

        return ($addsTempo);
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                if ($key == $this::nearest_title_ad) {

                    $refCompte = $info["ref_compte"];
                    $title = $info["title"];

                    $q = $this->_db->prepare("select titre, ref_ad, ref_compte, levenshtein(titre, :title) as dist 
                            , first_publication_date, zipcode, city, ref_compte, has_phone, ref_commune, adds_tempo.ref_titre, ref_texte, statut, ref_campaign
                        from adds_tempo, titres where 
                        adds_tempo.ref_titre = titres.ref_titre
                        and ref_compte = :ref_compte
                        and levenshtein(titre, :title) <= 2
                        order by dist
                        limit 1;");
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->bindValue(":title", $title);

                    $q->execute();
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $ad = false;
        if ($data) {

            $ad = new \spamtonprof\stp_api\AddsTempo($data);
        }

        return ($ad);
    }

    public function update_all($info)
    {
        $q = null;
        if (array_key_exists('key', $info)) {

            $key = $info['key'];

            if ($key == $this::update_statut_ad_refuse) {

                $refCompte = $info["ref_compte"];

                $req = "update adds_tempo set statut = '" . $this::bloque . "' where statut = '" . $this::publie . "' and ref_compte = :ref_compte";
                $q = $this->_db->prepare($req);
                $q->bindValue(":ref_compte", $refCompte);
                $q->execute();
            }
            
            if ($key == $this::block_ads_of_act) {
                
                $refCompte = $info["ref_compte"];
                $req = "update adds_tempo set statut = '" . $this::bloque . "' where ref_compte = :ref_compte";
                $q = $this->_db->prepare($req);
                $q->bindValue(":ref_compte", $refCompte);
                $q->execute();
            }
            
        }

        $q->execute();

        $ads = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $ad = new \spamtonprof\stp_api\AddsTempo($data);
            $ads[] = $ad;
        }

        return ($ads);
    }

    public function getAll($info) 
    {
        $q = null;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                if ($key == $this::get_ads_online) {

                    $refCompte = $info["ref_compte"];

                    $q = $this->_db->prepare("select * from adds_tempo where ref_compte = :ref_compte and statut = '" . $this::online . "'");
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->execute();
                }
                
                if ($key == $this::get_ads_to_block_during_check) {
                    
                    $refCompte = $info["ref_compte"];
                    
                    $q = $this->_db->prepare("select * from adds_tempo where ref_compte = :ref_compte and ( now() - interval '2 minutes' < first_publication_date )");
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->execute();
                }
                

                if ($key == $this::get_ads_online_in_campaign) {

                    $ref_campaign = $info["ref_campaign"];

                    $q = $this->_db->prepare("select * from adds_tempo where ref_campaign = :ref_campaign and statut = '" . $this::online . "'");
                    $q->bindValue(":ref_campaign", $ref_campaign);
                    $q->execute();
                    
                }
            } else {

                if (array_key_exists("no_ref_commune", $info)) {
                    $limit = $info["toMatch"];
                    $q = $this->_db->prepare('select * from adds_tempo where ref_commune is null limit :limit');
                    $q->bindValue(":limit", $limit);
                }
                if (array_key_exists("ref_compte", $info)) {
                    $refCompte = $info["ref_compte"];
                    $q = $this->_db->prepare('select * from adds_tempo where ref_compte = :ref_compte');
                    $q->bindValue(":ref_compte", $refCompte);
                }
            }
        }

        $q->execute();

        $ads = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $ad = new \spamtonprof\stp_api\AddsTempo($data);
            $ads[] = $ad;
        }

        return ($ads);
    }

    // pour mettre à jour les ref communes des ads tempo en cherchant la commune correspondante la base de communes open data soft
    public function updateAllRefCommune($ads)
    {
        $lbcCommuneMg = new \spamtonprof\stp_api\LbcCommuneManager();

        $slack = new \spamtonprof\slack\Slack();

        foreach ($ads as $ad) {

            $param = $ad->getCity() . " " . $ad->getZipcode();

            $records = $lbcCommuneMg->getAllFromODS($param);

            $nits = $records->nhits;
            $communes = $records->records;

            if ($nits == 0) {

                $records = $lbcCommuneMg->getAllFromODS($ad->getZipcode());
                $communes = $records->records;

                $communes = $lbcCommuneMg->matchByZipCode($communes, $ad->getZipCode());

                if (count($communes) != 1) {
                    $winner = $lbcCommuneMg->findClosest($communes, $ad->getCity());
                } else {
                    $winner = $communes[0];
                }

                if (! $winner) {
                    $ad->setRef_commune("pas de match");
                    $this->updateRefCommune($ad);
                    break;
                    $slack->sendMessages("log", array(
                        "!!!!!!!!! aucun match avec open data soft : " . $param . "  !!!!!!!!!!!"
                    ));
                }

                $slack->sendMessages("log", array(
                    "!!!!!!!!! pas de match : " . $param,
                    "retenu : " . $winner->fields->libelle_d_acheminement . " " . $winner->fields->code_postal
                ));
            } else if ($nits > 1) {

                $communes = $lbcCommuneMg->matchByZipCode($communes, $ad->getZipCode());

                if (count($communes) != 1) {
                    $winner = $lbcCommuneMg->findClosest($communes, $ad->getCity());
                } else {
                    $winner = $communes[0];
                }

                $winner = $lbcCommuneMg->findClosest($communes, $ad->getCity());

                $slack->sendMessages("log", array(
                    "!!!!!!!!! trop de match : " . $param,
                    "retenu : " . $winner->fields->libelle_d_acheminement . " " . $winner->fields->code_postal
                ));
            } else {

                $winner = $communes[0];
            }

            $ref_commune = $winner->fields->code_commune_insee . $winner->fields->code_postal;

            $ad->setRef_commune($ref_commune);
            $this->updateRefCommune($ad);
        }
    }

    public function update_first_publication_date(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set first_publication_date = :first_publication_date where ref_ad = :ref_ad');
        $q->bindValue(":first_publication_date", $ad->getFirst_publication_date());
        $q->bindValue(":ref_ad", $ad->getRef_ad());
        $q->execute();
    }

    public function update_zipcode(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set zipcode = :zipcode where ref_ad = :ref_ad');
        $q->bindValue(":zipcode", $ad->getZipcode());
        $q->bindValue(":ref_ad", $ad->getRef_ad());
        $q->execute();
    }

    public function update_city(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set city = :city where ref_ad = :ref_ad');
        $q->bindValue(":city", $ad->getCity());
        $q->bindValue(":ref_ad", $ad->getRef_ad());
        $q->execute();
    }

    public function update_id(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set id = :id where ref_ad = :ref_ad');
        $q->bindValue(":id", $ad->getId());
        $q->bindValue(":ref_ad", $ad->getRef_ad());
        $q->execute();
    }

    public function update_has_phone(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set has_phone = :has_phone where ref_ad = :ref_ad');
        $q->bindValue(":has_phone", $ad->getHas_phone());
        $q->bindValue(":ref_ad", $ad->getRef_ad());
        $q->execute();
    }

    public function update_statut(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set statut = :statut where ref_ad = :ref_ad');
        $q->bindValue(":statut", $ad->getStatut());
        $q->bindValue(":ref_ad", $ad->getRef_ad());
        $q->execute();
    }

    public function updateRefCommune(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set ref_commune = :ref_commune where id = :id');
        $q->bindValue(":ref_commune", $ad->getRef_commune());
        $q->bindValue(":id", $ad->getId());
        $q->execute();
    }
}
