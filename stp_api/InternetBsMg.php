<?php
namespace spamtonprof\stp_api;

class InternetBsMg

{

    private $api_url = "http://testapi.internet.bs/", $api_key = "testapi", $password = "testpass", $call_url = "", $command, $base_url;

    // Instance de PDO
    public function __construct($test_mode = true)

    {
        if (! $test_mode) {
            $this->api_url = "https://api.internet.bs/";
            $this->api_key = INTERNET_BS_API_KEY;
            $this->password = INTERNET_BS_PASS;
        }

        $this->base_url = $this->api_url . "[command]?apikey=" . $this->api_key . '&password=' . urlencode($this->password);
    }

    public function set_planet_hoster_ns($domains)
    {
        $datas = [];
        foreach ($domains as $domain) {

            $datas[] = $this->DomainUpdate($domain, array(
                "hybrid2313.fr.ns.planethoster.net",
                "hybrid2313-1.fr.ns.planethoster.net"
            ));
        }

        prettyPrint($datas);
    }

    public function DomainUpdate($domain, $ns_list = [])
    {
        $command = "Domain/Update";

        $params = [];
        $params["domain"] = $domain;

        if (count($ns_list) != 0) {
            $params['ns_list'] = implode(",", $ns_list);
        }

        $ret = $this->call($command, $params);

        return ($ret);
    }

    // $type can be A, AAAA, DYNAMIC, CNAME, MX, SRV, TXT and NS
    public function addDnsRecord($domain, $type, $value)
    {
        $command = "Domain/DnsRecord/Add";

        $ret = $this->call($command, [
            'FullRecordName' => $domain,
            'Type' => $type,
            'Value' => $value
        ]);

        return ($ret);
    }

    public function removeAllDnsRecord($domain)
    {
        $allDns = $this->listAllDns($domain);
        
        $total_records = $allDns->total_records;
        
        if($total_records == 0){
            return;
        }
        
        $allDns = $allDns->records;
        foreach ($allDns as $dns) {

            $name = $dns->name;
            $value = $dns->value;
            $type = $dns->type;

            if ($type != 'NS') {

                
                $ret = $this->removeDnsRecord($domain, $type, $value);
                
                
                $ret = json_decode($ret);
                
                print_r($ret);
                
                
            }
        }
    }

    public function removeDnsRecord($domain, $type, $value = false)
    {
        $command = "Domain/DnsRecord/Remove";

        
        $params = [];
        
        $params['FullRecordName'] = $domain;
        
            $params['Type'] = $type;
        
        if($value){
            $params['Value'] = $value;
        }
        
        $ret = $this->call($command, $params);

        return ($ret);
    }

    public function listAllDns($domain)
    {
        $command = "Domain/DnsRecord/List";

        $ret = $this->call($command, array(
            'Domain' => $domain
        ));

        return (json_decode($ret));
    }

    public function DomainList()
    {
        $command = "Domain/List";

        $ret = $this->call($command);

        return ($ret);
    }

    public function call($command, $params = [])
    {
        $this->call_url = str_replace("[command]", $command, $this->base_url);

        $params['ResponseFormat'] = 'JSON';

        if (count($params) != 0) {

            $params = http_build_query($params);
            $this->call_url = $this->call_url . "&" . $params;
        }

        $ch = curl_init($this->call_url); // such as http://example.com/example.xml
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        
        return ($data);
    }
}