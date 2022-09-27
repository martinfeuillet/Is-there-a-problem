<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://ingenius.agency/
 *  
 *
 * @package    Wc_Prod_Desc_Gen
 * @subpackage Wc_Prod_Desc_Gen/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 *  
 * @package    Wc_Prod_Desc_Gen
 * @subpackage Wc_Prod_Desc_Gen/includes
 * @author     Ingenius martin@ingenius.agency
 */
class Itap_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 *  
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-prod-desc-gen',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
