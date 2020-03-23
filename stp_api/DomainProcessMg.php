<?php
namespace spamtonprof\stp_api;

use Mailgun\Mailgun;
use Ovh\Api;

class DomainProcessMg

{

    /**
     * utilisation
     * 1) ajouter avec addNewDomains()
     *
     *
     *
     * 2) preparer $domainPsMg->addMailGunDns(); dans wd2.php
     * 3) executer : http://localhost/spamtonprof/wp-content/plugins/spamtonprof/hook/ovhAuthentification.php
     * pour recuperer credential ovh ( attention wd2.php sera excute apres )
     */

    /*
     * pour ajouter des nouveaux domaines a Stp
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

            if (! $subdomain) {
                $domain = $root;
            }

            echo ($domain . '<br>');
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
                echo ($stpDomain->getName() . ' existe deja !');
            }
        }
    }

    /*
     * checkDnsConf($domain) -> pour un nom de doamine regarde si la conf est valide et met a jour la colonne dans la base stp
     *
     */

    /*
     * cette fonction recupere tous les noms de domaines sans configuration dns adapte a mailgun
     * conf_done -> true or false ( va remplacer la colonne mx_ok ) -> changement a faire dans le select
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
     * cette fonction recupere tous les noms de domaines where mx_ok is false
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

    function validateDomains()
    {
        $slack = new \spamtonprof\slack\Slack();

        $domainMg = new \spamtonprof\stp_api\StpDomainManager();
        $domains = $domainMg->getAll(array(
            "domains_to_validate"
        ));

        if (count($domains) == 0) {
            $slack->sendMessages('domain-log', array(
                'Aucun domaine à valider ...'
            ));
            exit();
        }

        $valid = [];
        $not_valid = [];

        $i = 0;

        foreach ($domains as $domain) {

            $domain_name = $domain->getName();

            $mg = new \spamtonprof\stp_api\MailGunManager();
            $validated = $mg->isValid($domain_name);

            if ($validated) {
                $domain->setReady(true);
                $domainMg->updateReady($domain);
                $valid[] = $domain_name;
            } else {
                $domain->setMx_ok(false);
                $domainMg->updateMxOk($domain);
                $not_valid[] = $domain_name;
            }
            $i = $i + 1;
            if ($i > 20) {
                break;
            }
        }

        $slack->sendMessages('domain-log', array(
            'valid domain: ' . json_encode($valid)
        ));
        $slack->sendMessages('domain-log', array(
            'not valid domain: ' . json_encode($not_valid)
        ));
    }

    function addMailGunDns()
    {
        $slack = new \spamtonprof\slack\Slack();
        $i = 0;
        $msgs = [];

        $msgs[] = "Begin ...";

        $domainMg = new \spamtonprof\stp_api\StpDomainManager();
        $domains = $domainMg->getAll(array(
            'mx_ok' => 'false'
        ));

        if (count($domains) == 0) {
            $msgs[] = 'No more domains to process';
            $slack->sendMessages('domain-log', $msgs);
            return;
        }

        if (count($domains) != 1) {

            array_shift($domains);
        }

        $mg = new \spamtonprof\stp_api\MailGunManager();
        $internetBsMg = new \spamtonprof\stp_api\InternetBsMg(false);
        foreach ($domains as $domain) {

            $internetBsMg->removeAllDnsRecord($domain->getName());
            $domainName = $domain->getName();
            $msgs[] = '----------' . $domainName . '----------';
            $root = $domain->getRoot();
            $subdomain = $domain->getSubdomain();

            $dns = $mg->getOutBoundDns($domain->getName());
            $msgs[] = 'Processing outbound-dns';
            foreach ($dns as $dn) {
                $dns_subdomain = str_replace('.' . $root, '', $dn->getName());
                $target = $dn->getValue();
                $type = $dn->getType();
                if ($type == 'CNAME') {
                    $target = $target . '.';
                }
                $msgs[] = 'root : ' . $root;
                $msgs[] = 'type : ' . $type;
                $msgs[] = 'name : ' . $dns_subdomain;
                $msgs[] = 'target : ' . $target;

                if ($type == 'MX') {
                    $target = explode(" ", $target)[1];
                }

                $full_domain = $root;
                if ($dns_subdomain != "" && $dns_subdomain != $root) {
                    $full_domain = $dns_subdomain . '.' . $root;
                }

                $ret = $internetBsMg->addDnsRecord($full_domain, $type, $target);
                $ret = print_r($ret, true);
                $msgs[] = $ret;

                if (strpos($ret, 'SUCCESS') === false) {
                    $msgs[] = 'Erreur. End of running ...';

                    $slack->sendMessages('domain-log', $msgs);
                    exit();
                }
            }

            $dns = $mg->getInBoundDns($domain->getName());
            $msgs[] = 'Processing inbound-dns';
            foreach ($dns as $dn) {
                $target = $dn->getValue() . '.';
                $type = $dn->getType();
                $prio = $dn->getPriority();
                $dns_subdomain = $subdomain;
                $target = $prio . ' ' . $target;
                $msgs[] = 'root : ' . $root;
                $msgs[] = 'type : ' . $type;
                $msgs[] = 'name : ' . $dns_subdomain;
                $msgs[] = 'target : ' . $target;
                // $this->addDnsOvh($root, $dns_subdomain, $target, $type);

                if ($type == 'MX') {
                    $target = explode(" ", $target)[1];
                }

                $full_domain = $root;
                if ($dns_subdomain != "" && $dns_subdomain != $root) {
                    $full_domain = $dns_subdomain . '.' . $root;
                }

                $ret = $internetBsMg->addDnsRecord($full_domain, $type, $target);
                $ret = print_r($ret, true);
                $msgs[] = $ret;

                if (strpos($ret, 'SUCCESS') === false) {
                    $msgs[] = 'Erreur. End of running ...';
                    $slack->sendMessages('domain-log', $msgs);
                    exit();
                }
            }

            $domain->setMx_ok(true);
            $domainMg->updateMxOk($domain);

            if (count($msgs) > 20) {

                $slack->sendMessages('domain-log', $msgs);
                $msgs = [];
            }
            $i = $i + 1;
            if ($i > 10) {
                break;
            }
        }

        $slack->sendMessages('domain-log', $msgs);
    }
}

    
    