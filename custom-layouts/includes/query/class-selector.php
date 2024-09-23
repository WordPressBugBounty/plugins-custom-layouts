<?php
namespace Custom_Layouts\Query;

use Custom_Layouts\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figures out, based on saved S&F Queries, which WP Queries to affect, by assigning `sf_query_id` to the
 * appropriate WP Queries
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

class Selector {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private static $queries;
	private static $wp_search_query_id;

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

	/**
	 * Register the stylesheets for the public-facing side of the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		self::$queries = Util::get_queries();
		self::attach_queries( self::$queries );

	}

	/**
	 * Mark WP queries for modification by attaching `sf_query_id`.
	 *
	 * @since    1.0.0
	 * @param      array $queries        An array of all S&F Query Settings
	 */
	public static function attach_queries( $queries ) {

		/*
		foreach ($queries as $sf_query) {

			//now store their query settings somewhere to use
			$integration_type = $sf_query['query_integration']['integration_type'];
			$id = $sf_query['id'];

			if( 'wp_search' === $integration_type ){

				//if( self::is_wordpress_search_page() ){
				self::setup_wp_search($id);
				//}
			}
			else if( 'search_results' === $integration_type ){

				//if( self::is_wordpress_search_page() ){
				self::setup_wp_search($id);
				//}
			}

		}*/
	}

	/**
	 * Register the stylesheets for the public-facing side of the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function is_wordpress_search_page() {

		/*
		if( is_search() ){
			return true;
		}*/

		return true;
	}

	/**
	 * Setup logic for the WP Search Results integration type
	 *
	 * @since    1.0.0
	 * @param      integer $id        The ID of the S&F Query
	 */
	public static function setup_wp_search( $id ) {

		self::$wp_search_query_id = $id;

		add_action( 'pre_get_posts', 'Custom_Layouts\\Query\\Selector::attach_wp_search', 1000 );
	}

	/**
	 * Detect whether the query is the one used on WP Search Results page (yoursite.com/?s=) and
	 * attach `sf_query_id`
	 *
	 * @since    1.0.0
	 * @param      object $query        The WP Query object
	 */
	public static function attach_wp_search( $query ) {

		if ( is_admin() ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( empty( self::$wp_search_query_id ) ) {
			return;
		}

		if ( ! is_search() ) {
			return;
		}

		if ( is_archive() ) {
			return;
		}

		if ( ( $query->is_search() ) && ( isset( $query->query['s'] ) ) ) {
			$query->set( 'custom_layouts_query_id', self::$wp_search_query_id );
			self::$wp_search_query_id = '';
		}

		remove_action( 'pre_get_posts', 'Custom_Layouts\\Query\\Selector::attach_wp_search', 1000 );
	}
}


