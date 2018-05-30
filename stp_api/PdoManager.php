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
//                 to_log_slack(array("str1" => "connection bdd" ));
            } catch (Exception $e) {
                to_log_slack(array("str1" => "error connection bdd" . $e->getMessage()));
                echo ("fuck");
            }
        }
        return self::$bdd;
    }
}

