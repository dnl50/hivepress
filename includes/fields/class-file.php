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
	 * Multiple status.
	 *
	 * @var bool
	 */
	protected $multiple;

	/**
	 * File formats.
	 *
	 * @var array
	 */
	protected $file_formats;

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {
		parent::get_attributes();

		// Set multiple status.
		if ( $this->get_multiple() ) {
			$this->attributes['multiple'] = true;
		}

		// Set file formats.
		if ( $this->get_file_formats() ) {
			$this->attributes['accept'] = '.' . implode( ',.', $this->get_file_formats() );
		}

		return $this->attributes;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<input type="' . esc_attr( $this->get_type() ) . '" name="' . esc_attr( $this->get_name() ) . '" value="' . esc_attr( $this->get_value() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';
	}
}