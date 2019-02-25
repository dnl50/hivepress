<?php
/**
 * Listing report form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing report form class.
 *
 * @class Listing_Report
 */
class Listing_Report extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'title'   => esc_html__( 'Report Listing', 'hivepress' ),
				'captcha' => false,
				'fields'  => [
					'reason' => [
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'order'      => 10,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}