<?php
namespace spamtonprof\stp_api;

use PDO;

class NbEmailManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function deleteList($info)
    {
        if (is_array($info)) {
            if (array_key_exists("week", $info) && array_key_exists("year", $info)) {
                $q = $this->_db->prepare("delete FROM nb_email where week = :week and year = :year");
                $q->bindValue(':week', $info['week'], PDO::PARAM_INT);
                $q->bindValue(':year', $info['year'], PDO::PARAM_INT);
                $q->execute();
            }
        }
    }

    public function feed($week, $year)
    {
        $q = $this->_db->prepare("
            select count(ref_compte) as nb_message, ref_compte from mail_eleve
            where extract(week from date_reception) = :week and extract(year from date_reception) = :year
            group by ref_compte
            order by nb_message desc");
        $q->bindValue(':week', $week, PDO::PARAM_INT);
        $q->bindValue(':year', $year, PDO::PARAM_INT);
        $q->execute();
        
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            echo("ref compte : " . $data["ref_compte"]. "<br>");
            $q2 = $this->_db->prepare("insert into nb_email(week, year, nb_message, ref_compte) values (:week, :year, :nb_message, :ref_compte)");
            $q2->bindValue(':week', $week, PDO::PARAM_INT);
            $q2->bindValue(':year', $year, PDO::PARAM_INT);
            $q2->bindValue(':nb_message', $data["nb_message"], PDO::PARAM_INT);
            $q2->bindValue(':ref_compte', $data["ref_compte"], PDO::PARAM_INT);
            $q2->execute();
        }
    }
}