<?php
/**
 * 
 *  pour recevoir les hooks de stripe en mode prof
 *  Voil� les hooks re�us :
 *  - invoice.payment_succeeded pour transf�rer les fonds au prof
 *  
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

$slack = new \spamtonprof\slack\Slack();

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

$appelMg = new \spamtonprof\stp_api\StpAppelManager();

if ($event_json->direction == "Inbound") {

    if ($event_json->state == "missed") {
        $from = formatNum($event_json->from_number);
        $to = formatNum($event_json->to_number);

        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

        $stpAppel = new \spamtonprof\stp_api\StpAppel(array(
            "to" => $to,
            "from" => $from,
            "date" => $now->format(PG_DATETIME_FORMAT)
        ));
        
        // on enregistre l'appel
        $appelMg->add($stpAppel);
        
        // on v�rifie que le num est pas d�j� en base
        $appels = $appelMg -> getAll(array("from" => $from));
        
        if(!empty($appels)){
            $slack->sendMessages("missed-call", array("sms d�j� envoy� auparavant"));
            prettyPrint(true);
        }
        

        // notif slack
        $slack->sendMessages("missed-call", array(
            "appel manqu� du : " . $from,
            "vers : " . $to
        ));

        // on d�termine si il faut envoyer un sms

        if (strpos($from, '+336') !== false || strpos($from, '+337') !== false) {

            $sms = false;
            switch ($to) {
                case '+33644607367':
                    $sms = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sms/elisabeth.txt");
                    break;
                case '+33644647599':
                    $sms = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sms/sebastien.txt");
                    break;
            }

            if (! $sms) {
                $slack->sendMessages("missed-call", array(
                    "impossible de trouver le sms � envoyer depuis : " . $to
                ));
            } else {
                // $sms = utf8_encode($sms);
                $toky = new \spamtonprof\stp_api\Toky();

                $retour = $toky->sendSms($to, $from, $sms);

                serializeTemp($retour);
                
                if ($retour["success"]) {
                    $slack->sendMessages("missed-call", array(
                        "sms envoy� !"
                    ));
                } else {
                    $slack->sendMessages("missed-call", array(
                        "erreur envoi sms"
                    ));
                }
            }
        } else {
            $slack->sendMessages("missed-call", array(
                "pas de sms � envoyer : c'est un fixe !!"
            ));
        }
    }
}
prettyPrint(true);