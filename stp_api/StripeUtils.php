<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */



class StripeUtils 
{
    
    public function extract($object_stripe,$key){
        
        
        if($key == 'states_end_trial'){
            
            return(array('current'=>$object_stripe->data->object->status,'previous'=>$object_stripe->data->previous_attributes->status));
            
            
        }
    }
    
    
}

