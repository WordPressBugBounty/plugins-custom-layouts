<?php
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

class Query {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	/*
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}*/

	public static function init() {
		add_action( 'pre_get_posts', 'Custom_Layouts\\Query::setup_queries', 100000, 1 ); // try to be the last thing to attach to the hook
		// self::$queries = Util::get_queries();
		// self::attach_queries(self::$queries);

	}

	public static function setup_queries( $query ) {

		if ( $query->get( 'custom_layouts_query_id' ) ) {

			// Util::get_query
			// need to use a shared function
			$query_id = intval( $query->get( 'custom_layouts_query_id' ) );

			$query_data       = Settings::get_section_data( $query_id, 'query' );
			$integration_data = Settings::get_section_data( $query_id, 'integration' );

			$query->set( 'post_type', $query_data['post_types'] );
			$query->set( 'posts_per_page', $query_data['posts_per_page'] );
			$query->set( 'post_status', $query_data['post_status'] );
		}
	}
	public static function setup_query( $query ) {

		if ( $query->get( 'custom_layouts_query_id' ) ) {

			// Util::get_query
			// need to use a shared function
			$query_integration_values = get_post_meta( $query->get( 'custom_layouts_query_id' ), 'custom-layouts-layout', true );
			$query_settings           = get_post_meta( $query->get( 'custom_layouts_query_id' ), 'custom-layouts-query', true );
			// $this->setup_query($query);
		}

	}

}
