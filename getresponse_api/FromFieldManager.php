<?php
namespace spamtonprof\getresponse_api;

use PDO;

class FromFieldManager
{

    private $getresponse;
    

    // Instance de PDO
    public function __construct()
    {
        $this->getresponse = new \GetResponse(GR_API);
        
    }
    
    /**
     * 
     * @param string $email
     * @return \spamtonprof\getresponse_api\FromField
     */

    public function get($email)
    {
        $params = array("query" => array("email" => $email));
        
        $fromFields = $this->getresponse->getFromFields($params);
        
        foreach ($fromFields as $fromField){
            
            return($fromField);
        }
        return(false);
        
    }
    


}