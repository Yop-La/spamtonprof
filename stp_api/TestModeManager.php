<?php
namespace spamtonprof\stp_api;

use PDO;
use DateTime;
use Exception;
/*
 * Cette classe sert à gérér ( CRUD ) les plans de paiement paypal
 * attention un billing plan ( equivalent service/produit dans stripe ) ne peut avoir qu'un seul type de paiement definition regulier
 * ainsi ici un billing plan est équivalent à un plan dans stp
 *
 *
 * // $host_split = explode('.',$_SERVER['HTTP_HOST']);
 * // $testMode = ($host_split[0] == 'localhost' || $host_split[0] == 'localhost:8081') && $host_split[1] == '' ? TRUE : FALSE;
 * // $domain = $testMode ? 'http://localhost/' : 'https://www.spamtonprof.com/';
 *
 *
 *
 *
 *
 */
class TestModeManager

{

    static $pagesSlugInTest = [];

    private $testMode;

    public function __construct($param) //$param can be true of false or pageslug
    
    {
        if ($param == "true") {
            $this->testMode = true;
        } elseif ($param == "false") {
            $this->testMode = false;
        } else {
            if (in_array($param, TestModeManager::$pagesSlugInTest)) {
                $this->testMode = true;
            } else {
                $this->testMode = false;
            }
        }
    }

    public function initDebuger()
    {
        if ($this->testMode) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
    }
    

    /**
     *
     * @return boolean
     */
    public function testMode()
    {
        return $this->testMode;
    }

    public function getPublicStripeKey()
    {
        if ($this->testMode) {
            return TEST_PUBLIC_KEY_STRP;
        } else {
            return PROD_PUBLIC_KEY_STRP;
        }
    }

    public function getSecretStripeKey()
    {
        if ($this->testMode) {
            return TEST_SECRET_KEY_STRP;
        } else {
            return PROD_SECRET_KEY_STRP;
        }
    }
}

    
    