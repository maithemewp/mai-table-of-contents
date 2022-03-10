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
