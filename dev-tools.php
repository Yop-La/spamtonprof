<?php

function formatNums($nums)
{
    for ($i = 0; $i < count($nums); $i ++)
        foreach ($nums as $num) {
            $num = $nums[$i];
            $num = str_replace([
                " ",
                ".",
                "/",
                "\\",
                "-"
            ], "", $num);
            $nums[$i] = $num;
        }
    return ($nums);
}

function formatNum($num)
{
    $num = str_replace([
        " ",
        ".",
        "/",
        "\\",
        "-"
    ], "", $num);
    return ($num);
}

function isNotNull($var)
{
    return (! is_null($var));
}

function printInfoCompteTest()
{
    $accountManager = new \spamtonprof\stp_api\AccountManager();

    $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();

    $comptesTest = array(

        868,

        867
    );

    foreach ($comptesTest as $compteTest) {

        $account = $accountManager->get($compteTest);

        echo ("<br>-------- compte n° " . $account->ref_compte() . "------ <br>" . "<br> ");

        echo ("prenom eleve : " . $account->eleve()->prenom() . "<br> ");

        echo ("statut : " . $account->statut() . "<br> ");

        echo ("attente paiement : " . $account->attente_paiement() . "<br>");

        echo ("maths : " . $account->maths() . "physique : " . $account->physique() . "francais : " . $account->francais() . "<br>");

        echo ("email eleve  : ");

        $contacts = $getResponseManager->getContact($account->eleve());

        foreach ($contacts as $contact) {

            echo ($contact->campaign->name . " - ");
        }

        echo ("<br>");

        // echo($contacts["campaign"]);

        echo ("email parent  : ");

        $contacts = $getResponseManager->getContact($account->proche());

        foreach ($contacts as $contact) {

            echo ($contact->campaign->name . " - ");
        }

        echo ("<br>");
    }
}

function resetCompteTest()

{
    $accountManager = new \spamtonprof\stp_api\AccountManager();

    $getResponseManager = new \spamtonprof\stp_api\GetResponseManager();

    $comptesTest = array(

        868,

        867
    );

    foreach ($comptesTest as $compteTest) {

        $account = $accountManager->get($compteTest);

        $account->setStatut("essai");

        $account->setAttente_paiement(true);

        $accountManager->updateAfterSubsCreated($account);

        $getResponseManager->resetToEssai($account);
    }

    echo ("reset des comptes de test over ! ");
}

/**
 *
 * @param Object $object
 *            pour bien indenter en json l'objet à l'écran
 */
function prettyPrint($object)
{
    header('Content-Type: application/json');

    echo (json_encode($object, JSON_PRETTY_PRINT));

    exit(0);
}

function serializeTemp($object, $file = "/tempo/tempoObject", $rel = true)
{
    $s = serialize($object);
    if ($rel) {
        $file = dirname(__FILE__) . $file;
    }

    file_put_contents($file, $s);
}

function unserializeTemp($file = "/tempo/tempoObject", $rel = true)
{
    if ($rel) {
        $file = dirname(__FILE__) . $file;
    }

    if (file_exists($file)) {
        $s = file_get_contents($file);
        $a = unserialize($s);
        return ($a);
    } else {
        return (false);
    }
}

function toUtf8(array $arr)
{
    for ($i = 0; $i < count($arr); $i ++) {
        $value = $arr[$i];
        $encoding = mb_detect_encoding($value, 'UTF-8', true);

        if (! $encoding) {
            $arr[$i] = utf8_encode($value);
        }
    }
    return ($arr);
}

function prettyPrintArray(array $arr)
{
    echo ("<pre>");

    print_r($arr);

    echo ("</pre>");

    exit(0);
}

function saveArrayAsCsv($array, $filename, $delimiter = ";")
{
    $f = fopen($filename, 'w');

    // loop over the input array
    foreach ($array as $line) {

        if (is_object($line)) {
            $line = $line->__toString();
            $line = array(
                $line
            );
        }
        // generate csv lines from the inner arrays

        fputcsv($f, $line, $delimiter);
    }

    fclose($f);
}

function call($url, $http_method = 'GET', $params = array(), $async = null)
{
    if ($http_method == 'GET') {
        $url = $url . "?" . http_build_query($params);
    }

    $params = json_encode($params);

    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_ENCODING => 'gzip,deflate',
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => 'SpamTonProf'
    );

    if ($async) {
        $options[CURLOPT_TIMEOUT_MS] = 1000;
    }

    if ($http_method == 'POST') {
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $params;
    } else if ($http_method == 'DELETE') {
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    }
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $response = json_decode(curl_exec($curl));
    curl_close($curl);
    return (object) $response;
}

/* pour décoder le body des messages de gmail */
function base64url_decode($base64url)
{
    $base64 = strtr($base64url, '-_', '+/');
    $plainText = base64_decode($base64);
    return ($plainText);
}

// pour importer des plans de paiements depuis un csv vers la table stp_plan_paiement
function importPlanPaiementFromCsv()
{
    $StpPlanMg = new \spamtonprof\stp_api\StpPlanManager();

    $row = 0;
    if (($handle = fopen("formules_plan_paiements.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $row ++;

            $tarif = $data[3];

            if ($tarif != "" and $row != 1) {

                $arrPlan = array(
                    "nom" => $data[2],
                    "tarif" => $tarif,
                    "ref_formule" => $data[0],
                    "ref_plan_old" => $data[4]
                );

                $StpPlan = new \spamtonprof\stp_api\StpPlan($arrPlan);

                echo (json_encode($StpPlan));

                echo ("<br>");

                $StpPlan = $StpPlanMg->add($StpPlan);

                if ($StpPlan->getRef_plan_old() != "") {

                    $StpPlanMg->updateRefPlanOld($StpPlan);
                }
            }
        }
        fclose($handle);
    }
}

// pour générer des classes et des managers
// example :
// $tableName = 'stp_eleve';
// $path = dirname(__FILE__) . "/wp-content/plugins/spamtonprof/stp_api";
// $nameSpace = 'spamtonprof\stp_api';
// generateClassAndManager($tableName, $path, $nameSpace);
function generateClassAndManager($tableName, $path, $nameSpace)
{
    $classeNameParts = explode('_', $tableName);

    $classeName = $classeNameParts[0];

    for ($i = 1; $i < count($classeNameParts); $i ++) {

        $classeNamePart = $classeNameParts[$i];

        $classeName = $classeName . ucfirst($classeNamePart);
    }

    $fileName = $classeName . '.php';
    $fileNameMg = $classeName . 'Manager.php';

    $pdoMg = new \spamtonprof\stp_api\PdoManager();

    $bdd = $pdoMg->getBdd();

    $q = $bdd->prepare("SELECT column_name FROM information_schema.columns
    WHERE table_name   = :table_name");

    $q->bindValue(':table_name', $tableName);

    $q->execute();

    $columns = [];

    while ($data = $q->fetch()) {

        $columns[] = $data['column_name'];
    }

    $nbColumns = count($columns);

    $pathFile = $path . '/' . ucfirst($fileName);
    $pathFileMg = $path . '/' . ucfirst($fileNameMg);

    echo ($pathFile . "<br>");
    echo ($pathFileMg . "<br>");

    /* écriture de la classe */

    file_put_contents($pathFile, '<?php' . PHP_EOL);
    file_put_contents($pathFile, 'namespace ' . $nameSpace . ';' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFile, 'class ' . $classeName . ' implements \JsonSerializable' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFile, '{' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFile, 'protected' . PHP_EOL, FILE_APPEND);
    for ($i = 0; $i < $nbColumns; $i ++) {

        $column = $columns[$i];

        if ($i == $nbColumns - 1) {
            file_put_contents($pathFile, '$' . $column . ';' . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents($pathFile, '$' . $column . ', ' . PHP_EOL, FILE_APPEND);
        }
    }

    file_put_contents($pathFile, ' public function __construct(array $donnees = array()) { $this->hydrate($donnees); } public function hydrate(array $donnees) { foreach ($donnees as $key => $value) { $method = "set" . ucfirst($key); if (method_exists($this, $method)) { $this->$method($value); } } }', FILE_APPEND);
    for ($i = 0; $i < $nbColumns; $i ++) {

        $column = $columns[$i];

        // getters
        file_put_contents($pathFile, 'public function get' . ucfirst($column) . '()' . PHP_EOL, FILE_APPEND);
        file_put_contents($pathFile, '{' . PHP_EOL, FILE_APPEND);
        file_put_contents($pathFile, 'return $this->' . $column . ';' . PHP_EOL, FILE_APPEND);
        file_put_contents($pathFile, '}' . PHP_EOL, FILE_APPEND);

        // setters
        file_put_contents($pathFile, 'public function set' . ucfirst($column) . "($$column)" . PHP_EOL, FILE_APPEND);
        file_put_contents($pathFile, '{' . PHP_EOL, FILE_APPEND);
        file_put_contents($pathFile, '$this->' . $column . " = $$column;" . PHP_EOL, FILE_APPEND);
        file_put_contents($pathFile, '}' . PHP_EOL, FILE_APPEND);
    }

    file_put_contents($pathFile, ' public function jsonSerialize() { $vars = get_object_vars($this); return $vars; }', FILE_APPEND);
    file_put_contents($pathFile, '}', FILE_APPEND);

    /* fin écriture de la classe */

    /* écriture du manager */
    file_put_contents($pathFileMg, '<?php' . PHP_EOL);
    file_put_contents($pathFileMg, 'namespace ' . $nameSpace . ';' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, 'class ' . $classeName . 'Manager ' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, ' { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } ', FILE_APPEND);

    // fonction add
    file_put_contents($pathFileMg, "public function add($classeName $" . lcfirst($classeName) . "){" . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '$q = $this->_db->prepare(' . "'insert into " . $tableName . "(", FILE_APPEND);

    for ($i = 0; $i < $nbColumns; $i ++) {

        $column = $columns[$i];

        if ($i == $nbColumns - 1) {
            file_put_contents($pathFileMg, $column . ') values( ', FILE_APPEND);
        } else {
            file_put_contents($pathFileMg, $column . ', ', FILE_APPEND);
        }
    }

    for ($i = 0; $i < $nbColumns; $i ++) {

        $column = $columns[$i];

        if ($i == $nbColumns - 1) {
            file_put_contents($pathFileMg, ':' . $column . ")');", FILE_APPEND);
        } else {
            file_put_contents($pathFileMg, ':' . $column . ',', FILE_APPEND);
        }
    }

    for ($i = 0; $i < $nbColumns; $i ++) {

        $column = $columns[$i];

        file_put_contents($pathFileMg, '$q->bindValue(\':' . $column . '\', ' . '$' . lcfirst($classeName) . '->' . 'get' . ucfirst($column) . '());', FILE_APPEND);
    }
    file_put_contents($pathFileMg, '$q->execute();' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '//-----------------  à finir ----------------' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '//-----------------' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '$' . lcfirst($classeName) . '->' . 'set' . ucfirst('ref_') . '($this->_db->lastInsertId());' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '//-----------------  à finir ----------------' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '//-----------------' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, 'return (' . '$' . lcfirst($classeName) . ');}' . PHP_EOL, FILE_APPEND);
    file_put_contents($pathFileMg, '}' . PHP_EOL, FILE_APPEND);

    /* fin écriture du manager */
}

function extractFirstMail($string)
{
    $pattern = "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i";
    $emails = [];
    preg_match_all($pattern, $string, $emails);
    return ($emails[0][0]);
}

function toSimilarTo(array $elements)
{
    $nbElem = count($elements);

    $retour = "";

    for ($i = 0; $i < $nbElem; $i ++) {
        $element = $elements[$i];

        if ($i == $nbElem - 1) {
            $retour = $retour . '%' . $element . '%';
        } else {
            $retour = $retour . '%' . $element . '%' . '|';
        }
    }
    return ($retour);
}

function toPgArray(array $elements, $parenthese = false)
{
    $rBracket = "}";
    $lBracket = "{";

    if ($parenthese) {
        $rBracket = ")";
        $lBracket = "(";
    }

    $nbElem = count($elements);
    $arrayPar = "";

    for ($i = 0; $i < $nbElem; $i ++) {
        $element = $elements[$i];

        if ($i == 0) {

            $arrayPar = $arrayPar . $lBracket;
        }
        if ($i == $nbElem - 1) {

            $arrayPar = $arrayPar . $element . $rBracket;
        } else {
            $arrayPar = $arrayPar . $element . ", ";
        }
    }
    return ($arrayPar);
}

function extractAttribute(array $objects, string $attribute)
{
    $retour = [];

    foreach ($objects as $object) {

        $object = json_decode(json_encode($object), true);

        $retour[] = $object[$attribute];
    }
    return ($retour);
}

function readCsv($filePath)
{
    $rows = [];

    $row = 1;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            $row ++;
            $row = [];
            for ($c = 0; $c < $num; $c ++) {
                $row[] = $data[$c];
            }
            $rows[] = $row;
        }
        fclose($handle);
        return ($rows);
    }
}

function printGmailColors()
{
    $cols = [
        "#000000",
        "#434343",
        "#666666",
        "#999999",
        "#cccccc",
        "#efefef",
        "#f3f3f3",
        "#ffffff",
        "#fb4c2f",
        "#ffad47",
        "#fad165",
        "#16a766",
        "#43d692",
        "#4a86e8",
        "#a479e2",
        "#f691b3",
        "#f6c5be",
        "#ffe6c7",
        "#fef1d1",
        "#b9e4d0",
        "#c6f3de",
        "#c9daf8",
        "#e4d7f5",
        "#fcdee8",
        "#efa093",
        "#ffd6a2",
        "#fce8b3",
        "#89d3b2",
        "#a0eac9",
        "#a4c2f4",
        "#d0bcf1",
        "#fbc8d9",
        "#e66550",
        "#ffbc6b",
        "#fcda83",
        "#44b984",
        "#68dfa9",
        "#6d9eeb",
        "#b694e8",
        "#f7a7c0",
        "#cc3a21",
        "#eaa041",
        "#f2c960",
        "#149e60",
        "#3dc789",
        "#3c78d8",
        "#8e63ce",
        "#e07798",
        "#ac2b16",
        "#cf8933",
        "#d5ae49",
        "#0b804b",
        "#2a9c68",
        "#285bac",
        "#653e9b",
        "#b65775",
        "#822111",
        "#a46a21",
        "#aa8831",
        "#076239",
        "#1a764d",
        "#1c4587",
        "#41236d",
        "#83334c"
    ];

    foreach ($cols as $col) {
        echo ('<div style="background-color:' . $col . '";padding=5px;>' . $col . '</div>');
    }
}

function addLabelToAllProf($labelName, $hexColor)
{
    $first = null;
    $mailProf = null;
    $slack = new \spamtonprof\slack\Slack();
    
    $lock = false;
    
    do {
        
        $profMg = new \spamtonprof\stp_api\StpProfManager();
        
        $prof = $profMg->getNextInboxToProcess();
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $prof->setProcessing_date($now->format(PG_DATETIME_FORMAT));
        $profMg->updateProcessingDate($prof);
        
        if (! $lock) {
            $lock = true;
            $first = $prof->getEmail_stp();
            echo ('premier ' . $first . '<br>');
            $slack->sendMessages('log', array(
                'first',
                $first
            ));
        } else {
            $mailProf = $prof->getEmail_stp();
            echo ($mailProf . '<br>');
            $slack->sendMessages('log', array(
                'next',
                $mailProf
            ));
        }
        $gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
        $gmailAccount = $gmailAccountMg->get($prof->getRef_gmail_account());
        
        $gmail = new spamtonprof\gmailManager\GmailManager($gmailAccount->getEmail());
        
        $gmail->createLabel($labelName, $hexColor);
    } while ($first != $mailProf);
}

