<?php
/**
 * File field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File field class.
 *
 * @class File
 */
class File extends Field {

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * File formats.
	 *
	 * @var array
	 */
	protected $file_formats;

	/**
	 * Sets multiple property.
	 *
	 * @param bool $multiple Multiple property.
	 */
	final protected function set_multiple( $multiple ) {
		$this->multiple = boolval( $multiple );
	}

	/**
	 * Sets file formats.
	 *
	 * @param int $file_formats File formats.
	 */
	final protected function set_file_formats( $file_formats ) {
		$this->file_formats = (array) $file_formats;
	}

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	final protected function get_attributes() {

		// Set multiple property.
		if ( $this->multiple ) {
			$this->attributes['multiple'] = true;
		}

		// Set file formats.
		if ( ! empty( $this->file_formats ) ) {
			$this->attributes['accept'] = '.' . implode( ',.', $this->file_formats );
		}

		return $this->attributes;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';
	}
}
