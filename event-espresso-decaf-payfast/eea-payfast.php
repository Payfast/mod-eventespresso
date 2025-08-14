<?php

/*
  Plugin Name: Payfast Payment Gateway for Event Espresso
  Plugin URI: https://www.payfast.io
  Description: Payfast payment gateway
  Version: 1.2.1
  Author: Payfast
  Author URI: https://www.payfast.io
  Copyright (c) 2025 Payfast (Pty) Ltd
 */
define('EE_PAYFAST_VERSION', '1.2.1');
define('EE_PAYFAST_PLUGIN_FILE', __FILE__);
define('EE_PAYFAST_PLUGIN_PATH', plugin_dir_path(__FILE__));

function load_espresso_payfast()
{
    if (class_exists('EE_Addon')) {
        require_once EE_PAYFAST_PLUGIN_PATH . 'EE_Payfast.class.php';
        EE_Payfast::register_addon();
    }
}

add_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_payfast');
