<?php

function str_to_bool($bool_str)
{
    if ($bool_str == 'true') {
        return (true);
    }
    return (false);
}

function domain_to_url()
{
    $domain = $_SESSION["domain"];
    if ($domain == "localhost") {
        return ("http://$domain/spamtonprof");
    }

    if ($domain = 'spamtonprof.com') {
        return ("http://$domain");
    }

    return (false);
}

function extract_cookies($curl_result)
{
    $cookies = array();
    $matches = [];
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $curl_result, $matches);
    $cookies = array();
    
    foreach ($matches[1] as $item) {

        parse_str($item, $cookie);
        $cookies = array_merge($cookies, $cookie);
    }
    $matches = $matches[1];

    $cookies = [];
    foreach ($matches as $cookie) {
        $cookie = explode("=", $cookie);
        $cookies[$cookie[0]] = $cookie[1];
    }

    return ($cookies);
}

function unaccent($str)
{
    $transliteration = array(
        'Ĳ' => 'I',
        'Ö' => 'O',
        'Œ' => 'O',
        'Ü' => 'U',
        'ä' => 'a',
        'æ' => 'a',
        'ĳ' => 'i',
        'ö' => 'o',
        'œ' => 'o',
        'ü' => 'u',
        'ß' => 's',
        'ſ' => 's',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ā' => 'A',
        'Ą' => 'A',
        'Ă' => 'A',
        'Ç' => 'C',
        'Ć' => 'C',
        'Č' => 'C',
        'Ĉ' => 'C',
        'Ċ' => 'C',
        'Ď' => 'D',
        'Đ' => 'D',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ē' => 'E',
        'Ę' => 'E',
        'Ě' => 'E',
        'Ĕ' => 'E',
        'Ė' => 'E',
        'Ĝ' => 'G',
        'Ğ' => 'G',
        'Ġ' => 'G',
        'Ģ' => 'G',
        'Ĥ' => 'H',
        'Ħ' => 'H',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ī' => 'I',
        'Ĩ' => 'I',
        'Ĭ' => 'I',
        'Į' => 'I',
        'İ' => 'I',
        'Ĵ' => 'J',
        'Ķ' => 'K',
        'Ľ' => 'K',
        'Ĺ' => 'K',
        'Ļ' => 'K',
        'Ŀ' => 'K',
        'Ł' => 'L',
        'Ñ' => 'N',
        'Ń' => 'N',
        'Ň' => 'N',
        'Ņ' => 'N',
        'Ŋ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ø' => 'O',
        'Ō' => 'O',
        'Ő' => 'O',
        'Ŏ' => 'O',
        'Ŕ' => 'R',
        'Ř' => 'R',
        'Ŗ' => 'R',
        'Ś' => 'S',
        'Ş' => 'S',
        'Ŝ' => 'S',
        'Ș' => 'S',
        'Š' => 'S',
        'Ť' => 'T',
        'Ţ' => 'T',
        'Ŧ' => 'T',
        'Ț' => 'T',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ū' => 'U',
        'Ů' => 'U',
        'Ű' => 'U',
        'Ŭ' => 'U',
        'Ũ' => 'U',
        'Ų' => 'U',
        'Ŵ' => 'W',
        'Ŷ' => 'Y',
        'Ÿ' => 'Y',
        'Ý' => 'Y',
        'Ź' => 'Z',
        'Ż' => 'Z',
        'Ž' => 'Z',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ā' => 'a',
        'ą' => 'a',
        'ă' => 'a',
        'å' => 'a',
        'ç' => 'c',
        'ć' => 'c',
        'č' => 'c',
        'ĉ' => 'c',
        'ċ' => 'c',
        'ď' => 'd',
        'đ' => 'd',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ē' => 'e',
        'ę' => 'e',
        'ě' => 'e',
        'ĕ' => 'e',
        'ė' => 'e',
        'ƒ' => 'f',
        'ĝ' => 'g',
        'ğ' => 'g',
        'ġ' => 'g',
        'ģ' => 'g',
        'ĥ' => 'h',
        'ħ' => 'h',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ī' => 'i',
        'ĩ' => 'i',
        'ĭ' => 'i',
        'į' => 'i',
        'ı' => 'i',
        'ĵ' => 'j',
        'ķ' => 'k',
        'ĸ' => 'k',
        'ł' => 'l',
        'ľ' => 'l',
        'ĺ' => 'l',
        'ļ' => 'l',
        'ŀ' => 'l',
        'ñ' => 'n',
        'ń' => 'n',
        'ň' => 'n',
        'ņ' => 'n',
        'ŉ' => 'n',
        'ŋ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ø' => 'o',
        'ō' => 'o',
        'ő' => 'o',
        'ŏ' => 'o',
        'ŕ' => 'r',
        'ř' => 'r',
        'ŗ' => 'r',
        'ś' => 's',
        'š' => 's',
        'ť' => 't',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ū' => 'u',
        'ů' => 'u',
        'ű' => 'u',
        'ŭ' => 'u',
        'ũ' => 'u',
        'ų' => 'u',
        'ŵ' => 'w',
        'ÿ' => 'y',
        'ý' => 'y',
        'ŷ' => 'y',
        'ż' => 'z',
        'ź' => 'z',
        'ž' => 'z',
        'Α' => 'A',
        'Ά' => 'A',
        'Ἀ' => 'A',
        'Ἁ' => 'A',
        'Ἂ' => 'A',
        'Ἃ' => 'A',
        'Ἄ' => 'A',
        'Ἅ' => 'A',
        'Ἆ' => 'A',
        'Ἇ' => 'A',
        'ᾈ' => 'A',
        'ᾉ' => 'A',
        'ᾊ' => 'A',
        'ᾋ' => 'A',
        'ᾌ' => 'A',
        'ᾍ' => 'A',
        'ᾎ' => 'A',
        'ᾏ' => 'A',
        'Ᾰ' => 'A',
        'Ᾱ' => 'A',
        'Ὰ' => 'A',
        'ᾼ' => 'A',
        'Β' => 'B',
        'Γ' => 'G',
        'Δ' => 'D',
        'Ε' => 'E',
        'Έ' => 'E',
        'Ἐ' => 'E',
        'Ἑ' => 'E',
        'Ἒ' => 'E',
        'Ἓ' => 'E',
        'Ἔ' => 'E',
        'Ἕ' => 'E',
        'Ὲ' => 'E',
        'Ζ' => 'Z',
        'Η' => 'I',
        'Ή' => 'I',
        'Ἠ' => 'I',
        'Ἡ' => 'I',
        'Ἢ' => 'I',
        'Ἣ' => 'I',
        'Ἤ' => 'I',
        'Ἥ' => 'I',
        'Ἦ' => 'I',
        'Ἧ' => 'I',
        'ᾘ' => 'I',
        'ᾙ' => 'I',
        'ᾚ' => 'I',
        'ᾛ' => 'I',
        'ᾜ' => 'I',
        'ᾝ' => 'I',
        'ᾞ' => 'I',
        'ᾟ' => 'I',
        'Ὴ' => 'I',
        'ῌ' => 'I',
        'Θ' => 'T',
        'Ι' => 'I',
        'Ί' => 'I',
        'Ϊ' => 'I',
        'Ἰ' => 'I',
        'Ἱ' => 'I',
        'Ἲ' => 'I',
        'Ἳ' => 'I',
        'Ἴ' => 'I',
        'Ἵ' => 'I',
        'Ἶ' => 'I',
        'Ἷ' => 'I',
        'Ῐ' => 'I',
        'Ῑ' => 'I',
        'Ὶ' => 'I',
        'Κ' => 'K',
        'Λ' => 'L',
        'Μ' => 'M',
        'Ν' => 'N',
        'Ξ' => 'K',
        'Ο' => 'O',
        'Ό' => 'O',
        'Ὀ' => 'O',
        'Ὁ' => 'O',
        'Ὂ' => 'O',
        'Ὃ' => 'O',
        'Ὄ' => 'O',
        'Ὅ' => 'O',
        'Ὸ' => 'O',
        'Π' => 'P',
        'Ρ' => 'R',
        'Ῥ' => 'R',
        'Σ' => 'S',
        'Τ' => 'T',
        'Υ' => 'Y',
        'Ύ' => 'Y',
        'Ϋ' => 'Y',
        'Ὑ' => 'Y',
        'Ὓ' => 'Y',
        'Ὕ' => 'Y',
        'Ὗ' => 'Y',
        'Ῠ' => 'Y',
        'Ῡ' => 'Y',
        'Ὺ' => 'Y',
        'Φ' => 'F',
        'Χ' => 'X',
        'Ψ' => 'P',
        'Ω' => 'O',
        'Ώ' => 'O',
        'Ὠ' => 'O',
        'Ὡ' => 'O',
        'Ὢ' => 'O',
        'Ὣ' => 'O',
        'Ὤ' => 'O',
        'Ὥ' => 'O',
        'Ὦ' => 'O',
        'Ὧ' => 'O',
        'ᾨ' => 'O',
        'ᾩ' => 'O',
        'ᾪ' => 'O',
        'ᾫ' => 'O',
        'ᾬ' => 'O',
        'ᾭ' => 'O',
        'ᾮ' => 'O',
        'ᾯ' => 'O',
        'Ὼ' => 'O',
        'ῼ' => 'O',
        'α' => 'a',
        'ά' => 'a',
        'ἀ' => 'a',
        'ἁ' => 'a',
        'ἂ' => 'a',
        'ἃ' => 'a',
        'ἄ' => 'a',
        'ἅ' => 'a',
        'ἆ' => 'a',
        'ἇ' => 'a',
        'ᾀ' => 'a',
        'ᾁ' => 'a',
        'ᾂ' => 'a',
        'ᾃ' => 'a',
        'ᾄ' => 'a',
        'ᾅ' => 'a',
        'ᾆ' => 'a',
        'ᾇ' => 'a',
        'ὰ' => 'a',
        'ᾰ' => 'a',
        'ᾱ' => 'a',
        'ᾲ' => 'a',
        'ᾳ' => 'a',
        'ᾴ' => 'a',
        'ᾶ' => 'a',
        'ᾷ' => 'a',
        'β' => 'b',
        'γ' => 'g',
        'δ' => 'd',
        'ε' => 'e',
        'έ' => 'e',
        'ἐ' => 'e',
        'ἑ' => 'e',
        'ἒ' => 'e',
        'ἓ' => 'e',
        'ἔ' => 'e',
        'ἕ' => 'e',
        'ὲ' => 'e',
        'ζ' => 'z',
        'η' => 'i',
        'ή' => 'i',
        'ἠ' => 'i',
        'ἡ' => 'i',
        'ἢ' => 'i',
        'ἣ' => 'i',
        'ἤ' => 'i',
        'ἥ' => 'i',
        'ἦ' => 'i',
        'ἧ' => 'i',
        'ᾐ' => 'i',
        'ᾑ' => 'i',
        'ᾒ' => 'i',
        'ᾓ' => 'i',
        'ᾔ' => 'i',
        'ᾕ' => 'i',
        'ᾖ' => 'i',
        'ᾗ' => 'i',
        'ὴ' => 'i',
        'ῂ' => 'i',
        'ῃ' => 'i',
        'ῄ' => 'i',
        'ῆ' => 'i',
        'ῇ' => 'i',
        'θ' => 't',
        'ι' => 'i',
        'ί' => 'i',
        'ϊ' => 'i',
        'ΐ' => 'i',
        'ἰ' => 'i',
        'ἱ' => 'i',
        'ἲ' => 'i',
        'ἳ' => 'i',
        'ἴ' => 'i',
        'ἵ' => 'i',
        'ἶ' => 'i',
        'ἷ' => 'i',
        'ὶ' => 'i',
        'ῐ' => 'i',
        'ῑ' => 'i',
        'ῒ' => 'i',
        'ῖ' => 'i',
        'ῗ' => 'i',
        'κ' => 'k',
        'λ' => 'l',
        'μ' => 'm',
        'ν' => 'n',
        'ξ' => 'k',
        'ο' => 'o',
        'ό' => 'o',
        'ὀ' => 'o',
        'ὁ' => 'o',
        'ὂ' => 'o',
        'ὃ' => 'o',
        'ὄ' => 'o',
        'ὅ' => 'o',
        'ὸ' => 'o',
        'π' => 'p',
        'ρ' => 'r',
        'ῤ' => 'r',
        'ῥ' => 'r',
        'σ' => 's',
        'ς' => 's',
        'τ' => 't',
        'υ' => 'y',
        'ύ' => 'y',
        'ϋ' => 'y',
        'ΰ' => 'y',
        'ὐ' => 'y',
        'ὑ' => 'y',
        'ὒ' => 'y',
        'ὓ' => 'y',
        'ὔ' => 'y',
        'ὕ' => 'y',
        'ὖ' => 'y',
        'ὗ' => 'y',
        'ὺ' => 'y',
        'ῠ' => 'y',
        'ῡ' => 'y',
        'ῢ' => 'y',
        'ῦ' => 'y',
        'ῧ' => 'y',
        'φ' => 'f',
        'χ' => 'x',
        'ψ' => 'p',
        'ω' => 'o',
        'ώ' => 'o',
        'ὠ' => 'o',
        'ὡ' => 'o',
        'ὢ' => 'o',
        'ὣ' => 'o',
        'ὤ' => 'o',
        'ὥ' => 'o',
        'ὦ' => 'o',
        'ὧ' => 'o',
        'ᾠ' => 'o',
        'ᾡ' => 'o',
        'ᾢ' => 'o',
        'ᾣ' => 'o',
        'ᾤ' => 'o',
        'ᾥ' => 'o',
        'ᾦ' => 'o',
        'ᾧ' => 'o',
        'ὼ' => 'o',
        'ῲ' => 'o',
        'ῳ' => 'o',
        'ῴ' => 'o',
        'ῶ' => 'o',
        'ῷ' => 'o',
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Ж' => 'Z',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'I',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'K',
        'Ц' => 'T',
        'Ч' => 'C',
        'Ш' => 'S',
        'Щ' => 'S',
        'Ы' => 'Y',
        'Э' => 'E',
        'Ю' => 'Y',
        'Я' => 'Y',
        'а' => 'A',
        'б' => 'B',
        'в' => 'V',
        'г' => 'G',
        'д' => 'D',
        'е' => 'E',
        'ё' => 'E',
        'ж' => 'Z',
        'з' => 'Z',
        'и' => 'I',
        'й' => 'I',
        'к' => 'K',
        'л' => 'L',
        'м' => 'M',
        'н' => 'N',
        'о' => 'O',
        'п' => 'P',
        'р' => 'R',
        'с' => 'S',
        'т' => 'T',
        'у' => 'U',
        'ф' => 'F',
        'х' => 'K',
        'ц' => 'T',
        'ч' => 'C',
        'ш' => 'S',
        'щ' => 'S',
        'ы' => 'Y',
        'э' => 'E',
        'ю' => 'Y',
        'я' => 'Y',
        'ð' => 'd',
        'Ð' => 'D',
        'þ' => 't',
        'Þ' => 'T',
        'ა' => 'a',
        'ბ' => 'b',
        'გ' => 'g',
        'დ' => 'd',
        'ე' => 'e',
        'ვ' => 'v',
        'ზ' => 'z',
        'თ' => 't',
        'ი' => 'i',
        'კ' => 'k',
        'ლ' => 'l',
        'მ' => 'm',
        'ნ' => 'n',
        'ო' => 'o',
        'პ' => 'p',
        'ჟ' => 'z',
        'რ' => 'r',
        'ს' => 's',
        'ტ' => 't',
        'უ' => 'u',
        'ფ' => 'p',
        'ქ' => 'k',
        'ღ' => 'g',
        'ყ' => 'q',
        'შ' => 's',
        'ჩ' => 'c',
        'ც' => 't',
        'ძ' => 'd',
        'წ' => 't',
        'ჭ' => 'c',
        'ხ' => 'k',
        'ჯ' => 'j',
        'ჰ' => 'h'
    );
    $str = str_replace(array_keys($transliteration), array_values($transliteration), $str);
    return $str;
}

function generateRandomString($length = 4)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i ++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function objectToObject($instance, $className)
{
    return unserialize(sprintf('O:%d:"%s"%s', strlen($className), $className, strstr(strstr(serialize($instance), '"'), ':')));
}

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

function extract_text_from_node($classname, $body)
{
    $dom = new DomDocument();
    $dom->loadHTML($body);
    $finder = new DomXPath($dom);
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
    $text = $nodes[0]->textContent;
    return ($text);
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

function outputCSV($data,$file_name = 'file.csv') {
    # output headers so that the file is downloaded rather than displayed
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$file_name");
    # Disable caching - HTTP 1.1
    header("Cache-Control: no-cache, no-store, must-revalidate");
    # Disable caching - HTTP 1.0
    header("Pragma: no-cache");
    # Disable caching - Proxies
    header("Expires: 0");
    
    # Start the ouput
    $output = fopen("php://output", "w");
    
    # Then loop through the rows
    foreach ($data as $row) {
        # Add the rows to the body
        fputcsv($output, $row); // here you can change delimiter/enclosure
    }
    # Close the stream off
    fclose($output);
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

function extract_url($string)
{
    $matches = array();

    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $matches);

    return ($matches);
}

function randomDateInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDate = new DateTime();
    $randomDate->setTimestamp($randomTimestamp);
    return $randomDate;
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

function gene_text($spins)
{
    $spin_gen = new \ContentSpinning\Spin();
    $paras = [];
    foreach ($spins as $spin) {

        $paras[] = $spin_gen->process($spin);
    }

    $txt = implode(PHP_EOL . PHP_EOL, $paras);

    return ($txt);
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

function readCsv($filePath, $sep = ",")
{
    $rows = [];

    $row = 1;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, $sep)) !== FALSE) {
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

        $gmail = new spamtonprof\googleMg\GoogleManager($gmailAccount->getEmail());

        $gmail->createLabel($labelName, $hexColor);
    } while ($first != $mailProf);
}

function pgArrayToArray($pgArray)
{
    $pgArray = str_replace([
        '{',
        '}'
    ], "", $pgArray);
    return (explode(",", $pgArray));
}

function arrayToPgArray($array)
{
    $str = implode(',', $array);
    $str = '{' . $str . '}';
    return ($str);
}

// pour terminer rapidement le processus d'inscription d'un compte de test
function finishTrialInscription($refAbo, $refProf = 59)
{
    $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));

    $abonnement = new \spamtonprof\stp_api\StpAbonnement(array(
        "ref_abonnement" => $refAbo,
        "ref_prof" => $refProf,
        "date_attribution_prof" => $now,
        'first_prof_assigned' => true
    ));

    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

    $abonnementMg->updateRefProf($abonnement);

    $abonnementMg->updateDateAttributionProf($abonnement);

    $abonnementMg->updateFirstProfAssigned($abonnement);

    $abonnement->setDebut_essai($now->format(PG_DATE_FORMAT));
    $end = $now->add(new DateInterval('P7D'));
    $abonnement->setFin_essai($end->format(PG_DATE_FORMAT));

    $abonnementMg->updateDebutEssai($abonnement);
    $abonnementMg->updateFinEssai($abonnement);
}

function url_get_contents($url)
{
    if (! function_exists('curl_init')) {
        die('The cURL library is not installed.');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


function quote($word){
    return('"' . $word . '"');
}


// le fichier doit être un export des inscription du formulaire à l'inscription de la semaine d'essai
// le code est nathalie ou natali pour le moment, c'est configurable
// exemple d'appel: count_inscription_teleprospecteur("inscription-decembre.csv");
function count_inscription_teleprospecteur($path_csv_file){
    
    
    $rows = readCsv($path_csv_file, ",");
    
    $targets = [];
    
    $nb_cols = [];
    
    foreach ($rows as $row) {
        
        
        $rmqs = [];
        
        $nb_col = count($row);
        
        if ($nb_col <= 15) {
            continue;
            // prettyPrint($row);
        }
        
        //email eleve
        $rmqs[] = $row[4];
        
        if ($nb_col == 21) {

            $rmqs["rmq matiere 1"] = $row[2];
            $rmqs["rmq matiere 2"] = $row[4];
            $rmqs["rmq matiere 3"] = $row[8];
            $rmqs["rmq matiere 4"] = $row[10];
            $rmqs["rmqs"] = $row[16];
            $rmqs["code"] = $row[19];
            $targets[] = $rmqs;
        }
        
        if ($nb_col == 33) {
            
            
            $rmqs["rmq matiere 1"] = $row[13];
            $rmqs["rmq matiere 2"] = $row[15];
            $rmqs["rmq matiere 3"] = $row[17];
            $rmqs["rmq matiere 4"] = $row[19];
            $rmqs["rmqs"] = $row[28];
            $rmqs["code"] = $row[31];
            $targets[] = $rmqs;
        }
        
        $nb_cols[] = $nb_col;
        
    }
    
    $res = [];
    
    foreach ($targets as $target){
        
        $target = array_map('strtolower', $target);
        
        $email = array_shift($target);
        
        $matches  = preg_grep ('/.*nathali.*|.*natali.*/', $target);
        
        if($matches){
            $matches[] = $email;
            $res[] = $matches;
        }
        
        
    }
    
    prettyPrint(array(count($res),$res));
    
    
    
}


