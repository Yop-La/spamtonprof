<?php
namespace spamtonprof\stp_api;

class InternetBsMg

{

    private $api_url = "http://testapi.internet.bs/", $api_key = "testapi", $password = "testpass", $call_url = "", $command;

    // Instance de PDO
    public function __construct($test_mode = true)

    {
        if (! $test_mode) {
            $this->api_url = "https://api.internet.bs/";
            $this->api_key = INTERNET_BS_API_KEY;
            $this->password = INTERNET_BS_PASS;
        }

        $this->call_url = $this->api_url . "[command]?apikey=" . $this->api_key . '&password=' . urlencode($this->password);
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

    public function DomainList()
    {
        $command = "Domain/List";

        $ret = $this->call($command);

        return ($ret);
    }

    public function call($command, $params = [])
    {
        $this->call_url = str_replace("[command]", $command, $this->call_url);

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