<?php
namespace spamtonprof\stp_api;

class StpOffreSpamExpressManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpOffreSpamExpress $stpOffreSpamExpress)
    {
        $q = $this->_db->prepare('insert into stp_offre_spam_express(ref_pole, ref_categorie_scolaire, name, price, main, title) values(:ref_pole,:ref_categorie_scolaire,:name,:price,:main,:title)');
        $q->bindValue(':ref_pole', $stpOffreSpamExpress->getRef_pole());
        $q->bindValue(':ref_categorie_scolaire', $stpOffreSpamExpress->getRef_categorie_scolaire());
        $q->bindValue(':name', $stpOffreSpamExpress->getName());
        $q->bindValue(':price', $stpOffreSpamExpress->getPrice());
        $q->bindValue(':main', $stpOffreSpamExpress->getMain(), \PDO::PARAM_BOOL);
        $q->bindValue(':title', $stpOffreSpamExpress->getTitle());
        $q->execute();

        $stpOffreSpamExpress->setRef_offre($this->_db->lastInsertId());

        return ($stpOffreSpamExpress);
    }

    public static function cast(\spamtonprof\stp_api\StpOffreSpamExpress $object)
    {
        return ($object);
    }

    public function update_stripe_price_test(\spamtonprof\stp_api\StpOffreSpamExpress $offre)
    {
        $q = $this->_db->prepare("update stp_offre_spam_express set stripe_price_test = :stripe_price_test where ref_offre = :ref_offre");
        $q->bindValue(":stripe_price_test", $offre->getStripe_price_test());
        $q->bindValue(":ref_offre", $offre->getRef_offre());
        $q->execute();
    }

    public function update_stripe_price(\spamtonprof\stp_api\StpOffreSpamExpress $offre)
    {
        $q = $this->_db->prepare("update stp_offre_spam_express set stripe_price = :stripe_price where ref_offre = :ref_offre");
        $q->bindValue(":stripe_price", $offre->getStripe_price());
        $q->bindValue(":ref_offre", $offre->getRef_offre());
        $q->execute();
    }

    public function update_stripe_product(\spamtonprof\stp_api\StpOffreSpamExpress $offre)
    {
        $q = $this->_db->prepare("update stp_offre_spam_express set stripe_product = :stripe_product where ref_offre = :ref_offre");
        $q->bindValue(":stripe_product", $offre->getStripe_product());
        $q->bindValue(":ref_offre", $offre->getRef_offre());
        $q->execute();
    }

    public function update_stripe_product_test(\spamtonprof\stp_api\StpOffreSpamExpress $offre)
    {
        $q = $this->_db->prepare("update stp_offre_spam_express set stripe_product_test = :stripe_product_test where ref_offre = :ref_offre");
        $q->bindValue(":stripe_product_test", $offre->getStripe_product_test());
        $q->bindValue(":ref_offre", $offre->getRef_offre());
        $q->execute();
    }

    public function get($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stp_offre_spam_express where ref_offre = :ref_offre limit 1");
        $q->bindValue("ref_offre", $info);

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'get_by_cat_and_pole') {
                    $ref_cat = $params['ref_cat'];
                    $ref_pole = $params['ref_pole'];
                }
            }
        }

        $q->execute();

        $offre = false;

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {

            $offre = new \spamtonprof\stp_api\StpOffreSpamExpress($data);

            if ($constructor) {
                $constructor["objet"] = $offre;
                $this->construct($constructor);
            }
        }

        return ($offre);
    }

    public function getAll($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stp_offre_spam_express");

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'get_by_cat_and_pole') {
                    $ref_cat = $params['ref_cat'];
                    $ref_pole = $params['ref_pole'];

                    $q = $this->_db->prepare("select * from stp_offre_spam_express where ref_categorie_scolaire = :ref_categorie_scolaire and ref_pole = :ref_pole");
                    $q->bindValue('ref_categorie_scolaire', $ref_cat);
                    $q->bindValue('ref_pole', $ref_pole);
                }

                if ($key == 'get_by_cat_and_pole_and_ref_offer') {
                    $ref_cat = $params['ref_cat'];
                    $ref_pole = $params['ref_pole'];
                    $ref_offer = $params['ref_offer'];

                    $q = $this->_db->prepare("
                        select * from stp_offre_spam_express 
                            where 
                                ref_categorie_scolaire = :ref_categorie_scolaire and ref_pole = :ref_pole
                                and name in ( select name from stp_offre_spam_express where ref_offre = :ref_offer)");
                    $q->bindValue('ref_categorie_scolaire', $ref_cat);
                    $q->bindValue('ref_pole', $ref_pole);
                    $q->bindValue('ref_offer', $ref_offer);
                }
            }
        }

        $q->execute();

        $offres = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $offre = new \spamtonprof\stp_api\StpOffreSpamExpress($data);

            if ($constructor) {
                $constructor["objet"] = $offre;
                $this->construct($constructor);
            }
            $offres[] = $offre;
        }

        return ($offres);
    }

    public function construct($constructor)
    {
        $offre = self::cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {
                case "ref_categorie_scolaire":
                    $cat_mg = new \spamtonprof\stp_api\StpCategorieScolaireManager();
                    $cat = $cat_mg->get($offre->getRef_categorie_scolaire());
                    $offre->setCat($cat);
                    break;

                case "ref_pole":
                    $pole_mg = new \spamtonprof\stp_api\StpPoleManager();
                    $pole = $pole_mg->get($offre->getRef_pole());
                    $offre->setPole($pole);
                    break;
            }
        }
    }
}
