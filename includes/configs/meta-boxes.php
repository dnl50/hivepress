<?php
/**
 * Meta boxes configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_attributes'        => [
		'title'  => esc_html__( 'Attributes', 'hivepress' ),
		'screen' => 'listing',
		'fields' => [],
	],

	'listing_attribute_edit'    => [
		'title'  => esc_html__( 'Edit', 'hivepress' ),
		'screen' => 'listing_attribute',
		'fields' => [
			'editable'   => [
				'label'   => esc_html__( 'Editable', 'hivepress' ),
				'caption' => esc_html__( 'Allow front-end editing', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 10,
			],

			'edit_field' => [
				'label'    => esc_html__( 'Field Type', 'hivepress' ),
				'type'     => 'select',
				'options'  => 'fields',
				'required' => true,
				'order'    => 20,
			],
		],
	],

	'listing_attribute_search'  => [
		'title'  => esc_html__( 'Search', 'hivepress' ),
		'screen' => 'listing_attribute',
		'fields' => [
			'searchable'   => [
				'label'   => esc_html__( 'Searchable', 'hivepress' ),
				'caption' => esc_html__( 'Display in the search form', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 10,
			],

			'filterable'   => [
				'label'   => esc_html__( 'Filterable', 'hivepress' ),
				'caption' => esc_html__( 'Display in the filter form', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 20,
			],

			'sortable'     => [
				'label'   => esc_html__( 'Sortable', 'hivepress' ),
				'caption' => esc_html__( 'Display in the sort form', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 30,
			],

			'search_field' => [
				'label'   => esc_html__( 'Field Type', 'hivepress' ),
				'type'    => 'select',
				'options' => 'fields',
				'order'   => 40,
			],
		],
	],

	'listing_attribute_display' => [
		'title'  => esc_html__( 'Display', 'hivepress' ),
		'screen' => 'listing_attribute',
		'fields' => [
			'display_areas'  => [
				'label'       => esc_html__( 'Areas', 'hivepress' ),
				'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
				'type'        => 'checkboxes',
				'order'       => 10,
				'options'     => [
					'view_summary_primary'   => esc_html__( 'Summary (primary)', 'hivepress' ),
					'view_summary_secondary' => esc_html__( 'Summary (secondary)', 'hivepress' ),
					'view_full_primary'      => esc_html__( 'Full (primary)', 'hivepress' ),
					'view_full_secondary'    => esc_html__( 'Full (secondary)', 'hivepress' ),
				],
			],

			'display_format' => [
				'label'       => esc_html__( 'Format', 'hivepress' ),
				'description' => esc_html__( 'Set the attribute display format, the following placeholders are available: %value%.', 'hivepress' ),
				'type'        => 'text',
				'default'     => '%value%',
				'order'       => 20,
			],
		],
	],

	'listing_category_settings' => [
		'screen' => 'listing_category',

		'fields' => [
			'image'                 => [
				'label'        => esc_html__( 'Image', 'hivepress' ),
				'caption'      => esc_html__( 'Select Image', 'hivepress' ),
				'type'         => 'attachment_select',
				'file_formats' => [ 'jpg', 'jpeg', 'png' ],
				'order'        => 10,
			],

			'order'                 => [
				'label'     => esc_html__( 'Order', 'hivepress' ),
				'type'      => 'number',
				'min_value' => 0,
				'order'     => 20,
			],

			'display_subcategories' => [
				'label'   => esc_html__( 'Display', 'hivepress' ),
				'caption' => esc_html__( 'Display subcategories', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 30,
			],
		],
	],
];
