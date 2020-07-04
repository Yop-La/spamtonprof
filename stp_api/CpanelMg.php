<?php
namespace spamtonprof\stp_api;

class CpanelMg

{

    private $APITOKEN = "OGNKRC51XOO4IR9H6QY7PE5POVMS4UG3";

    private $username = "yopla";

    public function __construct()

    {}

    public function call($module, $function, $params = false)
    {
        $ch = curl_init();

        if ($params) {
            $params = "?$params";
        } else {
            $params = "";
        }

        curl_setopt($ch, CURLOPT_URL, "https://hybrid2313.fr.ns.planethoster.net:2083/execute/$module/$function$params");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers = array();
        $headers[] = "Authorization: cpanel $this->username:$this->APITOKEN";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return ($result);
    }

    public function list_mysql_db()
    {
        $module = "Mysql";
        $function = "list_databases";

        $res = $this->call($module, $function);

        $res = json_decode($res);

        $dbs = $res->data;

        $ret = [];
        foreach ($dbs as $db) {
            $ret[] = $db->database;
        }

        return ($ret);
    }

    public function create_data_base($dbname)
    {
        $module = "Mysql";
        $function = "create_database";

        $dbname = "yopla_" . str_replace(".", "_", $dbname);

        $dbs = $this->list_mysql_db();

        foreach ($dbs as $db) {

            if ($db == $dbname) {
                $this->delete_database($dbname);
            }
        }

        $params = array(
            'name' => $dbname
        );

        $params = http_build_query($params);

        $this->call($module, $function, $params);

        return ($dbname);
    }

    public function create_data_base_with_admin($dbname, $password)
    {
//         $this->delete_user($dbname);

//         $this->delete_database($dbname);

        $db_name = $this->create_data_base($dbname);

        $password = $this->create_db_user($db_name, $password);

        $this->set_privileges_on_database($db_name, $db_name);

        return (array(
            'db_name' => $db_name,
            'password' => $password
        ));
    }

    public function delete_user($name)
    {
        $module = "Mysql";
        $function = "delete_user";

        $params = array(
            'name' => $name
        );

        $params = http_build_query($params);

        $this->call($module, $function, $params);
    }

    public function delete_database($name)
    {
        $module = "Mysql";
        $function = "delete_database";

        $params = array(
            'name' => $name
        );

        $params = http_build_query($params);

        $this->call($module, $function, $params);
    }

    public function list_users()
    {
        $module = "Mysql";
        $function = "list_users";

        $params = [];
        $params = http_build_query($params);

        $ret = $this->call($module, $function, $params);

        $res = json_decode($ret);

        $dbs = $res->data;

        $ret = [];
        foreach ($dbs as $db) {
            $ret[] = $db->user;
        }

        return ($ret);
    }

    public function ad_domain($domain, $rootdomain)
    {
        $module = "SubDomain";
        $function = "addsubdomain";

        $params = array(
            'domain' => $domain,
            'rootdomain' => $rootdomain,
            'dir' => "/" . $rootdomain . '.' . $domain
        );

        $params = http_build_query($params);

        $ret = $this->call($module, $function, $params);

        return ($ret);
    }

    // public function add_dns_record($domain, $value)
    // {
    // $module = "ZoneEdit";
    // $function = "add_zone_record";

    // $params = array(
    // 'domain' => $domain,
    // 'name' => "",
    // 'type' => 'TXT',
    // 'txtdata' => $value
    // );

    // $params = http_build_query($params);

    // $ret = $this->call($module, $function, $params);

    // prettyPrint($ret);

    // return ($ret);
    // }
    public function ad_sub_domain($domain, $rootdomain)
    {
        $module = "SubDomain";
        $function = "addsubdomain";

        $params = array(
            'domain' => $domain,
            'rootdomain' => $rootdomain,
            'dir' => "/" . $rootdomain . '.' . $domain
        );

        $params = http_build_query($params);

        $ret = $this->call($module, $function, $params);

        return ($ret);
    }

    public function create_db_user($name, $password)
    {
        $module = "Mysql";
        $function = "create_user";

        $users = $this->list_users();

        foreach ($users as $user) {

            if ($user == $name) {
                $this->delete_user($name);
            }
        }

        $params = array(
            'name' => $name,
            'password' => $password
        );

        $params = http_build_query($params);

        $this->call($module, $function, $params);

        return ($password);
    }

    public function set_privileges_on_database($user, $db)
    {
        $module = "Mysql";
        $function = "set_privileges_on_database";

        $params = array(
            'user' => $user,
            'database' => $db,
            'privileges' => "ALL PRIVILEGES"
        );

        $params = http_build_query($params);

        $res = $this->call($module, $function, $params);

        return ($res);
    }

    public function start_wp_backup($url = "template.aafhaiti.org")
    {
        $module = "WordPressBackup";
        $function = "start";

        $params = array(
            'site' => $url
        );

        $params = http_build_query($params);

        $res = $this->call($module, $function, $params);

        return ($res);
    }

    public function add_sub_domain($subdomain, $domain)
    {
        $module = "SubDomain";
        $function = "addsubdomain";

        $dir = $subdomain . '.' . $domain;

        $params = array(
            'domain' => $subdomain,
            'rootdomain' => $domain,
            'dir' => $dir
        );

        $params = http_build_query($params);

        $res = $this->call($module, $function, $params);

        return ($res);
    }
}