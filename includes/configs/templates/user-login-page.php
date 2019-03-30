<?php
/**
 * User login page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'columns' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-row' ],
			],

			'blocks'     => [
				'content' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-col-sm-4', 'hp-col-sm-offset-4', 'hp-col-xs-12' ],
					],

					'blocks'     => [
						'login_form' => [
							'type'  => 'user_login_form',
							'order' => 10,
						],
					],
				],
			],
		],
	],
];