<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_filter( 'rank_math/researches/toc_plugins', 'maitoc_rank_math_toc_plugins' );
/**
 * Adds support for Rank Math TOC score.
 *
 * @since 1.4.0
 *
 * @link https://rankmath.com/kb/table-of-contents/
 *
 * @param array $toc_plugins The existing plugins.
 *
 * @return array
 */
function maitoc_rank_math_toc_plugins( $toc_plugins ) {
	$toc_plugins['mai-table-of-contents/mai-table-of-contents.php'] = 'Mai Table of Contents';

	return $toc_plugins;
}

add_action( 'maicca_cca', 'maitoc_maicca_cca' );
/**
 * Adds a custom TOC filter if the block is present.
 *
 * This function checks if the 'acf/mai-table-of-contents' block is present
 * in the content and adds a filter to indicate that a custom TOC is used.
 *
 * @param array $args Arguments containing the content to check.
 */
function maitoc_maicca_cca( $args ) {
	static $first = true;

	// Bail if already ran.
	if ( ! $first ) {
		return;
	}

	// Bail if content doesn't have a TOC.
	if ( ! has_block( 'acf/mai-table-of-contents', $args['content'] ) ) {
		return;
	}

	/**
	 * Tell Mai Table of Contents that we have a custom TOC.
	 *
	 * @param bool $has_custom Whether or not we have a custom TOC.
	 * @param int  $post_id    The post ID.
	 *
	 * @return bool
	 */
	add_filter( 'mai_table_of_contents_has_custom', '__return_true' );
}
