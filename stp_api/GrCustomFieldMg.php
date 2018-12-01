<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class GrCustomFieldMg implements \JsonSerializable
{

    const PRENOM_PROCHE_ID = '3ytt8';
    const REF_ELEVE_ID = 'v0ASk';
    const UPDATE_DAY_NUMBER = 'vAwOl';
    
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

