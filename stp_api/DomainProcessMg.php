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

    function changeMxDomains()
    {
        $applicationKey = "At40SjPHzysRGhkL";
        $applicationSecret = "qwOB6M0tBRgHH8hzebtWGFqZzcK4UMry";
        $consumer_key = unserializeTemp("/tempo/consumerKey");

        $ovh = new Api($applicationKey, // Application Key
        $applicationSecret, // Application Secret
        'ovh-eu', // Endpoint of API OVH Europe (List of available endpoints)
        $consumer_key); // Consumer Key

        $subdomains = [
            'maths1',
            'maths2',
            'maths3',
            'maths4',
            'maths5'
        ];

        $domain = $results = [];

        foreach ($subdomains as $subdomain) {

            $result1 = $ovh->post('/domain/zone/thomas-cours.fr/record', array(
                'fieldType' => 'MX', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
                'subDomain' => $subdomain, // Resource record subdomain (type: string)
                'target' => '10 mx1.forwardmx.io.' // Required: Resource record target (type: string)
            ));
            $results[] = $result1;

            $result2 = $ovh->post('/domain/zone/thomas-cours.fr/record', array(
                'fieldType' => 'MX', // Required: Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
                'subDomain' => $subdomain, // Resource record subdomain (type: string)
                'target' => '20 mx2.forwardmx.io.' // Required: Resource record target (type: string)
            ));
            $results[] = $result2;
        }

        prettyPrint($results);
    }
}

    
    