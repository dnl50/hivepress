<?php
/**
 * Listings block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings block class.
 *
 * @class Listings
 */
class Listings extends Block {

	/**
	 * Template mode.
	 *
	 * @var string
	 */
	protected $mode = 'view';

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Listings number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Listings category.
	 *
	 * @var int
	 */
	protected $category;

	/**
	 * Listings order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Featured flag.
	 *
	 * @var bool
	 */
	protected $featured;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'listings' ),

				'settings' => [
					'columns'  => [
						'label'    => hivepress()->translator->get_string( 'columns_number' ),
						'type'     => 'select',
						'default'  => 3,
						'required' => true,
						'_order'   => 10,

						'options'  => [
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'   => [
						'label'     => hivepress()->translator->get_string( 'items_number' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'required'  => true,
						'_order'    => 20,
					],

					'category' => [
						'label'       => hivepress()->translator->get_string( 'category' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'_order'      => 30,
					],

					'order'    => [
						'label'    => hivepress()->translator->get_string( 'sort_order' ),
						'type'     => 'select',
						'required' => true,
						'_order'   => 40,

						'options'  => [
							'created_date' => hivepress()->translator->get_string( 'by_date_added' ),
							'title'        => hivepress()->translator->get_string( 'by_title' ),
							'random'       => hivepress()->translator->get_string( 'by_random' ),
						],
					],

					'featured' => [
						'label'  => hivepress()->translator->get_string( 'display_only_featured_listings' ),
						'type'   => 'checkbox',
						'_order' => 50,
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'number' => get_option( 'hp_listings_per_page' ),
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		global $wp_query;

		$output = '';

		if ( $this->number ) {

			// Get column width.
			$columns      = absint( $this->columns );
			$column_width = 12;

			if ( $columns > 0 && $columns <= 12 ) {
				$column_width = round( $column_width / $columns );
			}

			// Get listing queries.
			$regular_query  = $wp_query;
			$featured_query = null;

			if ( ! isset( $this->context['listings'] ) ) {

				// Set query.
				$query = $this->get_context( 'listing_query' );

				if ( empty( $query ) ) {
					$query = Models\Listing::query()->filter(
						[
							'status' => 'publish',
						]
					)->limit( $this->number );

					// Set category.
					if ( $this->category ) {
						$query->filter( [ 'categories__in' => $this->category ] );
					}

					// Set order.
					if ( 'title' === $this->order ) {
						$query->order( [ 'title' => 'asc' ] );
					} elseif ( 'random' === $this->order ) {
						$query->order( 'random' );
					} else {
						$query->order( [ 'created_date' => 'desc' ] );
					}

					// Set featured flag.
					if ( $this->featured ) {
						$query->filter( [ 'featured' => true ] );
					}
				}

				// Get cached IDs.
				$listing_ids = null;

				if ( 'random' !== $this->order ) {
					$listing_ids = hivepress()->cache->get_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/listing' );

					if ( is_array( $listing_ids ) ) {
						$query = Models\Listing::query()->filter(
							[
								'status' => 'publish',
								'id__in' => $listing_ids,
							]
						)->order( 'id__in' )
						->limit( count( $listing_ids ) );
					}
				}

				// Query regular listings.
				$regular_query = new \WP_Query( $query->get_args() );

				// Cache IDs.
				if ( 'random' !== $this->order && is_null( $listing_ids ) && $regular_query->post_count <= 1000 ) {
					hivepress()->cache->set_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/listing', wp_list_pluck( $regular_query->posts, 'ID' ) );
				}
			} elseif ( 'edit' !== $this->mode && hivepress()->request->get_context( 'featured_ids' ) ) {

				// Query featured listings.
				$featured_query = new \WP_Query(
					Models\Listing::query()->filter(
						[
							'status' => 'publish',
							'id__in' => hivepress()->request->get_context( 'featured_ids', [] ),
						]
					)->order( 'random' )
					->limit( get_option( 'hp_listings_featured_per_page' ) )
					->get_args()
				);
			}

			if ( $regular_query->have_posts() || $featured_query ) {
				if ( 'edit' === $this->mode ) {
					$output .= '<table class="hp-listings hp-table">';
				} else {
					$output .= '<div class="hp-listings hp-grid hp-block">';
					$output .= '<div class="hp-row">';
				}

				// Render featured listings.
				if ( $featured_query ) {
					while ( $featured_query->have_posts() ) {
						$featured_query->the_post();

						// Get listing.
						$listing = Models\Listing::query()->get_by_id( get_post() );

						if ( $listing ) {
							$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

							// Render listing.
							$output .= ( new Template(
								[
									'template' => 'listing_' . $this->mode . '_block',

									'context'  => [
										'listing' => $listing,
									],
								]
							) )->render();

							$output .= '</div>';
						}
					}
				}

				// Render regular listings.
				while ( $regular_query->have_posts() ) {
					$regular_query->the_post();

					// Get listing.
					$listing = Models\Listing::query()->get_by_id( get_post() );

					if ( $listing ) {
						if ( 'edit' !== $this->mode ) {
							$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';
						}

						// Render listing.
						$output .= ( new Template(
							[
								'template' => 'listing_' . $this->mode . '_block',

								'context'  => [
									'listing' => $listing,
								],
							]
						) )->render();

						if ( 'edit' !== $this->mode ) {
							$output .= '</div>';
						}
					}
				}

				if ( 'edit' === $this->mode ) {
					$output .= '</table>';
				} else {
					$output .= '</div>';
					$output .= '</div>';
				}
			}

			// Reset query.
			wp_reset_postdata();
		}

		return $output;
	}
}
