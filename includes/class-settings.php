<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

// Get it started.
add_action( 'acf/init', function() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	new Mai_Table_Of_Contents_Settings;
});

class Mai_Table_Of_Contents_Settings {

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

		add_action( 'acf/render_field/key=field_5dd59edcd62e7', [ $this, 'custom_css' ] );
		add_filter( 'acf/load_field/key=field_5dd59edcd62e7',   [ $this, 'get_post_types' ] );
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
		$field['choices'] = array();

		// Get all public post types.
		$post_types = get_post_types( array(
			'public' => true,
		) );

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
}
