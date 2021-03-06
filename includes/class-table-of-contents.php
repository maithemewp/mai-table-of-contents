<?php

// Get it started.
add_action( 'plugins_loaded', function() {
	new Mai_Table_Of_Contents;
});

class Mai_Table_Of_Contents {

	function __construct() {
		$this->hooks();
	}

	function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'acf/init',           array( $this, 'register_block' ), 10, 3 );
		add_shortcode( 'mai_toc',         array( $this, 'register_shortcode' ) );
		add_filter( 'the_content',        array( $this, 'get_the_content' ) );
	}

	function enqueue_style() {
		wp_register_style( 'mai-table-of-contents', MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$this->get_suffix()}.css", array(), MAI_TABLE_OF_CONTENTS_VERSION );
	}

	function register_block() {
		// Bail if no ACF Pro >= 5.8.
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}
		// Register.
		acf_register_block_type( array(
			'name'            => 'mai-table-of-contents',
			'title'           => __( 'Mai Table of Contents', 'mai-table-of-contents' ),
			'description'     => __( 'A table of contents block.', 'mai-table-of-contents' ),
			'icon'            => 'list-view',
			'category'        => 'formatting',
			'keywords'        => array( 'table', 'contents', 'toc' ),
			'mode'            => 'preview',
			'multiple'        => false,
			'enqueue_style'   => MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$this->get_suffix()}.css",
			'render_callback' => array( $this, 'do_toc' ),
			'supports'        => array(
				'align'  => array( 'wide' ),
				'ancher' => true,
			),
		) );
	}

	function do_toc( $block, $content = '', $is_preview = false ) {
		$custom   = get_field( 'mai-toc_custom' );
		$open     = $custom ? get_field( 'maitoc_open' ) : get_option( 'options_maitoc_open', true );
		$headings = $custom ? get_field( 'maitoc_headings' ) : get_option( 'options_maitoc_headings', 2 );
		echo $is_preview ? $this->get_preview( $open ) : $this->get_toc( $open, $headings, $post_id ='', $block['align'] );
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

	function get_preview( $open ) {
		$open = $open ? ' open' : '';
		$html = '<div class="mai-toc">';
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $open );
				$html .= '<summary class="mai-toc__summary">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', __( 'Table of Contents', 'mai-table-of-contents' ) );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">[%s]</span>', __( 'Hide', 'mai-table-of-contents' ) );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">[%s]</span>', __( 'Show', 'mai-table-of-contents' ) );
					$html .= '</span>';
				$html .= '</summary>';
				$html .= '<ul class="mai-toc__list mai-toc--parent">';
					$html .= '<li class="mai-toc__listitem">';
						$html .= sprintf( '<a class="mai-toc__link scroll-to" href="#">%s</a>', __( 'Example Heading', 'mai-table-of-contents' ) );
					$html .= '</li>';
					$html .= '<li class="mai-toc__listitem">';
						$html .= '<details class="mai-toc__details">';
							$html .= '<summary class="mai-toc__summary">';
								$html .= '<span class="mai-toc__row">';
									$html .= sprintf( '<a class="mai-toc__link scroll-to" href="#">%s</a>', __( 'Example Heading', 'mai-table-of-contents' ) );
									$html .= '<span role="button" class="mai-toc__icon mai-toc--open">+</span>';
									$html .= '<span role="button" class="mai-toc__icon mai-toc--close">−</span>';
								$html .= '</span>';
							$html .= '</summary>';
							$html .= '<ul class="mai-toc__list mai-toc--child">';
								$html .= '<li class="mai-toc__listitem">';
									$html .= sprintf( '<a class="mai-toc__link scroll-to" href="#">%s</a>', __( 'Example Nested Heading', 'mai-table-of-contents' ) );
								$html .= '</li>';
							$html .= '</ul>';
						$html .= '</details>';
					$html .= '</li>';
					$html .= '<li class="mai-toc__listitem">';
						$html .= sprintf( '<a class="mai-toc__link scroll-to" href="#">%s</a>', __( 'Example Heading', 'mai-table-of-contents' ) );
					$html .= '</li>';
				$html .= '</ul>';
			$html .= '</details>';
		$html .= '</div>';
		return $html;
	}

	function get_toc( $open = true, $headings = 2, $post_id = '', $align = '' ) {
		// Get post ID.
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		// Bail if not the right post.
		if ( get_the_ID() !== $post_id ) {
			return;
		}
		// Build the HTML.
		$content = get_post_field( 'post_content', $post_id );
		$data    = $this->get_data( $content );
		return $this->get_html( $data['matches'], $open, $headings, $align );
	}

	function get_html( $matches, $open, $headings, $align = '' ) {
		// Bail if no matches.
		if ( ! $matches ) {
			return '';
		}
		// Bail if not enough h2s.
		if ( count( $matches ) < absint( $headings ) ) {
			return;
		}
		// Enqueue styles.
		wp_enqueue_style( 'mai-table-of-contents' );
		// Get classes.
		$classes = 'mai-toc';
		if ( $align && ( 'wide' === $align ) ) {
			$classes .= ' alignwide';
		}
		// Get open string.
		$open = $open ? ' open' : '';
		// Build HTML.
		$html = sprintf( '<div class="%s">', $classes );
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $open );
				$html .= '<summary class="mai-toc__summary" tabindex="0">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', __( 'Table of Contents', 'mai-table-of-contents' ) );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">[%s]</span>', __( 'Hide', 'mai-table-of-contents' ) );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">[%s]</span>', __( 'Show', 'mai-table-of-contents' ) );
					$html .= '</span>';
				$html .= '</summary>';
				$html .= '<ul class="mai-toc__list mai-toc--parent">';
					foreach( $matches as $values ) {
						$html .= '<li class="mai-toc__listitem" tabindex="-1">';
							$link = sprintf( '<a class="mai-toc__link scroll-to" href="#%s" tabindex="0">%s</a>', $values['id'], $values['text'] );
							if ( $values['children'] ) {
								$html .= '<details class="mai-toc__details">';
									$html .= '<summary class="mai-toc__summary">';
										$html .= '<span class="mai-toc__row">';
											$html .= $link;
											$html .= '<span role="button" tabindex="0" class="mai-toc__icon mai-toc--open">&#x2b;</span>';
											$html .= '<span role="button" tabindex="0" class="mai-toc__icon mai-toc--close">&#x2212;</span>';
										$html .= '</span>';
									$html .= '</summary>';
									$html .= '<ul class="mai-toc__list mai-toc--child">';
										foreach( $values['children'] as $child ) {
											$html .= sprintf( '<li class="mai-toc__listitem" tabindex="-1"><a class="mai-toc__link scroll-to" href="#%s" tabindex="0">%s</a></li>', $child['id'], $child['text'] );
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

	function get_the_content( $content ) {

		// Bail if not singular content.
		if ( ! is_singular() || is_front_page() ) {
			return $content;
		}

		// Bail if not the main query.
		if ( ! is_main_query() ) {
			return $content;
		}

		// Get post_types (with ACF strange key).
		$post_types = get_option( 'options_maitoc_post_types', array() );

		// Check if auto-displayed.
		$displayed  = in_array( get_post_type(), (array) $post_types );
		$has_toc    = has_block( 'acf/mai-table-of-contents' ) || has_shortcode( $content, 'mai_toc' );

		// Bail if no toc.
		if ( ! ( $displayed || $has_toc ) ) {
			return $content;
		}

		// Get the content/matches data.
		$data = $this->get_data( $content );

		$toc = '';

		if ( $displayed && ! $has_toc ) {
			$open     = get_field( 'maitoc_open', 'options' );
			$headings = get_field( 'maitoc_headings', 'options' );
			$toc      = $this->get_toc( $open, $headings );
		}

		// Return the altered content.
		return $toc . $data['content'];
	}

	function get_data( $content ) {

		// Starting data.
		$data = array(
			'content' => $content,
			'matches' => array(),
		);

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

			$h3s = [];

			// Loop through next sibling elements, and stop at the next h2.
			while( ( $node = $node->nextSibling ) && ( 'h2' !== $node->nodeName ) ) {

				$h3s = $xpath->query( 'descendant-or-self::h3', $node );

				if ( ! $h3s->length ) {
					continue;
				}

				foreach ( $h3s as $h3 ) {
					$text = $h3->nodeValue;
					$slug = $h3->getAttribute( 'id' );

					if ( ! $slug ) {
						$i    = 2;
						$text = $h3->nodeValue;
						$slug = sanitize_title( $text );
						while ( false !== in_array( $slug, $anchors ) ) {
							$slug = sprintf( '%s-%d', $slug, $i++ );
						}
						$h3->setAttribute( 'id', $slug );
					}

					$anchors[] = $slug;

					$data['matches'][ $index ]['children'][] = array(
						'id'   => $slug,
						'text' => $text,
					);
				}
			}
		}

		$data['content'] = $dom->saveHTML();

		return $data;
	}

	function get_suffix() {
		$debug  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		return $debug ? '' : '.min';
	}
}
