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

/**
 * Gets DOMDocument object.
 *
 * Mai Engine owns the canonical implementation, in lib/functions/utilities.php. Prefer it
 * so there is one copy to maintain. The local fallback exists because this plugin also runs
 * standalone, and must stay behavior-identical.
 *
 * @since 1.6.8
 *
 * @param string $html Any given HTML string.
 *
 * @return DOMDocument
 */
function maitoc_get_dom_document( $html ) {
	if ( function_exists( 'mai_get_dom_document' ) ) {
		return mai_get_dom_document( $html );
	}

	// Create the new document.
	$dom = new DOMDocument();

	// Modify state.
	$libxml_previous_state = libxml_use_internal_errors( true );

	// Encode.
	$html = mb_encode_numericentity( $html, [0x80, 0x10FFFF, 0, ~0], 'UTF-8' );

	// Load the content in the document HTML.
	$dom->loadHTML( "<div>$html</div>" );

	// Handle wraps.
	$container = $dom->getElementsByTagName( 'div' )->item( 0 );
	$container = $container->parentNode->removeChild( $container );

	while ( $dom->firstChild ) {
		$dom->removeChild( $dom->firstChild );
	}

	while ( $container->firstChild ) {
		$dom->appendChild( $container->firstChild );
	}

	// Handle errors.
	libxml_clear_errors();

	// Restore.
	libxml_use_internal_errors( $libxml_previous_state );

	return $dom;
}

/**
 * Saves HTML from DOMDocument and decodes entities, except those that would turn escaped
 * text back into live markup.
 *
 * @since 1.6.8
 *
 * @param DOMDocument $dom
 *
 * @return string
 */
function maitoc_get_dom_html( $dom ) {
	if ( function_exists( 'mai_get_dom_html' ) ) {
		return mai_get_dom_html( $dom );
	}

	// Fallback for running without Mai Engine. This MUST stay behavior-identical to the
	// canonical version: it is the live code path on those sites, not dead code.
	//
	// This previously ran mb_convert_encoding( $html, 'UTF-8', 'HTML-ENTITIES' ), which
	// decoded everything and so un-escaped what DOMDocument had deliberately escaped. Text an
	// author typed as &lt;script&gt; came back out as a live script tag, and JSON in a data
	// attribute was broken out of its own quotes. Decode by result instead: anything that
	// would decode to a character capable of creating markup or ending an attribute value
	// stays escaped, everything else decodes as before.
	static $structural = [ '<', '>', '"', "'" ];

	return preg_replace_callback(
		'/&(?:#[0-9]+|#[xX][0-9a-fA-F]+|[A-Za-z][A-Za-z0-9]*);/',
		static function ( $match ) use ( $structural ) {
			$decoded = html_entity_decode( $match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			// Unknown entity: html_entity_decode hands it back unchanged. Leave it alone.
			if ( $decoded === $match[0] ) {
				return $match[0];
			}

			return in_array( $decoded, $structural, true ) ? $match[0] : $decoded;
		},
		$dom->saveHTML()
	);
}
