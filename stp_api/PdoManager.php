<?php
namespace spamtonprof\stp_api;

use PDO;
use Exception;

class PdoManager

{

    public static $bdd = null;

    public static function getBdd()
    
    {
        if (is_null(PdoManager::$bdd)) {
            
            try {
                
                self::$bdd = new PDO('pgsql:host=' . DB_HOST_PG . ';port=5432;application_name=stp;dbname=' . DB_NAME_PG . ';user=' . DB_USER_PG . ';password=' . DB_PASSWORD_PG);
                
//                 self::$bdd = new PDO('pgsql:host=' . DB_HOST_PG_LOCAL . ';port=5432;application_name=stp;dbname=' . DB_NAME_PG_LOCAL . ';user=' . DB_USER_PG_LOCAL . ';password=' . DB_PASSWORD_PG_LOCAL);
                
                // to_log_slack(array("str1" => "connection bdd" ));
            } catch (Exception $e) {
                
                $slack = new \spamtonprof\slack\Slack();
                
                $slack->sendMessages("log", array("error connection bdd : " . $e->getMessage()));
                
                echo ("echec connexion à la bdd");
            }
        }
        
        return self::$bdd;
    }
}

