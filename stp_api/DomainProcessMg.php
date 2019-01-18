<?php
namespace spamtonprof\stp_api;

use Mailgun\Mailgun;
use Ovh\Api;

class DomainProcessMg

{
    
    
    /**
     *  utilisation 
     *  1) ajouter avec addNewDomains() 
     *  
     *  
     *  
     *  2) préparer $domainPsMg->addMailGunDns(); dans wd2.php
     *  3) éxécuter : http://localhost/spamtonprof/wp-content/plugins/spamtonprof/hook/ovhAuthentification.php 
     *      pour récupérer credential ovh ( attention wd2.php sera éxcuté après )
     * 
     */

    /*
     * pour ajouter des nouveaux domaines à Stp
     * le domain root doit nous appartenir
     *
     */
    function addNewsDomains(string $root, array $subdomains, $type = 'mailgun', $addToMailGun = true)
    {
        $domainMg = new \spamtonprof\stp_api\StpDomainManager();

        $mg = false;
        if ($addToMailGun) {
            $mg = new \spamtonprof\stp_api\MailGunManager();
        }

        foreach ($subdomains as $subdomain) {

            $domain = $subdomain . '.' . $root;
            echo($domain . '<br>');
            $stpDomain = $domainMg->get(array(
                'name' => $domain
            ));
            if (! $stpDomain) {

                $stpDomain = $domainMg->add(new \spamtonprof\stp_api\StpDomain(array(
                    "name" => $domain,
                    "mail_provider" => $type,
                    'in_black_list' => false,
                    'mx_ok' => false
                )));

                if ($mg) {

                    $mg->addDomain($domain);
                }
            } else {
                echo ($stpDomain->getName() . ' existe déjà !');
            }
        }
    }

    /*
     * checkDnsConf($domain) -> pour un nom de doamine regarde si la conf est valide et met à jour la colonne dans la base stp
     *
     */

    /*
     * cette fonction récupère tous les noms de domaines sans configuration dns adapté à mailgun
     * conf_done -> true or false ( va remplacer la colonne mx_ok ) -> changement à faire dans le select
     * conf_valid -> true or false
     *
     */
    function configureMailGunDns()
    {
        $domainMg = new \spamtonprof\stp_api\StpDomainManager();
        $domains = $domainMg->getAll(array(
            'mx_ok' => 'false'
        ));

        $mg = Mailgun::create('039ff1551369afa5ba9e4d4750591df1-8889127d-2bb4f343');

        foreach ($domains as $domain) {

            $domainName = $domain->getName();

            echo ('----------' . $domainName . '----------' . '<br>');
            $root = $domain->getRoot();
            $subdomain = $domain->getSubdomain();

            $dns = $mg->domains()
                ->show($domain->getName())
                ->getOutboundDNSRecords();

            foreach ($dns as $dn) {

                $dns_subdomain = str_replace('.' . $root, '', $dn->getName());
                $target = $dn->getValue();
                $type = $dn->getType();

                if ($type == 'CNAME') {
                    $target = $target . '.';
                }

                echo ('root : ' . $root . '<br>');
                echo ('type : ' . $type . '<br>');
                echo ('name : ' . $dns_subdomain . '<br>');
                echo ('target : ' . $target . '<br>');

                $this->addDnsOvh($root, $dns_subdomain, $target, $type);
            }

            $dns = $mg->domains()
                ->show($domain->getName())
                ->getInboundDNSRecords();

            foreach ($dns as $dn) {

                $target = $dn->getValue() . '.';
                $type = $dn->getType();
                $prio = $dn->getPriority();
                $dns_subdomain = $subdomain;

                $target = $prio . ' ' . $target;

                echo ('root : ' . $root . '<br>');
                echo ('type : ' . $type . '<br>');
                echo ('name : ' . $dns_subdomain . '<br>');
                echo ('target : ' . $target . '<br>' . '<br>');

                $this->addDnsOvh($root, $dns_subdomain, $target, $type);
            }

            $domain->setMx_ok(true);
            $domainMg->updateMxOk($domain);
        }
    }

    /*
     * cette fonction récupère tous les noms de domaines where mx_ok is false
     * et fait la conf dns pour forwardemail
     *
     */
    function configureMailForwardEmail()
    {
        $domainMg = new \spamtonprof\stp_api\StpDomainManager();
        $domains = $domainMg->getAll(array(
            'mx_ok' => 'false'
        ));

        foreach ($domains as $domain) {

            $domainName = $domain->getName();

            echo ('----------' . $domainName . '----------' . '<br>');
            $root = $domain->getRoot();
            $subdomain = $domain->getSubdomain();

            $this->addDnsOvh($root, $subdomain, '10 mx1.forwardemail.net.', 'MX');
            $this->addDnsOvh($root, $subdomain, '20 mx2.forwardemail.net.', 'MX');
            $this->addDnsOvh($root, $subdomain, 'v=spf1 a mx include:spf.forwardemail.net -all', 'TXT');
            $this->addDnsOvh($root, $subdomain, 'forward-email=mailsfromlbc@gmail.com', 'TXT');

            $domain->setMx_ok(true);
            $domainMg->updateMxOk($domain);
        }
    }

    function addMxForwardDomain()
    {
        $domainMg = new \spamtonprof\stp_api\StpDomainManager();

        $mxForward = new \MxForward();

        $domains = $mxForward->getDomains();

        foreach ($domains as $domain) {

            if ($domain->status == 'active') {

                $domainMg->add(new \spamtonprof\stp_api\StpDomain(array(
                    'name' => $domain->domain,
                    'mail_provider' => 'mxforward',
                    'mx_ok' => true,
                    'in_black_list' => false
                )));
            }
        }
    }

    // type peut valoir TXT, CNAME ou MX
    function addDnsOvh($root, $subdomain, $target, $type)
    {
        $consumer_key = unserializeTemp("/tempo/consumerKey");

        $ovh = new Api(applicationKeyOvh, // Application Key
        applicationSecretOvh, // Application Secret
        'ovh-eu', // Endpoint of API OVH Europe (List of available endpoints)
        $consumer_key); // Consumer Key

        $result = $ovh->post('/domain/zone/' . $root . '/record', array(
            'fieldType' => $type, // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
            'subDomain' => $subdomain, // Resource record subdomain (type: string)
            'target' => $target, // Required: Resource record target (type: string)
            'ttl' => 3600
        ));

        $ovh->post('/domain/zone/' . $root . '/refresh');

        return ($result);
    }

    function addMailGunDns()
    {
        $domainMg = new \spamtonprof\stp_api\StpDomainManager();
        $domains = $domainMg->getAll(array(
            'mx_ok' => 'false'
        ));
        $mg = new \spamtonprof\stp_api\MailGunManager();

        foreach ($domains as $domain) {
            $domainName = $domain->getName();
            echo ('----------' . $domainName . '----------' . '<br>');
            $root = $domain->getRoot();
            $subdomain = $domain->getSubdomain();

            $dns = $mg->getOutBoundDns($domain->getName());
            foreach ($dns as $dn) {
                $dns_subdomain = str_replace('.' . $root, '', $dn->getName());
                $target = $dn->getValue();
                $type = $dn->getType();
                if ($type == 'CNAME') {
                    $target = $target . '.';
                }
                echo ('root : ' . $root . '<br>');
                echo ('type : ' . $type . '<br>');
                echo ('name : ' . $dns_subdomain . '<br>');
                echo ('target : ' . $target . '<br>');
                $this->addDnsOvh($root, $dns_subdomain, $target, $type);
            }
            
            $dns = $mg->getInBoundDns($domain->getName());
            foreach ($dns as $dn) {
                $target = $dn->getValue() . '.';
                $type = $dn->getType();
                $prio = $dn->getPriority();
                $dns_subdomain = $subdomain;
                $target = $prio . ' ' . $target;
                echo ('root : ' . $root . '<br>');
                echo ('type : ' . $type . '<br>');
                echo ('name : ' . $dns_subdomain . '<br>');
                echo ('target : ' . $target . '<br>' . '<br>');
                $this->addDnsOvh($root, $dns_subdomain, $target, $type);
            }
            $domain->setMx_ok(true);
            $domainMg->updateMxOk($domain);
        }
    }
}

    
    