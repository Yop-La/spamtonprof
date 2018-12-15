<?php
namespace spamtonprof\stp_api;

use PDO;

class StpCouponManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpCoupon $stpCoupon)
    {
        $q = $this->_db->prepare('insert into stp_coupon(ref_stripe, name, client_limit) values(:ref_stripe,:name,:client_limit)');

        $q->bindValue(':ref_stripe', $stpCoupon->getRef_stripe());
        $q->bindValue(':name', $stpCoupon->getName());
        $q->bindValue(':client_limit', $stpCoupon->getClient_limit());
        $q->execute();

        $stpCoupon->setRef_coupon($this->_db->lastInsertId());

        return ($stpCoupon);
    }

    public function get($info)
    {
        if (array_key_exists('name', $info)) {

            $name = $info['name'];
            $q = $this->_db->prepare('select * from stp_coupon where name = :name limit 1');
            $q->bindValue(':name', $name);
            $q->execute();
        }

        if (array_key_exists('ref_coupon', $info)) {

            $refCoupon = $info['ref_coupon'];
            $q = $this->_db->prepare('select * from stp_coupon where ref_coupon = :ref_coupon');
            $q->bindValue(':ref_coupon', $refCoupon);
            $q->execute();
        }

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\StpCoupon($data));
        } else {
            return (false);
        }
    }
}