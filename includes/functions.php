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

	// h2s.
	$h2s = $dom->getElementsByTagName( 'h2' );

	// Bail less than 2 h2s.
	if ( ! $h2s || ( $h2s->length < 2 ) ) {
		return $data;
	}

	$xpath = new DOMXPath( $dom );
	$reset = true;

	// Loop through h2s.
	foreach ( $h2s as $index => $h2 ) {
		$text  = $h2->nodeValue;
		$id    = $h2->getAttribute( 'id' );

		if ( ! $id ) {
			$id = sanitize_title( $text );
		}

		$id    = maitoc_get_unique_id( $id, $reset );
		$reset = false;
		$h2->setAttribute( 'id', $id );

		$data['matches'][ $index ] = [
			'id'       => $id,
			'text'     => $text,
			'children' => [],
		];

		$h3s      = [];
		$h3_reset = true;

		// Loop through next sibling elements, and stop at the next h2.
		while( ( $h2 = $h2->nextSibling ) && ( 'h2' !== $h2->nodeName ) ) {
			$h3s = $xpath->query( 'descendant-or-self::h3', $h2 );

			if ( ! $h3s->length ) {
				continue;
			}

			foreach ( $h3s as $h3 ) {
				$text  = $h3->nodeValue;
				$id    = $h3->getAttribute( 'id' );

				if ( ! $id ) {
					$id = sanitize_title( $text );
				}

				$id       = maitoc_get_unique_id( $id, $h3_reset );
				$h3_reset = false;
				$h3->setAttribute( 'id', $id );

				$data['matches'][ $index ]['children'][] = [
					'id'   => $id,
					'text' => $text,
				];
			}
		}
	}

	$data['content'] = $dom->saveHTML( $dom->documentElement );

	return $data;
}

/**
 * Gets a unique id value.
 * Each time an id is found the total number is incremented
 * as the anchor value.
 *
 * Reset parameters is needed because the data is created once for the toc
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
 * @param bool   $reset Reset the ancrhos.
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
