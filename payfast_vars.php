<?php
/**
 * espresso_display_payfast
 * 
 * Setting of payment variables
 * 
 * @param $payment_data
 **/
function espresso_display_payfast($payment_data) 
{
	extract($payment_data);
	global $wpdb;
	include_once ('Payfast.php');
	$myPayfast = new EE_Payfast();
	echo '<!-- Event Espresso Payfast Gateway Version ' . $myPayfast->gateway_version . '-->';
	global $org_options;
	$payfast_settings = get_option('event_espresso_payfast_settings');
	$payfast_id = empty($payfast_settings['payfast_id']) ? '' : $payfast_settings['payfast_id'];
	$payfast_cur = empty($payfast_settings['currency_format']) ? '' : $payfast_settings['currency_format'];
	$no_shipping = isset($payfast_settings['no_shipping']) ? $payfast_settings['no_shipping'] : '0';
	$use_sandbox = $payfast_settings['use_sandbox'];
    
    $myPayfast->addField('merchant_key', $payfast_settings['payfast_merchant_key']);
	$myPayfast->addField('merchant_id', $payfast_settings['payfast_merchant_id']);
    
    
	if ($use_sandbox) 
    {
		$myPayfast->enableTestMode();
	}

	$myPayfast->addField('business', $payfast_id);
	if ($payfast_settings['force_ssl_return']) 
    {
		$home = str_replace("http://", "https://", home_url());
	} 
    else 
    {
		$home = home_url();
	}
    
    $myPayfast->addField('return_url', $home . '/?page_id=' . $org_options['return_url'] . '&r_id=' . $registration_id. '&type=payfast');
	$myPayfast->addField('cancel_return', $home . '/?page_id=' . $org_options['cancel_return']);
	$myPayfast->addField('notify_url', $home . '/?page_id=' . $org_options['notify_url'] . '&id=' . $attendee_id . '&r_id=' . $registration_id . '&event_id=' . $event_id . '&attendee_action=post_payment&form_action=payment&type=payfast');
	
    $event_name = $wpdb->get_var('SELECT event_name FROM ' . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'");
	$myPayfast->addField('cmd', '_cart');
	$myPayfast->addField('upload', '1');
	$sql = "SELECT attendee_session FROM " . EVENTS_ATTENDEE_TABLE . " WHERE id='" . $attendee_id . "'";
	$session_id = $wpdb->get_var($sql);
	$sql = "SELECT ac.cost, ac.quantity, ed.event_name, a.price_option, a.fname, a.lname, dc.coupon_code_price, dc.use_percentage FROM " . EVENTS_ATTENDEE_COST_TABLE . " ac JOIN " . EVENTS_ATTENDEE_TABLE . " a ON ac.attendee_id=a.id JOIN " . EVENTS_DETAIL_TABLE . " ed ON a.event_id=ed.id ";
	$sql .= " LEFT JOIN " . EVENTS_DISCOUNT_CODES_TABLE . " dc ON a.coupon_code=dc.coupon_code ";
	$sql .= " WHERE attendee_session='" . $session_id . "'";
	$items = $wpdb->get_results($sql);
	$coupon_amount = empty( $items[0]->coupon_code_price ) ? 0 : $items[0]->coupon_code_price;
	$is_coupon_pct = ( !empty( $items[0]->use_percentage ) && $items[0]->use_percentage == 'Y' ) ? true : false;
	
    //payfast sends through only one amount - cart total
    $cartTotal = 0;
    foreach ($items as $key=>$item) 
    {
		$myPayfast->addField('item_name', $item->price_option . ' for ' . $item->event_name . '. Attendee: '. $item->fname . ' ' . $item->lname);
	}

	if (!empty($coupon_amount)) 
    {
		if ($is_coupon_pct) 
        {
			$myPayfast->addField('discount_rate_cart', $coupon_amount);
		} 
        else 
        {
			$myPayfast->addField('discount_amount_cart', $coupon_amount);
		}
	}
    $cartTotal= $payment_data['event_cost'];
    $myPayfast->addField('amount', $cartTotal);
    
	$myPayfast->addField('currency_code', $payfast_cur);
	$myPayfast->addField('image_url', empty($payfast_settings['image_url']) ? '' : $payfast_settings['image_url']);
	$myPayfast->addField('no_shipping ', $no_shipping);
	$myPayfast->addField('first_name', $fname);
	$myPayfast->addField('last_name', $lname);
	$myPayfast->addField('email', $attendee_email);
	$myPayfast->addField('address1', $address);
	$myPayfast->addField('city', $city);
	$myPayfast->addField('state', $state);
	$myPayfast->addField('zip', $zip);
    $myPayfast->addField('custom_str1', $registration_id);
    $myPayfast->addField('m_payment_id', $registration_id);
	
	if (!empty($payfast_settings['bypass_payment_page']) && $payfast_settings['bypass_payment_page'] == 'Y') 
    {
		$myPayfast->submitPayment();
	} 
    else 
    {
		if (empty($payfast_settings['button_url'])) 
        {
			if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/payfast/payfast.png")) 
            {
				$button_url = EVENT_ESPRESSO_GATEWAY_DIR . "/payfast/payfast.png";
			} 
            else 
            {
				$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/payfast/payfast.png";
			}
		} 
        elseif (isset($payfast_settings['button_url'])) 
        {
			$button_url = $payfast_settings['button_url'];
		} 
        else 
        {
			$button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/payfast/payfast.png";
		}
		$myPayfast->submitButton($button_url, 'payfast');
	}

	if ($use_sandbox) 
    {
    	echo '<h3 style="color:#ff0000;" title="Payments will not be processed">' . __('Payfast Debug Mode Is Turned On', 'event_espresso') . '</h3>';
		$myPayfast->dump_fields();
	}
}

add_action('action_hook_espresso_display_offsite_payment_gateway', 'espresso_display_payfast');