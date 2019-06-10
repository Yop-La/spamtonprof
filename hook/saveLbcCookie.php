<?php
use FastRoute\RouteParser\Std;

/**
 * pour sauvergarder le cookie leboncoin d'un compte lbc
 */
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// voir "r�cup�rer toutes les annonces d'un compte leboncoin" dans evernote - en prod - date cr�ation : 08/10/2018

if (! function_exists('http_parse_headers')) {

    function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = ''; // [+]
        foreach (explode("\n", $raw_headers) as $h) {
            $h = explode(':', $h, 2);
            if (isset($h[1])) {
                if (! isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } elseif (is_array($headers[$h[0]])) {
                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(
                        trim($h[1])
                    )); // [+]
                } else {
                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge(array(
                        $headers[$h[0]]
                    ), array(
                        trim($h[1])
                    )); // [+]
                }
                $key = $h[0]; // [+]
            } else {
                if (substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
                } elseif (! $key) {
                    $headers[0] = trim($h[0]);
                    trim($h[0]);
                }
            }
        }
        return $headers;
    }
}
if (! function_exists('http_parse_cookie')) {

    function http_parse_cookie($szHeader, $object = true)
    {
        $obj = new stdClass();
        $arrCookie = array();
        $arrObj = array();
        $arrCookie = explode("\n", $szHeader);
        for ($i = 0; $i < count($arrCookie); $i ++) {
            $cookie = $arrCookie[$i];
            $attributes = explode(';', $cookie);
            $arrCookie[$i] = array();
            foreach ($attributes as $attrEl) {
                $tmp = explode('=', $attrEl, 2);
                if (count($tmp) < 2) {
                    continue;
                }
                $key = trim($tmp[0]);
                $value = trim($tmp[1]);
                if ($key == 'version' || $key == 'path' || $key == 'expires' || $key == 'domain' || $key == 'comment') {
                    if (! isset($arrObj[$key])) {
                        $arrObj[$key] = $value;
                    }
                } else {
                    $arrObj['cookies'][$key] = $value;
                }
            }
        }
        if ($object === true) {
            $obj = (object) $arrObj;
            $return = $obj;
        } else {
            $return = $arrObj;
        }
        return $return;
    }
}

$ret = new \stdClass();
$ret->ret = "false";
if (array_key_exists("cookies", $_POST) && array_key_exists("ref_compte", $_POST)) {


    $cookies = base64_decode($cookies);


    $cookies = http_parse_cookie($cookies);

    if (property_exists($cookies, "cookies")) {
        $luat = $cookies->cookies['luat'];

        $lbcAcctMg = new \spamtonprof\stp_api\LbcAccountManager();
        $act = $lbcAcctMg->get(array(
            'ref_compte' => $ref_compte
        ));

        $act->setCookie($luat);
        $lbcAcctMg->updateCookie($act);

        $lbcApi = new \spamtonprof\stp_api\LbcApi();
        $userId = $lbcApi->getUserId($luat);

        $act->setUser_id($userId);
        $lbcAcctMg->updateUserId($act);

        $lbcAcctMg = new \spamtonprof\stp_api\LbcAccountManager();
        $act = $lbcAcctMg->get(array(
            'ref_compte' => $ref_compte
        ));

        if ($act->getCookie()) {
            $ret->ret = $act;
        }
    }
}

prettyPrint($ret);


