<?php

/**
 * Plugin Name:     Starter Plugin
 * Plugin URI:      https://website.com
 * Description:     Core funtionality for website.com
 * Version:         0.1.0
 *
 * Author:          BizBudding, Mike Hemberger
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Starter_Plugin Class.
 *
 * @since 0.1.0
 */
final class Starter_Plugin {

	/**
	 * @var   Starter_Plugin The one true Starter_Plugin
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Starter_Plugin Instance.
	 *
	 * Insures that only one instance of Starter_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Starter_Plugin::setup_constants() Setup the constants needed.
	 * @uses    Starter_Plugin::includes() Include the required files.
	 * @uses    Starter_Plugin::hooks() Activate, deactivate, etc.
	 * @see     Starter_Plugin()
	 * @return  object | Starter_Plugin The one true Starter_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Starter_Plugin;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'STARTER_PLUGIN_VERSION' ) ) {
			define( 'STARTER_PLUGIN_VERSION', '0.1.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'STARTER_PLUGIN_PLUGIN_DIR' ) ) {
			define( 'STARTER_PLUGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path.
		if ( ! defined( 'STARTER_PLUGIN_INCLUDES_DIR' ) ) {
			define( 'STARTER_PLUGIN_INCLUDES_DIR', STARTER_PLUGIN_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'STARTER_PLUGIN_PLUGIN_URL' ) ) {
			define( 'STARTER_PLUGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'STARTER_PLUGIN_PLUGIN_FILE' ) ) {
			define( 'STARTER_PLUGIN_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'STARTER_PLUGIN_BASENAME' ) ) {
			define( 'STARTER_PLUGIN_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}

	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
		// Includes.
		foreach ( glob( STARTER_PLUGIN_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {

		add_action( 'admin_init', array( $this, 'updater' ) );
		add_action( 'init',       array( $this, 'register_content_types' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @uses    https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return  void
	 */
	public function updater() {

		// Bail if current user cannot manage plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/bizbudding/starter-plugin/', __FILE__, 'textdomain' );
	}

	/**
	 * Register content types.
	 *
	 * @return  void
	 */
	public function register_content_types() {

		/***********************
		 *  Custom Post Types  *
		 ***********************/

		// register_post_type( 'slideshow', array(
		// 	'exclude_from_search' => false,
		// 	'has_archive'         => true,
		// 	'hierarchical'        => false,
		// 	'labels'              => array(
		// 		'name'               => _x( 'Slideshows', 'Slideshow general name',         'textdomain' ),
		// 		'singular_name'      => _x( 'Slideshow',  'Slideshow singular name',        'textdomain' ),
		// 		'menu_name'          => _x( 'Slideshows', 'Slideshow admin menu',           'textdomain' ),
		// 		'name_admin_bar'     => _x( 'Slideshow',  'Slideshow add new on admin bar', 'textdomain' ),
		// 		'add_new'            => _x( 'Add New',    'Slideshow',                      'textdomain' ),
		// 		'add_new_item'       => __( 'Add New Slideshow',                            'textdomain' ),
		// 		'new_item'           => __( 'New Slideshow',                                'textdomain' ),
		// 		'edit_item'          => __( 'Edit Slideshow',                               'textdomain' ),
		// 		'view_item'          => __( 'View Slideshow',                               'textdomain' ),
		// 		'all_items'          => __( 'All Slideshows',                               'textdomain' ),
		// 		'search_items'       => __( 'Search Slideshows',                            'textdomain' ),
		// 		'parent_item_colon'  => __( 'Parent Slideshows:',                           'textdomain' ),
		// 		'not_found'          => __( 'No Slideshows found.',                         'textdomain' ),
		// 		'not_found_in_trash' => __( 'No Slideshows found in Trash.',                'textdomain' )
		// 	),
		// 	'menu_icon'          => 'dashicons-images-alt2',
		// 	'public'             => true,
		// 	'publicly_queryable' => true,
		// 	'show_in_menu'       => true,
		// 	'show_in_nav_menus'  => true,
		// 	'show_ui'            => true,
		// 	'rewrite'            => array( 'slug' => 'slideshows', 'with_front' => false ),
		// 	'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'genesis-cpt-archives-settings', 'genesis-adjacent-entry-nav' ),
		// 	// 'taxonomies'         => array( 'slideshow_cat' ),
		// ) );

		/***********************
		 *  Custom Taxonomies  *
		 ***********************/

		// register_taxonomy( 'taxonomy', array( 'post' ), array(
		// 	'hierarchical'               => false,
		// 	'labels'                     => array(
		// 		'name'                       => _x( 'Taxonomies', 'Taxonomy General Name',  'textdomain' ),
		// 		'singular_name'              => _x( 'Taxonomy',   'Taxonomy Singular Name', 'textdomain' ),
		// 		'menu_name'                  => __( 'Taxonomies',                           'textdomain' ),
		// 		'all_items'                  => __( 'All Items',                            'textdomain' ),
		// 		'parent_item'                => __( 'Parent Item',                          'textdomain' ),
		// 		'parent_item_colon'          => __( 'Parent Item:',                         'textdomain' ),
		// 		'new_item_name'              => __( 'New Item Name',                        'textdomain' ),
		// 		'add_new_item'               => __( 'Add New Item',                         'textdomain' ),
		// 		'edit_item'                  => __( 'Edit Item',                            'textdomain' ),
		// 		'update_item'                => __( 'Update Item',                          'textdomain' ),
		// 		'view_item'                  => __( 'View Item',                            'textdomain' ),
		// 		'separate_items_with_commas' => __( 'Separate items with commas',           'textdomain' ),
		// 		'add_or_remove_items'        => __( 'Add or remove items',                  'textdomain' ),
		// 		'choose_from_most_used'      => __( 'Choose from the most used',            'textdomain' ),
		// 		'popular_items'              => __( 'Popular Items',                        'textdomain' ),
		// 		'search_items'               => __( 'Search Items',                         'textdomain' ),
		// 		'not_found'                  => __( 'Not Found',                            'textdomain' ),
		// 	),
		// 	'meta_box_cb'                => false, // Hides metabox.
		// 	'public'                     => true,
		// 	'show_admin_column'          => true,
		// 	'show_in_nav_menus'          => true,
		// 	'show_tagcloud'              => true,
		// 	'show_ui'                    => true,
		// ) );

	}

	/**
	 * Plugin activation.
	 *
	 * @return  void
	 */
	public function activate() {
		$this->register_content_types();
		flush_rewrite_rules();
	}

}

/**
 * The main function for that returns Starter_Plugin
 *
 * The main function responsible for returning the one true Starter_Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Starter_Plugin(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Starter_Plugin The one true Starter_Plugin Instance.
 */
function Starter_Plugin() {
	return Starter_Plugin::instance();
}

// Get Starter_Plugin Running.
Starter_Plugin();
