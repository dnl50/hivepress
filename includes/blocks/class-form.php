<?php
/**
 * Form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form block class.
 *
 * @class Form
 */
class Form extends Block {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected $form;

	/**
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected $redirect;

	/**
	 * Form values.
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Form header.
	 *
	 * @var array
	 */
	protected $header = [];

	/**
	 * Form footer.
	 *
	 * @var array
	 */
	protected $footer = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get arguments.
		$form_args = [];

		// Set model.
		$model = hp\call_class_method( '\HivePress\Forms\\' . $this->form, 'get_meta', [ 'model' ] );

		if ( $model ) {
			$form_args['model'] = $this->get_context( $model );
		}

		// Set redirect.
		if ( $this->redirect ) {
			$form_args['redirect'] = $this->redirect;
		}

		// Set attributes.
		$form_args['attributes'] = $this->attributes;

		// Set header.
		if ( $this->header ) {
			$form_args['header'] = ( new Container(
				[
					'context' => $this->context,
					'tag'     => false,
					'blocks'  => $this->header,
				]
			) )->render();
		}

		// Set footer.
		if ( $this->footer ) {
			$form_args['footer'] = ( new Container(
				[
					'context' => $this->context,
					'tag'     => false,
					'blocks'  => $this->footer,
				]
			) )->render();
		}

		// Create form.
		$form = hp\create_class_instance( '\HivePress\Forms\\' . $this->form, [ $form_args ] );

		if ( $form ) {

			// Set values.
			$form->set_values( $this->values, true );

			if ( $form->get_method() === 'POST' ) {
				$form->set_values( $_POST, true );
			} elseif ( $form->get_method() === 'GET' ) {
				$form->set_values( $_GET, true );
			}

			// Render form.
			$output .= $form->render();
		}

		return $output;
	}
}
