<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link        https://wordpress.org/plugins/
 * @since       1.0.0
 *
 * @package     Better_Comments_Reply_Manager
 * @subpackage  Better_Comments_Reply_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since       1.0.0
 * @package     Better_Comments_Reply_Manager
 * @subpackage  Better_Comments_Reply_Manager/includes
 * @author      Hernán Villanueva <chvillanuevap@gmail.com>
 */
class Better_Comments_Reply_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     Better_Comments_Reply_Manager_Loader    $loader     Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string      $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string      $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'better-comments-reply-manager';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Better_Comments_Reply_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Better_Comments_Reply_Manager_i18n. Defines internationalization functionality.
	 * - Better_Comments_Reply_Manager_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-' . $this->plugin_name . '-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-' . $this->plugin_name . '-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-' . $this->plugin_name . '-admin.php';

		$this->loader = new Better_Comments_Reply_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Better_Comments_Reply_Manager_i18n class in order to set the domain
	 * and to register the hook with WordPress.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function set_locale() {

		$plugin_i18n = new Better_Comments_Reply_Manager_i18n( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Better_Comments_Reply_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

		// Load styles.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		// Add or remove the comment meta to comments on entry.
		$this->loader->add_action( 'comment_post', $plugin_admin, 'add_comment_meta', 10, 2 );

		// Add the 'Need Reply' custom column.
		$this->loader->add_filter( 'manage_edit-comments_columns' , $plugin_admin, 'add_comments_column' );
		$this->loader->add_filter( 'manage_comments_custom_column', $plugin_admin, 'comments_column_content', 10, 2 );

		// Add link in comment status links.
		$this->loader->add_filter( 'comment_status_links', $plugin_admin, 'comment_status_links' );

		// Add reply status CSS class for the comments.
		$this->loader->add_filter( 'comment_class', $plugin_admin, 'comment_class', 10, 5 );

		// Return comments that need a reply in the comment table.
		$this->loader->add_action( 'pre_get_comments', $plugin_admin, 'get_comments' );

		// Add action in the comment row actions.
		$this->loader->add_filter( 'comment_row_actions', $plugin_admin, 'comment_row_actions', 10, 2 );

		// Add actions in the comment bulk actions.
		$this->loader->add_action( 'admin_footer-edit-comments.php', $plugin_admin, 'comment_bulk_actions_js' );

		// Add actions in the `Edit Comment` page.
		$this->loader->add_filter( 'edit_comment_misc_actions', $plugin_admin, 'edit_comment_misc_actions', 10, 2 );

		// Add actions to bulk mark comments.
		$this->loader->add_action( 'admin_action_bcrm_bulk_mark_as_needs_reply',         $plugin_admin, 'admin_action_bulk_mark_comments' );    // Top drowndown
		$this->loader->add_action( 'admin_action_bcrm_bulk_mark_as_does_not_need_reply', $plugin_admin, 'admin_action_bulk_mark_comments' );    // Top drowndown
		$this->loader->add_action( 'admin_action_-1',                                    $plugin_admin, 'admin_action_bulk_mark_comments' );    // Bottom dropdown (assumes top dropdown = default value).

		// Add actions to mark comments.
		$this->loader->add_action( 'admin_action_bcrm_mark_as_needs_reply',         $plugin_admin, 'admin_action_mark_comments' );
		$this->loader->add_action( 'admin_action_bcrm_mark_as_does_not_need_reply', $plugin_admin, 'admin_action_mark_comments' );

		// Add actions to edit mark comments.
		$this->loader->add_filter( 'comment_edit_redirect', $plugin_admin, 'admin_action_edit_mark_comments', 10, 2 );

		// Display admin notices.
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since   1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since   1.0.0
	 * @return  Better_Comments_Reply_Manager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since   1.0.0
	 * @return  string  The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since   1.0.0
	 * @return  string  The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
