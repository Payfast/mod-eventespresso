<?php

/**
 * Payfast Class
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 * 
 * Portions of this file contain code Copyright (C) 2004-2008 soeren - All rights reserved.
 * 
 * Author 		Nico Loubser
 * @package		Event Espresso Payfast Gateway
 * @category	Library
 */
require_once('payfast_common.inc');
 
class EE_Payfast extends Espresso_PaymentGateway 
{

	public $gateway_version = '1.1.2';
    private $curlUrl='';
    private $pfHost='';
    private $attendee_session_id='';
   
	/**
	 * Initialize the Payfast gateway
	 *
	 * @param string $attendee_session_id the session id used to retrieve the products price from db, in order to compare it to posted data
	 * @return void
	 */
	public function __construct($attendee_session='') 
    {
		parent::__construct();
		// Some default values of the class
		$this->gatewayUrl = 'https://www.payfast.co.za/eng/process';
        $this->curlUrl    = 'https://www.payfast.co.za/eng/query/validate';
        $this->pfHost     = 'www.payfast.co.za';
        
        if(!empty($attendee_session))
            $this->attendee_session_id= $attendee_session;
        
		$this->ipnLogFile = EVENT_ESPRESSO_UPLOAD_DIR . 'logs/payfast.ipn_results.log';
		// Populate $fields array with a few default
		$this->addField('rm', '2');	 // Return method = POST
		$this->addField('cmd', '_xclick');
    }

	/**
	 * Enables the test mode
	 *
	 * @param none
	 * @return none
	 */
	public function enableTestMode() 
    {
        pflog('in test mode');
		$this->testMode = TRUE;
		$this->gatewayUrl = 'https://sandbox.payfast.co.za/eng/process';
        $this->curlUrl    = 'https://sandbox.payfast.co.za/eng/query/validate';
        $this->pfHost     = 'sandbox.payfast.co.za';
        
        pflog('Test mode enabled');
	}

    /**
     * logErrors
     * 
     * @param string error to log
     * 
     */
	public function logErrors($errors) 
    {
		if ($this->logIpn) 
        {
			// Timestamp
			$text = '[' . date('m/d/Y g:i A') . '] - ';

			// Success or failure being logged?
			$text .= "Errors from IPN Validation:\n";
			$text .= $errors;

			// Write to log
			file_put_contents($this->ipnLogFile, $text, FILE_APPEND)
							or do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, 'could not write to payfast log file');
		}
	}

	/**
	 * Validate the IPN notification
	 *
     * @author Nico Loubser
     * 
	 * @param none
	 * @return boolean
	 */
	public function validateIpn() 
    {
		global $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
        
        // Variable Initialization
        $pfError = false;
        $pfErrMsg = '';
        $pfDone = false;
        $pfData = array();
        $pfOrderId = '';
        $pfParamString = '';
        
        pflog('Payfast ITN call recieved');
        
        //some standard error checking and data validation
        //was correct POST  data recieved
        $pfData= pfGetData();
        if( $pfData === false )
        {
            pflog( 'Verify post data' );
            $pfError = true;
            $pfErrMsg = PF_ERR_BAD_ACCESS;
        }
        pflog( 'PayFast Data: '. print_r( $pfData, true ) );
        
        //is the security signatures similar?
        if( !$pfError && !$pfDone )
        {
            pflog( 'Verify security signature' );        
            // If signature different, log for debugging
            if( !pfValidSignature( $pfData, $pfParamString ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
            }
        }
        
        // Verify source IP 
        if( !$pfError && !$pfDone )
        {
            pflog('Verify source IP');
            pflog('Source IP ' . $_SERVER['REMOTE_ADDR']);
        
            if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
            }
        }
        
        //gets the saved amount in the DB to compare with the value of the IPN 
        if(!empty($this->attendee_session_id))
        {
            $attendee_array= array();
            $attendee_return_array= array();        
            $attendee_array['attendee_session']='';
                                
            pflog('Attendee_session : ' . $this->attendee_session_id);
        
            if( !empty($this->attendee_session_id) )
            {
                $attendee_array['attendee_session'] = $this->attendee_session_id;
                $attendee_return_array = apply_filters('filter_hook_espresso_get_total_cost', $attendee_array);
                pflog("Values of purchase in DB : ".print_r($attendee_return_array, true));
                
                if($attendee_return_array['total_cost'] != $_POST['amount_gross'])
                {
                    $pfError = true;
                    $pfErrMsg = PF_ERR_AMOUNT_MISMATCH;
                }
            }
            else
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ATTENDEE_SESSION_ID;
            }
        }                
                        
        //exit any errors not related to the curl post here
        if($pfError)
        {
            pflog('The following errors occured : \r\n');
            pflog($pfErrMsg);
            return false;
        }
        
        //build the curl parameter string
        $req='';
        foreach ($_POST as $key => $value) 
        {
            if($key != 'signature')
            {
			    $this->ipnData["$key"] = $value;
			    $errors .= "key = " . $key . "\nvalue = " . $value . "\n";
			    $value = urlencode(stripslashes($value));
			    $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
			    $req .= $key . '=' . $value . '&';
            }
		}
        $req = substr( $req, 0, -1 );
              
        return pfValidData($this->pfHost,$req,$this->curlUrl);
   	}
}