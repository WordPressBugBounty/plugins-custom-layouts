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
	 * Init
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_filter( 'custom-layouts/admin/layout_info', 'Custom_Layouts\\Integrations\\Search_Filter_Pro::layout_info', 10 );
	}
	public static function layout_info( $layout_info ) {
		$layout_info['hasSearchFilter']     = self::is_enabled();
		$layout_info['searchFilterQueries'] = self::get_search_form_options();
		return $layout_info;
	}
	private static function get_search_form_options() {

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
	public function add_routes() {

	}
	public static function is_enabled() {
		if ( defined( 'SEARCH_FILTER_VERSION' ) ) {
			return true;
		}
		return false;
	}

	public static function get_search_forms() {
		if ( defined( 'SEARCH_FILTER_VERSION' ) ) {
			return true;
		}
		return false;
	}
}
