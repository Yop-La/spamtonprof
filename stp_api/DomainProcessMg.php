<?php
namespace spamtonprof\stp_api;

use Ovh\Api;

class DomainProcessMg

{

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

    function addTextDns($domain, $subdomain, $target)
    {
        $applicationKey = "At40SjPHzysRGhkL";
        $applicationSecret = "qwOB6M0tBRgHH8hzebtWGFqZzcK4UMry";
        $consumer_key = unserializeTemp("/tempo/consumerKey");

        $ovh = new Api($applicationKey, // Application Key
        $applicationSecret, // Application Secret
        'ovh-eu', // Endpoint of API OVH Europe (List of available endpoints)
        $consumer_key); // Consumer Key

        $result3 = $ovh->post('/domain/zone/' . $domain . '/record', array(
            'fieldType' => 'TXT', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
            'subDomain' => $subdomain, // Resource record subdomain (type: string)
            'target' => $target // Required: Resource record target (type: string)
        ));
        return ($result3);
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

    
    