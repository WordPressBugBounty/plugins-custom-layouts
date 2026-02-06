<?php
/**
 * Handles the frontend display of the layout
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes/layout
 */

namespace Custom_Layouts\Layout;

use Custom_Layouts\Settings;
use Custom_Layouts\Util;
use Custom_Layouts\Template\Controller as Template_Controller;
use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Core\Cache;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core frontend class of a layout.
 */
class Controller {

	/**
	 * The layout ID.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * The layout name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Processed layout settings.
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Raw layout settings from the database.
	 *
	 * @var array
	 */
	protected $settings_raw = array();

	/**
	 * The input type for the layout.
	 *
	 * @var string
	 */
	protected $input_type = '';

	/**
	 * Whether to use caching for this layout.
	 *
	 * @var bool
	 */
	protected $use_cache = true;

	/**
	 * Query data for the layout.
	 *
	 * @var array
	 */
	protected $query_data = array();

	/**
	 * Whether the layout has been initialized.
	 *
	 * @var bool
	 */
	private $has_init = false;

	/**
	 * Layout values array.
	 *
	 * @var array
	 */
	private $values = array();

	/**
	 * HTML attributes for the layout container.
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Constructor for the layout controller.
	 *
	 * TODO - refactor the cache parameter.
	 *
	 * @since 1.0.0
	 * @param mixed  $args  Either a layout ID (int) or an array of settings.
	 * @param string $cache Whether to use caching ('yes' or 'no').
	 */
	public function __construct( $args, $cache = 'yes' ) {
		$this->init( $args, $cache );
	}

	/**
	 * Initialize the layout controller with settings and data.
	 *
	 * @since 1.0.0
	 * @param mixed  $args  Either a layout ID (int) or an array of settings.
	 * @param string $cache Whether to use caching ('yes' or 'no').
	 */
	private function init( $args, $cache = 'yes' ) {

		if ( is_scalar( $args ) ) {
			// Then it is an ID for a template, load the template data.
			$this->id      = absint( $args );
			$this->id      = apply_filters( 'custom-layouts/layout/id', $this->id );
			$settings_args = Settings::get_layout_data( $this->id );

		} else {
			// Then it is an array of settings passed (usually from a block or shortcode).
			$this->id      = -1;
			$settings_args = $args;
		}
		$settings_args = $this->deprecated( $settings_args );

		// TODO - get defaults from settings - already.
		$default_settings = Settings::get_settings_defaults( array( 'layout', 'query' ) );

		// Defaults.
		$default_settings['container_class'] = '';

		/*
		if( ! isset( $default_settings['post_type'] ) ) {
			// TODO - remove - normalise this between blocks and our own UI
			$default_settings['post_type'] = array( 'post' );
		} */

		if ( $cache === 'no' ) {
			$this->use_cache = false;
		}

		$this->settings_raw = $settings_args;
		$this->settings     = wp_parse_args( $settings_args, $default_settings );

		// $this->data_type = $this->settings['data_type'];

		// Set init to true, the following functions require it.
		$this->has_init = true;

		// Attributes needs has_init to be true so we can use `get_values`.
		$this->prepare_settings(); // @phpstan-ignore method.resultUnused
		$this->set_attributes();

		// Add user defined custom classes.
		$this->add_class( $this->settings['add_class'] );
	}

	/**
	 * Performs action once the settings have been stored locally.
	 *
	 * @since 1.4.0
	 */
	private function prepare_settings() {
	}

	/**
	 * Handle deprecated settings and migrate them to new format.
	 *
	 * @since 1.0.0
	 * @param array $settings_args The settings array to process.
	 * @return array The processed settings array.
	 */
	private function deprecated( $settings_args ) {

		if ( isset( $settings_args['columns'] ) ) {
			$settings_args['columns_large'] = $settings_args['columns'];
		}
		return $settings_args;
	}

	/**
	 * Goes through the settings, if unlocked, uses its own setting, if locked, walks through
	 * other settings until it finds something unlocked and copies that value.
	 *
	 * @since 1.0.0
	 * @param string $device_size    The device size (xsmall, small, medium, large).
	 * @param string $setting_prefix The prefix for the setting to retrieve.
	 * @return string The attribute value for the device type.
	 */
	private function get_device_type_attribute_value( $device_size, $setting_prefix ) {

		$device_order       = array( 'xsmall', 'small', 'medium', 'large' );
		$device_count       = count( $device_order );
		$active_value_index = $setting_prefix . '_' . $device_size;
		$active_lock_index  = $setting_prefix . '_' . $device_size . '_locked';

		if ( isset( $this->settings[ $active_lock_index ] ) && ( $this->settings[ $active_lock_index ] === 'no' ) ) {
			return $this->settings[ $active_value_index ];
		}

		// Find the current device in the order.
		$active_device_index = array_search( $device_size, $device_order, true );

		if ( $active_device_index === false ) {
			return '';
		} elseif ( $active_device_index === $device_count - 1 ) {
			return $this->settings[ $active_value_index ];
		}

		for ( $device_index = $active_device_index + 1; $device_index < $device_count; $device_index++ ) {

			$device_type  = $device_order[ $device_index ];
			$value_index  = $setting_prefix . '_' . $device_type;
			$locked_index = $setting_prefix . '_' . $device_type . '_locked';

			// Don't check the last item to see if it was locked - it can't be.
			if ( $device_index === $device_count - 1 ) {
				return $this->settings[ $value_index ];
			}

			$is_device_locked = true;
			if ( isset( $this->settings[ $locked_index ] ) && ( $this->settings[ $locked_index ] === 'no' ) ) {
				$is_device_locked = false;
			}
			if ( ! $is_device_locked ) {
				// Then we want this value.
				return $this->settings[ $value_index ];
			}
		}
		// @phpstan-ignore deadCode.unreachable
		return '';
	}

	/**
	 * Set CSS classes for the layout based on settings.
	 *
	 * @since 1.0.0
	 */
	private function set_classes() {

		$modifier_class = '';
		// TODO - check all columns.
		if ( absint( $this->settings['columns_medium'] ) > 1 ) {
			if ( 'yes' === $this->settings['use_masonry'] ) {
				$modifier_class = 'cl-layout--masonry';
			} else {
				if ( 'yes' === $this->settings['equal_height_rows'] ) {
					$modifier_class .= ' cl-layout--equal-rows';
				}
				if ( 'yes' === $this->settings['fill_last_row'] ) {
					$modifier_class .= ' cl-layout--fill-last-row';
				}
			}
		}

		$this->add_class( $modifier_class );

		// TODO - frontend classes don't respect responsive locked status (don't add them if they are locked).
		$column_classes_arr = array();
		array_push( $column_classes_arr, 'cl-layout--col-l-' . $this->settings['columns_large'] );

		array_push( $column_classes_arr, 'cl-layout--col-m-' . $this->get_device_type_attribute_value( 'medium', 'columns' ) );
		array_push( $column_classes_arr, 'cl-layout--col-s-' . $this->get_device_type_attribute_value( 'small', 'columns' ) );
		array_push( $column_classes_arr, 'cl-layout--col-xs-' . $this->get_device_type_attribute_value( 'xsmall', 'columns' ) );

		$column_classes = ' ' . implode( ' ', $column_classes_arr );
		$this->add_class( $column_classes );
	}

	/**
	 * Set the HTML attributes for the layout element.
	 *
	 * @since 1.0.0
	 */
	private function set_attributes() {

		$base_class = 'cl-layout';
		$type_class = '';
		$type_class = ' cl-layout--grid';

		$id_class = '';
		if ( $this->id !== -1 ) {
			$id_class = ' cl-layout--id-' . $this->id;
		}

		$this->attributes['class'] = $base_class . $type_class . $id_class;

		// TODO.
		/*
		if ( 1 === absint( $this->settings['columns'] ) ) {
			$this->attributes['role'] = 'list'; // TODO - allow different types of roles depening on settings.
		} else {
			$this->attributes['role'] = 'grid'; // TODO - allow different types of roles depening on settings.
		}*/
	}

	/**
	 * Check if the layout controller has been initialized.
	 *
	 * @since 1.0.0
	 * @return bool True if initialized, false otherwise.
	 */
	protected function has_init() {
		if ( ! $this->has_init ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'If you are extending the Layout constructor, make sure to call `parent::_construct()` at the top of the child constructor.', 'custom-layouts' ), '1.0.0' );
			return false;
		}
		return true;
	}

	/**
	 * Add CSS class(es) to the layout element.
	 *
	 * @since 1.0.0
	 * @param string $class_names The class name(s) to add.
	 */
	public function add_class( $class_names ) {

		if ( ! $this->has_init() ) {
			return;
		}

		if ( empty( $class_names ) ) {
			return;
		}

		$this->attributes['class'] .= ' ' . $class_names;
	}

	/**
	 * Add CSS class(es) to the layout container element.
	 *
	 * @since 1.0.0
	 * @param string $class_names The class name(s) to add.
	 */
	public function add_container_class( $class_names ) {

		if ( ! $this->has_init() ) {
			return;
		}

		if ( empty( $class_names ) ) {
			return;
		}

		$this->settings['container_class'] .= ' ' . $class_names;
	}

	/**
	 * Add an HTML attribute to the layout element.
	 *
	 * @since 1.0.0
	 * @param string $attribute_name  The attribute name.
	 * @param string $attribute_value The attribute value.
	 */
	protected function add_attribute( $attribute_name, $attribute_value ) {

		if ( ! $this->has_init() ) {
			return;
		}

		$this->attributes[ $attribute_name ] = $attribute_value;
	}

	/**
	 * Get all HTML attributes for the layout element.
	 *
	 * @since 1.0.0
	 * @return array The attributes array.
	 */
	protected function get_attributes() {

		if ( ! $this->has_init() ) {
			return array();
		}
		return $this->attributes;
	}

	/**
	 * Get the values array.
	 *
	 * @since 1.0.0
	 * @return array|void The values array or void if not initialized.
	 */
	protected function get_values() {

		if ( ! $this->has_init() ) {
			return;
		}
		return $this->values;
	}

	/**
	 * Display the HTML output of the layout.
	 *
	 * @since 1.0.0
	 * @param bool $return_output Whether to return the output instead of echoing it.
	 * @return string|void The HTML output if $return_output is true, void otherwise.
	 */
	public function render( $return_output = false ) {

		if ( ! $this->has_init() ) {
			return '';
		}

		// We don't want to modify the internal values.
		// $settings = $this->settings;

		// Trigger action when starting.
		do_action( 'custom-layouts/layout/before_render', $this->settings, $this->name );

		// Modify args before render.
		$this->settings = apply_filters( 'custom-layouts/layout/render_args', $this->settings, $this->name );
		$this->set_classes();

		ob_start();
		// Generate inline CSS if we couldn't create a CSS file.
		if ( 'inline' === CSS_Loader::get_mode() ) {
			echo '<style>';
			$template_id = $this->settings['template_id'];
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS output from CSS_Loader is safe
			echo CSS_Loader::get_layout_css();
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS output from CSS_Loader is safe
			echo CSS_Loader::get_template_css( $template_id );
			echo '</style>';
		}
		// If item spacing was done via a block (dynamic) then we need some inline CSS to set it.
		// We need to render old blocks using item_spacing with the new grid_gap and margin setttings.
		if ( isset( $this->settings['item_spacing'] ) ) {
			// Convert the old item spacing to grid gap measurements and margin.
			$spacing_gap                   = ( absint( $this->settings['item_spacing'] ) * 2 ) . 'px';
			$margin                        = absint( $this->settings['item_spacing'] ) . 'px';
			$grid_gap                      = array(
				'column' => $spacing_gap,
				'row'    => $spacing_gap,
			);
			$this->settings['grid_gap']    = $grid_gap;
			$this->settings['margin_size'] = array(
				'top'    => $margin,
				'right'  => $margin,
				'bottom' => $margin,
				'left'   => $margin,
			);
		} elseif ( ! isset( $this->settings['grid_gap'] ) ) {
			$this->settings['grid_gap'] = array(
				'column' => '10px',
				'row'    => '10px',
			);
		}

		$grid_gap   = $this->settings['grid_gap'];
		$column_gap = isset( $grid_gap['column'] ) ? $grid_gap['column'] : '0px';
		$row_gap    = isset( $grid_gap['row'] ) ? $grid_gap['row'] : '0px';

		// Add the gap.
		ob_start();
		?>--cl-layout-gap-c: <?php echo esc_attr( $column_gap ); ?>;--cl-layout-gap-r: <?php echo esc_attr( $row_gap ); ?>;
		<?php
		$css = ob_get_clean();
		$this->add_attribute( 'style', $css );

		// Figure out margin + padding.
		$container_css = '';

		if ( isset( $this->settings['padding_size'] ) && $this->settings['padding_size'] !== '' ) {
			$container_css .= '--cl-layout-padding:' . CSS_Loader::parse_unit_quad( $this->settings['padding_size'] ) . ';';
		}
		if ( isset( $this->settings['margin_size'] ) && $this->settings['margin_size'] !== '' ) {
			$container_css .= '--cl-layout-margin:' . CSS_Loader::parse_unit_quad( $this->settings['margin_size'] ) . ';';
		}

		if ( isset( $this->settings['background_color'] ) && $this->settings['background_color'] !== '' ) {
			$container_css .= '--cl-layout-background-color:' . $this->settings['background_color'] . ';';
		}
		if ( isset( $this->settings['background_gradient'] ) && $this->settings['background_gradient'] !== '' ) {
			$container_css .= '--cl-layout-background-gradient:' . $this->settings['background_gradient'] . ';';
		}

		$container_class = $this->settings['container_class'] !== '' ? ' ' . $this->settings['container_class'] : '';
		$container_class = apply_filters( 'custom-layouts/layout/container_class', $container_class, $this->settings );
		echo '<div class="cl-layout-container' . esc_attr( $container_class ) . '" style="' . esc_attr( $container_css ) . '">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped by Util::get_attributes_html()
		echo '<div ' . Util::get_attributes_html( $this->get_attributes() ) . '>';
		if ( 'yes' === $this->settings['use_masonry'] ) {
			echo '<div class="cl-layout__masonry-content">';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped in build() method
		echo $this->build( $this->settings );
		if ( 'yes' === $this->settings['use_masonry'] ) {
			echo '</div>';
		}
		echo '</div>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped in build_pagination() method
		echo $this->build_pagination( $this->settings );
		echo '</div>';

		$output = ob_get_clean();

		// Modify output html.
		$output = apply_filters( 'custom-layouts/layout/render_output', $output, $this->name, $this->settings );

		if ( ! $return_output ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped and filtered
			echo $output;
		}

		// Trigger action when finished.
		do_action( 'custom-layouts/layout/after_render', $this->settings, $this->name );

		if ( $return_output ) {
			return $output;
		}
	}

	/**
	 * Get the input type of the layout.
	 *
	 * @since 1.0.0
	 * @return string The input type.
	 */
	public function get_input_type() {

		if ( ! $this->has_init() ) {
			return '';
		}
		return $this->settings['input_type'];
	}

	/**
	 * Get a specific setting value by name.
	 *
	 * @since 1.0.0
	 * @param string|bool $setting_name The setting name to retrieve.
	 * @return mixed|bool The setting value or false if not found.
	 */
	public function get_setting( $setting_name = false ) {

		if ( ! $this->has_init() ) {
			return false;
		}

		if ( ! $setting_name ) {
			return false;
		}

		if ( isset( $this->settings[ $setting_name ] ) ) {
			return $this->settings[ $setting_name ];
		}

		return false;
	}


	/**
	 * The main function that constructs the main part of the layout.
	 *
	 * @since 1.0.0
	 * @param array $settings The layout settings.
	 * @return string The HTML output of the layout.
	 */
	public function build( $settings ) {
		if ( ! $this->has_init() ) {
			return '';
		}

		ob_start();

		$query_data = $this->get_query();

		if ( 'grid' === $this->settings['display_mode'] ) {

			if ( ! isset( $this->settings['template_id'] ) ) {
				return '';
			}

			$template_id = absint( $this->settings['template_id'] );

			if ( isset( $query_data['ids'] ) ) {
				if ( count( $query_data['ids'] ) > 0 ) {
					foreach ( $query_data['ids'] as $result_id ) {
						$attributes = array(
							'class' => 'cl-layout__item cl-layout__item--id-' . absint( $result_id ),
						);
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped by Util::get_attributes_html()
						echo '<div ' . Util::get_attributes_html( $attributes ) . '>';

						$template_controller = new Template_Controller( $template_id );
						$template_controller->render( $result_id );

						echo '</div>';
					}
				} else {

					echo wp_kses_post( '<div class="cl-layout__no-results">' . do_shortcode( $this->settings['no_results_message'] ) . '</div>' );
				}
			}
		}

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Build the pagination HTML for the layout.
	 *
	 * @since 1.0.0
	 * @param array $settings The layout settings.
	 * @return string The HTML output of the pagination.
	 */
	public function build_pagination( $settings ) {

		if ( ! $this->has_init() ) {
			return '';
		}

		ob_start();
		$query_data = $this->get_query();

		if ( 'numbers' === $settings['pagination_type'] ) {
			$current_page = $query_data['current_page'];
			$big          = 9999999;
			echo '<div class="cl-pagination">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() returns escaped HTML
			echo paginate_links(
				array(
					'base'    => str_replace( (string) $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'  => '?paged=%#%',
					'current' => $current_page,
					'total'   => $query_data['total_pages'],
				)
			);
			echo '</div>';
		}

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Get the transient name for caching query results.
	 *
	 * @since 1.0.0
	 * @param array $query_args The query arguments.
	 * @return string The transient name.
	 */
	protected function get_transient_name( $query_args ) {
		$transient_name = 'layout_query_' . wp_json_encode( $query_args );

		// TODO.
		/*
		if ( 'rand' === $this->query_args['orderby'] ) {
			// When using rand, we'll cache a number of random queries and pull those to avoid querying rand on each page load.
			$rand_index      = wp_rand( 0, max( 1, absint( apply_filters( 'woocommerce_product_query_max_rand_cache_count', 5 ) ) ) );
			$transient_name .= $rand_index;
		}*/

		return $transient_name;
	}

	/**
	 * Run the WP_Query and return query data.
	 *
	 * @since 1.0.0
	 * @param array $extra_args Extra query arguments to merge.
	 * @return array The query data including IDs, pagination info, etc.
	 */
	protected function run_query( $extra_args = array() ) {

		$paged = 1;

		if ( $this->settings['pagination_type'] !== 'none' ) {
			// Get paged.
			if ( is_front_page() ) {
				$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
			} else {
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			}
		}

		// Legacy - we used to have a single `post_type` field, and replaced it with a `post_types` field.
		// This covers users who have upgraded but not resaved their settings / layouts / blocks.
		$post_types = array();
		if ( isset( $this->settings['post_type'] ) ) {
			array_push( $post_types, $this->settings['post_type'] );
		} else {
			$post_types = empty( $this->settings['post_types'] ) ? array( 'post' ) : $this->settings['post_types'];
		}

		$query_args = array(
			'fields'         => 'ids',
			'post_type'      => $post_types,
			// 'post_status'    => $this->settings['post_status'],
			'post_status'    => array( 'publish' ),
			'posts_per_page' => intval( $this->settings['posts_per_page'] ),
			'paged'          => $paged,
		);

		if ( isset( $this->settings['order_by'] ) && isset( $this->settings['order_dir'] ) ) {
			if ( 'default' !== $this->settings['order_by'] ) {
				$query_args['orderby'] = $this->settings['order_by'];
			}
			$query_args['order'] = $this->settings['order_dir'];
		}
		if ( isset( $this->settings['ignore_sticky_posts'] ) ) {
			$query_args['ignore_sticky_posts'] = $this->settings['ignore_sticky_posts'] === 'yes' ? true : false;
		}

		if ( $this->settings['exclude_current_post'] === 'yes' ) {
			if ( is_singular() || is_single() ) {
				$post_id                    = absint( get_queried_object_id() );
				$query_args['post__not_in'] = array( $post_id );
			}
		}

		if ( isset( $this->settings['taxonomy_query'] ) && isset( $this->settings['filter_taxonomies'] ) ) {
			if ( $this->settings['filter_taxonomies'] === 'yes' ) {
				$query_args['tax_query'] = self::parse_tax_query( $this->settings['taxonomy_query'] );
			}
		}
		if ( isset( $this->settings['authors'] ) && isset( $this->settings['filter_authors'] ) ) {

			if ( $this->settings['filter_authors'] === 'yes' ) {

				$query_args['author__in'] = array_map( 'intval', $this->settings['authors'] );
			}
		}

		// Calculate offset taking into consideration pagination.
		if ( isset( $this->settings['offset'] ) ) {
			$offset = absint( $this->settings['offset'] );
			if ( 0 !== $offset ) {
				$query_args['offset'] = ( ( $paged - 1 ) * $this->settings['posts_per_page'] ) + $offset;
			}
		}
		if ( isset( $this->settings['use_search_filter'] ) ) {
			if ( $this->settings['use_search_filter'] === 'yes' ) {
				$query_args['search_filter_id'] = absint( $this->settings['search_filter_id'] );
				if ( isset( $query_args['tax_query'] ) ) {
					unset( $query_args['tax_query'] );
				}
				unset( $query_args['offset'] );
				unset( $query_args['ignore_sticky_posts'] );
				if ( isset( $query_args['author__in'] ) ) {
					unset( $query_args['author__in'] );
				}
				$this->use_cache = false;
			}
		}

		if ( ! empty( $extra_args ) ) {
			$query_args = wp_parse_args( $extra_args, $query_args );
		}

		$this->use_cache = apply_filters( 'custom-layouts/layout/use_cache', $this->use_cache, $this->id, $this->settings, $query_args );
		$query_args      = apply_filters( 'custom-layouts/layout/query_args', $query_args, $this->id, $this->settings );

		// @phpstan-ignore if.alwaysFalse (debug constant can be changed)
		if ( CUSTOM_LAYOUTS_DEBUG ) {
			$this->use_cache = false;
		}

		// Force post_type to be array (just for consistency later).
		/*
		if ( is_scalar( $query_args[ 'post_type' ] ) ) {
			$query_args[ 'post_type' ] = array( $query_args[ 'post_type' ] );
		} */

		if ( $this->use_cache ) {
			// Find out whether a post type we are using, has recently had any modifications.
			$post_types_updated_option_key = 'cl_post_types_updated';
			$post_types_updated            = get_option( $post_types_updated_option_key );
			if ( ! $post_types_updated ) {
				$post_types_updated = array();
			}

			// See if the post types updated are in the current query.
			$reset_transients = count( array_intersect( $post_types_updated, $query_args['post_type'] ) ) >= 1 ? true : false;

			// If we are querying a post type that has had a modification, then clear all query transients.
			$transient_name = $this->get_transient_name( $query_args );

			if ( $reset_transients ) {
				// Clear query transients.
				Cache::purge_all_query_transients();
				update_option( $post_types_updated_option_key, array(), false );

			} else {
				$transient = Cache::get_transient( $transient_name );
				if ( $transient ) {
					return $transient;
				}
			}
		}
		$query = new \WP_Query( $query_args );
		// Need to loop through and return only IDs.
		$query_data = array(
			'ids'            => $query->posts,
			'current_page'   => $query->query_vars['paged'],
			'total_pages'    => $query->max_num_pages,
			'posts_per_page' => intval( $query->get( 'posts_per_page' ) ),
			'total_results'  => intval( $query->found_posts ),
		);

		if ( $this->use_cache ) {
			// We need to store other query info in transient, like #no results, #current_page.
			Cache::set_query_transient( $transient_name, $query_data );
		}

		return $query_data;
	}

	/**
	 * Run the query, if extra args are passed, then don't store the result, just return it.
	 *
	 * @since 1.0.0
	 * @param array $extra_args Extra query arguments to merge.
	 * @return array The query data.
	 */
	public function get_query( $extra_args = array() ) {

		// If its dynamic (extra_args passed) then run and return the query.
		if ( ! empty( $extra_args ) ) {
			return $this->run_query( $extra_args );
		}

		// Otherwise store the result and re-use as we know that will be likely.
		if ( empty( $this->query_data ) ) {
			$this->query_data = $this->run_query();
		}

		return $this->query_data;
	}

	/**
	 * Parse taxonomy query from custom format to WP_Query tax_query format.
	 *
	 * @since 1.0.0
	 * @param array $from_tax_query The taxonomy query in custom format.
	 * @return array The parsed tax_query array for WP_Query.
	 */
	public static function parse_tax_query( $from_tax_query ) {
		$to_tax_query = array();
		if ( is_array( $from_tax_query ) ) {
			$to_tax_query = array( 'relation' => 'AND' );
			foreach ( $from_tax_query as $tax_name => $tax_query ) {
				if ( is_array( $tax_query['include'] ) && ! empty( $tax_query['include'] ) ) {

					$sep_tax_query = array( 'relation' => 'AND' );

					foreach ( $tax_query['include'] as $tax_slug ) {
						$include = array(
							'taxonomy' => $tax_name,
							'field'    => 'slug',
							'terms'    => array( $tax_slug ),
							'operator' => 'IN',
						);
						array_push( $sep_tax_query, $include );
					}

					array_push( $to_tax_query, $sep_tax_query );
				}
			}
		}
		return $to_tax_query;
	}
}
