<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Table_Of_Contents_Display {
	protected $post_id;
	protected $has_toc;

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
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'get_header', [ $this, 'run' ] );
	}

	/**
	 * Runs toc.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function run() {
		// Bail if not singular content.
		if ( ! is_singular() || is_front_page() ) {
			return;
		}

		$this->post_id = get_the_ID();
		$this->has_toc = $this->has_toc();

		// Format headings.
		add_filter( 'the_content', [ $this, 'filter_content' ] );
	}

	/**
	 * Makes sure headings all have unique IDs.
	 * Maybe adds toc.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	function filter_content( $content ) {
		// Bail if no content.
		if ( ! $content ) {
			return $content;
		}

		// Make sure this only targets the post we want.
		// Fixes issue when Mai Post Grid or similar is used
		// inside a post that is showing a toc.
		if ( ! $this->post_id || get_the_ID() !== $this->post_id ) {
			return $content;
		}

		// Check for shortcode in filtered content.
		$this->has_toc = $this->has_toc && ! has_shortcode( $content, 'mai_toc' );

		// Get the content/matches data.
		$data = maitoc_get_data( $content );

		// Build the new content.
		$content = '';

		if ( $this->has_toc ) {
			$toc      = new Mai_Table_Of_Contents;
			$content .= $toc->get();
		}

		$content .= $data['content'];

		return $content;
	}

	/**
	 * Checks if a post has an auto-displayed toc.
	 *
	 * @return bool
	 */
	function has_toc() {
		// Get post_types (with ACF strange key).
		$post_types = (array) get_option( 'options_maitoc_post_types', [] );

		// Bail if not auto-displaying.
		if ( ! ( $post_types || in_array( get_post_type( $this->post_id ), $post_types ) ) ) {
			return false;
		}

		// Bail if already has block.
		if ( has_block( 'acf/mai-table-of-contents', $this->post_id ) ) {
			return false;
		}

		return true;
	}
}
