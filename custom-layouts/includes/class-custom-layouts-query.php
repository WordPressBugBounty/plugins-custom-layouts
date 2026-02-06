<?php
/**
 * Query handler class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

namespace Custom_Layouts;

use Custom_Layouts\Settings;
use Custom_Layouts\Util;
/**
 * Looks for `custom_layouts_query_id` in a WP_Query (pre_get_posts), and takes over the query
 * parses url args + query settings into queries to made on our own tables
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages custom layout queries.
 *
 * Handles WP_Query integration for custom layouts.
 *
 * @since      1.0.0
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */
class Query {

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'pre_get_posts', 'Custom_Layouts\\Query::setup_queries', 100000, 1 ); // Try to be the last thing to attach to the hook.
	}

	/**
	 * Setup queries based on custom_layouts_query_id.
	 *
	 * @param \WP_Query $query WordPress query object.
	 */
	public static function setup_queries( $query ) {

		if ( $query->get( 'custom_layouts_query_id' ) ) {

			// Util::get_query.
			// Need to use a shared function.
			$query_id = intval( $query->get( 'custom_layouts_query_id' ) );

			$query_data       = Settings::get_section_data( $query_id, 'query' );
			$integration_data = Settings::get_section_data( $query_id, 'integration' );

			$query->set( 'post_type', $query_data['post_types'] );
			$query->set( 'posts_per_page', $query_data['posts_per_page'] );
			$query->set( 'post_status', $query_data['post_status'] );
		}
	}
}
