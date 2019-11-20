<?php

// Get it started.
add_action( 'plugins_loaded', function() {
	new Mai_Table_Of_Contents_Settings;
});

class Mai_Table_Of_Contents_Settings {

	function __construct() {

		// Bail if ACF is not active.
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		// Hooks.
		$this->hooks();

	}

	function hooks() {

		// Add the options pages.
		acf_add_options_page( array(
			'page_title' => __( 'Mai Table of Contents', 'mai-table-of-contents' ),
			'menu_title' => __( 'Table of Contents', 'mai-table-of-contents' ),
			'menu_slug'  => 'mai-table-of-contents',
			'parent'     => 'options-general.php',
			'capability' => 'manage_options',
			'redirect'   => false
		) );

		// Add custom CSS.
		add_action( 'acf/input/admin_head', array( $this, 'custom_css' ) );

		add_filter( 'acf/load_field/key=field_5dd59edcd62e7', array( $this, 'get_post_types' ) );
	}

	function custom_css() {
		?>
		<style>
			input#acf-field_5dc5ab7ea6e00 {
				max-width: 80px;
			}
		</style>
		<?php
	}

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

