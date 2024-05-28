<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}
require_once __DIR__ . "/lib/vendor/autoload.php";

use Payfast\PayfastCommon\PayfastCommon;

class EEG_Payfast extends EE_Offsite_Gateway
{
    protected ?string $_payfast_merchant_id = '';
    protected ?string $_payfast_merchant_key = '';
    protected ?string $_payfast_passphrase = '';

    protected $_currencies_supported = array('ZAR');

    public function __construct()
    {
        $this->set_uses_separate_IPN_request(true);
        parent::__construct();
    }

    public function set_settings($settings_array)
    {
        parent::set_settings($settings_array);
        if ($this->_debug_mode) {
            $this->_gateway_url = 'https://sandbox.payfast.co.za/eng/process';
        } else {
            $this->_gateway_url = 'https://www.payfast.co.za/eng/process';
        }
    }


    public function set_redirection_info(
        $payment,
        $billing_info = array(),
        $return_url = null,
        $notify_url = null,
        $cancel_url = null
    ) {
        $transaction        = $payment->transaction();
        $primary_registrant = $transaction->primary_registration();
        $primary_attendee   = $primary_registrant->attendee();

        $redirect_args = array(
            'merchant_id'   => $this->_payfast_merchant_id,
            'merchant_key'  => $this->_payfast_merchant_key,
            'return_url'    => $return_url,
            'cancel_url'    => $cancel_url,
            'notify_url'    => $notify_url,
            'name_first'    => $primary_attendee->fname(),
            'name_last'     => $primary_attendee->lname(),
            'email_address' => $primary_attendee->email(),
            'amount'        => $payment->amount(),
            'item_name'     => $primary_registrant->reg_code(),
        );

        $pfOutput = '';
        // Create output string
        foreach ($redirect_args as $key => $val) {
            $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
        }

        $passPhrase = $this->_payfast_passphrase;
        if (empty($passPhrase)) {
            $pfOutput = substr($pfOutput, 0, -1);
        } else {
            $pfOutput = $pfOutput . "passphrase=" . urlencode($passPhrase);
        }

        $redirect_args['signature']  = md5($pfOutput);
        $redirect_args['user_agent'] = 'EventEspresso 4.8';

        $redirect_args = apply_filters("FHEE__EEG_Payfast__set_redirection_info__arguments", $redirect_args);

        $payment->set_redirect_url($this->_gateway_url);
        $payment->set_redirect_args($redirect_args);

        return $payment;
    }

    public function handle_payment_update($update_info, $transaction)
    {
        PayfastCommon::pflog('Payfast ITN call received');
        define("PF_DEBUG", $this->_debug_mode);
        // User agent constituents (for cURL)
        define('PF_SOFTWARE_NAME', 'Event Espresso');
        define('PF_SOFTWARE_VER', "4.10.46");
        define('PF_MODULE_NAME', 'Payfast-EventEspresso');
        define('PF_MODULE_VER', '1.1.9');

        $pfError       = false;
        $pfErrMsg      = '';
        $pfDone        = false;
        $pfData        = array();
        $pfParamString = '';

        //// Notify Payfast that information has been received
        $this->notifyPayfast();

        PayfastCommon::pflog('Get posted data');

        // Posted variables from ITN
        $pfData = PayfastCommon::pfGetData();

        PayfastCommon::pflog('Payfast Data: ' . print_r($pfData, true));

        if ($pfData === false) {
            $pfError  = true;
            $pfErrMsg = PayfastCommon::PF_ERR_BAD_ACCESS;
        }

        //// Verify security signature
        if (!$pfError && !$pfDone) {
            PayfastCommon::pflog('Verify security signature');

            $passPhrase   = $this->_payfast_passphrase;
            $pfPassPhrase = empty($passPhrase) ? null : $passPhrase;

            // If signature different, log for debugging
            if (!PayfastCommon::pfValidSignature($pfData, $pfParamString, $pfPassPhrase)) {
                $pfError  = true;
                $pfErrMsg = PayfastCommon::PF_ERR_INVALID_SIGNATURE;
            }
        }

        $pfHost = $this->_debug_mode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

        //// Verify data received
        if (!$pfError) {
            PayfastCommon::pflog('Verify data received');

            $pfValid = PayfastCommon::pfValidData($pfHost, $pfParamString);

            if (!$pfValid) {
                $pfError  = true;
                $pfErrMsg = PayfastCommon::PF_ERR_BAD_ACCESS;
            }
        }

        $payment = $this->_pay_model->get_payment_by_txn_id_chq_nmbr($pfData['pf_payment_id']);
        if (!$payment) {
            $payment = $transaction->last_payment();
        }

        //// Check data against internal order
        if (!$pfError && !$pfDone) {
            PayfastCommon::pflog('Check data against internal order');

            // Check order amount
            if (!PayfastCommon::pfAmountsEqual($pfData['amount_gross'], $payment->amount())) {
                $pfError  = true;
                $pfErrMsg = PayfastCommon::PF_ERR_AMOUNT_MISMATCH;
            }
        }

        if (!$this->validate_ipn($update_info, $payment)) {
            $this->log(sprintf(__("IPN failed validation", "event_espresso")), $transaction);

            return $payment;
        }

        list($status, $gateway_response) = $this->processPayment($pfError, $pfDone, $pfData);

        $this->checkError($pfError, $pfErrMsg);

        $this->isPaymentProcessed($payment, $status, $pfData['amount_gross'], $update_info, $gateway_response);

        return $payment;
    }

    public function validate_ipn($update_info, $payment)
    {
        if (apply_filters('FHEE__EEG_Payfast__validate_ipn__skip', true)) {
            return true;
        }
        if ($update_info === $_REQUEST) {
            $raw_post_data  = file_get_contents('php://input');
            $raw_post_array = explode('&', $raw_post_data);
            $update_info    = array();
            foreach ($raw_post_array as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) == 2) {
                    $update_info[$keyval[0]] = urldecode($keyval[1]);
                }
            }
        }

        $result = wp_remote_post(
            $this->_gateway_url,
            array('body' => $req ?? [], 'sslverify' => false, 'timeout' => 60)
        );

        if (!is_wp_error($result) && array_key_exists('body', $result) && strcmp($result['body'], "VERIFIED") == 0) {
            return true;
        } else {
            $payment->set_gateway_response(
                sprintf(
                    __("IPN Validation failed! Payfast responded with '%s'", "event_espresso"),
                    $result['body']
                )
            );
            $payment->set_status(EEM_Payment::status_id_failed);

            return false;
        }
    }

    public function update_txn_based_on_payment($payment)
    {
        $update_info   = $payment->details();
        $redirect_args = $payment->redirect_args();
        $transaction   = $payment->transaction();
        if (!$transaction) {
            $this->log(
                __(
                    'Payment with ID %d has no related transaction,
                 and so update_txn_based_on_payment could not be executed properly',
                    'event_espresso'
                ),
                $payment
            );

            return;
        }
        if (!is_array($update_info) || !isset($update_info['mc_shipping']) || !isset($update_info['tax'])) {
            $this->log(
                array(
                    'message' => __(
                        'Could not update transaction based on payment because the payment
                         details have not yet been put on the payment.
                         This normally happens during the IPN or returning from Payfast',
                        'event_espresso'
                    ),
                    'payment' => $payment->model_field_array()
                ),
                $payment
            );

            return;
        }

        $grand_total_needs_resaving = false;

        if ($grand_total_needs_resaving) {
            $transaction->total_line_item()->save_this_and_descendants_to_txn($transaction->ID());
        }
        $this->log(
            array(
                'message'                     => __('Updated transaction related to payment', 'event_espresso'),
                'transaction (updated)'       => $transaction->model_field_array(),
                'payment (updated)'           => $payment->model_field_array(),
                'grand_total_needed_resaving' => $grand_total_needs_resaving,
            ),
            $payment
        );
    }

    /**
     * @param $payment
     * @param $status
     * @param $amount_gross
     * @param $update_info
     * @param $gateway_response
     *
     * @return void
     */
    public function isPaymentProcessed($payment, $status, $amount_gross, $update_info, $gateway_response): void
    {
        if (!empty($payment)) {
            if ($payment->status() == $status && $payment->amount() == $amount_gross) {
                $this->log(
                    array(
                        'message'  => sprintf(
                            __(
                                'It appears we have received a duplicate IPN from Payfast for payment %d',
                                'event_espresso'
                            ),
                            $payment->ID()
                        ),
                        'payment'  => $payment->model_field_array(),
                        'IPN data' => $update_info
                    ),
                    $payment
                );
            } else {
                $payment->set_status($status);
                $payment->set_amount($amount_gross);
                $payment->set_gateway_response($gateway_response);
                $payment->set_details($update_info);
                $this->log(
                    array(
                        'message'  => sprintf(
                            __(
                                'Updated payment either from IPN or as part of POST from Payfast',
                                'event_espresso'
                            )
                        ),
                        'payment'  => $payment->model_field_array(),
                        'IPN_data' => $update_info
                    ),
                    $payment
                );
            }
        }
    }

    /**
     * @param bool $pfError
     * @param bool $pfDone
     * @param mixed $pfData
     *
     * @return array
     */
    public function processPayment(bool $pfError, bool $pfDone, mixed $pfData): array
    {
        if (!$pfError && !$pfDone) {
            PayfastCommon::pflog('check order and update payment status');

            if ($pfData['payment_status'] == 'COMPLETE') {
                PayfastCommon::pflog('- Complete');
                PayfastCommon::pflog('Payfast transaction id: ' . $pfData['pf_payment_id']);
                $status           = $this->_pay_model->approved_status();//approved
                $gateway_response = __('Your payment is approved.', 'event_espresso');
            } elseif ($pfData['payment_status'] == 'Pending') {
                $status           = $this->_pay_model->pending_status();//approved
                $gateway_response = __(
                    'Your payment is in progress. Another message will be sent when payment is approved.',
                    'event_espresso'
                );
            } else {
                $status           = $this->_pay_model->declined_status();//declined
                $gateway_response = __('Your payment has been declined.', 'event_espresso');
            }
        }

        return array($status, $gateway_response);
    }

    /**
     * @return void
     */
    public function notifyPayfast(): void
    {
        if ($_GET['action'] != "process_ipn") {
            header('HTTP/1.0 200 OK');
            flush();
        }
    }

    /**
     * @param bool $pfError
     * @param string $pfErrMsg
     *
     * @return void
     */
    public function checkError(bool $pfError, string $pfErrMsg): void
    {
        if ($pfError) {
            PayfastCommon::pflog('Error occurred: ' . $pfErrMsg);
        }
    }
}
