<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Table_Of_Contents_Block {
	/**
	 * Gets it started.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
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
		add_action( 'acf/init', [ $this, 'register_block' ] );
		add_action( 'acf/init', [ $this, 'register_field_group' ] );
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
				'render_callback' => [ $this, 'do_toc' ],
				'enqueue_assets'  => function() {
					if ( is_admin() ) {
						$suffix = maitoc_get_suffix();
						wp_enqueue_style( 'mai-table-of-contents', MAI_TABLE_OF_CONTENTS_PLUGIN_URL . "assets/css/mai-toc{$suffix}.css", [], MAI_TABLE_OF_CONTENTS_VERSION );
					}
				},
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
			'preview' => $is_preview,
			'align'   => $block['align'],
			'class'   => isset( $block['className'] ) && $block['className'] ? $block['className'] : '',
		];

		if ( $custom ) {
			$args['open']     = get_field( 'maitoc_open' );
			$args['headings'] = get_field( 'maitoc_headings' );
		}

		$toc = new Mai_Table_Of_Contents( $args );

		echo $toc->get();
	}

	/**
	 * Registers field groups.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_field_group() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			[
				'key'    => 'maitoc_table_of_contents_block',
				'title'  => __( 'Table of Contents', 'mai-table-of-contents' ),
				'fields' => [
					[
						'key'     => 'field_5dd59fad35b30',
						'label'   => __( 'Override default settings', 'mai-table-of-contents' ),
						'name'    => 'maitoc_custom',
						'type'    => 'true_false',
						'message' => __( 'Use custom settings', 'mai-table-of-contents' ),
					],
					[
						'key'               => 'field_5dd5a09a56ef9',
						'label'             => __( 'Load Open/Closed', 'mai-table-of-contents' ),
						'name'              => 'maitoc_open',
						'type'              => 'true_false',
						'default_value'     => 1,
						'ui'                => 1,
						'ui_off_text'       => __( 'Closed', 'mai-table-of-contents' ),
						'ui_on_text'        => __( 'Open', 'mai-table-of-contents' ),
						'conditional_logic' => [
							[
								[
									'field'    => 'field_5dd59fad35b30',
									'operator' => '==',
									'value'    => '1',
								],
							],
						],
					],
					[
						'key'               => 'field_5dd5a0d956efa',
						'label'             => __( 'Minimum Headings', 'mai-table-of-contents' ),
						'name'              => 'maitoc_headings',
						'type'              => 'number',
						'default_value'     => 2,
						'step'              => 1,
						'conditional_logic' => [
							[
								[
									'field'    => 'field_5dd59fad35b30',
									'operator' => '==',
									'value'    => '1',
								],
							],
						],
					],
				],
				'location' => [
					[
						[
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/mai-table-of-contents',
						],
					],
				],
			]
		);
	}
}
