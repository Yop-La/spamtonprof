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
    private $cpanel_credentials, $vps = true;

    public function __construct($vps = true)
    {
        $CPANEL_MUTU_CREDENTIALS = [];
        $CPANEL_MUTU_CREDENTIALS['host'] = CPANEL_MUTU_HOST; // ip or domain complete with its protocol and port
        $CPANEL_MUTU_CREDENTIALS['username'] = CPANEL_MUTU_USERNAME; // username of your server, it usually root.
        $CPANEL_MUTU_CREDENTIALS['auth_type'] = CPANEL_MUTU_AUTH_TYPE; // set 'hash' or 'password'
        $CPANEL_MUTU_CREDENTIALS['password'] = CPANEL_MUTU_PASSWORD; // long hash or your user's password

        $CPANEL_VPS_CREDENTIALS = [];
        $CPANEL_VPS_CREDENTIALS['host'] = CPANEL_VPS_HOST; // ip or domain complete with its protocol and port
        $CPANEL_VPS_CREDENTIALS['username'] = CPANEL_VPS_USERNAME; // username of your server, it usually root.
        $CPANEL_VPS_CREDENTIALS['auth_type'] = CPANEL_VPS_AUTH_TYPE; // set 'hash' or 'password'
        $CPANEL_VPS_CREDENTIALS['password'] = CPANEL_VPS_PASSWORD; // long hash or your user's password

        if ($vps) {
            $this->cpanel_credentials = $CPANEL_VPS_CREDENTIALS;
            $this->vps = true;
        } else {
            $this->cpanel_credentials = $CPANEL_MUTU_CREDENTIALS;
            $this->vps = false;
        }
    }

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

    function move_to_planethost_from_internet_bs_and_set_mail_gun_dns($domain)
    {
        $slack = new \spamtonprof\slack\Slack();
        $slack->sendMessages('domain-log', array(
            'migration de ' . $domain . ' vers vps + ajout dns mailgun'
        ));

        $cpanel = new \Gufy\CpanelPhp\Cpanel($this->cpanel_credentials);

        $internet_bs_api = new \spamtonprof\stp_api\InternetBsMg(false);

        $rets = $this->delete_mail_dns_for_mail_gun($domain);

        $dns_records = $this->getMailGunDns($domain);

        foreach ($dns_records as $dns_record) {

            $domain = $dns_record['domain'];
            $name = $dns_record['name'];
            $type = $dns_record['type'];
            $value = $dns_record['value'];
            $priority = $dns_record['priority'];

            if ($name == $domain) {
                $name = $name . ".";
            } else {
                $name = str_replace("." . $domain, "", $name);
            }

            if ($type == 'MX') {
                $rets[] = $dns_record;
                $rets[] = $cpanel->execute_action(3, 'Email', 'add_mx', 'yopla', array(
                    'domain' => $domain,
                    'exchanger' => $value,
                    'priority' => $priority
                ));
            } else if ($type == 'CNAME') {
                $rets[] = $dns_record;
                $rets[] = $cpanel->execute_action(2, 'ZoneEdit', 'add_zone_record', 'yopla', array(
                    'domain' => $domain,
                    'name' => $name,
                    'type' => $type,
                    'cname' => $value
                ));
            } else {
                $rets[] = $dns_record;
                $rets[] = $cpanel->execute_action(2, 'ZoneEdit', 'add_zone_record', 'yopla', array(
                    'domain' => $domain,
                    'name' => $name,
                    'type' => $type,
                    'txtdata' => $value,
                    'target' => $domain
                ));
            }
        }

        $internet_bs_api->removeAllDnsRecord($domain);

        $internet_bs_api->set_planet_hoster_ns([
            $domain
        ], $this->vps);

        return ($rets);
    }

    function delete_mail_dns_for_mail_gun($domain)
    {
        $rets = [];

        $cpanel = new \Gufy\CpanelPhp\Cpanel($this->cpanel_credentials);

        $mxRecords = $cpanel->execute_action(2, 'ZoneEdit', 'fetchzone_records', 'yopla', array(
            'domain' => $domain,
            'type' => 'MX'
        ));

        
        
        $mxRecords = $mxRecords['cpanelresult']['data'];

        
        do {

            foreach ($mxRecords as $mxRecord) {

                $rets[] = $cpanel->execute_action(3, 'Email', 'delete_mx', 'yopla', array(
                    'domain' => $domain,
                    'exchanger' => $mxRecord['exchange'],
                    'priority' => (int) $mxRecord['preference']
                ));
            }

            $dnsRecords = $cpanel->execute_action(2, 'ZoneEdit', 'fetchzone_records', 'yopla', array(
                'domain' => $domain
            ));

            $dnsRecords = $dnsRecords['cpanelresult']['data'];
            
            $dns_with_mail_in_name = [];

            foreach ($dnsRecords as $dnsRecord) {

                if (array_key_exists('name', $dnsRecord) && strpos($dnsRecord['name'], 'mail') !== false) {
                    $dns_with_mail_in_name[] = $dnsRecord;
                }
            }

            foreach ($dns_with_mail_in_name as $dns) {

                $rets[] = $dns;
                $rets[] = $cpanel->execute_action(2, 'ZoneEdit', 'remove_zone_record', 'yopla', array(
                    'domain' => $domain,
                    'line' => $dns['line']
                ));
            }
        } while (count($dns_with_mail_in_name) != 0);

        return ($rets);
    }

    function getMailGunDns($domain)
    {
        $mg = new \spamtonprof\stp_api\MailGunManager();

        $all_dns = [];

        $dnss = array_merge($mg->getOutBoundDns($domain), $mg->getInBoundDns($domain));

        foreach ($dnss as $dns) {

            $dns_ar = [];

            $dns_ar['name'] = $dns->getName();
            $dns_ar['value'] = $dns->getValue();
            $dns_ar['type'] = $dns->getType();
            $dns_ar['priority'] = $dns->getPriority();
            $dns_ar['validity'] = $dns->getValidity();
            $dns_ar['domain'] = $domain;

            $all_dns[] = $dns_ar;
        }

        return ($all_dns);
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

        shuffle($domains);

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

    
    