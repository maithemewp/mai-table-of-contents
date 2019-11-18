<?php

/**
 * Plugin Name:     Mai Table of Contents
 * Plugin URI:      https://maitheme.com
 * Description:     Automatically create a table of contents from headings in your posts.
 * Version:         0.1.2
 *
 * Author:          BizBudding, Mike Hemberger
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Mai_Table_Of_Contents Class.
 *
 * @since 0.1.0
 */
final class Mai_Table_Of_Contents {

	/**
	 * @var   Mai_Table_Of_Contents The one true Mai_Table_Of_Contents
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Table_Of_Contents Instance.
	 *
	 * Insures that only one instance of Mai_Table_Of_Contents exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Table_Of_Contents::setup_constants() Setup the constants needed.
	 * @uses    Mai_Table_Of_Contents::includes() Include the required files.
	 * @uses    Mai_Table_Of_Contents::hooks() Activate, deactivate, etc.
	 * @see     Mai_Table_Of_Contents()
	 * @return  object | Mai_Table_Of_Contents The one true Mai_Table_Of_Contents
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_Table_Of_Contents;
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-table-of-contents' ), '1.0' );
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-table-of-contents' ), '1.0' );
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
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_VERSION' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_VERSION', '0.1.2' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_PLUGIN_DIR' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path.
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_INCLUDES_DIR' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_INCLUDES_DIR', MAI_TABLE_OF_CONTENTS_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_PLUGIN_URL' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_PLUGIN_FILE' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_BASENAME' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
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
		foreach ( glob( MAI_TABLE_OF_CONTENTS_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		add_action( 'admin_init',             array( $this, 'updater' ) );
		add_filter( 'acf/settings/load_json', array( $this, 'load_json' ) );
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
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/maithemewp/mai-table-of-contents/', __FILE__, 'mai-table-of-contents' );
	}

	/**
	 * Add path to load acf json files.
	 *
	 * @param    array  The existing acf-json paths.
	 *
	 * @return   array  The modified paths.
	 */
	function load_json( $paths ) {
		$paths[] = untrailingslashit( MAI_TABLE_OF_CONTENTS_PLUGIN_DIR ) . '/acf-json';
		return $paths;
	}

}

/**
 * The main function for that returns Mai_Table_Of_Contents
 *
 * The main function responsible for returning the one true Mai_Table_Of_Contents
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Mai_Table_Of_Contents(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Mai_Table_Of_Contents The one true Mai_Table_Of_Contents Instance.
 */
function maitoc() {
	return Mai_Table_Of_Contents::instance();
}

// Get maitoc Running.
maitoc();
