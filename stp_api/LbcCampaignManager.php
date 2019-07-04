<?php
namespace spamtonprof\stp_api;

class LbcCampaignManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(lbcCampaign $lbcCampaign)
    {
        $q = $this->_db->prepare('insert into lbc_campaign(date, ref_compte, nb_ad_online, nb_ad_publie) values( :date,:ref_compte,:nb_ad_online,:nb_ad_publie)');
        $q->bindValue(':date', $lbcCampaign->getDate());
        $q->bindValue(':ref_compte', $lbcCampaign->getRef_compte());
        $q->bindValue(':nb_ad_online', $lbcCampaign->getNb_ad_online());
        $q->bindValue(':nb_ad_publie', $lbcCampaign->getNb_ad_publie());
        $q->execute();

        $lbcCampaign->setRef_campaign($this->_db->lastInsertId());

        return ($lbcCampaign);
    }
}
