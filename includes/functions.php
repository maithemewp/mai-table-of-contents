<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * Gets suffix for scripts.
 *
 * @since 1.4.0
 *
 * @return string
 */
function maitoc_get_suffix() {
	return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
}
