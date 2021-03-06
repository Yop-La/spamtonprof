<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class InvoiceManager
{

    protected $code_api = '9JSHrX1D6TJ32QQDoIzl/spamtonprof';

    public function __construct(array $donnees = array())
    {}

    public function get_invoice_by_id($id)
    {

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://spamtonprof.vosfactures.fr/invoices/' . $id . '.json?api_token=' . $this->code_api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return ($result);
    }

    public function get_invoice_by_number($number)
    {

        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://spamtonprof.vosfactures.fr/invoices.json?number=' . $number . '&api_token=' . $this->code_api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return ($result);
    }
    
    
    public function create_invoice($invoice){
        
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();
        
        $body = new \stdClass();
        $body->api_token = $this->code_api;
        $body->invoice = $invoice;
        
        curl_setopt($ch, CURLOPT_URL, 'https://spamtonprof.vosfactures.fr/invoices.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        
   
        return($result);
        
    }
    
    
    
}

