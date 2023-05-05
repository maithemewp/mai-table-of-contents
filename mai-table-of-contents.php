<?php

/**
 * Plugin Name:     Mai Table of Contents
 * Plugin URI:      https://bizbudding.com/mai-design-pack/
 * Description:     Automatically create a table of contents from headings in your posts.
 * Version:         1.4.3
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Mai_Table_Of_Contents_Plugin Class.
 *
 * @since 0.1.0
 */
final class Mai_Table_Of_Contents_Plugin {

	/**
	 * @var   Mai_Table_Of_Contents_Plugin The one true Mai_Table_Of_Contents_Plugin
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Table_Of_Contents_Plugin Instance.
	 *
	 * Insures that only one instance of Mai_Table_Of_Contents_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Table_Of_Contents_Plugin::setup_constants() Setup the constants needed.
	 * @uses    Mai_Table_Of_Contents_Plugin::includes() Include the required files.
	 * @uses    Mai_Table_Of_Contents_Plugin::hooks() Activate, deactivate, etc.
	 * @see     Mai_Table_Of_Contents_Plugin()
	 * @return  object | Mai_Table_Of_Contents_Plugin The one true Mai_Table_Of_Contents_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_Table_Of_Contents_Plugin;
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
			define( 'MAI_TABLE_OF_CONTENTS_VERSION', '1.4.3' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_PLUGIN_DIR' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Clases Path.
		if ( ! defined( 'MAI_TABLE_OF_CONTENTS_CLASSES_DIR' ) ) {
			define( 'MAI_TABLE_OF_CONTENTS_CLASSES_DIR', MAI_TABLE_OF_CONTENTS_PLUGIN_DIR . 'classes/' );
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
			define( 'MAI_TABLE_OF_CONTENTS_BASENAME', plugin_basename( __FILE__ ) );
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
		// Classes.
		foreach ( glob( MAI_TABLE_OF_CONTENTS_CLASSES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'updater' ] );
		add_action( 'plugins_loaded', [ $this, 'run' ] );
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

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}

		// Add icons for Dashboard > Updates screen.
		if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
			$updater->addResultFilter(
				function ( $info ) use ( $icons ) {
					$info->icons = $icons;
					return $info;
				}
			);
		}
	}

	/**
	 * Runs plugin if ACF Pro is active.
	 *
	 * @return void
	 */
	public function run() {
		if ( ! class_exists( 'acf_pro' ) ) {
			return;
		}

		new Mai_Table_Of_Contents_Settings;
		new Mai_Table_Of_Contents_Block;
		new Mai_Table_Of_Contents_Display;
	}
}

/**
 * The main function for that returns Mai_Table_Of_Contents_Plugin
 *
 * The main function responsible for returning the one true Mai_Table_Of_Contents_Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Mai_Table_Of_Contents_Plugin(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Mai_Table_Of_Contents_Plugin The one true Mai_Table_Of_Contents_Plugin Instance.
 */
function maitoc() {
	return Mai_Table_Of_Contents_Plugin::instance();
}

// Get maitoc Running.
maitoc();
