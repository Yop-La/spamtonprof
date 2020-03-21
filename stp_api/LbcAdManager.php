<?php
namespace spamtonprof\stp_api;

class LbcAdManager
{

    private $_db;

    const not_ready = 'not_ready', is_ready = 'is_ready';

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function pushAdsFromTadabase($delete = false)
    {
        $tadaBaseApi = new \spamtonprof\stp_api\ExTadabaseMg();

        $ads = $tadaBaseApi->getDoneAds();

        foreach ($ads as $ad) {

            $ad = $this->cast_tadabase_ad($ad);
            $this->add($ad);

            if ($delete) {
                $tadaBaseApi->deleteAd($ad->getTadabase_id());
            }
        }
    }

    public function prepare_ads()
    {
        $boys = [
            'gaston',
            'arnaud',
            'jacques',
            'gilles',
            'séverin',
            'stéphane',
            'alphonse',
            'désiré',
            'gérard',
            'frédéric',
            'jeannot',
            'fernand',
            'eugène',
            'jérémie',
            'timothée',
            'philippe',
            'félix',
            'amaury',
            'clément',
            'adrien',
            'gustave',
            'raphaël',
            'nicolas',
            'aimé',
            'valéry',
            'augustin',
            'raoul',
            'honoré',
            'thierry',
            'sébastien',
            'anatole',
            'hilaire',
            'antoine',
            'baptiste',
            'lazare',
            'léonard',
            'apollinaire',
            'joël',
            'damien',
            'georges',
            'pascal',
            'armand',
            'françois',
            'maxime',
            'grégoire',
            'rémy',
            'edmond',
            'yves'
        ];
        $girls = [
            'antoinette',
            'violette',
            'rosette',
            'clarisse',
            'béatrice',
            'lucienne',
            'victoire',
            'michèle',
            'berthe',
            'lucille',
            'aude',
            'laure',
            'carole',
            'monique',
            'frédérique',
            'alexandrie',
            'cécile',
            'gilberte',
            'germaine',
            'claire',
            'marine',
            'aurélie',
            'jeannette',
            'élodie',
            'mathilde',
            'mignon',
            'chanté',
            'joséphine',
            'odette',
            'marceline',
            'renée',
            'claudine',
            'eugénie',
            'albertine',
            'mariette',
            'reine',
            'virginie',
            'vienne',
            'agnès',
            'colette',
            'ambre',
            'fifi',
            'odile',
            'micheline',
            'mélanie',
            'placide',
            'gisèle'
        ];

        $faceApi = new \spamtonprof\stp_api\ExFaceApi();

        $ads = $this->getAll(array(
            'key' => $this::not_ready
        ));

        foreach ($ads as $ad) {

            $names = $girls;
            $gender = 'female';
            if ($ad->getMale()) {
                $names = $boys;
                $gender = 'male';
            }

            $new_name = $names[array_rand($names)];

            $emotions = [
                'joy',
                'neutral'
            ];
            $emotion = $emotions[random_int(0, 1)];

            $url = $faceApi->getFaceUrl($gender, $emotion);

            $ad->setImage_url($url);
            $this->updateImageUrl($ad);

            $name = $ad->getName();

            if ($name) {

                $body = $ad->getBody();
                $body = str_replace($name, ucfirst($new_name), $body);

                // $body = str_replace('[name]', $new_name, $body);

                $ad->setBody($body);
                $this->updateBody($ad);
            }

            $ad->setReady(true);
            $this->updateReady($ad);
        }
    }

    public function add(lbcAd $lbcAd)
    {
        $q = $this->_db->prepare('insert into lbc_ad( body, subject, male, name,category, matiere, period) values(:body,:subject,:male, :name,:category, :matiere, :period)');

        $q->bindValue(':body', $lbcAd->getBody());
        $q->bindValue(':subject', $lbcAd->getSubject());

        $q->bindValue(':name', $lbcAd->getName());

        $q->bindValue(':male', $lbcAd->getMale(), \PDO::PARAM_BOOL);

        $q->bindValue(':matiere', $lbcAd->getMatiere());

        $q->bindValue(':category', $lbcAd->getCategory());

        $q->bindValue(':period', $lbcAd->getPeriod());

        $q->execute();

        $lbcAd->setRef_ad($this->_db->lastInsertId());

        return ($lbcAd);
    }

    public function getAll($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from lbc_ad");
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                if ($key == $this::not_ready) {

                    $q = $this->_db->prepare('select * from lbc_ad where ready is false or ready is null');
                }

                if ($key == $this::is_ready) {

                    $q = $this->_db->prepare('select * from lbc_ad where ready is true or ready is null');
                }
            }
        }

        $q->execute();

        $ads = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $lbcAd = new \spamtonprof\stp_api\LbcAd($data);

            $ads[] = $lbcAd;
        }
        return ($ads);
    }

    public function cast(\spamtonprof\stp_api\LbcAd $ad)
    {
        return ($ad);
    }

    public function cast_tadabase_ad($tadabse_ad)
    {
        $ad = new \spamtonprof\stp_api\LbcAd();

        $ad->setTadabase_id($tadabse_ad->id);
        $ad->setSubject($tadabse_ad->field_36);
        $ad->setBody($tadabse_ad->field_37);

        $male = false;
        if ($tadabse_ad->field_42 == 'Homme') {
            $male = true;
        }
        $ad->setMale($male);

        $ad->setName($tadabse_ad->field_39);

        $ad->setCategory($tadabse_ad->field_43);
        $ad->setMatiere(json_encode($tadabse_ad->field_46));
        $ad->setPeriod($tadabse_ad->field_48);

        return ($ad);
    }

    public function updateImageUrl(LbcAd $ad)
    {
        $q = $this->_db->prepare('update lbc_ad set image_url = :image_url where ref_ad = :ref_ad');
        $q->bindValue(':image_url', $ad->getImage_url());
        $q->bindValue(':ref_ad', $ad->getRef_ad());
        $q->execute();
        return ($ad);
    }

    public function updateBody(LbcAd $ad)
    {
        $q = $this->_db->prepare('update lbc_ad set body = :body where ref_ad = :ref_ad');
        $q->bindValue(':body', $ad->getBody());
        $q->bindValue(':ref_ad', $ad->getRef_ad());
        $q->execute();
        return ($ad);
    }

    public function updateReady(LbcAd $ad)
    {
        $q = $this->_db->prepare('update lbc_ad set ready = :ready where ref_ad = :ref_ad');
        $q->bindValue(':ready', $ad->getReady(), \PDO::PARAM_BOOL);
        $q->bindValue(':ref_ad', $ad->getRef_ad());
        $q->execute();
        return ($ad);
    }
}
