<?php
namespace spamtonprof\stp_api;

class GmxPrcsMg
{

    function addGmxActFromCsv($pathFile, $sep = ':')
    {
        $rows = readCsv($pathFile, $sep);

        $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
        foreach ($rows as $row) {
            $gmxAct = $gmxActMg->add(new \spamtonprof\stp_api\GmxAct(array(
                'password' => $row[1],
                'mail' => $row[0]
            )));
            $gmxAct->setHas_redirection(false);
            $gmxActMg->updateHasRedirection($gmxAct);
        }
    }
}
