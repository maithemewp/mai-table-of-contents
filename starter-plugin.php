<?php
/**
 * Plugin Name:     Website - plugin
 * Plugin URI:      https://website.com
 * Description:     Plugin worthy funtionality for website.com
 * Version:         1.0.0
 *
 * Author:          BizBudding, Mike Hemberger
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Website_Plugin_Setup' ) ) :

/**
 * Main Website_Plugin_Setup Class.
 *
 * @since 1.0.0
 */
final class Website_Plugin_Setup {

	/**
	 * @var Website_Plugin_Setup The one true Website_Plugin_Setup
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Main Website_Plugin_Setup Instance.
	 *
	 * Insures that only one instance of Website_Plugin_Setup exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   1.0.0
	 * @static  var array $instance
	 * @uses    Website_Plugin_Setup::setup_constants() Setup the constants needed.
	 * @uses    Website_Plugin_Setup::includes() Include the required files.
	 * @uses    Website_Plugin_Setup::setup() Activate, deactivate, etc.
	 * @see     Website_Plugin()
	 * @return  object | Website_Plugin_Setup The one true Website_Plugin_Setup
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup
			self::$instance = new Website_Plugin_Setup;
			// Methods
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->setup();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'Website_Plugin' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'Website_Plugin' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'WEBSITE_PLUGIN_VERSION' ) ) {
			define( 'WEBSITE_PLUGIN_VERSION', '1.0.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'WEBSITE_PLUGIN_PLUGIN_DIR' ) ) {
			define( 'WEBSITE_PLUGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path
		if ( ! defined( 'WEBSITE_PLUGIN_INCLUDES_DIR' ) ) {
			define( 'WEBSITE_PLUGIN_INCLUDES_DIR', WEBSITE_PLUGIN_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'WEBSITE_PLUGIN_PLUGIN_URL' ) ) {
			define( 'WEBSITE_PLUGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'WEBSITE_PLUGIN_PLUGIN_FILE' ) ) {
			define( 'WEBSITE_PLUGIN_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'WEBSITE_PLUGIN_BASENAME' ) ) {
			define( 'WEBSITE_PLUGIN_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}

	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function includes() {
		foreach ( glob( WEBSITE_PLUGIN_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
		require_once( WEBSITE_PLUGIN_INCLUDES_DIR . 'vendor/extended-cpts.php' );
		require_once( WEBSITE_PLUGIN_INCLUDES_DIR . 'vendor/extended-taxos.php' );
	}

	public function setup() {

		add_action( 'init', array( $this, 'register_content_types' ) );

		register_activation_hook(   __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	}

	public function register_content_types() {

		/***********************
		 *  Custom Post Types  *
		 ***********************/

		// Testimonials
		// register_extended_post_type( 'testimonial', array(
		// 	'menu_icon'           => 'dashicons-format-quote',
		// 	'public'              => false,
		// 	'publicly_queryable'  => true,
		// 	'show_ui'             => true,
		// 	'show_in_menu'        => true,
		// 	'show_in_nav_menus'   => false,
		// 	'exclude_from_search' => true,
		// 	'supports'            => array( 'title', 'editor', 'thumbnail' ),
		// ), array(
		// 	'singular' => 'Testimonial',
		// 	'plural'   => 'Testimonials',
		// 	'slug'     => 'testimonials'
		// ) );

		/***********************
		 *  Custom Taxonomies  *
		 ***********************/

		// Testimonial Categories
		// register_extended_taxonomy( 'testimonial_cat', 'testimonial', array(
		// 	'public'            => false,
		// 	'hierarchical'      => true,
		// 	'query_var'         => true,
		// 	'show_in_menu'      => true,
		// 	'show_in_nav_menus' => false,
		// 	'show_ui'           => true,
		// 	'rewrite'           => array(
		// 		'slug'       => 'testimonial-category',
		// 		'with_front' => true,
		// 	),
		// ), array(
		// 	'singular' => 'Testimonial Category',
		// 	'plural'   => 'Testimonial Categories',
		// ) );

	}

	public function activate() {

		$this->register_content_types();

		flush_rewrite_rules();
	}

}
endif; // End if class_exists check.

/**
 * The main function for that returns Website_Plugin_Setup
 *
 * The main function responsible for returning the one true Website_Plugin_Setup
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Website_Plugin(); ?>
 *
 * @since 1.0.0
 *
 * @return object|Website_Plugin_Setup The one true Website_Plugin_Setup Instance.
 */
function Website_Plugin() {
	return Website_Plugin_Setup::instance();
}

// Get Website_Plugin Running.
Website_Plugin();
