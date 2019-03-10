<?php
/**
 * Post model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post model class.
 *
 * @class Post
 */
abstract class Post extends Model {

	/**
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	final public static function get( $id ) {

		// Get alias data.
		$data = get_post( absint( $id ), ARRAY_A );

		if ( ! is_null( $data ) && hp\prefix( static::$name ) === $data['post_type'] ) {
			$values = [];

			// Get alias meta.
			$meta = array_map(
				function( $meta_values ) {
					return reset( $meta_values );
				},
				get_post_meta( $data['ID'] )
			);

			// Get instance values.
			foreach ( array_keys( static::$fields ) as $field_name ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$values[ $field_name ] = hp\get_array_value( $data, array_search( $field_name, static::$aliases, true ) );
				} else {
					$values[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
				}
			}

			// Create and fill instance.
			$instance = new static();

			$instance->set_id( $data['ID'] );
			$instance->fill( $values );

			return $instance;
		}

		return null;
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	final public function save() {

		// Alias instance values.
		$data = [];
		$meta = [];

		foreach ( static::$fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->values, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$data[ array_search( $field_name, static::$aliases, true ) ] = $field->get_value();
				} else {
					$meta[ $field_name ] = $field->get_value();
				}
			} else {
				$this->add_errors( $field->get_errors() );
			}
		}

		// Create or update instance.
		if ( empty( $this->errors ) ) {
			if ( is_null( $this->id ) ) {
				$id = wp_insert_post( array_merge( $data, [ 'post_type' => hp\prefix( static::$name ) ] ) );

				if ( 0 !== $id ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( wp_update_post( array_merge( $data, [ 'ID' => $this->id ] ) ) === 0 ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_post_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
			}

			return true;
		}

		return false;
	}

	/**
	 * Deletes instance from the database.
	 *
	 * @return bool
	 */
	final public function delete() {
		return $this->id && wp_delete_post( $this->id, true ) !== false;
	}
}