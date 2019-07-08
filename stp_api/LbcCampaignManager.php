<?php
namespace spamtonprof\stp_api;

class LbcCampaignManager
{

    private $_db;

    const campaign_to_analyse = 'campaign_to_analyse', clients_campaigns_to_analyse = 'clients_campaigns_to_analyse',succeed_compte_campaigns = 'succeed_compte_campaigns';

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function update_checked(\spamtonprof\stp_api\LbcCampaign $campaign)
    {
        $q = $this->_db->prepare('update lbc_campaign set checked = :checked where ref_campaign = :ref_campaign');
        $q->bindValue(":checked", $campaign->getChecked(), \PDO::PARAM_BOOL);
        $q->bindValue(":ref_campaign", $campaign->getRef_campaign());
        $q->execute();
    }
    
    public function update_fail(\spamtonprof\stp_api\LbcCampaign $campaign)
    {
        $q = $this->_db->prepare('update lbc_campaign set fail = :fail where ref_campaign = :ref_campaign');
        $q->bindValue(":fail", $campaign->getFail(), \PDO::PARAM_BOOL);
        $q->bindValue(":ref_campaign", $campaign->getRef_campaign());
        $q->execute();
    }
    
    public function update_nb_ad_online(\spamtonprof\stp_api\LbcCampaign $campaign)
    {
        $q = $this->_db->prepare('update lbc_campaign set nb_ad_online = :nb_ad_online where ref_campaign = :ref_campaign');
        $q->bindValue(":nb_ad_online", $campaign->getNb_ad_online());
        $q->bindValue(":ref_campaign", $campaign->getRef_campaign());
        $q->execute();
    }

    public function add(lbcCampaign $lbcCampaign)
    {
        $q = $this->_db->prepare('insert into lbc_campaign(date, ref_compte, nb_ad_online, nb_ad_publie,checked,fail) 
            values( :date,:ref_compte,:nb_ad_online,:nb_ad_publie, false, false)');
        $q->bindValue(':date', $lbcCampaign->getDate());
        $q->bindValue(':ref_compte', $lbcCampaign->getRef_compte());
        $q->bindValue(':nb_ad_online', $lbcCampaign->getNb_ad_online());
        $q->bindValue(':nb_ad_publie', $lbcCampaign->getNb_ad_publie());
        $q->execute();

        $lbcCampaign->setRef_campaign($this->_db->lastInsertId());

        return ($lbcCampaign);
    }

    public function getAll($info)
    {
        $q = null;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                if ($key == $this::campaign_to_analyse) {

                    $q = $this->_db->prepare("select * from lbc_campaign where 
		              ref_compte not in (select distinct(ref_compte) from adds_tempo where statut = 'publie')
		              and ref_compte in (select distinct(ref_compte) from adds_tempo )
		              and checked = false
                      order by date desc;");
                }
                
                if ($key == $this::succeed_compte_campaigns) {
                    
                    $ref_compte = $info['ref_compte'];                    
                    $q = $this->_db->prepare("select * from lbc_campaign
                        where ref_compte = :ref_compte and checked is true and fail is false");
                    
                    $q->bindValue(':ref_compte', $ref_compte);
                }

                if ($key == $this::clients_campaigns_to_analyse) {

                    $ref_client = $info['ref_client'];

                    $q = $this->_db->prepare("select * from lbc_campaign 
                        where ref_compte in (select ref_compte from compte_lbc where ref_client = :ref_client)
	                       and now() - interval '7 days' <= date
                           and checked is true
                           order by date;");

                    $q->bindValue(':ref_client', $ref_client);
                }
            }
        }

        $q->execute();

        $campaigns = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $campaign = new \spamtonprof\stp_api\LbcCampaign($data);
            $campaigns[] = $campaign;
        }

        return ($campaigns);
    }
}
