<?php
namespace spamtonprof\stp_api;

class LbcAdsImmoManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(lbcAdsImmo $lbcAdsImmo)
    {
        $q = $this->_db->prepare('insert into lbc_ads_immo( price, square, url, first_publication_date, city, type_bien,zipcode,index_date, room, notified, target, category, text, date_last_crawl) 
        values( :price,:square,:url,:first_publication_date, :city, :type_bien,:zipcode,:index_date, :room, false,:target, :category, :text, now() )');

        $q->bindValue(':price', $lbcAdsImmo->getPrice());
        $q->bindValue(':square', $lbcAdsImmo->getSquare());
        $q->bindValue(':url', $lbcAdsImmo->getUrl());
        $q->bindValue(':text', $lbcAdsImmo->getText());
        $q->bindValue(':city', $lbcAdsImmo->getCity());
        $q->bindValue(':type_bien', $lbcAdsImmo->getType_bien());
        $q->bindValue(':zipcode', $lbcAdsImmo->getZipcode());
        $q->bindValue(':room', $lbcAdsImmo->getRoom());
        $q->bindValue(':target', $lbcAdsImmo->getTarget());
        $q->bindValue(':category', $lbcAdsImmo->getCategory());
        $q->bindValue(':index_date', $lbcAdsImmo->getIndex_date()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':first_publication_date', $lbcAdsImmo->getFirst_publication_date()
            ->format(PG_DATETIME_FORMAT));
        $q->execute();

        $lbcAdsImmo->setRef_ad($this->_db->lastInsertId());

        return ($lbcAdsImmo);
    }

    public function update_view_prix_moyen_rennes()
    {
        $q = $this->_db->prepare("
            create or replace view prix_moyen_rennes as
            select round(avg(price/square)) as prixmoyen,zipcode  from lbc_ads_immo
            where type_bien  not in ('Terrain','Parking','Autre')
             group by zipcode;
        ");
        $q->execute();
    }

    public function get_opportunities_coloc4($target)
    {
        $data = false;

        $q = $this->_db->prepare("
                select ref_ad,price, square,url,lbc_ads_immo.city, type_bien, lbc_ads_immo.zipcode, first_publication_date, room,round(price/square) as prixm,prixmoyen,room  from lbc_ads_immo,prix_moyen_rennes
                where type_bien  not in ('Terrain','Parking','Autre')
                 and price < 170000
                 and round(price/square) > 900
                 and (price - prixmoyen)/prixmoyen>0.2
                 and room > 3
                 and lower(target) like lower(:target)
                 and price < 150000
                 and notified = false
                 and lbc_ads_immo.zipcode = prix_moyen_rennes.zipcode
                 order by price;

        ");
        $q->bindValue(':target', "%$target%");
        $q->execute();

        $ads = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $ads[] = $data;
        }
        return ($ads);
    }

    
    public function get_ads_to_crawl($target, $category)
    {
        $data = false;
        
        $q = $this->_db->prepare("
            select * from lbc_ads_immo where category = :category 
                and lower(target) like lower(:target) 
                and now() >  date_last_crawl + interval '1 day'
                and date_retrait is null;
        ");
        $q->bindValue(':target', "%$target%");
        $q->bindValue(':category', "$category");
        $q->execute();
        
        $ads = [];
        
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $ads[] = new \spamtonprof\stp_api\LbcAdsImmo($data);
        }
        return ($ads);
    }
    
    public function get_opportunities_coloc($target)
    {
        $data = false;

        $q = $this->_db->prepare("
            select ref_ad,price, square,url,lbc_ads_immo.city, type_bien, lbc_ads_immo.zipcode, first_publication_date, room,round(price/square) as prixm,prixmoyen,room  from lbc_ads_immo,prix_moyen_rennes
            where type_bien  not in ('Terrain','Parking','Autre')
             and price < 70000
             and lower(target) like lower(:target)
             and round(price/square) > 800
             and (price - prixmoyen)/prixmoyen>0.2
             and room >= 2
             and lbc_ads_immo.zipcode = prix_moyen_rennes.zipcode
             and notified = false
             and square > 50
			 order by price;
        ");
        $q->bindValue(':target', "%$target%");
        $q->execute();

        $ads = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $ads[] = $data;
        }
        return ($ads);
    }

    public function get_opportunities_coloc2($target)
    {
        $data = false;

        $q = $this->_db->prepare("
            select ref_ad,price, square,url,lbc_ads_immo.city, type_bien, lbc_ads_immo.zipcode, first_publication_date, room,round(price/square) as prixm,prixmoyen,room  from lbc_ads_immo,prix_moyen_rennes
            where type_bien  not in ('Terrain','Parking','Autre')
             and price < 100000
             and lower(target) like lower(:target)
             and round(price/square) > 900
             and (price - prixmoyen)/prixmoyen>0.2
             and room <= 2
             and lbc_ads_immo.zipcode = prix_moyen_rennes.zipcode
             and notified = false
             and square > 30
			 order by price;
        ");
        $q->bindValue(':target', "%$target%");
        $q->execute();

        $ads = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $ads[] = $data;
        }
        return ($ads);
    }

    public function get_opportunities_coloc3($target)
    {
        $data = false;

        $q = $this->_db->prepare("
            select ref_ad,price, square,url,lbc_ads_immo.city, type_bien, lbc_ads_immo.zipcode, first_publication_date, room,round(price/square) as prixm,prixmoyen,room  from lbc_ads_immo,prix_moyen_rennes
            where type_bien  not in ('Terrain','Parking','Autre')
             and price < 170000
             and round(price/square) > 900
             and (price - prixmoyen)/prixmoyen>0.2
             and room = 3
             and lower(target) like lower(:target)
             and price < 120000
             and lbc_ads_immo.zipcode = prix_moyen_rennes.zipcode
             and notified = false 
             order by price;
        ");
        $q->bindValue(':target', "%$target%");
        $q->execute();

        $ads = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $ads[] = $data;
        }
        return ($ads);
    }

    public function update_notified(LbcAdsImmo $ad)
    {
        $q = $this->_db->prepare('update lbc_ads_immo set notified = :notified where ref_ad = :ref_ad');
        $q->bindValue(':ref_ad', $ad->getRef_ad());
        $q->bindValue(':notified', $ad->getNotified(), \PDO::PARAM_BOOL);
        $q->execute();

        return ($ad);
    }

    public function get_most_recent_ad($target, $category)
    {
        $data = false;

        $q = $this->_db->prepare('select * from lbc_ads_immo 
            where lower(target) like lower(:target) 
             and category = :category
            order by index_date desc limit 1');
        $q->bindValue(':target', "%$target%");
        $q->bindValue(':category', $category);
        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\LbcAdsImmo($data));
        } else {
            return (false);
        }
    }
}
