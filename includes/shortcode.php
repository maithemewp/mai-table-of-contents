<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_shortcode( 'mai_toc', 'maitoc_register_toc_shortcode' );
/**
 * Registers shortcode.
 *
 * @since 0.1.0
 *
 * @return string
 */
function maitoc_register_toc_shortcode( $atts ) {
	$toc = new Mai_Table_Of_Contents( $atts );

	return $toc->get();
}
