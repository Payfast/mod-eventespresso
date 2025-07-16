<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

class EE_PMT_Payfast extends EE_PMT_Base
{
    public function __construct($pm_instance = null)
    {
        require_once $this->file_folder() . 'EEG_Payfast.gateway.php';
        $this->_gateway             = new EEG_Payfast();
        $this->_pretty_name         = __("Payfast", 'event espresso');
        $this->_default_description = sprintf(
            __(
                'You will be forwarded to Payfast in order to make payment',
                'event_espresso'
            ),
            '<strong>',
            '</strong>'
        );
        parent::__construct($pm_instance);
        $this->_default_button_url        = $this->file_url() . 'lib' . DS . 'payfast-logo.png';
        $this->_uses_separate_IPN_request = true;
    }

    public function generate_new_billing_form(EE_Transaction $transaction = null)
    {
        return null;
    }

    public function generate_new_settings_form(): EE_Payment_Method_Form
    {
        return new EE_Payment_Method_Form(array(
                                              'payment_method_type' => $this,
                                              'extra_meta_inputs'   => array(
                                                  'payfast_merchant_id'  => new EE_Text_Input(array(
                                                                                                  'html_label_text' => sprintf(
                                                                                                      __(
                                                                                                          "Payfast Merchant ID %s",
                                                                                                          "event_espresso"
                                                                                                      ),
                                                                                                      $this->get_help_tab_link(
                                                                                                      )
                                                                                                  ),
                                                                                              )),
                                                  'payfast_merchant_key' => new EE_Text_Input(array(
                                                                                                  'html_label_text' => sprintf(
                                                                                                      __(
                                                                                                          "Payfast Merchant Key %s",
                                                                                                          "event_espresso"
                                                                                                      ),
                                                                                                      $this->get_help_tab_link(
                                                                                                      )
                                                                                                  ),
                                                                                              )),
                                                  'payfast_passphrase'   => new EE_Text_Input(array(
                                                                                                  'html_label_text' => sprintf(
                                                                                                      __(
                                                                                                          "Payfast Passphrase %s",
                                                                                                          "event_espresso"
                                                                                                      ),
                                                                                                      $this->get_help_tab_link(
                                                                                                      )
                                                                                                  ),
                                                                                              )),
                                              ),

                                          ));
    }

    public function help_tabs_config(): array
    {
        return array(
            $this->get_help_tab_name() => array(
                'title'    => __("Payfast Settings", 'event_espresso'),
                'filename' => 'payment_methods_overview_payfast'
            )
        );
    }

    public function finalize_payment_for($transaction): ?EE_Payment
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $this->handle_ipn($_POST, $transaction);
        } else {
            return parent::finalize_payment_for($transaction);
        }
    }
}
