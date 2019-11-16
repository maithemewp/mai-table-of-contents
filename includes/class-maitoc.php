<?php

// Get it started.
add_action( 'plugins_loaded', function() {
	new Mai_TOC;
});

class Mai_TOC {

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 10, 0 );
		add_action( 'acf/init',           array( $this, 'register_block' ), 10, 3 );
		add_shortcode( 'mai_toc',         array( $this, 'register_shortcode' ) );
		add_filter( 'the_content',        array( $this, 'add_heading_ids' ) );
	}

	function enqueue_style() {
		$debug  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$suffix = $debug ? '' : '.min';
		wp_register_style( 'mai-toc', MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$suffix}.css", array(), MAI_TABLE_OF_CONTENTS_VERSION );
	}

	function register_block() {
		// Bail if no ACF Pro >= 5.8.
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}
		// Register.
		acf_register_block_type( array(
			'name'            => 'maitoc',
			'title'           => __( 'Mai Table of Contents', 'mai-table-of-contents' ),
			'description'     => __( 'A table of contents block.', 'mai-table-of-contents' ),
			'icon'            => 'list-view',
			'category'        => 'formatting',
			'keywords'        => array( 'table', 'contents', 'toc' ),
			'mode'            => 'auto',
			'render_callback' => array( $this, 'do_toc' ),
			'supports'        => array(
				'align'  => array( 'wide', 'left', 'right' ),
				'ancher' => true,
			),
		) );
	}

	function do_toc( $block, $content = '', $is_preview = false ) {
		$open     = get_field( 'maitoc_open' );
		$headings = get_field( 'maitoc_headings' );
		echo $is_preview ? '<div style="background:#f7f7f7;text-align:center;padding:24px;border:1px solid #ebe9eb;">[table of contents will display here]</div>' : $this->get_toc( $open, $headings );
	}

	function register_shortcode( $atts ) {

		// Atts.
		$atts = shortcode_atts( array(
			'open'     => true,
			'headings' => 2,
		), $atts, 'mai_toc' );

		// Sanitize.
		$atts = array(
			'open'     => filter_var( $atts['open'], FILTER_VALIDATE_BOOLEAN ),
			'headings' => absint( $atts['headings'] ),
		);

		return $this->get_toc( $atts['open'], $atts['headings'] );
	}

	function get_toc( $open = true, $headings = 2, $post_id = '' ) {
		// Get post ID.
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		// Bail if not the right post.
		if ( get_the_ID() !== $post_id ) {
			return;
		}
		$content = get_the_content( null, false, $post_id );
		$data    = $this->get_data( $content );
		return $this->get_html( $data['matches'], $open, $headings );
	}

	function add_heading_ids( $content ) {

		// Bail if not singular content.
		if ( ! is_singular() ) {
			return $content;
		}

		// Bail if not the main query.
		if ( ! is_main_query() ) {
			return $content;
		}

		// Bail if no toc.
		if ( ! ( has_block( 'acf/maitoc' ) || has_shortcode( $content, 'mai_toc' ) ) ) {
			return $content;
		}

		// Get the content/matches data.
		$data = $this->get_data( $content );

		// Return the altered content.
		return $data['content'];
	}

	function get_data( $content ) {

		// Starting data.
		$data = array(
			'content' => $content,
			'matches' => array(),
		);

		// Create the new document.
		$dom = new DOMDocument;

		// Modify state.
		$libxml_previous_state = libxml_use_internal_errors( true );

		// Load the content in the document HTML.
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" ) );

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

		// Set empty variables.
		$anchors = array();

		// Loop through h2s.
		foreach ( $h2s as $index => $node ) {

			$text = $node->nodeValue;
			$slug = $node->getAttribute( 'id' );
			if ( ! $slug ) {
				$i    = 2;
				$slug = sanitize_title( $text );
				while ( false !== in_array( $slug, $anchors ) ) {
					$slug = sprintf( '%s-%d', $slug, $i++ );
				}
				$node->setAttribute( 'id', $slug );
			}

			$anchors[] = $slug;
			$data['matches'][ $index ] = array(
				'id'       => $slug,
				'text'     => $text,
				'children' => array(),
			);

			// Loop through next sibling elements, and stop at the next h2.
			while( ( $node = $node->nextSibling ) && ( 'h2' !== $node->nodeName ) ) {

				// Skip if not an h3.
				if ( 'h3' !== $node->nodeName ) {
					continue;
				}

				$text = $node->nodeValue;
				$slug = $node->getAttribute( 'id' );

				if ( ! $slug ) {
					$i    = 2;
					$text = $node->nodeValue;
					$slug = sanitize_title( $text );
					while ( false !== in_array( $slug, $anchors ) ) {
						$slug = sprintf( '%s-%d', $slug, $i++ );
					}
					$node->setAttribute( 'id', $slug );
				}

				$anchors[] = $slug;
				$data['matches'][ $index ]['children'][] = array(
					'id'   => $slug,
					'text' => $text,
				);
			}
		}

		$data['content'] = $dom->saveHTML();

		return $data;
	}

	function get_html( $matches, $open, $headings ) {
		// Bail if no matches.
		if ( ! $matches ) {
			return '';
		}
		// Bail if not enough h2s.
		if ( count( $matches ) < absint( $headings ) ) {
			return;
		}
		// Enqueue styles.
		wp_enqueue_style( 'mai-toc' );
		// Get open string.
		$open = $open ? ' open' : '';
		// Build HTML.
		$html = '<div class="maitoc">';
			$html .= sprintf( '<details class="maitoc__showhide"%s>', $open );
				$html .= '<summary class="maitoc__summary" tabindex="0">';
					$html .= '<span class="maitoc__row">';
						$html .= sprintf( '<span class="maitoc__col">%s</span>', __( 'Table of Contents', 'mai-table-of-contents' ) );
						$html .= sprintf( '<span class="maitoc__col maitoc__toggle maitoc--close">[%s]</span>', __( 'Hide', 'mai-table-of-contents' ) );
						$html .= sprintf( '<span class="maitoc__col maitoc__toggle maitoc--open">[%s]</span>', __( 'Click to Show', 'mai-table-of-contents' ) );
					$html .= '</span>';
				$html .= '</summary>';
				$html .= '<ul class="maitoc__list maitoc--parent">';
					foreach( $matches as $values ) {
						$html .= '<li class="maitoc__listitem" tabindex="-1">';
							$link = sprintf( '<a class="maitoc__link scroll-to" href="#%s" tabindex="0">%s</a>', $values['id'], $values['text'] );
							if ( $values['children'] ) {
								$html .= '<details class="maitoc__details">';
									$html .= '<summary class="maitoc__summary">';
										$html .= '<span class="maitoc__row">';
											$html .= $link;
											$html .= '<span role="button" tabindex="0" class="maitoc__icon maitoc--open">&#x2b;</span>';
											$html .= '<span role="button" tabindex="0" class="maitoc__icon maitoc--close">&#x2212;</span>';
										$html .= '</span>';
									$html .= '</summary>';
									$html .= '<ul class="maitoc__list maitoc--child">';
										foreach( $values['children'] as $child ) {
											$html .= sprintf( '<li class="maitoc__listitem" tabindex="-1"><a class="maitoc__link scroll-to" href="#%s" tabindex="0">%s</a></li>', $child['id'], $child['text'] );
										}
									$html .= '</ul>';
								$html .= '</details>';
							} else {
								$html .= $link;
							}
						$html .= '</li>';
					}
				$html .= '</ul>';
			$html .= '</details>';
		$html .= '</div>';
		return $html;
	}

}
