<?php
/**
 * WooCommerce Integration Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts\Integrations;

/**
 * All Search & Filter Pro integration functionality
 */
class Search_Filter_Pro {


	/**
	 * Query Options
	 *
	 * @since 1.2.5
	 *
	 * @var int The query ID to connect to.
	 */
	private static $connect_search_filter_query_id = 0;

	/**
	 * Query Objects
	 *
	 * @var array The list of query objects.
	 */
	private static $query_objects = array();

	/**
	 * Init.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public static function init() {
		add_filter( 'custom-layouts/admin/layout_info', 'Custom_Layouts\\Integrations\\Search_Filter_Pro::layout_info', 10 );

		/**
		 * V3 Integration Options.
		 */
		add_filter( 'custom-layouts/layout/container_class', array( __CLASS__, 'add_container_class' ), 10, 2 );

		add_filter( 'custom-layouts/layout/query_args', array( __CLASS__, 'add_search_filter_query_args' ), 10, 3 );

		add_filter( 'custom-layouts/layout/use_cache', array( __CLASS__, 'layout_use_cache' ), 10, 3 );

		// Add BB options to S&F admin UI.
		add_action( 'search-filter/settings/init', array( __CLASS__, 'add_integration_option' ), 10 );

		// Hide CSS selector options from query editor.
		add_action( 'search-filter/settings/init', array( __CLASS__, 'hide_css_selector_options' ), 10 );

		// Add hook to update the query attributes.
		add_action( 'search-filter/frontend/data/start', array( __CLASS__, 'attach_update_query_attributes' ), 10 );

		// Add support for adding a `search_filter_query_id` attribute to the shortcode directly.
		add_filter( 'shortcode_atts_custom-layout', array( __CLASS__, 'shortcode_attributes' ), 20, 3 );
	}

	/**
	 * Add Search & Filter integration info to layout data.
	 *
	 * @param array $layout_info The layout info array.
	 * @return array The enhanced layout info.
	 */
	public static function layout_info( $layout_info ) {
		$has_search_filter = false;
		$query_options     = array();

		if ( self::is_search_filter_version( 2 ) ) {
			$has_search_filter = true;
			$query_options     = self::get_v2_queries_options();
		} elseif ( self::is_search_filter_version( 3 ) ) {
			$has_search_filter = true;
			$query_options     = self::get_v3_queries_options();
		}

		$layout_info['hasSearchFilter']     = $has_search_filter;
		$layout_info['searchFilterQueries'] = $query_options;
		return $layout_info;
	}

	/**
	 * Get Search & Filter v3 query options.
	 *
	 * @param string $integration_type The integration type.
	 * @return array Array of query options.
	 */
	private static function get_v3_queries_options( $integration_type = '' ) {

		$query_options = array();
		$queries       = \Search_Filter\Queries::find(
			array(
				'status' => 'enabled', // Only get enabled queries.
				'number' => 0, // Get all.
			),
			'records'
		);

		foreach ( $queries as $query_record ) {
			$query_option        = new \StdClass();
			$query_option->value = $query_record->get_id();
			$query_option->label = html_entity_decode( esc_html( $query_record->get_name() ) );
			array_push( $query_options, $query_option );
		}

		return $query_options;
	}

	/**
	 * Get Search & Filter v2 query options.
	 *
	 * @return array Array of query options.
	 */
	private static function get_v2_queries_options() {

		$search_form_options = array();
		$posts_query         = 'post_type=search-filter-widget&post_status=publish&posts_per_page=-1';

		$custom_posts = new \WP_Query( $posts_query );
		if ( $custom_posts->post_count > 0 ) {
			foreach ( $custom_posts->posts as $post ) {
				$search_form_option        = new \StdClass();
				$search_form_option->value = $post->ID;
				$search_form_option->label = html_entity_decode( esc_html( $post->post_title ) );
				array_push( $search_form_options, $search_form_option );
			}
		}
		return $search_form_options;
	}

	/**
	 * Detects whether to load the version 2 or 3 integration.
	 *
	 * @since 1.0.0
	 * @param int $version The version to check.
	 * @return bool Whether the version is matched or not.
	 */
	public static function is_search_filter_version( $version ) {

		// Test for version 2.
		if ( $version === 2 ) {
			if ( ! defined( 'SEARCH_FILTER_VERSION' ) ) {
				return false;
			}
			if ( version_compare( SEARCH_FILTER_VERSION, '2.0.0', '>=' ) && version_compare( SEARCH_FILTER_VERSION, '3.0.0-beta-1', '<' ) ) {
				return true;
			}
		} elseif ( $version === 3 ) {
			if ( ! defined( 'SEARCH_FILTER_VERSION' ) ) {
				return false;
			}
			if ( version_compare( SEARCH_FILTER_VERSION, '3.0.0-beta-2', '>=' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Add the integration option to S&F UI.
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public static function add_integration_option() {
		// Get the setting.
		$integration_type_setting = \Search_Filter\Queries\Settings::get_setting( 'queryIntegration' );
		if ( ! $integration_type_setting ) {
			return;
		}

		$new_integration_type_option = array(
			'label' => __( 'Custom Layouts', 'custom-layouts' ),
			'value' => 'custom-layouts/layout',
		);
		$integration_type_setting->add_option( $new_integration_type_option );
	}

	/**
	 * Hide the CSS selector options from the query editor dynamic options tab.
	 *
	 * @since 1.2.3
	 * @return void
	 */
	public static function hide_css_selector_options() {
		$depends_conditions = array(
			'relation' => 'AND',
			'action'   => 'hide',
			'rules'    => array(
				array(
					'option'  => 'queryIntegration',
					'compare' => '!=',
					'value'   => 'custom-layouts/layout',
				),
			),
		);

		$query_container = \Search_Filter\Queries\Settings::get_setting( 'queryContainer' );
		if ( $query_container ) {
			$query_container->add_depends_condition( $depends_conditions );
		}

		$pagination_selector = \Search_Filter\Queries\Settings::get_setting( 'queryPaginationSelector' );
		if ( $pagination_selector ) {
			$pagination_selector->add_depends_condition( $depends_conditions );
		}

		$query_posts_container = \Search_Filter\Queries\Settings::get_setting( 'queryPostsContainer' );
		if ( $query_posts_container ) {
			$query_posts_container->add_depends_condition( $depends_conditions );
		}
	}

	/**
	 * Attach query attribute update hooks.
	 *
	 * @since 1.2.3
	 * @return void
	 */
	public static function attach_update_query_attributes() {
		// Auto set the CSS selector options.
		add_filter( 'search-filter/queries/query/get_attributes', array( __CLASS__, 'update_query_attributes' ), 10, 2 );
	}
	/**
	 * Automatically set the CSS selector options.
	 *
	 * @since 1.3.0
	 * @param array $attributes The attributes.
	 * @param mixed $query The query ID or object.
	 * @return array The attributes.
	 */
	public static function update_query_attributes( $attributes, $query ) {
		$id = 0;
		// Legacy.
		if ( is_scalar( $query ) ) {
			$id = $query;
		} else {
			$id = $query->get_id();
		}

		if ( ! isset( $attributes['queryIntegration'] ) ) {
			return $attributes;
		}
		// Set `queryContainer` and `paginationSelector` automatically.
		$query_integration = $attributes['queryIntegration'];

		if ( $query_integration !== 'custom-layouts/layout' ) {
			return $attributes;
		}

		// Get the layout ID for more specific class targetting.
		$layout_has_masonry = $query->get_render_config_value( 'layoutHasMasonry' );

		$base_query_container_selector     = '.search-filter-query--id-' . absint( $id );
		$attributes['queryContainer']      = $base_query_container_selector;
		$attributes['queryPostsContainer'] = $base_query_container_selector . ' .cl-layout';

		if ( $layout_has_masonry ) {
			// By setting the queryContainer to the same as the masonry content container, we prevent the DOM
			// object from being destroyed meaning we can re-using the same masonry object to re-init the layout
			// in JS.
			$attributes['queryContainer']      = $base_query_container_selector . ' .cl-layout .cl-layout__masonry-content';
			$attributes['queryPostsContainer'] = $base_query_container_selector . ' .cl-layout .cl-layout__masonry-content';
		}
		$attributes['queryPaginationSelector'] = $base_query_container_selector . ' .cl-pagination a';

		$attributes['customLayoutsHasMasonry'] = $layout_has_masonry;
		return $attributes;
	}
	/**
	 * Add the search & filter classes to the container.
	 *
	 * @since 1.3.0
	 * @param string $container_class The container class.
	 * @param array  $settings The layout settings.
	 * @return string The modified container class.
	 */
	public static function add_container_class( $container_class, $settings ) {
		if ( ! isset( $settings['use_search_filter'] ) ) {
			return $container_class;
		}
		if ( ! isset( $settings['search_filter_id'] ) ) {
			return $container_class;
		}

		if ( $settings['use_search_filter'] !== 'yes' ) {
			return $container_class;
		}

		if ( self::is_search_filter_version( 2 ) ) {
			$container_class .= ' search-filter-results-' . absint( $settings['search_filter_id'] );
		} elseif ( self::is_search_filter_version( 3 ) ) {
			$container_class .= ' search-filter-query--id-' . absint( $settings['search_filter_id'] );
		}

		return $container_class;
	}

	/**
	 * Try to get the Search & Filter ID from settings or shortcode.
	 *
	 * @since 1.3.0
	 * @param array $settings The layout settings.
	 * @return int The Search & Filter ID or 0 if not found.
	 */
	private static function try_get_search_filter_id( $settings ) {
		// There are two ways we might want to connect S&F to our query, via a layout settings or the shortcode.

		// First try the shortcode attribute.
		if ( ! empty( self::$connect_search_filter_query_id ) ) {
			return self::$connect_search_filter_query_id;
		}

		// If that's not found proceed to check the layout settings.
		if ( ! isset( $settings['use_search_filter'] ) ) {
			return 0;
		}
		if ( $settings['use_search_filter'] !== 'yes' ) {
			return 0;
		}

		if ( ! isset( $settings['search_filter_id'] ) ) {
			return 0;
		}
		return absint( $settings['search_filter_id'] );
	}

	/**
	 * Add the search & filter query args to the query.
	 *
	 * @since 1.3.0
	 * @param array $query_args The query arguments.
	 * @param int   $layout_id The layout ID.
	 * @param array $settings The layout settings.
	 * @return array The modified query arguments.
	 */
	public static function add_search_filter_query_args( $query_args, $layout_id, $settings ) {

		// Try to get a search filter ID to attach to the query.
		$search_filter_id = self::try_get_search_filter_id( $settings );
		if ( empty( $search_filter_id ) ) {
			return $query_args;
		}

		if ( self::is_search_filter_version( 2 ) ) {
			$query_args['search_filter_id'] = $search_filter_id;
		} elseif ( self::is_search_filter_version( 3 ) ) {
			$query_args['search_filter_query_id'] = $search_filter_id;

			// Capture additional layout data for v3 render settings.
			$query = self::get_query_object_by_id( $search_filter_id );
			if ( $query ) {
				// Set the layout ID for more specific class targetting.
				$query->set_render_config_value( 'layoutId', $layout_id );

				// Store whether this layout has masonry for setting the posts container.
				$layout_has_masonry = false;
				if ( isset( $settings['use_masonry'] ) && $settings['use_masonry'] === 'yes' ) {
					$layout_has_masonry = true;
				}
				$query->set_render_config_value( 'layoutHasMasonry', $layout_has_masonry );

				// Support "load more" `paged` parameter if its in the URL.
				if ( isset( $_GET['paged'] ) ) {
					$query_args['paged'] = absint( $_GET['paged'] );
				}
			}
		} else {
			return $query_args;
		}

		if ( isset( $query_args['tax_query'] ) ) {
			unset( $query_args['tax_query'] );
		}
		unset( $query_args['offset'] );
		unset( $query_args['ignore_sticky_posts'] );
		if ( isset( $query_args['author__in'] ) ) {
			unset( $query_args['author__in'] );
		}

		// Always reset the connected search filter query ID.
		self::$connect_search_filter_query_id = 0;
		return $query_args;
	}


	/**
	 * Get a query object by its ID, store a reference to it so we
	 * we can re-use it when needed.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @param int $query_id The query ID.
	 * @return \Search_Filter\Queries\Query|null The query object or null if not found.
	 */
	private static function get_query_object_by_id( $query_id ) {
		if ( isset( self::$query_objects[ $query_id ] ) ) {
			return self::$query_objects[ $query_id ];
		}
		$query = \Search_Filter\Queries\Query::find( array( 'id' => $query_id ) );
		if ( ! is_wp_error( $query ) ) {
			self::$query_objects[ $query_id ] = $query;
			return $query;
		}
		return null;
	}
	/**
	 * Disable query caching for S&F queries.
	 *
	 * @since 1.3.0
	 * @param bool  $use_cache Whether to use cache.
	 * @param int   $layout_id The layout ID.
	 * @param array $settings The layout settings.
	 * @return bool Whether to use cache.
	 */
	public static function layout_use_cache( $use_cache, $layout_id, $settings ) {

		$search_filter_id = self::try_get_search_filter_id( $settings );
		if ( empty( $search_filter_id ) ) {
			return $use_cache;
		}

		if ( self::is_search_filter_version( 2 ) || self::is_search_filter_version( 3 ) ) {
			return false;
		}

		return $use_cache;
	}


	/**
	 * Support for adding a `search_filter_id` (v2) or `search_filter_query_id` (v3) attribute
	 * to the shortcode directly.
	 *
	 * @since 1.3.0
	 * @param array $out The output array.
	 * @param array $pairs The default pairs.
	 * @param array $atts The shortcode attributes.
	 * @return array The modified output array.
	 */
	public static function shortcode_attributes( $out, $pairs, $atts ) {

		if ( self::is_search_filter_version( 2 ) ) {
			if ( ! isset( $atts['search_filter_id'] ) ) {
				return $out;
			}
			self::$connect_search_filter_query_id = absint( $atts['search_filter_id'] );
		} elseif ( self::is_search_filter_version( 3 ) ) {
			if ( ! isset( $atts['search_filter_query_id'] ) ) {
				return $out;
			}
			self::$connect_search_filter_query_id = absint( $atts['search_filter_query_id'] );
		} else {
			return $out;
		}

		$out['cache'] = 'no'; // Disable query caching.
		return $out;
	}
}
