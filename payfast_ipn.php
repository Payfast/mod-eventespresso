<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */

require_once('payfast_common.inc');
 
/**
 * espresso_transactions_payfast_get_attendee_id
 * 
 * returns attendee id
 * 
 * @return attendee ID
 */ 
 
function espresso_transactions_payfast_get_attendee_id($attendee_id) 
{
    if (!empty($_REQUEST['id'])) 
    {
        $attendee_id = $_REQUEST['id'];
    }
    return $attendee_id;
}

/**
 * espresso_process_payfast
 * 
 * handles the ipn (itn) data
 * @param $payment_data
 */

function espresso_process_payfast($payment_data) 
{
    do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
    $payment_data['txn_type'] = 'Payfast';
    $payment_data['txn_id'] = 0;
    $payment_data['payment_status'] = 'Incomplete';
    $payment_data['txn_details'] = serialize($_REQUEST);
    include_once ('Payfast.php');
    
    //// Notify PayFast that information has been received
    header( 'HTTP/1.0 200 OK' );
    flush();    
    
    if(isset($payment_data['attendee_session']) && !empty($payment_data['attendee_session']))
        $attendee_session_id= $payment_data['attendee_session'];
    else
        $attendee_session_id='';
        
    $myPayfast = new EE_Payfast($attendee_session_id);
    
    echo '<!--Event Espresso Payfast Gateway Version ' . $myPayfast->gateway_version . '-->';
    
    $myPayfast->ipnLog = TRUE;
    $payfast_settings = get_option('event_espresso_payfast_settings');
    
    if ($payfast_settings['use_sandbox']) 
    {
        $myPayfast->enableTestMode();
    }
    if ($myPayfast->validateIpn()) 
    {
        $payment_data['txn_details'] = serialize($myPayfast->ipnData);

        $payment_data['txn_id'] = $myPayfast->ipnData['pf_payment_id'];
        
        pflog("myPayfast->ipnData : " . print_r($myPayfast->ipnData, true));
        pflog("paymentdata  ".print_r($payment_data, true));
        
        if ($myPayfast->ipnData['payment_status'] == 'COMPLETE')
        {
            
            $payment_data['payment_status'] = 'Completed';
            
            if ($payfast_settings['use_sandbox']) 
            {
                // For this, we'll just email ourselves ALL the data as plain text output.
                $subject = 'Instant Payment Notification - Gateway Variable Dump';
                $body = "An instant payment notification was successfully recieved\n";
                $body .= "from " . $myPayfast->ipnData['payer_email'] . " on " . date('m/d/Y');
                $body .= " at " . date('g:i A') . "\n\nDetails:\n";
                foreach ($myPayfast->ipnData as $key => $value) 
                {
                    $body .= "\n$key: $value\n";
                }
                wp_mail($payment_data['contact'], $subject, $body);
            }
        } 
        else if ($myPayfast->ipnData['payment_status'] == 'PENDING')
        {
            $payment_data['payment_status'] = 'Pending';
            
            if ($payfast_settings['use_sandbox']) 
            {
                // For this, we'll just email ourselves ALL the data as plain text output.
                $subject = 'Instant Payment Notification - Gateway Variable Dump';
                $body = "An instant payment notification was successfully recieved\n";
                $body .= "from " . $myPayfast->ipnData['payer_email'] . " on " . date('m/d/Y');
                $body .= " at " . date('g:i A') . "\n\nDetails:\n";
                foreach ($myPayfast->ipnData as $key => $value) 
                {
                    $body .= "\n$key: $value\n";
                }
                wp_mail($payment_data['contact'], $subject, $body);
            }
        } 
        else
        {
            $subject = 'Instant Payment Notification - Gateway Variable Dump';
            $body = "An instant payment notification failed\n";
            $body .= "from " . $myPayfast->ipnData['payer_email'] . " on " . date('m/d/Y');
            $body .= " at " . date('g:i A') . "\n\nDetails:\n";
            foreach ($myPayfast->ipnData as $key => $value) 
            {
                $body .= "\n$key: $value\n";
            }
            wp_mail($payment_data['contact'], $subject, $body);
        }
    }

    add_action('action_hook_espresso_email_after_payment', 'espresso_email_after_payment');

    return $payment_data;
}