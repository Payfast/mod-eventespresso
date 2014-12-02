<?php

function event_espresso_payfast_payment_settings() 
{
    global $active_gateways;
    if (isset($_POST['update_payfast'])) 
    {
        $payfast_settings['payfast_merchant_id'] =  $_POST['payfast_merchant_id'];
        $payfast_settings['payfast_merchant_key'] = $_POST['payfast_merchant_key'];
        
        $payfast_settings['image_url'] = $_POST['image_url'];
        $payfast_settings['currency_format'] = $_POST['currency_format'];
        $payfast_settings['use_sandbox'] = empty($_POST['use_sandbox']) ? false : true;
        $payfast_settings['bypass_payment_page'] = $_POST['bypass_payment_page'];
        $payfast_settings['force_ssl_return'] = empty($_POST['force_ssl_return']) ? false : true;
        $payfast_settings['no_shipping'] = $_POST['no_shipping'];
        $payfast_settings['button_url'] = $_POST['button_url'];
        update_option( 'event_espresso_payfast_settings', $payfast_settings );
        echo '<div id="message" class="updated fade"><p><strong>' . __('Payfast settings saved.', 'event_espresso') . '</strong></p></div>';
    }
    $payfast_settings = get_option( 'event_espresso_payfast_settings' );
    if ( empty( $payfast_settings ) ) 
    {
        if (file_exists(EVENT_ESPRESSO_GATEWAY_DIR . "/payfast/payfast.png" ) ) 
        {
            $button_url = EVENT_ESPRESSO_GATEWAY_URL . "/payfast/payfast.png";
        } 
        else 
        {
            $button_url = EVENT_ESPRESSO_PLUGINFULLURL . "gateways/payfast/payfast.png";
        }
        $payfast_settings['payfast_merchant_id'] = '';
        $payfast_settings['payfast_merchant_key'] = '';
        $payfast_settings['image_url'] = '';
        $payfast_settings['currency_format'] = 'ZAR';
        $payfast_settings['use_sandbox'] = false;
        $payfast_settings['bypass_payment_page'] = 'N';
        $payfast_settings['force_ssl_return'] = false;
        $payfast_settings['no_shipping'] = '0';
        $payfast_settings['button_url'] = $button_url;
        if (add_option('event_espresso_payfast_settings', $payfast_settings, '', 'no') == false) 
        {
            update_option('event_espresso_payfast_settings', $payfast_settings);
        }
    }

    //Open or close the postbox div
    if ( empty( $_REQUEST['deactivate_payfast'] ) && ( !empty( $_REQUEST['activate_payfast'] ) || array_key_exists( 'payfast', $active_gateways ) ) ) 
    {
        $postbox_style = '';
    } 
    else 
    {
        $postbox_style = 'closed';
    }
    ?>

    <div class="metabox-holder">
        <div class="postbox <?php echo $postbox_style; ?>">
            <div title="Click to toggle" class="handlediv"><br /></div>
            <h3 class="hndle">
                <?php _e('Payfast Settings', 'event_espresso'); ?>
            </h3>
            <div class="inside">
                <div class="padding">
                    <?php
                    if ( !empty( $_REQUEST['activate_payfast'] ) ) 
                    {
                        $active_gateways['payfast'] = dirname(__FILE__);
                        update_option( 'event_espresso_active_gateways', $active_gateways );
                    }
                    if ( !empty($_REQUEST['deactivate_payfast'] ) ) 
                    {
                        unset( $active_gateways['payfast'] );
                        update_option('event_espresso_active_gateways', $active_gateways);
                    }
                    echo '<ul>';
                    if (array_key_exists('payfast', $active_gateways)) 
                    {
                        echo '<li id="deactivate_payfast" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&deactivate_payfast=true\';" class="red_alert pointer"><strong>' . __('Deactivate Payfast Payment?', 'event_espresso') . '</strong></li>';
                        event_espresso_display_payfast_settings();
                    } 
                    else 
                    {
                        echo '<li id="activate_payfast" style="width:30%;" onclick="location.href=\'' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=payment_gateways&activate_payfast=true\';" class="green_alert pointer"><strong>' . __('Activate Payfast Payment?', 'event_espresso') . '</strong></li>';
                    }
                    echo '</ul>';
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

//Payfast Settings Form
function event_espresso_display_payfast_settings() {
    $payfast_settings = get_option('event_espresso_payfast_settings');
   
    ?>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
        <table width="99%" border="0" cellspacing="5" cellpadding="5">
            <tr>
                <td valign="top"><ul>
                        <li>
                            <label for="payfast_id">
                                <?php _e('Payfast Merchant ID', 'event_espresso'); ?>
                            </label>
                            <input type="text" name="payfast_merchant_id" size="35" value="<?php echo $payfast_settings['payfast_merchant_id']; ?>">
                            <br />
                            
                            <label for="payfast_id">
                                <?php _e('Payfast Merchant key', 'event_espresso'); ?>
                            </label>
                            <input type="text" name="payfast_merchant_key" size="35" value="<?php echo $payfast_settings['payfast_merchant_key']; ?>">
                            
                        </li>
                        <li>
                            <label for="currency_format">
                                <?php _e('Select the Currency for Your Country', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=currency_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
                            </label>
                            <select name="currency_format">
                                <option value="<?php echo $payfast_settings['currency_format']; ?>"><?php echo $payfast_settings['currency_format']; ?></option>
                                <option value="USD">
                                    <?php _e('U.S. Dollars ($)', 'event_espresso'); ?>
                                </option>
                                <option value="GBP">
                                    <?php _e('Pounds Sterling (&pound;)', 'event_espresso'); ?>
                                </option>
                                <option value="CAD">
                                    <?php _e('Canadian Dollars (C $)', 'event_espresso'); ?>
                                </option>
                                <option value="AUD">
                                    <?php _e('Australian Dollars (A $)', 'event_espresso'); ?>
                                </option>
                                <option value="BRL">
                                    <?php _e('Brazilian Real (only for Brazilian users)', 'event_espresso'); ?>
                                </option>
                                <option value="CHF">
                                    <?php _e('Swiss Franc', 'event_espresso'); ?>
                                </option>
                                <option value="CZK">
                                    <?php _e('Czech Koruna', 'event_espresso'); ?>
                                </option>
                                <option value="DKK">
                                    <?php _e('Danish Krone', 'event_espresso'); ?>
                                </option>
                                <option value="EUR">
                                    <?php _e('Euros (&#8364;)', 'event_espresso'); ?>
                                </option>
                                <option value="HKD">
                                    <?php _e('Hong Kong Dollar ($)', 'event_espresso'); ?>
                                </option>
                                <option value="HUF">
                                    <?php _e('Hungarian Forint', 'event_espresso'); ?>
                                </option>
                                <option value="ILS">
                                    <?php _e('Israeli Shekel', 'event_espresso'); ?>
                                </option>
                                <option value="JPY">
                                    <?php _e('Yen (&yen;)', 'event_espresso'); ?>
                                </option>
                                <option value="MXN">
                                    <?php _e('Mexican Peso', 'event_espresso'); ?>
                                </option>
                                <option value="MYR">
                                    <?php _e('Malaysian Ringgits (only for Malaysian users)', 'event_espresso'); ?>
                                </option>
                                <option value="NOK">
                                    <?php _e('Norwegian Krone', 'event_espresso'); ?>
                                </option>
                                <option value="NZD">
                                    <?php _e('New Zealand Dollar ($)', 'event_espresso'); ?>
                                </option>
                                <option value="PHP">
                                    <?php _e('Philippine Pesos', 'event_espresso'); ?>
                                </option>
                                <option value="PLN">
                                    <?php _e('Polish Zloty', 'event_espresso'); ?>
                                </option>
                                <option value="SEK">
                                    <?php _e('Swedish Krona', 'event_espresso'); ?>
                                </option>
                                <option value="SGD">
                                    <?php _e('Singapore Dollar ($)', 'event_espresso'); ?>
                                </option>
                                <option value="THB">
                                    <?php _e('Thai Baht', 'event_espresso'); ?>
                                </option>
                                <option value="TRY">
                                    <?php _e('Turkish Lira (only for Turkish users)', 'event_espresso'); ?>
                                </option>
                                <option value="TWD">
                                    <?php _e('Taiwan New Dollars', 'event_espresso'); ?>
                                </option>
                                <option value="ZAR">
                                    <?php _e('South African Rands', 'event_espresso'); ?>
                                </option>
                            </select>
                             </li>
                     
                    </ul></td>
                <td valign="top"><ul><li>
                        <label for="bypass_payment_page">
                            <?php _e('Bypass Payment Overview Page', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=bypass_confirmation"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
                        </label>
                        <?php
                        $values = array(
                                array('id' => 'N', 'text' => __('No', 'event_espresso')),
                                array('id' => 'Y', 'text' => __('Yes', 'event_espresso')));
                        echo select_input('bypass_payment_page', $values, $payfast_settings['bypass_payment_page']);
                        ?>
                        </li>
                        <li>
                            <label for="use_sandbox">
                                <?php _e('Use the Debugging Feature and the', 'event_espresso'); ?> <a href="https://www.payfast.com/developers/" title="Payfast Sandbox Login||Sandbox Tutorial||Getting Started with Payfast Sandbox" target="_blank"><?php _e('Payfast Sandbox', 'event_espresso'); ?></a><a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=payfast_sandbox_info"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
                            </label>
                            <input name="use_sandbox" type="checkbox" value="1" <?php echo $payfast_settings['use_sandbox'] ? 'checked="checked"' : '' ?> />
                            <br />
                        </li>
                        <li>
                            <label for="force_ssl_return">
                                <?php _e('Force HTTPS on Return URL', 'event_espresso'); ?>
                                <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=force_ssl_return"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
                            </label>
                            <input name="force_ssl_return" type="checkbox" value="1" <?php echo $payfast_settings['force_ssl_return'] ? 'checked="checked"' : '' ?> /></li>
                        
                        <li>
                            <label for="button_url">
                                <?php _e('Button Image URL', 'event_espresso'); ?> <a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=button_image"><img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>/images/question-frame.png" width="16" height="16" /></a>
                            </label>
                            <input type="text" name="button_url" size="34" value="<?php echo $payfast_settings['button_url']; ?>" />
                            <a href="media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true&amp;width=640&amp;height=580&amp;rel=button_url" id="add_image" class="thickbox" title="Add an Image"><img src="images/media-button-image.gif" alt="Add an Image"></a>  </li><li>
                            <label><?php _e('Current Button Image:', 'event_espresso'); ?></label>
                            <?php echo '<img src="' . $payfast_settings['button_url'] . '" />'; ?></li>
                    </ul></td>
            </tr>
        </table>
        <p><strong style="color:#F00"><?php _e('Attention!', 'event_espresso'); ?></strong><br /><?php _e('For Payfast IPN to work, you need a Business or Premier account.', 'event_espresso'); ?>
        <p>
            <input type="hidden" name="update_payfast" value="update_payfast">
            <input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Payfast Settings', 'event_espresso') ?>" id="save_payfast_settings" />
        </p>
    </form>
    <div id="payfast_sandbox_info" style="display:none">
        <h2><?php _e('Payfast Sandbox', 'event_espresso'); ?></h2>
        <p><?php _e('In addition to using the Payfast Sandbox fetaure. The debugging feature will also output the form varibales to the payment page, send an email to the admin that contains the all Payfast variables.', 'event_espresso'); ?></p>
        <hr />
        <p><?php _e('The Payfast Sandbox is a testing environment that is a duplicate of the live Payfast site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live Payfast environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?></p>
    </div>
    <div id="image_url_info" style="display:none">
        <h2>
            <?php _e('Payfast Image URL (logo for payment page)', 'event_espresso'); ?>
        </h2>
        <p>
            <?php _e('The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the Payfast checkout pages.', 'event_espresso'); ?>
        </p>
        <p>
            <?php _e('Default - Your business name, if you have a Business account, or your email address, if you have Premier or Personal account.', 'event_espresso'); ?>
        </p>
    </div>
    <div id="currency_info" style="display:none">
        <h2><?php _e('Payfast Currency', 'event_espresso'); ?></h2>
        <p><?php _e('Payfast uses 3-character ISO-4217 codes for specifying currencies in fields and variables. </p><p>The default currency code is US Dollars (USD). If you want to require or accept payments in other currencies, select the currency you wish to use. The dropdown lists all currencies that Payfast (currently) supports.', 'event_espresso'); ?> </p>
    </div>
    <div id="no_shipping" style="display:none">
        <h2><?php _e('Shipping Address', 'event_espresso'); ?></h2>
        <p><?php _e('By default, Payfast will display shipping address information on the Payfast payment screen. If you plan on shipping items to a registrant (shirts, invoices, etc) then use this option. Otherwise it should not be used, as it will require a shipping address when someone registers for an event.', 'event_espresso'); ?></p>
    </div>
    <?php
}

add_action('action_hook_espresso_display_gateway_settings','event_espresso_payfast_payment_settings');