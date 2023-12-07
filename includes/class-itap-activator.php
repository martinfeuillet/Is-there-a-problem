<?php

/**
 * Fired during plugin activation
 *
 * @link       https://ingenius.agency/
 *
 *
 * @package    Wc_Prod_Desc_Gen
 * @subpackage Wc_Prod_Desc_Gen/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 *
 * @package    Wc_Prod_Desc_Gen
 * @subpackage Wc_Prod_Desc_Gen/includes
 * @author     Ingenius martin@ingenius.agency
 */
class Itap_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     *
     */
    public static function activate() {
        // Check if this is a multisite installation
        if ( is_multisite() ) {
            // Check if WooCommerce is network activated
            if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
                add_action( 'admin_notices' , function () {
                    echo '<div class="error"><p>WooCommerce must be network activated for this plugin to work.</p></div>';
                    deactivate_plugins( plugin_basename( __FILE__ ) );
                } );
                exit( 'WooCommerce is not network-activated. Please network-activate WooCommerce first.' );
            }
        } else {
            // Check if WooCommerce is active on a regular WordPress installation
            if ( ! in_array( 'woocommerce/woocommerce.php' , apply_filters( 'active_plugins' , get_option( 'active_plugins' ) ) ) ) {
                add_action( 'admin_notices' , function () {
                    echo '<div class="error"><p>WooCommerce must be activated for this plugin to work.</p></div>';
                    deactivate_plugins( plugin_basename( __FILE__ ) );
                } );
                exit( 'WooCommerce is not installed or activated. Please install and activate WooCommerce first.' );
            }
        }

        // Additional activation code here (if any)
    }
}
