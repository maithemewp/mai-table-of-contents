<?php

/**
 * Plugin Name:     Mai Table of Contents
 * Plugin URI:      https://maitheme.com
 * Description:     Automatically create a table of contents from headings in your posts.
 * Version:         0.1.0
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
			define( 'MAI_TABLE_OF_CONTENTS_VERSION', '0.1.0' );
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
		// $updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/bizbudding/starter-plugin/', __FILE__, 'mai-table-of-contents' );
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
		// 		'name'               => _x( 'Slideshows', 'Slideshow general name',         'mai-table-of-contents' ),
		// 		'singular_name'      => _x( 'Slideshow',  'Slideshow singular name',        'mai-table-of-contents' ),
		// 		'menu_name'          => _x( 'Slideshows', 'Slideshow admin menu',           'mai-table-of-contents' ),
		// 		'name_admin_bar'     => _x( 'Slideshow',  'Slideshow add new on admin bar', 'mai-table-of-contents' ),
		// 		'add_new'            => _x( 'Add New',    'Slideshow',                      'mai-table-of-contents' ),
		// 		'add_new_item'       => __( 'Add New Slideshow',                            'mai-table-of-contents' ),
		// 		'new_item'           => __( 'New Slideshow',                                'mai-table-of-contents' ),
		// 		'edit_item'          => __( 'Edit Slideshow',                               'mai-table-of-contents' ),
		// 		'view_item'          => __( 'View Slideshow',                               'mai-table-of-contents' ),
		// 		'all_items'          => __( 'All Slideshows',                               'mai-table-of-contents' ),
		// 		'search_items'       => __( 'Search Slideshows',                            'mai-table-of-contents' ),
		// 		'parent_item_colon'  => __( 'Parent Slideshows:',                           'mai-table-of-contents' ),
		// 		'not_found'          => __( 'No Slideshows found.',                         'mai-table-of-contents' ),
		// 		'not_found_in_trash' => __( 'No Slideshows found in Trash.',                'mai-table-of-contents' )
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
		// 		'name'                       => _x( 'Taxonomies', 'Taxonomy General Name',  'mai-table-of-contents' ),
		// 		'singular_name'              => _x( 'Taxonomy',   'Taxonomy Singular Name', 'mai-table-of-contents' ),
		// 		'menu_name'                  => __( 'Taxonomies',                           'mai-table-of-contents' ),
		// 		'all_items'                  => __( 'All Items',                            'mai-table-of-contents' ),
		// 		'parent_item'                => __( 'Parent Item',                          'mai-table-of-contents' ),
		// 		'parent_item_colon'          => __( 'Parent Item:',                         'mai-table-of-contents' ),
		// 		'new_item_name'              => __( 'New Item Name',                        'mai-table-of-contents' ),
		// 		'add_new_item'               => __( 'Add New Item',                         'mai-table-of-contents' ),
		// 		'edit_item'                  => __( 'Edit Item',                            'mai-table-of-contents' ),
		// 		'update_item'                => __( 'Update Item',                          'mai-table-of-contents' ),
		// 		'view_item'                  => __( 'View Item',                            'mai-table-of-contents' ),
		// 		'separate_items_with_commas' => __( 'Separate items with commas',           'mai-table-of-contents' ),
		// 		'add_or_remove_items'        => __( 'Add or remove items',                  'mai-table-of-contents' ),
		// 		'choose_from_most_used'      => __( 'Choose from the most used',            'mai-table-of-contents' ),
		// 		'popular_items'              => __( 'Popular Items',                        'mai-table-of-contents' ),
		// 		'search_items'               => __( 'Search Items',                         'mai-table-of-contents' ),
		// 		'not_found'                  => __( 'Not Found',                            'mai-table-of-contents' ),
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
