<?php
namespace spamtonprof\stp_api;

use PDO;
use Exception;

class PdoManager

{

    public static $bdd = null;

    public static function getBdd()

    {
        $slack = new \spamtonprof\slack\Slack();
        
        if (is_null(PdoManager::$bdd)) {

            try {
                $probleme_client = false;
                if (defined('PROBLEME_CLIENT')) {
                    $probleme_client = true;
                }

                if (LOCAL && !$probleme_client) {
                    
                    self::$bdd = new PDO('pgsql:host=' . DB_HOST_PG_LOCAL . ';port=5432;application_name=stp;dbname=' . DB_NAME_PG_LOCAL . ';user=' . DB_USER_PG_LOCAL . ';password=' . DB_PASSWORD_PG_LOCAL);
                    
                    
                } else {
                    
                    self::$bdd = new PDO('pgsql:host=' . DB_HOST_PG . ';port=5432;application_name=stp;dbname=' . DB_NAME_PG . ';user=' . DB_USER_PG . ';password=' . DB_PASSWORD_PG);
                    
                    
                }

                self::$bdd->exec("SET TIME ZONE 'Europe/Paris';");
            } catch (Exception $e) {


                $slack->sendMessages("log", array(
                    "error connection bdd : " . $e->getMessage()
                ));

            }
        }

        return self::$bdd;
    }
    

}

