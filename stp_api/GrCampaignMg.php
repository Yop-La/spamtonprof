<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class GrCampaignMg implements \JsonSerializable
{

    const STP_ELEVE = "a4mfj";

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

