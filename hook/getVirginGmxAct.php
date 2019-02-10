<?php
/**
 * 
 *  ppour générer un compte lbc avant publication d'annonces par zenno ( en prod )
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



if ($_POST['password'] == HOOK_SECRET) {

    $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
    $slack = new \spamtonprof\slack\Slack();
    
    do {
        
        $gmxAct = $gmxActMg->get(array(
            'virgin'
        ));
        
        $email = $gmxAct->getMail();
        
        $smtpServer = new \spamtonprof\stp_api\SmtpServer(array(
            'host' => 'mail.gmx.com',
            'port' => 587,
            'password' => $gmxAct->getPassword(),
            'username' => $email
        ));
        
        $send = $smtpServer->sendEmail('Smtp de ' . $email . ' bien activé', 'soutien.par.mail@gmail.com', 'Smtp de ' . $email . ' bien actif', $email, 'Spammy', false);
        
        if (! $send) {
            
            $slack->sendMessages('log', array(
                'Smtp inactif sur ce compte gmx : ' . $email . '. Il faut l\'activer '
            ));
            
            $gmxAct->setSmtp_enabled(false);
            $gmxActMg->updateHasRedirection($gmxAct);
        }
    } while (! $send);
    
    
    

    $ret = new \stdClass();
    $ret->gmx_act = $gmxAct;

    prettyPrint($ret);
}