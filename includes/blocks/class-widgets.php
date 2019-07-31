<?php
/**
 * Widgets block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Widgets block class.
 *
 * @class Widgets
 */
class Widgets extends Block {

	/**
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Widget area.
	 *
	 * @var string
	 */
	protected $area;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		ob_start();
		dynamic_sidebar( hp\prefix( $this->area ) );
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}
}