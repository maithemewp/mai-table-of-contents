<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

// Get it started.
add_action( 'plugins_loaded', function() {
	new Mai_Table_Of_Contents;
});

class Mai_Table_Of_Contents {

	function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'acf/init',    [ $this, 'register_block' ], 10, 3 );
		add_shortcode( 'mai_toc',  [ $this, 'register_shortcode' ] );
		add_filter( 'the_content', [ $this, 'get_the_content' ] );
	}

	/**
	 * Register block.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_block() {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}

		acf_register_block_type(
			[
				'name'            => 'mai-table-of-contents',
				'title'           => __( 'Mai Table of Contents', 'mai-table-of-contents' ),
				'description'     => __( 'A table of contents block.', 'mai-table-of-contents' ),
				'icon'            => 'list-view',
				'category'        => 'formatting',
				'keywords'        => [ 'table', 'contents', 'toc' ],
				'mode'            => 'preview',
				'multiple'        => false,
				'enqueue_assets'  => function(){
					if ( is_admin() ) {
						wp_enqueue_style( 'mai-table-of-contents', MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$this->get_suffix()}.css", [], MAI_TABLE_OF_CONTENTS_VERSION );
					}
				},
				'render_callback' => [ $this, 'do_toc' ],
				'supports'        => [
					'align'  => [ 'wide' ],
					'ancher' => true,
				],
			]
		);
	}

	/**
	 * Renders table of contents.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function do_toc( $block, $content = '', $is_preview = false ) {
		$custom = get_field( 'maitoc_custom' );
		$args   = [
			'open'     => $custom ? get_field( 'maitoc_open' ) : get_option( 'options_maitoc_open', true ),
			'headings' => $custom ? get_field( 'maitoc_headings' ) : get_option( 'options_maitoc_headings', 2 ),
			'style'    => get_option( 'options_maitoc_style' ),
			'align'    => $block['align'],
		];

		if ( isset( $block['className'] ) && ! empty( $block['className'] ) ) {
			$args['class'] = $block['className'];
		}

		echo $is_preview ? $this->get_preview( $args['open'] ) : $this->get_toc( $args, $post_id = '' );
	}

	/**
	 * Register shortcode.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function register_shortcode( $atts ) {
		return $this->get_toc( $atts );
	}

	/**
	 * Gets table of contents for editor.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_preview( $open ) {
		$labels = $this->get_labels();
		$open   = $open ? ' open': '';
		$html   = $this->get_css();
		$html  .= '<div class="mai-toc">';
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $open );
				$html .= '<summary class="mai-toc__summary">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', $labels['label'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">%s</span>', $labels['hide'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">%s</span>', $labels['show'] );
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
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_toc( $args, $post_id = '' ) {
		// Get post ID.
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Bail if not the right post.
		if ( get_the_ID() !== $post_id ) {
			return;
		}

		// Atts.
		$args = shortcode_atts(
			[
				'open'     => true,
				'headings' => 2,
				'style'    => '',
				'class'    => '',
				'align'    => '', // Accepts "wide".
			],
			$args,
			'mai_toc'
		);

		// Sanitize.
		$args = [
			'open'     => filter_var( $args['open'], FILTER_VALIDATE_BOOLEAN ),
			'headings' => absint( $args['headings'] ),
			'style'    => sanitize_html_class( $args['style'] ),
			'class'    => esc_attr( $args['class'] ),
			'align'    => esc_html( $args['align'] ),
		];

		$args['style'] = $args['style'] ?: 'default';

		// Build the HTML.
		$content = get_post_field( 'post_content', $post_id );
		$data    = $this->get_data( $content );

		return $this->get_html( $data['matches'], $args );
	}

	/**
	 * Gets table of contents html.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_html( $matches, $args ) {
		if ( ! $matches ) {
			return '';
		}

		// Bail if not enough h2s.
		if ( count( $matches ) < absint( $args['headings'] ) ) {
			return;
		}

		// Get the labels.
		$labels = $this->get_labels();

		// Get classes.
		$classes = 'mai-toc';

		if ( $args['style'] ) {
			$classes .= ' mai-toc-' . $args['style'];
		}

		if ( $args['align'] && ( 'wide' === $args['align'] ) ) {
			$classes .= ' alignwide';
		}

		if ( $args['class'] ) {
			$array = explode( ' ', $args['class'] );
			$array = array_filter( $array );
			$array = array_unique( $array );
			$array = array_map( 'trim', $array );
			$array = array_map( 'sanitize_html_class', $array );
			$new   = implode( ' ', $array );

			$classes .= ' ' . $new;
		}

		// Get open string.
		$args['open'] = $args['open'] ? ' open' : '';

		// Build HTML.
		$html  = $this->get_css();
		$html .= sprintf( '<div class="%s">', trim( $classes ) );
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $args['open'] );
				$html .= '<summary class="mai-toc__summary" tabindex="0">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', $labels['label'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">%s</span>', $labels['hide'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">%s</span>', $labels['show'] );
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

	/**
	 * Gets content with table of contents added.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
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
		$post_types = get_option( 'options_maitoc_post_types', [] );

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
			$toc = $this->get_toc(
				[
					'open'     => get_field( 'maitoc_open', 'options' ),
					'headings' => get_field( 'maitoc_headings', 'options' ),
					'style'    => get_field( 'maitoc_style', 'options' ),
				]
			);
		}

		// Return the altered content.
		return $toc . $data['content'];
	}

	/**
	 * Gets content as structured data.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_data( $content ) {

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

		// Set empty variables.
		$anchors = [];

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

			$data['matches'][ $index ] = [
				'id'       => $slug,
				'text'     => $text,
				'children' => [],
			];

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

					$data['matches'][ $index ]['children'][] = [
						'id'   => $slug,
						'text' => $text,
					];
				}
			}
		}

		$data['content'] = $dom->saveHTML();

		return $data;
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

		if ( ! is_admin() ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$href   = MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$suffix}.css";
			$css    = sprintf( '<link rel="stylesheet" href="%s" />', $href );
		}

		$loaded = true;

		return $css;
	}
}
