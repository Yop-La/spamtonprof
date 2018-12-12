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
 * Cette classe sert à gérér ( CRUD ) les plans de paiement stripe
 *
 * Elle sert aussi à créer des abonnements, des clients, des paiements, etc
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

    public function deleteAllDomains()
    {
        $domains = $this->mg->domains()
            ->index()
            ->getDomains();

        foreach ($domains as $domain) {
            $this->mg->domains()->delete($domain->getName());
        }
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
