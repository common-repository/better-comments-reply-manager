<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link        https://wordpress.org/plugins/
 * @since       1.0.0
 *
 * @package     Better_Comments_Reply_Manager
 * @subpackage  Better_Comments_Reply_Manager/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since       1.0.0
 * @package     Better_Comments_Reply_Manager
 * @subpackage  Better_Comments_Reply_Manager/includes
 * @author      Hernán Villanueva <chvillanuevap@gmail.com>
 */
class Better_Comments_Reply_Manager_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain( $domain ) {

		load_plugin_textdomain(
			$domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
