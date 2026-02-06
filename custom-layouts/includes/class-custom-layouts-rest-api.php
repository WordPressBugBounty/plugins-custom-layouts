<?php
/**
 * REST API functionality class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

namespace Custom_Layouts;

use Custom_Layouts\Core\Cache;
use Custom_Layouts\Settings;
use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Integrations\Gutenberg;
use Custom_Layouts\Layout\Controller as Layout_Controller;
use Custom_Layouts\Template\Controller as Template_Controller;
use stdClass;

/**
 * The file that defines the core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @link       http://codeamp.com
 * @since      3.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API controller for Custom Layouts.
 *
 * @since 1.0.0
 */
class Rest_API {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_routes' ) );
	}

	/**
	 * Register all REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function add_routes() {
		register_rest_route(
			'custom-layouts/v1',
			'/layout/results',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_layout_template_results' ),
				'args'                => array(
					'layout_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/templates/get',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_templates_options' ),
				'args'                => array(
					'language' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/layouts/get',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_layouts_options' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/wp/post_types',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_post_types' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/authors',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_authors' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/taxonomy/terms',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_taxonomy_terms' ),
				'args'                => array(
					'taxonomy_name' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/posts/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'search_posts' ),
				'args'                => array(
					'search_term' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/custom-fields',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_custom_fields' ),
				'args'                => array(
					'search_term' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/taxonomies',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_taxonomies' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/image_sizes',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_image_sizes' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/taxonomies/terms',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_taxonomies_term_data' ),
				'args'                => array(
					'post_id'    => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'taxonomies' => array(
						'type'              => 'array',
						'required'          => true,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'order_by'   => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
					'order_dir'  => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
					'max_number' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),

				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/custom-field',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_custom_field_data' ),
				'args'                => array(
					'post_id'          => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'custom_field_key' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),

				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/template/sources',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_template_sources' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/template/get',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_template' ),
				'args'                => array(
					'id' => array(
						// We need to support "default".
						'type'              => 'number',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/layout/get',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_layout' ),
				'args'                => array(
					'id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/template/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_template' ),
				'args'                => array(
					'id'             => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'instances'      => array(
						'type'              => 'array',
						'required'          => true,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'instance_order' => array(
						'type'              => 'array',
						'required'          => true,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'template'       => array(
						'type'              => 'array',
						'required'          => true,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'template_title' => array(
						'type'              => 'text',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/layout/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_layout' ),
				'args'                => array(
					'id'           => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'attributes'   => array(
						'type'              => 'array',
						'required'          => true,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'layout_title' => array(
						'type'              => 'text',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/wp/posts/results',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_post_type_results' ),
				'args'                => array(
					'post_type'           => array(
						'type'              => 'array',
						'required'          => true,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'posts_per_page'      => array(
						'type'              => 'number',
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'offset'              => array(
						'type'              => 'number',
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'order'               => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					),
					'order_dir'           => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					),
					'ignore_sticky_posts' => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					),
					'search_filter_id'    => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'taxonomy_query'      => array(
						'type'              => 'array',
						'required'          => false,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
					'author__in'          => array(
						'type'              => 'array',
						'required'          => false,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/wp/post',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_post_result' ),
				'args'                => array(
					'post_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/layout/info',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_layout_info' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/template/info',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_template_info' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
		register_rest_route(
			'custom-layouts/v1',
			'/css/regenerate',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'regenerate_css' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			'custom-layouts/v1',
			'/layout/info',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'set_layout_info' ),
				'permission_callback' => array( $this, 'permissions' ),
				'args'                => array(
					'breakpoints' => array(
						'type'              => 'array',
						'required'          => false,
						'sanitize_callback' => 'Custom_Layouts\\Util::deep_clean',
					),
				),
			)
		);
	}

	/**
	 * Get template posts from query data.
	 *
	 * @since 1.0.0
	 * @param array $query_data The query data containing post IDs.
	 * @return array Array of post data for templates.
	 */
	private function get_template_posts( $query_data ) {
		$posts               = array();
		$template_controller = new Template_Controller( 0 );

		foreach ( $query_data['ids'] as $post_id ) {
			$post = get_post( $post_id );
			array_push( $posts, $this->get_template_post( $post, $template_controller ) );
		}

		return $posts;
	}

	/**
	 * Get template posts from WP_Query object.
	 *
	 * @since 1.0.0
	 * @param \WP_Query $query The WP_Query object.
	 * @return array Array of post data for templates.
	 */
	private function get_template_posts_from_query( $query ) {
		$posts               = array();
		$template_controller = new Template_Controller( 0 );

		foreach ( $query->posts as $post ) {
			array_push( $posts, $this->get_template_post( $post, $template_controller ) );
		}

		return $posts;
	}

	/**
	 * Get template post data for a single post.
	 *
	 * @since 1.0.0
	 * @param \WP_Post            $post                The post object.
	 * @param Template_Controller $template_controller The template controller instance.
	 * @return array The post data array.
	 */
	private function get_template_post( $post, $template_controller ) {

		// TODO - restrict what's exposed.
		$post_id = $post->ID;

		$template_controller->set_post( $post );

		$title_element          = $template_controller->element( 'title' );
		$excerpt_element        = $template_controller->element( 'excerpt' );
		$author_element         = $template_controller->element( 'author' );
		$content_element        = $template_controller->element( 'content' );
		$comment_count_element  = $template_controller->element( 'comment_count' );
		$featured_media_element = $template_controller->element( 'featured_media' );
		$post_type_element      = $template_controller->element( 'post_type' );

		$post_data = array(
			'id'             => $post_id,
			'title'          => $title_element ? $title_element->get_data( $post ) : '',
			'excerpt'        => $excerpt_element ? $excerpt_element->get_data( $post ) : '',
			'author'         => $author_element ? $author_element->get_data( $post ) : '',
			'content'        => $content_element ? $content_element->get_data( $post ) : '',
			'published_date' => $post->post_date,
			'modified_date'  => $post->post_modified,
			'taxonomy'       => $post_id,
			'link'           => $post_id,
			'comment_count'  => $comment_count_element ? $comment_count_element->get_data( $post ) : '',
			'featured_media' => $featured_media_element ? $featured_media_element->get_data( $post ) : '',
			'post_type'      => $post_type_element ? $post_type_element->get_data( $post ) : '',
		);
		return $post_data;
	}

	/**
	 * Get the posts generated from a layout query.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_layout_template_results( \WP_REST_Request $request ) {
		$query_params      = $request->get_params();
		$layout_id         = $query_params['layout_id'];
		$layout_controller = new Layout_Controller( $layout_id );

		$query = $layout_controller->get_query( array( 'posts_per_page' => 10 /* , 'fields' => 'ids' */ ) );
		if ( $query['total_results'] === 0 ) {
			return rest_ensure_response( array() );
		}

		$posts = $this->get_template_posts( $query );

		return rest_ensure_response( $posts );
	}

	/**
	 * Get layout query results.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_layout_query_results( \WP_REST_Request $request ) {
		$query_params      = $request->get_params();
		$layout_id         = $query_params['layout_id'];
		$layout_controller = new Layout_Controller( $layout_id );

		$query = $layout_controller->get_query( array( 'posts_per_page' => 10 /* , 'fields' => 'ids' */ ) );
		if ( $query['total_results'] === 0 ) {
			return rest_ensure_response( array() );
		}

		$posts = $this->get_template_posts( $query );

		return rest_ensure_response( $posts );
	}

	/**
	 * Get post type results based on query parameters.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_post_type_results( \WP_REST_Request $request ) {

		// TODO - check to ensure the post types are public / rest enabled only.
		$query_params = $request->get_params();
		$tax_query    = array();

		// For now we restructure the received taxonomy_query format.
		// Used in WP_Query.
		if ( isset( $query_params['taxonomy_query'] ) ) {
			$tax_query = Layout_Controller::parse_tax_query( $query_params['taxonomy_query'] );
		}

		$defaults = array(
			'post_type'           => array( 'post' ),
			'posts_per_page'      => '9',
			'orderby'             => 'date',
			'order'               => 'desc',
			'ignore_sticky_posts' => false,
			'search_filter_id'    => 0,
			'tax_query'           => array(),
		);
		// We want to change tax_query from our JS structure, to native WP_Query structure.
		$query_args              = wp_parse_args( $query_params, $defaults );
		$query_args['tax_query'] = $tax_query; // Copy over the new tax_query.
		if ( isset( $query_args['_locale'] ) ) {
			unset( $query_args['_locale'] );
		}

		// TODO - need to standardise our queries across everything - use camelCase like in S&F.

		// Resume normal stuff.
		if ( isset( $query_params['ignore_sticky_posts'] ) ) {
			$query_args['ignore_sticky_posts'] = $query_params['ignore_sticky_posts'] === 'yes' ? true : false;
		}

		// TODO.
		// if ( isset( $query_params[ 'author__in' ] ) ) {
		// $query_args[ 'author__in' ] = array_map( 'intval', $query_params[ 'author__in' ] );
		// }

		$query = new \WP_Query( $query_args );

		if ( $query->post_count === 0 ) {
			return rest_ensure_response( array() );
		}

		$posts = $this->get_template_posts_from_query( $query );
		return rest_ensure_response( $posts );
	}

	/**
	 * Get a single post result.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_post_result( \WP_REST_Request $request ) {

		// TODO - check to ensure the post types are public / rest enabled only.
		$query_params = $request->get_params();
		$tax_query    = array();

		// For now we restructure the received taxonomy_query taxonomy_query format.
		// Used in WP_Query.
		$post_id = $query_params['post_id'];
		$post    = get_post( $post_id );

		$template_controller = new Template_Controller( 0 );

		$post_with_data = $this->get_template_post( $post, $template_controller );
		return rest_ensure_response( $post_with_data );
	}

	/**
	 * Fuzzy text search for any public post type.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function search_posts( \WP_REST_Request $request ) {

		// TODO - check to ensure the post types are public / rest enabled only.
		$query_params = $request->get_params();
		$args         = array(
			'public' => true,
		);

		$post_types   = array();
		$n_post_types = get_post_types( $args, 'objects' );
		foreach ( $n_post_types as $post_type ) {
			if ( ( $post_type->public ) && ( $post_type->name !== 'attachment' ) ) {
				array_push( $post_types, $post_type->name );
			}
		}

		$query_args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => '20',
			'orderby'        => 'post_title',
			'order'          => 'asc',
		);
		if ( isset( $query_params['search_term'] ) ) {
			$query_args['s'] = $query_params['search_term'];
		}

		$result = array();
		$query  = new \WP_Query( $query_args );

		if ( $query->post_count === 0 ) {
			return rest_ensure_response( array() );
		}

		foreach ( $query->posts as $post ) {
			// TODO - restrict what's exposed.
			$post_id = $post->ID;
			array_push(
				$result,
				array(
					'value' => $post_id,
					'label' => get_the_title( $post_id ),
				)
			);
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Get all unique custom field keys.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_custom_fields( \WP_REST_Request $request ) {
		$search_term  = '';
		$query_params = $request->get_params();

		if ( isset( $query_params['search_term'] ) ) {
			$search_term = stripslashes_deep( $query_params['search_term'] );
		}
		$result = $this->get_all_post_meta_keys( $search_term );

		return rest_ensure_response( $result );
	}

	/**
	 * Get all taxonomies.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_taxonomies( \WP_REST_Request $request ) {
		$result = Settings::get_taxonomies();
		return rest_ensure_response( $result );
	}

	/**
	 * Get all image sizes.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_image_sizes( \WP_REST_Request $request ) {
		$result = Settings::get_all_image_sizes();
		return rest_ensure_response( $result );
	}

	/**
	 * Get the custom field value for a particular custom field belonging to a post.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_custom_field_data( \WP_REST_Request $request ) {

		$query_params            = $request->get_params();
		$post_id                 = $query_params['post_id'];
		$custom_field_key        = $query_params['custom_field_key'];
		$custom_field_meta_value = get_post_meta( $post_id, $custom_field_key, true );

		$custom_field_value = '';
		if ( $custom_field_meta_value && is_scalar( $custom_field_meta_value ) ) {
			$custom_field_value = $custom_field_meta_value;
		}

		// In case we are dealing with HTML in a custom field, make sure it's safe.
		$custom_field_value = wp_kses_post( $custom_field_value );

		return rest_ensure_response( $custom_field_value );
	}

	/**
	 * Get the terms belonging to multiple taxonomies.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_taxonomies_term_data( \WP_REST_Request $request ) {

		$query_params = $request->get_params();
		$tax_query    = array();

		// For now we restructure the received taxonomy_query taxonomy_query format.
		// Used in WP_Query.
		$post_id    = $query_params['post_id'];
		$taxonomies = $query_params['taxonomies'];
		$order_by   = $query_params['order_by'];
		$order_dir  = $query_params['order_dir'];
		$max_number = $query_params['max_number'];

		$template_controller = new Template_Controller( 0 );
		$post                = get_post( $post_id );
		$template_controller->set_post( $post );
		$element = $template_controller->element( 'taxonomy' );
		if ( ! $element ) {
			return rest_ensure_response( array() );
		}
		$taxonomy_data = $element->get_data( $post, $taxonomies, $order_by, $order_dir, $max_number );

		return rest_ensure_response( $taxonomy_data );
	}

	/**
	 * Get all unique post meta keys from the database.
	 *
	 * @since 1.0.0
	 * @param string $search_term Optional search term to filter keys.
	 * @return array Array of meta keys.
	 */
	private function get_all_post_meta_keys( $search_term = '' ) {
		$ignore_list = array(
			'_wp_page_template',
			'_edit_lock',
			'_edit_last',
			'_menu_item_type',
			'_menu_item_menu_item_parent',
			'_menu_item_object_id',
			'_menu_item_object',
			'_menu_item_target',
			'_menu_item_classes',
			'_menu_item_xfn',
			'_menu_item_url',
			'search-filter-settings',
			'search-filter-query',
			'search-filter-integration',
			'search-filter-filters',
			'custom-layouts-layout',
			'custom-layouts-layout-attributes',
			'custom-layouts-query',
			'custom-layouts-template-css',
			'custom-layouts-template-data',
			'custom-layouts-template-instance-order',
			'custom-layouts-template-instances',
		);

		global $wpdb;
		$data = array();

		$case_sensitive = true;
		if ( defined( 'CUSTOM_LAYOUTS_CASE_SENSITIVE_CUSTOM_FIELDS' ) ) {
			if ( CUSTOM_LAYOUTS_CASE_SENSITIVE_CUSTOM_FIELDS === false ) {
				$case_sensitive = false;
			}
		}

		if ( $search_term !== '' ) {
			// Build query with WHERE clause using prepare().
			if ( $case_sensitive ) {
				$wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT(BINARY `meta_key`) as meta_key_binary, `meta_key`
						FROM $wpdb->postmeta
						WHERE meta_key LIKE %s
						ORDER BY `meta_key` ASC
						LIMIT 0, 15",
						'%' . $wpdb->esc_like( $search_term ) . '%'
					)
				);
			} else {
				$wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT(`meta_key`)
						FROM $wpdb->postmeta
						WHERE meta_key LIKE %s
						ORDER BY `meta_key` ASC
						LIMIT 0, 15",
						'%' . $wpdb->esc_like( $search_term ) . '%'
					)
				);
			}
		} else {
			// Build query without WHERE clause - no need for prepare().
			if ( $case_sensitive ) {
				$wpdb->get_results(
					"SELECT DISTINCT(BINARY `meta_key`) as meta_key_binary, `meta_key`
					FROM $wpdb->postmeta
					ORDER BY `meta_key` ASC
					LIMIT 0, 15"
				);
			} else {
				$wpdb->get_results(
					"SELECT DISTINCT(`meta_key`)
					FROM $wpdb->postmeta
					ORDER BY `meta_key` ASC
					LIMIT 0, 15"
				);
			}
		}

		foreach ( $wpdb->last_result as $k => $v ) {
			// $data[$v->meta_key] =   $v->meta_value;.
			$data[] = $v->meta_key;
		}

		return $data;
	}

	/**
	 * Get all the layout info - something similar to editorSettings.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_layout_info( \WP_REST_Request $request ) {

		$layout_info = array(
			'breakpoints'         => Settings::get_option( 'breakpoints' ),
			'cssMode'             => CSS_Loader::get_mode(),
			'postTypesTaxonomies' => Settings::get_post_types_taxonomies(),
			'taxonomies'          => Settings::get_taxonomies(),
		);
		$layout_info = apply_filters( 'custom-layouts/admin/layout_info', $layout_info );
		return rest_ensure_response( $layout_info );
	}

	/**
	 * Get all the template info - something similar to editorSettings.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_template_info( \WP_REST_Request $request ) {

		$template_info = array();
		$template_info = apply_filters( 'custom-layouts/admin/template_info', $template_info );
		return rest_ensure_response( $template_info );
	}

	/**
	 * Set layout info (e.g., breakpoints).
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function set_layout_info( \WP_REST_Request $request ) {
		$query_params        = $request->get_params();
		$allowed_breakpoints = array( 'medium', 'small', 'xsmall' );
		if ( isset( $query_params['breakpoints'] ) ) {
			$new_break_points = array();
			foreach ( $query_params['breakpoints'] as $device_type => $breakpoint ) {
				if ( in_array( $device_type, $allowed_breakpoints, true ) ) {
					$new_break_points[ $device_type ] = absint( $breakpoint );
				}
			}
			update_option( 'custom_layouts_breakpoints', $new_break_points, false );

			CSS_Loader::save_css();
		}

		// Refresh the options.
		return $this->get_layout_info( $request );
	}

	/**
	 * Get all the templates stored.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_templates_options( \WP_REST_Request $request ) {

		$language     = '';
		$query_params = $request->get_params();
		if ( isset( $query_params['language'] ) ) {
			$language = $query_params['language'];
		}

		$templates = Settings::get_templates_options( $language );
		return rest_ensure_response( $templates );
	}

	/**
	 * Get all the layouts stored.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_layouts_options( \WP_REST_Request $request ) {

		$layouts = Settings::get_layouts_options();

		return rest_ensure_response( $layouts );
	}

	/**
	 * Get all public post types.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_post_types( \WP_REST_Request $request ) {

		$post_types = Settings::get_public_post_types();
		return rest_ensure_response( $post_types );
	}

	/**
	 * Get all authors.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_authors( \WP_REST_Request $request ) {

		$authors = Settings::get_authors();
		return rest_ensure_response( $authors );
	}

	/**
	 * Get taxonomy terms for a specific taxonomy.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_taxonomy_terms( \WP_REST_Request $request ) {
		$query_params  = $request->get_params();
		$taxonomy_name = $query_params['taxonomy_name'];

		$args = array(
			'taxonomy'   => $taxonomy_name,
			'hide_empty' => false,
		);

		$taxonomy_terms = get_terms( $args );
		$terms          = array();
		if ( is_wp_error( $taxonomy_terms ) ) {
			$terms = $taxonomy_terms; // Send the error back.
		} else {
			// Loop through the terms and get slug + label.
			foreach ( $taxonomy_terms as $term ) {
				$term = array(
					'label' => $term->name,
					'value' => $term->slug,
				);
				array_push( $terms, $term );
			}
		}
		return rest_ensure_response( $terms );
	}

	/**
	 * Get template sources (post types).
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_template_sources( \WP_REST_Request $request ) {

		$post_types = Settings::get_public_post_types();
		// $layouts = Settings::get_layouts_options();.
		$data = array(
			'post_types' => $post_types,
			// 'layouts'    => $layouts,.

		);
		return rest_ensure_response( $data );
	}

	/**
	 * Rebuilds the CSS file.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function regenerate_css( \WP_REST_Request $request ) {
		CSS_Loader::save_css();
		$regenerate_info = array(
			'success' => 1,
		);
		return rest_ensure_response( $regenerate_info );
	}

	/**
	 * Get a template by ID.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_template( \WP_REST_Request $request ) {

		$query_params = $request->get_params();
		$template_id  = $query_params['id'];

		if ( $template_id === 0 ) {
			// Load template settings.
			$default_template = Settings::get_default_template();
			$data             = array(
				'instances'     => $default_template['instances'],
				'instanceOrder' => $default_template['instance_order'],
				'template'      => $default_template['template'],
			);
		} else {

			$instances      = Settings::get_section_data( $template_id, 'template-instances' );
			$instance_order = Settings::get_section_data( $template_id, 'template-instance-order' );
			$template       = Settings::get_section_data( $template_id, 'template-data' );

			$data = array(
				'instances'     => $instances ? $instances : new \stdClass(),
				'instanceOrder' => $instance_order ? $instance_order : array(),
				'template'      => $template ? $template : new \stdClass(),
			);
		}
		return rest_ensure_response( $data );
	}

	/**
	 * Get a layout by ID.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function get_layout( \WP_REST_Request $request ) {

		$query_params = $request->get_params();
		$layout_id    = $query_params['id'];

		if ( $layout_id === 0 ) {
			// Load template settings.
			$default_layout = Settings::get_default_layout();
			$data           = $default_layout;
		} else {

			// TODO - refactor.
			$layout          = Settings::get_section_data( $layout_id, 'layout' );
			$query           = Settings::get_section_data( $layout_id, 'query' );
			$layout_settings = array();
			if ( $layout ) {
				$layout_settings = array_merge( $layout_settings, $layout );
			}
			if ( $query ) {
				$layout_settings = array_merge( $layout_settings, $query );
			}
			$layout_defaults = Settings::get_settings_defaults( array( 'layout', 'query' ) );
			$layout_settings = wp_parse_args( $layout_settings, $layout_defaults );
			$layout_settings = Gutenberg::map_attributes( 'php', $layout_settings );

			$data = array(
				'attributes' => $layout_settings ? $layout_settings : new \stdClass(),
			);
		}
		return rest_ensure_response( $data );
	}

	/**
	 * Save a template.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function save_template( \WP_REST_Request $request ) {

		$query_params = $request->get_params();
		$template_id  = $query_params['id'];
		$is_new       = false;

		if ( 0 === $template_id ) {

			$template_title = __( 'New Template', 'custom-posts' );
			if ( isset( $query_params['template_title'] ) ) {
				$template_title = $query_params['template_title'];
			}
			$post_data = array(
				'post_title'   => $template_title,
				'post_content' => '',
				'post_type'    => 'cl-template',
				'post_status'  => 'publish',
			);

			/** @var int|\WP_Error $result */
			$result = wp_insert_post( $post_data );

			if ( is_wp_error( $result ) ) {
				$response = array(
					'error' => 1,
				);
				return rest_ensure_response( $response );
			}

			$is_new      = true;
			$template_id = $result;
		}

		$instances      = $query_params['instances'];
		$instance_order = $query_params['instance_order'];
		$template_data  = $query_params['template'];

		delete_post_meta( $template_id, 'custom-layouts-template-instances' );
		delete_post_meta( $template_id, 'custom-layouts-template-instance-order' );
		delete_post_meta( $template_id, 'custom-layouts-template-data' );

		update_post_meta( $template_id, 'custom-layouts-template-instances', $instances );
		update_post_meta( $template_id, 'custom-layouts-template-instance-order', $instance_order );
		update_post_meta( $template_id, 'custom-layouts-template-data', $template_data );
		update_post_meta( $template_id, 'custom-layouts-version', CUSTOM_LAYOUTS_VERSION );

		CSS_Loader::save_css( array( $template_id ) );

		$response = array();

		if ( $is_new ) {
			$response['id'] = $template_id;
		}
		return rest_ensure_response( $response );
	}

	/**
	 * Save a layout.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST response.
	 */
	public function save_layout( \WP_REST_Request $request ) {

		// TODO - all these save processes need refactoring and centralising
		// consider finishing that settings API.
		$query_params = $request->get_params();
		$layout_id    = $query_params['id'];
		$is_new       = false;

		if ( 0 === $layout_id ) {

			$layout_title = __( 'New Layout', 'custom-posts' );
			if ( isset( $query_params['layout_title'] ) ) {
				$layout_title = $query_params['layout_title'];
			}
			$post_data = array(
				'post_title'   => $layout_title,
				'post_content' => '',
				'post_type'    => 'cl-layout',
				'post_status'  => 'publish',
			);

			/** @var int|\WP_Error $result */
			$result = wp_insert_post( $post_data );

			if ( is_wp_error( $result ) ) {
				$response = array(
					'error' => 1,
				);
				return rest_ensure_response( $response );
			}

			$is_new    = true;
			$layout_id = $result;
		}

		$attributes = $query_params['attributes'];

		// Now we want to map the data back to PHP format.
		$attributes = Gutenberg::map_attributes( 'js', $attributes );

		// And then split into query + layout according to the settings.
		$layout_keys = array_keys( Settings::get_settings_defaults( array( 'layout' ) ) );
		$query_keys  = array_keys( Settings::get_settings_defaults( array( 'query' ) ) );

		$layout_data = array();
		$query_data  = array();

		foreach ( $layout_keys as $layout_key ) {
			if ( isset( $attributes[ $layout_key ] ) ) {
				$layout_data[ $layout_key ] = $attributes[ $layout_key ];
			}
		}

		foreach ( $query_keys as $query_key ) {
			if ( isset( $attributes[ $query_key ] ) ) {
				$query_data[ $query_key ] = $attributes[ $query_key ];
			}
		}

		delete_post_meta( $layout_id, 'custom-layouts-layout' );
		delete_post_meta( $layout_id, 'custom-layouts-query' );

		update_post_meta( $layout_id, 'custom-layouts-layout', $layout_data );
		update_post_meta( $layout_id, 'custom-layouts-query', $query_data );

		update_post_meta( $layout_id, 'custom-layouts-version', CUSTOM_LAYOUTS_VERSION );

		$response = array(
			// 'attributes' => $attributes,
		);

		if ( $is_new ) {
			$response['id'] = $layout_id;
		}
		return rest_ensure_response( $response );
	}

	/**
	 * Check request permissions.
	 *
	 * @since 1.0.0
	 * @return bool True if user has permission, false otherwise.
	 */
	public function permissions() {
		return current_user_can( 'manage_options' );
	}
}
$rest_api = new Rest_API();
