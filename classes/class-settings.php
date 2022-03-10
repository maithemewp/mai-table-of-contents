<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Table_Of_Contents_Settings {
	/**
	 * Gets it started.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_filter( 'acf/load_field/key=field_5dd59edcd62e7',   [ $this, 'get_post_types' ] );
		add_action( 'acf/render_field/key=field_5dd59edcd62e7', [ $this, 'custom_css' ] );
		add_action( 'acf/init',                                 [ $this, 'register_options_page' ] );
		add_action( 'acf/init',                                 [ $this, 'register_field_group' ] );
		add_filter( 'plugin_action_links_mai-table-of-contents/mai-table-of-contents.php', [ $this, 'add_settings_link' ], 10, 4 );
	}

	/**
	 * Gets post types for settings.
	 *
	 * @since 0.1.0
	 *
	 * @param array The field data.
	 *
	 * @return array
	 */
	function get_post_types( $field ) {
		// Reset choices.
		$field['choices'] = [];

		// Get all public post types.
		$post_types = get_post_types(
			[
				'public' => true,
			]
		);

		// Allow filtering of post types.
		$post_types = apply_filters( 'mai_table_of_contents_post_types', $post_types );

		// Loop through em.
		foreach( $post_types as $name ) {

			// Skip attachments.
			if ( 'attachment' === $name ) {
				continue;
			}

			// Get the post_type object.
			$post_type = get_post_type_object( $name );

			// Skip if no object.
			if ( ! $post_type ) {
				continue;
			}

			// Add it as a choice.
			$field['choices'][ $name ] = $post_type->label;
		}

		// Send it.
		return $field;
	}

	/**
	 * Adds custom CSS in the first field.
	 *
	 * @since 0.1.0
	 *
	 * @param array The field data.
	 *
	 * @return void
	 */
	function custom_css( $field ) {
		?>
		<style>
			input#acf-field_5dc5ab7ea6e00 {
				max-width: 80px;
			}
		</style>
		<?php
	}

	/**
	 * Registers options page.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function register_options_page() {
		if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
			return;
		}

		acf_add_options_sub_page(
			[
				'page_title' => __( 'Mai Table of Contents', 'mai-table-of-contents' ),
				'menu_title' => __( 'Table of Contents', 'mai-table-of-contents' ),
				'menu_slug'  => 'mai-table-of-contents',
				'parent'     => class_exists( 'Mai_Engine' ) ? 'mai-theme' : 'options-general.php',
				'capability' => 'manage_options',
				'position'   => 4,
				'redirect'   => false
			]
		);
	}

	/**
	 * Registers field groups.
	 *
	 * Location and choices added later via acf filters so
	 * get_post_types() and other functions are available.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_field_group() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			[
				'key'    => 'maitoc_table_of_contents_settings',
				'title'  => __( 'Table of Contents Settings', 'mai-table-of-contents' ),
				'fields' => [
					[
						'key'          => 'field_5dd59edcd62e7',
						'label'        => __( 'Post Types', 'mai-table-of-contents' ),
						'name'         => 'maitoc_post_types',
						'type'         => 'checkbox',
						'instructions' => __( 'Automatically display the table of contents at the beginning of the following post types.', 'mai-table-of-contents' ),
						'choices'      => [],
					],
					[
						'key'     => 'field_611ab3b3b9ecf',
						'label'   => __( 'Style', 'mai-table-of-contents' ),
						'name'    => 'maitoc_style',
						'type'    => 'radio',
						'choices' => [
							''        => __( 'Default', 'mai-table-of-contents' ),
							'minimal' => __( 'Minimal', 'mai-table-of-contents' ),
						],
					],
					[
						'key'           => 'field_5dc5aafea6dff',
						'label'         => __( 'Load Open/Closed', 'mai-table-of-contents' ),
						'name'          => 'maitoc_open',
						'type'          => 'true_false',
						'message'       => 'Load the table of contents open by default',
						'default_value' => 1,
					],
					[
						'key'           => 'field_5dc5ab7ea6e00',
						'label'         => __( 'Minimum Headings', 'mai-table-of-contents' ),
						'name'          => 'maitoc_headings',
						'type'          => 'number',
						'instructions'  => 'The table of contents will only display if the content has at least this many h2 headings.',
						'required'      => 1,
						'default_value' => 2,
						'step'          => 1,
					],
				],
				'location' => [
					[
						[
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'mai-table-of-contents',
						],
					],
				],
				'style' => 'seamless',
			]
		);
	}

	/**
	 * Return the plugin action links.  This will only be called if the plugin is active.
	 *
	 * @since 0.2.0
	 *
	 * @param array  $actions     Associative array of action names to anchor tags.
	 * @param string $plugin_file Plugin file name, ie my-plugin/my-plugin.php.
	 * @param array  $plugin_data Associative array of plugin data from the plugin file headers.
	 * @param string $context     Plugin status context, ie 'all', 'active', 'inactive', 'recently_active'.
	 *
	 * @return array
	 */
	function add_settings_link( $actions, $plugin_file, $plugin_data, $context ) {
		$url                 = admin_url( sprintf( '%s.php?page=mai-table-of-contents', class_exists( 'Mai_Engine' ) ? 'admin' : 'options-general' ) );
		$link                = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'mai-table-of-contents' ) );
		$actions['settings'] = $link;

		return $actions;
	}
}
