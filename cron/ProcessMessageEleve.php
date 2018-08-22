<?php
use spamtonprof\slack\Slack;

/**
 * pour la boite de seb - adaption possible sur d'autres boites ( voir la "Tracking - Labels gmail api" dans evernote pour mise en place )
 * il ne traque que les emails d'�l�ve ( pas les mails des �tudiants et des parents )
 *
 * ce script sert :
 * - � stocker dans mail eleve - les messages des �l�ves
 * - � attribuer des libell�es aux emails
 * - il tourne tous les 5 minutes
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

$profMg = new \spamtonprof\stp_api\StpProfManager();

$prof =  $profMg -> getNextInboxToProcess();

$gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
$gmailAccount = $gmailAccountMg->get($prof->getRef_gmail_account());

$gmailManager = new spamtonprof\gmailManager\GmailManager($gmailAccount->getEmail());

$slack -> sendMessages("message-eleve", array(" ----- ","Lecture de : " . $gmailAccount->getEmail()));

$MessEleveMg = new \spamtonprof\stp_api\StpMessageEleveManager();

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
$eleveMg = new \spamtonprof\stp_api\StpEleveManager();

// gestion last history id

$lastHistoryId = $gmailAccount->getLast_history_id();

$retour = $gmailManager->getNewMessages($lastHistoryId);

$messages = $retour["messages"];
$lastHistoryId = $retour["lastHistoryId"];

$gmailAccount->setLast_history_id($lastHistoryId);
$gmailAccountMg->updateHistoryId($gmailAccount);

foreach ($messages as $message) {
    
    $gmailId = $message->id;
    
    $from = extractFirstMail($gmailManager->getHeader($message, "From"));
    $snippet = $message->snippet;
    $subject = $gmailManager->getHeader($message, "Subject");
    $date = $gmailManager->getHeader($message, "Date");
    // $body = $gmailManager->getBody($message, "html");
    
    $dateReception = new \DateTime($date);
    $dateReception->setTimezone(new \DateTimeZone("Europe/Paris"));
    
    $eleve = false;
    $eleve = $eleveMg->get(array(
        "email" => $from
    ));
    

    echo ("mail : " . $from . " -- date reception : " . $dateReception->format(PG_DATETIME_FORMAT) . " -- message id : " . $gmailId . "<br><br>");

    
    if ($eleve) {
        
        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_statut_abonnement'
            ),
            "ref_eleve" => array(
                "construct" => array(
                    'ref_classe'
                )
            )
        );
        
        $abos = $aboMg->getAll(array(
            "ref_eleve" => $eleve->getRef_eleve(),
            "ref_prof" => $prof->getRef_prof()
        ), $constructor);
        
        $nbAbos = count($abos);
    
        $labelsNameToAdd = [];
        switch ($nbAbos) {
            case 0:
                $labelsNameToAdd[] = 'error_pas_formule';
                break;
            case 2:
                $labelsNameToAdd[] = 'error_double_formule';
                break;
            case 1:
    
                $abo = $abos[0];
                
      
                // sauvegarder le message
                $MessEleveMg->add(new \spamtonprof\stp_api\StpMessageEleve(array(
                    'ref_abonnement' => $abo->getRef_abonnement(),
                    'date_message' => $dateReception->format(PG_DATETIME_FORMAT),
                    'ref_gmail' => $gmailId,
                    'mail_expe' => $from
                )));
                
                // attribuer les libell�es
                
                $eleve = $abo->getEleve();
                $statut = $abo->getStatut();
                
                $classe = \spamtonprof\stp_api\StpClasse::cast($eleve->getClasse());
                $statut = \spamtonprof\stp_api\StpStatutAbonnement::cast($statut);
                
                $slack -> sendMessages("message_eleve", array(" ---- ","Nouveau message de : " .$eleve->getPrenom(), $classe->getNom_complet(), $gmailId, $dateReception->format(PG_DATETIME_FORMAT), "Avec ".$prof->getPrenom()));
                
                
                $labelsNameToAdd[] = $classe->getClasse();
                $labelsNameToAdd[] = $statut->getStatut_abonnement();
                
                // mettre � jour la date de dernier contact
                $abo->setDernier_contact($dateReception->format(PG_DATETIME_FORMAT));
                $aboMg->updateDernierContact($abo);
                
                break;
        }
        
        // attribuer les libell�s s
        $labelsToAdd = $gmailManager->getCustomLabelsToAdd($labelsNameToAdd);
                
        $gmailManager->modifyMessage($gmailId, $labelsToAdd, array());
    }
}

exit(0);

