<?php
namespace spamtonprof\stp_api;

use Mailgun\Mailgun;
use Ovh\Api;

class DomainProcessMg

{

    /*
     * pour ajouter des nouveaux domaines à MailGun et à Stp
     * le domain root doit nous appartenir
     *
     *
     *
     */
    function addNewsDomains(string $root, array $subdomains)
    {
        $domainMg = new \spamtonprof\stp_api\StpDomainManager();
        $mg = Mailgun::create('039ff1551369afa5ba9e4d4750591df1-8889127d-2bb4f343');

        foreach ($subdomains as $subdomain) {

            $domain = $subdomain . '.' . $root;
            $stpDomain = $domainMg->get(array(
                'name' => $domain
            ));
            if (! $stpDomain) {
                $mg->domains()->create($domain);

                $stpDomain = $domainMg->add(new \spamtonprof\stp_api\StpDomain(array(
                    "name" => $domain,
                    "mail_provider" => 'mailgun',
                    'in_black_list' => false,
                    'mx_ok' => false
                )));
            } else {
                echo ($stpDomain->getName() . ' existe déjà !');
            }
        }
    }

    function configureDns()
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
        $applicationKey = "At40SjPHzysRGhkL";
        $applicationSecret = "qwOB6M0tBRgHH8hzebtWGFqZzcK4UMry";
        $consumer_key = unserializeTemp("/tempo/consumerKey");

        $ovh = new Api($applicationKey, // Application Key
        $applicationSecret, // Application Secret
        'ovh-eu', // Endpoint of API OVH Europe (List of available endpoints)
        $consumer_key); // Consumer Key

        $result = $ovh->post('/domain/zone/' . $root . '/record', array(
            'fieldType' => $type, // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
            'subDomain' => $subdomain, // Resource record subdomain (type: string)
            'target' => $target // Required: Resource record target (type: string)
        ));
        return ($result);
    }

    function addMailGunDns($domain, $subdomain)
    {
        $applicationKey = "At40SjPHzysRGhkL";
        $applicationSecret = "qwOB6M0tBRgHH8hzebtWGFqZzcK4UMry";
        $consumer_key = unserializeTemp("/tempo/consumerKey");

        $ovh = new Api($applicationKey, // Application Key
        $applicationSecret, // Application Secret
        'ovh-eu', // Endpoint of API OVH Europe (List of available endpoints)
        $consumer_key); // Consumer Key

        $results = [];

        // $result1 = $ovh->post('/domain/zone/' . $domain . '/record', array(
        // 'fieldType' => 'MX', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
        // 'subDomain' => $subdomain, // Resource record subdomain (type: string)
        // 'target' => '10 mxa.mailgun.org.' // Required: Resource record target (type: string)
        // ));
        // $results[] = $result1;

        // $result2 = $ovh->post('/domain/zone/' . $domain . '/record', array(
        // 'fieldType' => 'MX', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
        // 'subDomain' => $subdomain, // Resource record subdomain (type: string)
        // 'target' => '10 mxb.mailgun.org.' // Required: Resource record target (type: string)
        // ));
        // $results[] = $result2;

        $result3 = $ovh->post('/domain/zone/' . $domain . '/record', array(
            'fieldType' => 'TXT', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
            'subDomain' => 'mailo._domainkey' . $subdomain, // Resource record subdomain (type: string)
            'target' => 'k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2kwrHwlQ4sxVNGniHKepEznNzQUt4D9i3HsJ7S8LNnLwt4BxBy8wpAxdaFf1S4ZOCiuzjxvJBbEYUYZuBJiGLpmPCk/Wi9l9FORIiLY3aLhu2kGkQUsyPXfzjSlJnJkHDARJtGPg1bgCk3a50XMhdxqCxkO/HG1rqARWVP2VzHwIDAQAB' // Required: Resource record target (type: string)
        ));
        $results[] = $result3;

        $result4 = $ovh->post('/domain/zone/' . $domain . '/record', array(
            'fieldType' => 'TXT', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
            'subDomain' => $subdomain, // Resource record subdomain (type: string)
            'target' => 'v=spf1 include:mailgun.org ~all' // Required: Resource record target (type: string)
        ));
        $results[] = $result4;

        return ($results);
    }
}

    
    