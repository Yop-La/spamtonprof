<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class GrTagMg implements \JsonSerializable
{

    const PARENT_REQUIRED = 'onwz';

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

