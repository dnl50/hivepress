<?php
/**
 * Container block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Container block class.
 *
 * @class Container
 */
class Container extends Block {

	/**
	 * Inner blocks.
	 *
	 * @var array
	 */
	private $blocks = [];

	/**
	 * Sets inner blocks.
	 *
	 * @param mixed $blocks Inner blocks.
	 */
	final public function set_blocks( $blocks ) {
		$this->blocks = [];

		foreach ( $blocks as $block_name => $block_args ) {
			$block_class = '\HivePress\Blocks\\' . $block_args['type'];

			$this->blocks[ $block_name ] = new $block_class( $block_args );
		}
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<' . esc_attr( $this->get_attribute( 'tag' ) ) . ' ' . hp_html_attributes( $this->get_attribute( 'attributes' ) ) . '>';

		foreach ( $this->blocks as $block ) {
			$output .= $block->render();
		}

		$output .= '</' . esc_attr( $this->get_attribute( 'tag' ) ) . '>';

		return $output;
	}
}
