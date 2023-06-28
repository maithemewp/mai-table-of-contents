<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Table_Of_Contents {
	protected $args;
	protected $post_id;
	protected $content;
	protected $data;
	protected $labels;

	/**
	 * Gets it started.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function __construct( $args, $post_id = 0, $content = null ) {
		// Atts.
		$args = shortcode_atts(
			[
				'preview'  => false,
				'open'     => get_option( 'options_maitoc_open', true ),
				'headings' => get_option( 'options_maitoc_headings', 2 ),
				'style'    => get_option( 'options_maitoc_style', '' ), // Default is empty.
				'class'    => '',
				'align'    => '', // Accepts "wide".
			],
			$args,
			'mai_toc'
		);

		// Sanitize.
		$args = [
			'preview'  => rest_sanitize_boolean( $args['preview'] ),
			'open'     => rest_sanitize_boolean( $args['open'] ),
			'headings' => absint( $args['headings'] ),
			'style'    => sanitize_html_class( $args['style'] ),
			'class'    => esc_attr( $args['class'] ),
			'align'    => esc_attr( $args['align'] ),
		];

		// Force default if empty.
		$args['style'] = $args['style'] ?: 'default';

		$this->args    = $args;
		$this->post_id = $post_id ?: get_the_ID();
		$this->content = is_null( $content ) ? trim( get_post_field( 'post_content', $this->post_id ) ) : $content;
		$this->data    = $this->get_data();
		$this->labels  = $this->get_labels();
	}

	/**
	 * Gets table of contents.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get() {
		return $this->args['preview'] || is_admin() ? $this->get_preview() : $this->get_toc();
	}

	/**
	 * Gets the formatted content.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	function get_content() {
		return $this->data['content'];
	}

	/**
	 * Gets table of contents for editor.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_preview() {
		$html = '<div class="mai-toc">';
			$html .= $this->get_css();
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $this->args['open'] ? ' open': '' );
				$html .= '<summary class="mai-toc__summary">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', $this->labels['label'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">%s</span>', $this->labels['hide'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">%s</span>', $this->labels['show'] );
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

	/**
	 * Gets table of contents for display.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	function get_toc() {
		$post_id = $this->post_id;

		static $cache = [];

		if ( isset( $cache[ $this->post_id ] ) ) {
			return $cache[ $this->post_id ];
		}

		$cache[ $this->post_id ] = '';

		if ( ! $this->content ) {
			return $cache[ $this->post_id ];
		}

		if ( ! $this->data['matches'] ) {
			return $cache[ $this->post_id ];
		}

		// Bail if not enough h2s.
		if ( count( $this->data['matches'] ) < $this->args['headings'] ) {
			return $cache[ $this->post_id ];
		}

		// Get classes.
		$classes = 'mai-toc';

		if ( $this->args['style'] ) {
			$classes .= ' mai-toc-' . $this->args['style'];
		}

		if ( $this->args['align'] && ( 'wide' === $this->args['align'] ) ) {
			$classes .= ' alignwide';
		}

		if ( $this->args['class'] ) {
			$array = explode( ' ', $this->args['class'] );
			$array = array_filter( $array );
			$array = array_unique( $array );
			$array = array_map( 'trim', $array );
			$array = array_map( 'sanitize_html_class', $array );
			$new   = implode( ' ', $array );

			$classes .= ' ' . $new;
		}

		// Build HTML.
		$html  = sprintf( '<div class="%s">', trim( $classes ) );
		$html .= $this->get_css();
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $this->args['open'] ? ' open' : '' );
				$html .= '<summary class="mai-toc__summary" tabindex="0">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', $this->labels['label'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">%s</span>', $this->labels['hide'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">%s</span>', $this->labels['show'] );
					$html .= '</span>';
				$html .= '</summary>';
				$html .= '<ul class="mai-toc__list mai-toc--parent">';
					foreach( $this->data['matches'] as $values ) {
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

		// Store in cache.
		$cache[ $this->post_id ] = $html;

		return $cache[ $this->post_id ];
	}

	/**
	 * Gets content as structured data.
	 * This can't be statically cached because a block or shortcode
	 * may be running this before the content is actually parsed for display.
	 * In this case it needs to format and retrieve the data twice.
	 *
	 * @access private
	 *
	 * @since 1.4.0
	 * @since 1.5.0 Moved inside this class.
	 *
	 * @return array
	 */
	function get_data() {
		// Starting data.
		$data = [
			'content' => $this->content,
			'matches' => [],
		];

		// Bail if no content.
		if ( empty( $this->content ) ) {
			return $data;
		}

		// Create the new document.
		$dom = new DOMDocument();

		// Modify state.
		$libxml_previous_state = libxml_use_internal_errors( true );

		// Encode.
		$html = mb_convert_encoding( $this->content, 'HTML-ENTITIES', 'UTF-8' );

		// Load the content in the document HTML.
		$dom->loadHTML( "<div>$html</div>" );

		// Handle wraps.
		$container = $dom->getElementsByTagName('div')->item(0);
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

		$xpath = new DOMXPath( $dom );
		$h2h3  = $xpath->query( '//h2 | //h3' );

		// Bail if no headings.
		if ( ! $h2h3->length ) {
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
			$id    = $this->get_node_id( $node, $reset );
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

		// If we have the minimum h2 headings.
		if ( count( $data['matches'] ) >= $this->args['headings'] ) {
			// Store TOC in new content.
			$data['content'] = $dom->saveHTML();
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
	 * See `get_unique_id()` for explanation.
	 *
	 * @since 1.4.3
	 * @since 1.5.0 Moved inside this class.
	 *
	 * @param string $id    The existing id to check.
	 * @param bool   $reset Reset the anchors.
	 *
	 * @return string
	 */
	function get_node_id( $node, $reset = false ) {
		$text  = $node->nodeValue;
		$id    = $node->getAttribute( 'id' );

		if ( ! $id && ! empty( $node->nodeValue ) ) {
			$id = sanitize_title( $node->nodeValue );
		}

		return $this->get_unique_id( $id, $reset );
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
	 * @since 1.5.0 Moved inside this class.
	 *
	 * @param string $id    The existing id to check.
	 * @param bool   $reset Reset the anchors.
	 *
	 * @return string
	 */
	function get_unique_id( $id, $reset = false ) {
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
	 * Gets table of contents labels.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_labels() {
		$labels = [
			'label' => __( 'Table of Contents', 'mai-table-of-contents' ),
			'hide'  => __( '[Hide]', 'mai-table-of-contents' ),
			'show'  => __( '[Show]', 'mai-table-of-contents' ),
		];

		$labels = apply_filters( 'mai_table_of_contents_labels', $labels );

		return $labels;
	}

	/**
	 * Gets toc css link if it hasn't been loaded yet.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	function get_css() {
		static $loaded = false;

		if ( $loaded ) {
			return;
		}

		$css = '';

		// if ( ! is_admin() && did_action( 'wp_print_styles' ) ) {
		if ( ! is_admin() ) {
			$suffix = maitoc_get_suffix();
			$href   = MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$suffix}.css";
			$css    = sprintf( '<link rel="stylesheet" href="%s" />', $href );
			$loaded = true;
		}

		return $css;
	}
}
