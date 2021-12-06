<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'acf/init', 'maitoc_add_field_groups' );
/**
 * Adds TOC settings.
 *
 * Location and choices added later via acf filters so
 * get_post_types() and other functions are available.
 *
 * @since 0.1.0
 *
 * @return void
 */
function maitoc_add_field_groups() {
	acf_add_local_field_group(
		[
			'key'    => 'group_5dc5aad92d38a',
			'title'  => __( 'Table of Contents Settings', 'mai-table-of-contents' ),
			'fields' => [
				[
					'key'          => 'field_5dd59edcd62e7',
					'label'        => __( 'Post Types', 'mai-table-of-contents' ),
					'name'         => 'maitoc_post_types',
					'type'         => 'checkbox',
					'instructions' => __( 'Automatically display the table of contents at the beginning of the following post types.', 'mai-table-of-contents' ),
					'choices'      => [],
				],
				[
					'key'     => 'field_611ab3b3b9ecf',
					'label'   => __( 'Style', 'mai-table-of-contents' ),
					'name'    => 'maitoc_style',
					'type'    => 'radio',
					'choices' => [
						''        => 'Default',
						'minimal' => 'Minimal',
					],
				],
				[
					'key'           => 'field_5dc5aafea6dff',
					'label'         => __( 'Load Open/Closed', 'mai-table-of-contents' ),
					'name'          => 'maitoc_open',
					'type'          => 'true_false',
					'message'       => 'Load the table of contents open by default',
					'default_value' => 1,
				],
				[
					'key'           => 'field_5dc5ab7ea6e00',
					'label'         => __( 'Minimum Headings', 'mai-table-of-contents' ),
					'name'          => 'maitoc_headings',
					'type'          => 'number',
					'instructions'  => 'The table of contents will only display if the content has at least this many h2 headings.',
					'required'      => 1,
					'default_value' => 2,
					'step'          => 1,
				],
			],
			'location' => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'mai-table-of-contents',
					],
				],
			],
			'style' => 'seamless',
		]
	);

	acf_add_local_field_group(
		[
			'key'    => 'group_5dd59f9f45942',
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
					'key'           => 'field_5dd5a09a56ef9',
					'label'         => __( 'Load Open/Closed', 'mai-table-of-contents' ),
					'name'          => 'maitoc_open',
					'type'          => 'true_false',
					'default_value' => 1,
					'ui'            => 1,
					'ui_off_text'   => __( 'Closed', 'mai-table-of-contents' ),
					'ui_on_text'    => __( 'Open', 'mai-table-of-contents' ),
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
