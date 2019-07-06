<?php

/*
 *
 * pour ajouter des comptes gmx fraichement achetés
 * 
 * 
 */
require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(- 1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


$gmx_acts = [
    'myung04gos@gmx.com:Azj7i3y6zq',
    'robenaprumdy@gmx.com:yu0Lr41qct',
    'dannacetbu@gmx.com:nR471rigyf',
    'larrylan3@gmx.com:uvfUt6xd4',
    'kamryncay3x@gmx.com:vug9L2qwsx6',
    'shellamovlinv6@gmx.com:tYzyu9251',
    'kaeleeb5bg@gmx.com:vjwc7eD70',
    'keebumbel@gmx.com:omu7v9Hrf2c',
    'lonnyqf0bbogust@gmx.com:y42v4O3qv80',
    'jeanjuls5@gmx.com:ovGggjsymv5',
    'aleenwhitisiju@gmx.com:k4w42N4ycbb',
    'elyzabethvuzg@gmx.com:mp24Tvt36',
    'osbournertn2l@gmx.com:t5hal42xdI4',
    'llesseyv4n@gmx.com:olvvaB5csj',
    'carrolbrel@gmx.com:tscRp8gwo17',
    'harperjafari7e52@gmx.com:ith5x0pCo',
    'lexyren4pzv@gmx.com:Ogbcxqaqu7w',
    'oracanestoa7@gmx.com:zXtg2olmg',
    'edythemc9f@gmx.com:ouultXxx7e',
    'rodgeeun@gmx.com:Ddytika6biy',
    'chsissubia@gmx.com:dy6o1tr3Dg2',
    'leshalacazehu@gmx.com:ccbcw7nKws9',
    'irvinglfza@gmx.com:hS2cxp9phk',
    'brockchiesa5d@gmx.com:l95u2Bidjf2',
    'americabarm@gmx.com:Penxc7rs8',
    'loritaaranas8hbw@gmx.com:wPffe6xxnp',
    'socorrohdpeste@gmx.com:yt7kGapojb',
    'lbram7xm@gmx.com:b58T9t40fwe',
    'kiandg9heyl@gmx.com:u6vn8ljQj',
    'creolamixonsy@gmx.com:gtt8iibP91m',
    'lintonoshcd@gmx.com:ehfvUx01b5p',
    'duanakai8e9@gmx.com:ddZiimuax4t',
    'evplatoz2@gmx.com:u2gbzTwn4',
    'wendic7iqb@gmx.com:z4bizrHic23',
    'lmoylerpwlk@gmx.com:kf4fcjb4sXh',
    'janee6yqamici@gmx.com:koPiuz8xzlt',
    'laluzz7@gmx.com:cd4qCv6701',
    'geokwreim@gmx.com:bc67hc3hN4',
    'antxcstragle@gmx.com:yoXiexzz5g',
    'jojoddnpathak@gmx.com:xA1761b80as',
    'corinnepufn3l@gmx.com:dhF86nvcbk',
    'glynis2nmat@gmx.com:Uhw1zows2ry',
    'vickiizzzbro@gmx.com:pxth88lGi',
    'richendrab0x8@gmx.com:xi6ilw8E6',
    'nelsonzppa@gmx.com:Ef1tn8ufs',
    'lbozychqwiw@gmx.com:tkr8Sgrob',
    'sug6b@gmx.com:b33pGklq1',
    'hadley9pcacho@gmx.com:qFkgnyqic6',
    'kimcartymz2@gmx.com:gy69Ab1gqu',
    'bertkuhreps@gmx.com:gv50Pnrsz',
    'deni8qh7pella@gmx.com:eawSy09vnbo',
    'rea1kjfvig@gmx.com:gx8Yic1qi',
    'shawnabiresbx@gmx.com:wuU9bx0m7',
    'izzywequ9i@gmx.com:fC0r2rp2cj',
    'kallaa3sj@gmx.com:qsacGs7nw',
    'mahu5qkegg@gmx.com:z75Acosraqi',
    'kassie9qrgorman@gmx.com:pIlv00r40e6',
    'gabriel7qt@gmx.com:cyx3hv4Cnb',
    'joetterue0u@gmx.com:aXjxgw7swvi',
    'jaylasa313f@gmx.com:zEkadl3dsaa',
    'mort3tsee@gmx.com:uitarqG7oq9',
    'remadebars3f@gmx.com:qajG2jw42',
    'we8qxqhecker@gmx.com:n6byptzxSrt',
    'tamsynrhihbk@gmx.com:nb4ngofP4',
    'ri2q4mabrey@gmx.com:o85la68hPd4',
    'ileanast68ve@gmx.com:h3tmHtnb2',
    'lanellw9myhand@gmx.com:kb7sqjXa8',
    'latoria088bca@gmx.com:g0Uzojh3lw',
    'bridieb7teats@gmx.com:bbra2bOwv1',
    'anasvobu8f@gmx.com:cge0r9iiR5',
    'melvillegu1zwe@gmx.com:ufni0misGs',
    'monicawowkvxpn@gmx.com:qs9fbXf0t',
    'billieigbig@gmx.com:fNl0b8c2v',
    'thorntonmedq3vr@gmx.com:ek1Rebdhh3a',
    'guapoqid@gmx.com:jvnw6y4Ro',
    'fran0cdanger@gmx.com:tf30bN6wst',
    'claretha1qma@gmx.com:sbytdWiqq5h',
    'joellexhrgold@gmx.com:sW07532qe',
    'colemanvirdei@gmx.com:svqhbqostN6',
    'galenmxvvl@gmx.com:n68ciatN7k'
];

$gmxActMg = new \spamtonprof\stp_api\GmxActManager();

foreach ($gmx_acts as $gmx_act) {
    $gmx_act = explode(":", $gmx_act);
    $gmxActMg->add(new \spamtonprof\stp_api\GmxAct(array(
        'mail' => $gmx_act[0],
        'password' => $gmx_act[1]
    )));
}