<?php
/*
Copyright (c) 2023 Payfast (Pty) Ltd You (being anyone who is not Payfast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active Payfast account. If your Payfast account is terminated for any reason, you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
*/
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
// define the plugin directory path and URL
define( 'EE_PAYFAST_PLUGIN_BASENAME', plugin_basename(EE_PAYFAST_PLUGIN_FILE));
define( 'EE_PAYFAST_URL', plugin_dir_url( __FILE__ ));

Class  EE_Payfast extends EE_Addon {
    /**
     * class constructor
     */
    public function __construct() {
    }
    public static function register_addon() {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Payfast',
            array(
                'version' => EE_PAYFAST_VERSION,
                'min_core_version' => '4.6.0.dev.000',
                'main_file_path' => EE_PAYFAST_PLUGIN_FILE,
                'admin_callback' => 'additional_admin_hooks',
                'payment_method_paths' => array(
                    EE_PAYFAST_PLUGIN_PATH . 'payment_methods' . DS . 'Payfast',
                ),
            ));
    }
    /**
     * 	additional_admin_hooks
     *
     *  @access 	public
     *  @return 	void
     */
    public function additional_admin_hooks() 
    {
        // is admin and not in M-Mode ?
        if ( is_admin() && ! EE_Maintenance_Mode::instance()->level() ) 
        {
            add_filter( 'plugin_action_links', array( $this, 'plugin_actions' ), 10, 2 );
        }
    }
    /**
     * plugin_actions
     *
     * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
     * @param $links
     * @param $file
     * @return array
     */
    public function plugin_actions( $links, $file ) 
    {
        if ( $file == EE_PAYFAST_PLUGIN_BASENAME ) 
        {
            // before other links
            array_unshift( $links, '<a href="admin.php?page=espresso_payment_settings">' . __('Settings') . '</a>' );
        }
        return $links;
    }
}
// End of file EE_New_Payment_Method.class.php
// Location: wp-content/plugins/espresso-new-payment-method/EE_New_Payment_Method.class.php