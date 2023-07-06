<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'acf/init', 'mai_register_toc_block' );
/**
 * Register block.
 *
 * @since 0.1.0
 *
 * @return void
 */
function mai_register_toc_block() {
	// "style": [ "file:../../assets/css/mai-toc.css", "mai-table-of-contents" ],

	register_block_type( __DIR__ . '/block.json' );
}

/**
 * Callback function to render the block.
 *
 * @since 0.1.0
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 *
 * @return void
 */
function mai_do_toc_block( $block, $content = '', $is_preview = false, $post_id = 0 ) {
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

add_action( 'acf/init', 'mai_register_toc_field_group' );
/**
 * Registers field groups.
 *
 * @since 0.1.0
 *
 * @return void
 */
function mai_register_toc_field_group() {
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
