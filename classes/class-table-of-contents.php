<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Table_Of_Contents {
	protected $args;
	protected $labels;

	/**
	 * Gets it started.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function __construct( $args = [] ) {
		// Atts.
		$args = shortcode_atts(
			[
				'preview'  => false,
				'open'     => get_option( 'options_maitoc_open', true ),
				'headings' => get_option( 'options_maitoc_headings', 2 ),
				'style'    => get_option( 'options_maitoc_style', 'default' ),
				'class'    => '',
				'align'    => '', // Accepts "wide".
			],
			$args,
			'mai_toc'
		);

		// Sanitize.
		$args = [
			'preview'  => filter_var( $args['preview'], FILTER_VALIDATE_BOOLEAN ),
			'open'     => filter_var( $args['open'], FILTER_VALIDATE_BOOLEAN ),
			'headings' => absint( $args['headings'] ),
			'style'    => sanitize_html_class( $args['style'] ),
			'class'    => esc_attr( $args['class'] ),
			'align'    => esc_attr( $args['align'] ),
		];

		$this->args   = $args;
		$this->labels = $this->get_labels();
	}

	/**
	 * Gets table of contents.
	 */
	function get() {
		return $this->args['preview'] ? $this->get_preview() : $this->get_toc();
	}

	/**
	 * Gets table of contents for editor.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_preview() {
		$html  = $this->get_css();
		$html .= '<div class="mai-toc">';
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
		$content = trim( get_post_field( 'post_content', get_the_ID() ) );

		if ( ! $content ) {
			return;
		}

		$data    = maitoc_get_data( $content );
		$matches = $data['matches'];

		if ( ! $matches ) {
			return;
		}

		// Bail if not enough h2s.
		if ( count( $matches ) < absint( $this->args['headings'] ) ) {
			return;
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
		$html  = $this->get_css();
		$html .= sprintf( '<div class="%s">', trim( $classes ) );
			$html .= sprintf( '<details class="mai-toc__showhide"%s>', $this->args['open'] ? ' open' : '' );
				$html .= '<summary class="mai-toc__summary" tabindex="0">';
					$html .= '<span class="mai-toc__row">';
						$html .= sprintf( '<span class="mai-toc__col">%s</span>', $this->labels['label'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--close">%s</span>', $this->labels['hide'] );
						$html .= sprintf( '<span class="mai-toc__col mai-toc__toggle mai-toc--open">%s</span>', $this->labels['show'] );
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

		if ( ! is_admin() && did_action( 'wp_print_styles' ) ) {
			$suffix = maitoc_get_suffix();
			$href   = MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$suffix}.css";
			$css    = sprintf( '<link rel="stylesheet" href="%s" />', $href );
			$loaded = true;
		}

		return $css;
	}
}
