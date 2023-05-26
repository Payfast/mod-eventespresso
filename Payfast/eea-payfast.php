<?php
/*
  Plugin Name: Payfast Payment Gateway for Event Espresso
  Plugin URI: https://www.payfast.io
  Description: Payfast payment gateway
  Version: 1.1.8
  Author: Payfast
  Author URI: https://www.payfast.io
  Copyright (c) 2023 Payfast (Pty) Ltd You (being anyone who is not Payfast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active Payfast account. If your Payfast account is terminated for any reason, you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
define( 'EE_PAYFAST_VERSION', '1.1.8' );
define( 'EE_PAYFAST_PLUGIN_FILE',  __FILE__ );
define( 'EE_PAYFAST_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

function load_espresso_payfast()
{
    if ( class_exists( 'EE_Addon' ))
    {
    // new_payment_method version
    require_once ( EE_PAYFAST_PLUGIN_PATH . 'EE_Payfast.class.php' );
    EE_Payfast::register_addon();
    }
}
add_action( 'AHEE__EE_System__load_espresso_addons', 'load_espresso_payfast' );