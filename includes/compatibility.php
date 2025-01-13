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
 * @since 1.6.5
 *
 * @param array $args Arguments containing the content to check.
 */
function maitoc_maicca_cca( $args ) {
	static $has_run = false;

	// Bail if already ran.
	if ( $has_run ) {
		return;
	}

	// Bail if content doesn't have a TOC.
	if ( ! has_block( 'acf/mai-table-of-contents', $args['content'] ) ) {
		return;
	}

	// Add the custom TOC filter.
	add_filter( 'mai_table_of_contents_has_custom', '__return_true' );

	// Set flag.
	$has_run = true;
}

add_filter( 'mai_publisher_page_ads', 'maitoc_mai_publisher_page_ads' );
/**
 * Adds a custom TOC filter if the block is present.
 *
 * @since 1.6.5
 *
 * @param array $ads The existing ads.
 *
 * @return array
 */
function maitoc_mai_publisher_page_ads( $ads ) {
	static $has_run = false;

	// Bail if already ran.
	if ( $has_run ) {
		return $ads;
	}

	// Loop through page ads.
	foreach( $ads as $ad ) {
		// Skip if content doesn't have a TOC.
		if ( ! str_contains( $ad['content'], 'mai-toc__summary' ) ) {
			continue;
		}

		// Add the custom TOC filter.
		add_filter( 'mai_table_of_contents_has_custom', '__return_true' );

		// Set flag.
		$has_run = true;

		break;
	}

	return $ads;
}
