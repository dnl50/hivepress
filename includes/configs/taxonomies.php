<?php
/**
 * Taxonomies configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_category' => [
		'object_type' => [ 'listing', 'listing_attribute' ],

		'args'        => [
			'hierarchical' => true,
			'rewrite'      => [ 'slug' => 'listing-category' ],
		],
	],
];
