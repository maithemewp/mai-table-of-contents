<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * Gets content as structured data.
 *
 * @access private
 *
 * @since 1.4.0
 *
 * @return array
 */
function maitoc_get_data( $content ) {
	// Starting data.
	$data = [
		'content' => $content,
		'matches' => [],
	];

	// Bail if no content.
	if ( empty( $content ) ) {
		return $data;
	}

	// Create the new document.
	$dom = new DOMDocument();

	// Modify state.
	$libxml_previous_state = libxml_use_internal_errors( true );

	// Load the content in the document HTML.
	$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );

	// Handle errors.
	libxml_clear_errors();

	// Restore.
	libxml_use_internal_errors( $libxml_previous_state );

	$xpath = new DOMXPath( $dom );
	$h2h3  = $xpath->query( '//h2 | //h3' );

	// Bail if no headings.
	if ( ! $h2h3 ) {
		return $data;
	}

	$reset         = true;
	$current_h2_id = false;

	foreach ( $h2h3 as $index => $node ) {
		// Skip if another heading is before the first h2.
		if ( ! $current_h2_id && 'h2' !== $node->nodeName ) {
			continue;
		}

		// Set vars.
		$id    = maitoc_get_node_id( $node, $reset );
		$text  = $node->nodeValue;
		$reset = false;

		// Set id.
		$node->setAttribute( 'id', $id );

		// Build data.
		switch ( $node->nodeName ) {
			case 'h2':
				// Set current.
				$current_h2_id = $id;

				// Add to data.
				$data['matches'][ $current_h2_id ] = [
					'id'       => $id,
					'text'     => $text,
					'children' => [],
				];
			break;
			case 'h3':
				// Add to data.
				$data['matches'][ $current_h2_id ]['children'][] = [
					'id'   => $id,
					'text' => $text,
				];
			break;
		}
	}

	// If we have at least 3 h2 headings.
	if ( count( $data['matches'] ) > 2 ) {
		// Store TOC in new content.
		$data['content'] = $dom->saveHTML( $dom->documentElement );
	}
	// Not enough headings.
	else {
		// Clear matches.
		$data['matches'] = [];
	}

	return $data;
}

/**
 * Gets a unique id value from node.
 * See `maitoc_get_unique_id()` for explanation.
 *
 * @since 1.4.3
 *
 * @param string $id    The existing id to check.
 * @param bool   $reset Reset the anchors.
 *
 * @return string
 */
function maitoc_get_node_id( $node, $reset = false ) {
	$text  = $node->nodeValue;
	$id    = $node->getAttribute( 'id' );

	if ( ! $id && ! empty( $node->nodeValue ) ) {
		$id = sanitize_title( $node->nodeValue );
	}

	return maitoc_get_unique_id( $id, $reset );
}

/**
 * Gets a unique id value.
 * Each time an id is found the total number is incremented
 * as the anchor value.
 *
 * Reset parameter is needed because the data is created once for the toc
 * and once in the actual content so without it the heading id's are incremented
 * continuously and won't match.
 *
 * $anchors = [
 *    'some-heading'     => 1,
 *    'repeated-heading' => 3,
 * ];
 *
 * @since 1.4.0
 *
 * @param string $id    The existing id to check.
 * @param bool   $reset Reset the anchors.
 *
 * @return string
 */
function maitoc_get_unique_id( $id, $reset = false ) {
	static $anchors = [];

	if ( $reset ) {
		$anchors = [];
	}

	if ( isset( $anchors[ $id ] ) ) {
		$anchors[ $id ]++;
		$id = sprintf( '%s-%d', $id, $anchors[ $id ] );
	} else {
		$anchors[ $id ] = 1;
	}

	return $id;
}

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
