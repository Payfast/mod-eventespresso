<?php

/*
 * Copyright (c) 2025 Payfast (Pty) Ltd
 */
if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit();
}
// define the plugin directory path and URL
define('EE_PAYFAST_PLUGIN_BASENAME', plugin_basename(EE_PAYFAST_PLUGIN_FILE));
define('EE_PAYFAST_URL', plugin_dir_url(__FILE__));

class  EE_Payfast extends EE_Addon
{
    /**
     * class constructor
     */
    public function __construct()
    {
    }

    public static function register_addon()
    {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Payfast',
            array(
                'version'              => EE_PAYFAST_VERSION,
                'min_core_version'     => '4.6.0.dev.000',
                'main_file_path'       => EE_PAYFAST_PLUGIN_FILE,
                'admin_callback'       => 'additional_admin_hooks',
                'payment_method_paths' => array(
                    EE_PAYFAST_PLUGIN_PATH . 'payment_methods' . DS . 'Payfast',
                ),
            )
        );
    }

    /**
     *    additional_admin_hooks
     *
     * @access    public
     * @return    void
     */
    public function additional_admin_hooks()
    {
        if (is_admin() && !EE_Maintenance_Mode::instance()->level()) {
            add_filter('plugin_action_links', array($this, 'plugin_actions'), 10, 2);
        }
    }

    /**
     * plugin_actions
     *
     * @param $links
     * @param $file
     *
     * @return array
     */
    public function plugin_actions($links, $file)
    {
        if ($file == EE_PAYFAST_PLUGIN_BASENAME) {
            array_unshift($links, '<a href="admin.php?page=espresso_payment_settings">' . __('Settings') . '</a>');
        }

        return $links;
    }
}
