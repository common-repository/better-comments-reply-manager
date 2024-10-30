<?php

/**
 * @link        https://wordpress.org/plugins/
 * @since       1.0.0
 * @package     Better_Comments_Reply_Manager
 *
 * @wordpress-plugin
 *
 * Plugin Name: Better Comments Reply Manager
 * Plugin URI:  https://wordpress.org/plugins/
 * Description: With this plugin, you can easily check which comments require a reply from each blog post's author.
 * Version:     1.0.0
 * Author:      Hernán Villanueva
 * Author URI:  https://profiles.wordpress.org/chvillanuevap/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: better-comments-reply-manager
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-better-comments-reply-manager-activator.php
 */
function activate_better_comments_reply_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-better-comments-reply-manager-activator.php';
	Better_Comments_Reply_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-better-comments-reply-manager-deactivator.php
 */
function deactivate_better_comments_reply_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-better-comments-reply-manager-deactivator.php';
	Better_Comments_Reply_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_better_comments_reply_manager' );
register_deactivation_hook( __FILE__, 'deactivate_better_comments_reply_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-better-comments-reply-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since   1.0.0
 */
function run_better_comments_reply_manager() {

	$plugin = new Better_Comments_Reply_Manager();
	$plugin->run();

}

run_better_comments_reply_manager();
