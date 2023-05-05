<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Table_Of_Contents_Display {
	protected $post_id;
	protected $has_toc;
	protected $has_block;
	protected $has_shortcode;

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

		// Set post ID.
		$this->post_id = get_the_ID();

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
		// Bail if not main query in the loop.
		if ( ! ( is_main_query() && in_the_loop() ) ) {
			return $content;
		}

		// Bail if no content.
		if ( ! $content ) {
			return $content;
		}

		// Make sure this only targets the post we want.
		// Fixes issue when Mai Post Grid or similar is used
		// inside a post that is showing a toc.
		// The addition of in_the_loop() check may have fixed this as well.
		if ( ! $this->post_id || get_the_ID() !== $this->post_id ) {
			return $content;
		}

		// Check if we have a block or shortcode.
		$this->has_toc       = $this->has_toc();
		$this->has_block     = $this->has_block();
		$this->has_shortcode = $this->has_shortcode( $content );

		// Bail if no TOC.
		if ( ! ( $this->has_toc || $this->has_block || $this->has_shortcode ) ) {
			return;
		}

		// Set it up.
		$html = '';
		$toc  = new Mai_Table_Of_Contents( [], $this->post_id, $content );

		// If no block or shortcode in the content, add TOC.
		if ( ! ( $this->has_block || $this->has_shortcode ) ) {
			$html .= $toc->get();
		}

		// Use markup with matched heading ids.
		$html .= $toc->get_content();

		return $html;
	}

	/**
	 * Checks if a post has an auto-displayed toc.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	function has_toc() {
		// Get post_types (with ACF strange key).
		$post_types = (array) get_option( 'options_maitoc_post_types', [] );

		// If auto-displaying on this post type and doesn't have a toc block. Shortcode checked later in content.
		return $post_types && in_array( get_post_type( $this->post_id ), $post_types );
	}

	/**
	 * If has TOC block.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	function has_block() {
		return has_block( 'acf/mai-table-of-contents', $this->post_id );
	}

	/**
	 * If has TOC shortcode.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	function has_shortcode( $content ) {
		return has_shortcode( $content, 'mai_toc' );
	}
}
