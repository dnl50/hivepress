<?php
/**
 * Admin component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin component class.
 *
 * @class Admin
 */
final class Admin {

	/**
	 * Array of post states.
	 *
	 * @var array
	 */
	private $post_states = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register post types.
		add_action( 'init', [ $this, 'register_post_types' ] );

		// Register taxonomies.
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		if ( is_admin() ) {

			// Manage admin pages.
			add_action( 'admin_menu', [ $this, 'add_pages' ] );
			add_filter( 'custom_menu_order', [ $this, 'order_pages' ] );
			add_filter( 'menu_order', [ $this, 'order_pages' ] );

			// Register settings.
			add_action( 'admin_init', [ $this, 'register_settings' ] );

			// Add options.
			add_action( 'hivepress/activate', [ $this, 'add_options' ] );

			// Manage post states.
			add_action( 'init', [ $this, 'register_post_states' ] );
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

			// Manage meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
			add_action( 'save_post', [ $this, 'update_meta_box' ], 10, 2 );

			// Add term boxes.
			add_action( 'admin_init', [ $this, 'add_term_boxes' ] );

			// Filter comments.
			add_filter( 'comments_clauses', [ $this, 'filter_comments' ] );

			// Enqueue scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render notices.
			add_action( 'admin_notices', [ $this, 'render_notices' ] );
		}
	}

	/**
	 * Registers post types.
	 */
	public function register_post_types() {
		foreach ( hivepress()->get_config( 'post_types' ) as $post_type => $post_type_args ) {
			register_post_type( hp\prefix( $post_type ), $post_type_args );
		}
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		foreach ( hivepress()->get_config( 'taxonomies' ) as $taxonomy => $taxonomy_args ) {
			register_taxonomy( hp\prefix( $taxonomy ), hp\prefix( $taxonomy_args['object_type'] ), $taxonomy_args );
		}
	}

	/**
	 * Adds admin pages.
	 */
	public function add_pages() {
		global $menu;

		// Add separator.
		$menu[] = [ '', 'manage_options', 'hp_separator', '', 'wp-menu-separator' ];

		// Add pages.
		add_menu_page( sprintf( esc_html__( '%s Settings', 'hivepress' ), HP_CORE_NAME ), HP_CORE_NAME, 'manage_options', 'hp_settings', [ $this, 'render_settings' ], HP_CORE_URL . '/assets/images/logo.svg' );
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Settings', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Settings', 'hivepress' ), 'manage_options', 'hp_settings' );
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Add-ons', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Add-ons', 'hivepress' ), 'manage_options', 'hp_addons', [ $this, 'render_addons' ] );
	}

	/**
	 * Orders admin pages.
	 *
	 * @param array $menu Menu items.
	 * @return array
	 */
	public function order_pages( $menu ) {
		if ( current_user_can( 'manage_options' ) ) {
			if ( is_array( $menu ) ) {

				// Get admin pages.
				$pages = [
					'hp_separator',
					'hp_settings',
				];

				foreach ( array_keys( hivepress()->get_config( 'post_types' ) ) as $post_type ) {
					$pages[] = 'edit.php?post_type=' . hp\prefix( $post_type );
				}

				// Filter menu items.
				$menu = array_filter(
					$menu,
					function( $name ) use ( $pages ) {
						return ! in_array( $name, $pages, true );
					}
				);

				// Insert menu items.
				array_splice( $menu, array_search( 'separator2', $menu, true ) - 1, 0, $pages );

				return $menu;
			} else {
				return true;
			}
		}
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Render admin page.
			$template_name = str_replace( '_', '-', substr( $name, strlen( 'render' ) + 1 ) );
			$template_path = HP_CORE_DIR . '/templates/admin/' . $template_name . '.php';

			if ( file_exists( $template_path ) ) {
				if ( 'settings' === $template_name ) {
					$tabs        = $this->get_settings_tabs();
					$current_tab = $this->get_settings_tab();
				} elseif ( 'addons' === $template_name ) {
					$tabs        = $this->get_addons_tabs();
					$current_tab = $this->get_addons_tab();
					$addons      = $this->get_addons( $current_tab );
				}

				include $template_path;
			}
		} elseif ( strpos( $name, 'validate_' ) === 0 ) {

			// Validate setting.
			return $this->validate_setting( substr( $name, strlen( 'validate' ) + 1 ), $args[0] );
		}
	}

	/**
	 * Registers settings.
	 */
	public function register_settings() {
		global $pagenow;

		if ( 'options.php' === $pagenow || ( 'admin.php' === $pagenow && 'hp_settings' === hp\get_array_value( $_GET, 'page' ) ) ) {

			// Get current tab.
			$tab = hp\get_array_value( hivepress()->get_config( 'options' ), $this->get_settings_tab() );

			if ( ! is_null( $tab ) ) {
				foreach ( hp\sort_array( $tab['sections'] ) as $section_name => $section ) {

					// Add settings section.
					add_settings_section( $section_name, esc_html( hp\get_array_value( $section, 'title' ) ), [ $this, 'render_settings_section' ], 'hp_settings' );

					// Register settings.
					foreach ( hp\sort_array( $section['fields'] ) as $option_name => $option ) {
						$option_name       = hp\prefix( $option_name );
						$option['default'] = get_option( $option_name );

						add_settings_field( $option_name, esc_html( $option['label'] ) . $this->render_tooltip( hp\get_array_value( $option, 'description' ) ), [ $this, 'render_settings_field' ], 'hp_settings', $section_name, array_merge( $option, [ 'name' => $option_name ] ) );
						register_setting( 'hp_settings', $option_name, [ $this, 'validate_' . hp\unprefix( $option_name ) ] );
					}
				}
			}
		}
	}

	/**
	 * Validates setting.
	 *
	 * @param string $name Option name.
	 * @return mixed
	 */
	private function validate_setting( $name ) {

		// Get current tab.
		$tab = hp\get_array_value( hivepress()->get_config( 'options' ), $this->get_settings_tab() );

		// Get setting.
		$setting = false;

		if ( ! is_null( $tab ) ) {
			foreach ( $tab['sections'] as $section_name => $section ) {
				foreach ( $section['fields'] as $option_name => $option ) {
					if ( $option_name === $name ) {
						$setting = $option;

						break 2;
					}
				}
			}
		}

		// Validate setting.
		if ( false !== $setting ) {

			// Get setting name.
			$setting_name = hp\prefix( $name );

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $setting['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				$field = new $field_class( $setting );

				// Validate field.
				$field->set_value( hp\get_array_value( $_POST, $setting_name ) );

				if ( $field->validate() ) {
					return $field->get_value();
				} else {
					foreach ( $field->get_errors() as $error ) {
						add_settings_error( $setting_name, $setting_name, esc_html( $error ) );
					}

					return get_option( $setting_name );
				}
			}
		}

		return false;
	}

	/**
	 * Renders settings section.
	 *
	 * @param array $args Section arguments.
	 */
	public function render_settings_section( $args ) {

		// Get current tab.
		$tab = hp\get_array_value( hivepress()->get_config( 'options' ), $this->get_settings_tab() );

		if ( ! is_null( $tab ) ) {

			// Get current section.
			$section = hp\get_array_value( $tab['sections'], $args['id'] );

			if ( ! is_null( $section ) ) {

				// Render description.
				if ( isset( $section['description'] ) ) {
					echo '<p>' . esc_html( $section['description'] ) . '</p>';
				}
			}
		}
	}

	/**
	 * Renders settings field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_settings_field( $args ) {
		$output = '';

		// Get field class.
		$field_class = '\HivePress\Fields\\' . $args['type'];

		if ( class_exists( $field_class ) ) {

			// Create field.
			$field = new $field_class( $args );

			$field->set_attributes( [ 'class' => [ 'hp-form__field', 'hp-form__field--' . str_replace( '_', '-', $field::get_type() ) ] ] );

			// Render field.
			$output .= $field->render();
		}

		echo $output;
	}

	/**
	 * Gets settings tabs.
	 *
	 * @return array
	 */
	private function get_settings_tabs() {
		return array_map(
			function( $section ) {
				return hp\get_array_value( $section, 'title' );
			},
			hp\sort_array( hivepress()->get_config( 'options' ) )
		);
	}

	/**
	 * Gets current settings tab.
	 *
	 * @return mixed
	 */
	private function get_settings_tab() {
		$current_tab = false;

		// Get all tabs.
		$tabs = array_keys( hp\sort_array( hivepress()->get_config( 'options' ) ) );

		$first_tab   = hp\get_array_value( $tabs, 0 );
		$current_tab = hp\get_array_value( $_GET, 'tab', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Gets add-ons.
	 *
	 * @param string $status Add-ons status.
	 * @return array
	 */
	private function get_addons( $status = 'all' ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Get cached add-ons.
		$addons = get_transient( 'hp_addons' );

		if ( false === $addons ) {
			$addons = [];

			// Query plugins.
			$api = plugins_api(
				'query_plugins',
				[
					'author' => 'hivepress',
					'fields' => [
						'icons' => true,
					],
				]
			);

			if ( ! is_wp_error( $api ) ) {

				// Filter add-ons.
				$addons = array_filter(
					$api->plugins,
					function( $plugin ) {
						return 'hivepress' !== $plugin->slug;
					}
				);

				// Cache add-ons.
				set_transient( 'hp_addons', $addons, DAY_IN_SECONDS );
			}
		}

		// Set add-on statuses.
		foreach ( $addons as $index => $addon ) {

			// Get path and status.
			$addon_path   = $addon->slug . '/' . $addon->slug . '.php';
			$addon_status = install_plugin_install_status( $addon );

			// Set activation status.
			if ( ! in_array( $addon_status['status'], [ 'install', 'update_available' ], true ) && ! is_plugin_active( $addon_path ) ) {
				$addon_status['status'] = 'activate';
				$addon_status['url']    = admin_url(
					'plugins.php?' . http_build_query(
						[
							'action'   => 'activate',
							'plugin'   => $addon_path,
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $addon_path ),
						]
					)
				);
			}

			unset( $addon_status['version'] );

			$addons[ $index ]->name = str_replace( HP_CORE_NAME . ' ', '', $addon->name );
			$addons[ $index ]       = (object) array_merge( (array) $addon, $addon_status );
		}

		// Filter add-ons.
		if ( 'all' !== $status ) {
			$addons = array_filter(
				$addons,
				function( $addon ) use ( $status ) {
					return 'installed' === $status && 'install' !== $addon->status;
				}
			);
		}

		return $addons;
	}

	/**
	 * Gets add-ons tabs.
	 *
	 * @return array
	 */
	private function get_addons_tabs() {

		// Set tabs.
		$tabs = [
			'all'       => [
				'name'  => esc_html__( 'All', 'hivepress' ),
				'count' => 0,
			],
			'installed' => [
				'name'  => esc_html__( 'Installed', 'hivepress' ),
				'count' => 0,
			],
		];

		// Get add-ons.
		$addons = $this->get_addons();

		// Set tab counts.
		$tabs['all']['count']       = count( $addons );
		$tabs['installed']['count'] = count(
			array_filter(
				$addons,
				function( $addon ) {
					return 'install' !== $addon->status;
				}
			)
		);

		// Filter tabs.
		$tabs = array_filter(
			$tabs,
			function( $tab ) {
				return 0 !== $tab['count'];
			}
		);

		return $tabs;
	}

	/**
	 * Gets current add-ons tab.
	 *
	 * @return mixed
	 */
	private function get_addons_tab() {
		$current_tab = false;

		// Get all tabs.
		$tabs = array_keys( $this->get_addons_tabs() );

		$first_tab   = hp\get_array_value( $tabs, 0 );
		$current_tab = hp\get_array_value( $_GET, 'addon_status', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Adds options.
	 */
	public function add_options() {
		foreach ( hivepress()->get_config( 'options' ) as $tab ) {
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $option_name => $option ) {
					if ( isset( $option['default'] ) ) {
						add_option( hp\prefix( $option_name ), $option['default'] );
					}
				}
			}
		}
	}

	/**
	 * Registers post states.
	 */
	public function register_post_states() {
		foreach ( hivepress()->get_config( 'options' ) as $tab ) {
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $field_name => $field ) {
					if ( strpos( $field_name, 'page_' ) === 0 ) {
						$post_id = absint( get_option( hp\prefix( $field_name ) ) );

						if ( 0 !== $post_id ) {
							$this->post_states[ $post_id ] = $field['label'];
						}
					}
				}
			}
		}
	}

	/**
	 * Adds post states.
	 *
	 * @param array   $states Post states.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function add_post_states( $states, $post ) {
		if ( isset( $this->post_states[ $post->ID ] ) ) {
			$states[] = $this->post_states[ $post->ID ];
		}

		return $states;
	}

	/**
	 * Adds meta boxes.
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
			if ( hp\prefix( $meta_box['screen'] ) === $post_type ) {

				// Filter arguments.
				$meta_box = apply_filters( 'hivepress/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

				// Add meta box.
				if ( ! empty( $meta_box['fields'] ) ) {
					add_meta_box( hp\prefix( $meta_box_name ), $meta_box['title'], [ $this, 'render_meta_box' ], hp\prefix( $meta_box['screen'] ), hp\get_array_value( $meta_box, 'context', 'normal' ), hp\get_array_value( $meta_box, 'priority', 'default' ) );
				}
			}
		}
	}

	/**
	 * Updates meta box values.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_meta_box( $post_id, $post ) {
		global $pagenow;

		if ( 'post.php' === $pagenow ) {
			foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
				$screen = hp\prefix( $meta_box['screen'] );

				if ( $screen === $post->post_type || ( is_array( $screen ) && in_array( $post->post_type, $screen, true ) ) ) {

					// Filter arguments.
					$meta_box = apply_filters( 'hivepress/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

					foreach ( $meta_box['fields'] as $field_name => $field_args ) {

						// Get field class.
						$field_class = '\HivePress\Fields\\' . $field_args['type'];

						if ( class_exists( $field_class ) ) {

							// Create field.
							$field = new $field_class( $field_args );

							// Validate field.
							$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

							if ( $field->validate() ) {

								// Update meta value.
								update_post_meta( $post_id, hp\prefix( $field_name ), $field->get_value() );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Renders meta box fields.
	 *
	 * @param WP_Post $post Post object.
	 * @param array   $args Meta box arguments.
	 */
	public function render_meta_box( $post, $args ) {
		$output = '';

		// Get meta box name.
		$meta_box_name = hp\unprefix( $args['id'] );

		// Get meta box.
		$meta_box = hp\get_array_value( hivepress()->get_config( 'meta_boxes' ), $meta_box_name );

		if ( ! is_null( $meta_box ) ) {

			// Filter arguments.
			$meta_box = apply_filters( 'hivepress/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

			// Render fields.
			$output .= '<table class="form-table hp-form">';

			foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

				// Get field class.
				$field_class = '\HivePress\Fields\\' . $field_args['type'];

				if ( class_exists( $field_class ) ) {

					// Create field.
					$field = new $field_class( array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) );

					$field->set_attributes( [ 'class' => [ 'hp-form__field', 'hp-form__field--' . str_replace( '_', '-', $field::get_type() ) ] ] );

					// Get field value.
					$value = get_post_meta( $post->ID, hp\prefix( $field_name ), true );

					if ( '' === $value ) {
						$value = null;
					}

					$field->set_value( $value );

					if ( 'hidden' === $field_args['type'] ) {

						// Render field.
						$output .= $field->render();
					} else {
						$output .= '<tr>';

						// Render field label.
						$output .= '<th scope="row">' . esc_html( $field_args['label'] ) . $this->render_tooltip( hp\get_array_value( $field_args, 'description' ) ) . '</th>';

						// Render field.
						$output .= '<td>' . $field->render() . '</td>';

						$output .= '</tr>';
					}
				}
			}

			$output .= '</table>';
		}

		echo $output;
	}

	/**
	 * Adds term boxes.
	 */
	public function add_term_boxes() {
		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
			if ( taxonomy_exists( hp\prefix( $meta_box['screen'] ) ) ) {
				$taxonomy = hp\prefix( $meta_box['screen'] );

				// Update term boxes.
				add_action( 'edit_' . $taxonomy, [ $this, 'update_term_box' ] );
				add_action( 'create_' . $taxonomy, [ $this, 'update_term_box' ] );

				// Render term boxes.
				add_action( $taxonomy . '_edit_form_fields', [ $this, 'render_term_box' ], 10, 2 );
				add_action( $taxonomy . '_add_form_fields', [ $this, 'render_term_box' ], 10, 2 );
			}
		}
	}

	/**
	 * Updates term box values.
	 *
	 * @param int $term_id Term ID.
	 */
	public function update_term_box( $term_id ) {

		// Get term.
		$term = get_term( $term_id );

		if ( ! is_null( $term ) ) {
			foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
				$screen = hp\prefix( $meta_box['screen'] );

				if ( ! is_array( $screen ) && taxonomy_exists( $screen ) && $screen === $term->taxonomy ) {

					// Filter arguments.
					$meta_box = apply_filters( 'hivepress/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

					foreach ( $meta_box['fields'] as $field_name => $field_args ) {

						// Get field class.
						$field_class = '\HivePress\Fields\\' . $field_args['type'];

						if ( class_exists( $field_class ) ) {

							// Create field.
							$field = new $field_class( $field_args );

							// Validate field.
							$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

							if ( $field->validate() ) {

								// Update meta value.
								update_term_meta( $term->term_id, hp\prefix( $field_name ), $field->get_value() );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Renders term box fields.
	 *
	 * @param mixed  $term Term object.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function render_term_box( $term, $taxonomy = '' ) {
		$output = '';

		// Get term ID.
		$term_id = 0;

		if ( ! is_object( $term ) ) {
			$taxonomy = $term;
		} else {
			$term_id = $term->term_id;
		}

		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
			$screen = hp\prefix( $meta_box['screen'] );

			if ( ! is_array( $screen ) && taxonomy_exists( $screen ) && $screen === $taxonomy ) {

				// Filter arguments.
				$meta_box = apply_filters( 'hivepress/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

				foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

					// Get field class.
					$field_class = '\HivePress\Fields\\' . $field_args['type'];

					if ( class_exists( $field_class ) ) {

						// Create field.
						$field = new $field_class( array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) );

						$field->set_attributes( [ 'class' => [ 'hp-form__field', 'hp-form__field--' . str_replace( '_', '-', $field::get_type() ) ] ] );

						if ( ! is_object( $term ) ) {
							$output .= '<div class="form-field">';

							// Render label.
							$output .= '<label for="' . esc_attr( hp\prefix( $field_name ) ) . '">' . esc_html( $field_args['label'] ) . '</label>';

							// Render field.
							$output .= $field->render();

							// Render description.
							if ( isset( $field_args['description'] ) ) {
								$output .= '<p>' . esc_html( $field_args['description'] ) . '</p>';
							}

							$output .= '</div>';
						} else {
							$output .= '<tr class="form-field">';

							// Render label.
							$output .= '<th scope="row"><label for="' . esc_attr( hp\prefix( $field_name ) ) . '">' . esc_html( $field_args['label'] ) . '</label></th>';
							$output .= '<td>';

							// Get field value.
							$value = get_term_meta( $term->term_id, hp\prefix( $field_name ), true );

							if ( '' === $value ) {
								$value = null;
							}

							$field->set_value( $value );

							// Render field.
							$output .= $field->render();

							// Render description.
							if ( isset( $field_args['description'] ) ) {
								$output .= '<p class="description">' . esc_html( $field_args['description'] ) . '</p>';
							}

							$output .= '</td>';
							$output .= '</tr>';
						}
					}
				}
			}
		}

		echo $output;
	}

	/**
	 * Filters comments.
	 *
	 * @param array $clauses Comment clauses.
	 * @return array
	 */
	public function filter_comments( $clauses ) {
		global $pagenow;

		if ( in_array( $pagenow, [ 'index.php', 'edit-comments.php' ], true ) ) {
			$clauses['where'] .= ' AND comment_type NOT LIKE "hp_%"';
		}

		return $clauses;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		global $pagenow;

		if ( in_array( $pagenow, [ 'edit-tags.php', 'term.php' ], true ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Renders notices.
	 */
	public function render_notices() {
		global $pagenow;

		if ( 'admin.php' === $pagenow && 'hp_settings' === hp\get_array_value( $_GET, 'page' ) ) {
			settings_errors();
		}
	}

	/**
	 * Renders tooltip.
	 *
	 * @param string $text Tooltip text.
	 * @return string
	 */
	private function render_tooltip( $text ) {
		$output = '';

		if ( ! empty( $text ) ) {
			$output .= '<div class="hp-tooltip"><span class="hp-tooltip__icon dashicons dashicons-editor-help"></span><div class="hp-tooltip__text">' . esc_html( $text ) . '</div></div>';
		}

		return $output;
	}
}
