<?php
namespace spamtonprof\stp_api;

use PDO;
use DateTime;
use Exception;
use Mailgun\Mailgun;
use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Currency;

/*
 *
 * Cette classe sert � g�r�r ( CRUD ) les plans de paiement stripe
 *
 * Elle sert aussi � cr�er des abonnements, des clients, des paiements, etc
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
class MailGunManager

{

    private $mg = false;

    public function __construct()
    {
        // First, instantiate the SDK with your API credentials
        $this->mg = Mailgun::create(mailGunKey);
    }

    public function deleteDomain($name)
    {
        $this->mg->domains()->delete($name);
    }

    public function isValid($name)
    {
        $res = $this->mg->domains()->verify($name);

        $dns_ar = $res->getOutboundDNSRecords();
        $dns_ar = array_merge($res->getInboundDNSRecords(), $dns_ar);

        foreach ($dns_ar as $dns) {
            $dns->getValidity();

            if ($dns->getValidity() != 'valid') {
                return (false);
            }
        }

        return (true);
    }

    public function deleteAllDomains()
    {
        $domains = $this->mg->domains()
            ->index()
            ->getDomains();

        foreach ($domains as $domain) {
            $this->deleteDomain($domain->getName());
        }
    }

    public function listDomains()
    {
        $ret = [];
        $domains = $this->mg->domains()->index();

        foreach ($domains as $domain) {
            $ret[] = $domain->getName();
        }

        return ($ret);
    }

    public function addDomain($name)
    {
        $this->mg->domains()->create($name);
    }

    public function getOutBoundDns($domain)
    {
        $dns = $this->mg->domains()
            ->show($domain)
            ->getOutboundDNSRecords();

        return ($dns);

        $this->mg->domains()
            ->show($domain->getName())
            ->getInboundDNSRecords();
    }

    public function getInBoundDns($domain)
    {
        $dns = $this->mg->domains()
            ->show($domain)
            ->getInboundDNSRecords();

        return ($dns);
    }
}
