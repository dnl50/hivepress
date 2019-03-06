<?php
/**
 * Listing sort form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing sort form class.
 *
 * @class Listing_Sort
 */
class Listing_Sort extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'action' => home_url( '/' ),
				'method' => 'GET',
				'fields' => [
					'sort'      => [
						'label'   => esc_html__( 'Sort by', 'hivepress' ),
						'type'    => 'select',
						'options' => [],
						'order'   => 10,
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_listing',
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
