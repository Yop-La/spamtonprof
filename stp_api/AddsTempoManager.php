<?php
namespace spamtonprof\stp_api;

class AddsTempoManager
{

    private $_db;

    const online = "online", publie = "publie" , bloque = "bloque";
    
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function deleteAll($info)
    {
        if (is_array($info)) {
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
                    ref_commune in (select ref_commune from lbc_commune where population >= 20 and population <= 40)
                    and 
                    ref_compte in (select ref_compte from compte_lbc where date_publication <= (now() - interval '7 days') and ref_client = :ref_client);");
                $q->bindValue(":ref_client", $ref_client);

                $q->execute();
            }
        }
    }

    public function add(AddsTempo $addsTempo)
    {
        $q = $this->_db->prepare('insert into adds_tempo(first_publication_date, zipcode, city, id, ref_compte, has_phone, ref_commune, ref_texte, ref_titre, statut) values( :first_publication_date,:zipcode,:city,:id,:ref_compte,:has_phone, :ref_commune, :ref_texte, :ref_titre, :statut)');
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
        $q->execute();

        return ($addsTempo);
    }

    public function getAll($info)
    {
        $q = null;
        if (is_array($info)) {
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

        $q->execute();

        $ads = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $ad = new \spamtonprof\stp_api\AddsTempo($data);
            $ads[] = $ad;
        }

        return ($ads);
    }

    // pour mettre � jour les ref communes des ads tempo en cherchant la commune correspondante la base de communes open data soft
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

    public function updateRefCommune(\spamtonprof\stp_api\AddsTempo $ad)
    {
        $q = $this->_db->prepare('update adds_tempo set ref_commune = :ref_commune where id = :id');
        $q->bindValue(":ref_commune", $ad->getRef_commune());
        $q->bindValue(":id", $ad->getId());
        $q->execute();
    }
}
