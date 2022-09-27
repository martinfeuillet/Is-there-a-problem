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
class Itap_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 *  
	 */
	public static function activate() {
		//check if woocommerce and rankmath are installed
		if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			add_action('admin_notices', function () {
				//deactivate plugin
				deactivate_plugins(plugin_basename(__FILE__));
			});
			exit('woocommerce is not installed, please install it and activate it first');
		}
	}
}
