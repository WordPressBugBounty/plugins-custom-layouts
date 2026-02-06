<?php
/**
 * Permissions class for Custom Layouts
 *
 * Handles shortcode permission validation to prevent unauthorized access
 * to private/draft post content via the [custom-template] shortcode.
 *
 * @link       http://codeamp.com
 * @since      1.5.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

namespace Custom_Layouts;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permissions class for Custom Layouts - handles shortcode permission validation.
 *
 * Two-layer protection:
 * 1. Save-time: For users who can publish without approval, strip post_id if unauthorized
 * 2. Render-time: In preview mode, always check if previewing user can read target post
 *
 * @since 1.5.0
 */
class Permissions {

	/**
	 * Initialize permission hooks.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'sanitize_shortcode_permissions' ), 10, 1 );
	}

	/**
	 * Sanitize shortcodes by removing post_id attributes that reference posts the user cannot read.
	 *
	 * Only applies to users who can publish posts without approval. Users who need editorial
	 * approval can add any post_id - the reviewer will decide if it's appropriate.
	 *
	 * @since 1.5.0
	 *
	 * @param array<string, mixed> $data The sanitized post data.
	 * @return array<string, mixed> The filtered post data.
	 */
	public static function sanitize_shortcode_permissions( array $data ): array {
		// Get the post content.
		$content = isset( $data['post_content'] ) ? $data['post_content'] : '';

		// Early return if content is empty or not a string.
		if ( ! is_string( $content ) || empty( $content ) ) {
			return $data;
		}

		// Early return if no custom-template shortcode present.
		if ( strpos( $content, '[custom-template' ) === false ) {
			return $data;
		}

		// Get post type from the data being saved.
		$post_type = isset( $data['post_type'] ) && is_string( $data['post_type'] ) ? $data['post_type'] : 'post';

		// Check if user can publish without approval.
		// If they need approval, let the reviewer decide - don't filter.
		if ( ! self::current_user_can_publish( $post_type ) ) {
			return $data;
		}

		// Build regex to match [custom-template ...] shortcodes.
		$pattern = '/\[custom-template\s+([^\]]*)\]/i';

		// Find and process all matches.
		$result = preg_replace_callback( $pattern, array( __CLASS__, 'validate_shortcode_callback' ), $content );

		// Update content if preg_replace_callback succeeded.
		if ( null !== $result ) {
			$data['post_content'] = $result;
		}

		return $data;
	}

	/**
	 * Check if the current user can publish posts of the given type without needing approval.
	 *
	 * @since 1.5.0
	 *
	 * @param string $post_type The post type being saved.
	 * @return bool True if user can publish, false if they need approval.
	 */
	private static function current_user_can_publish( string $post_type ): bool {
		$post_type_object = get_post_type_object( $post_type );

		if ( $post_type_object && isset( $post_type_object->cap->publish_posts ) ) {
			$publish_cap = $post_type_object->cap->publish_posts;
			if ( is_string( $publish_cap ) ) {
				return current_user_can( $publish_cap );
			}
		}

		// Fallback to generic publish_posts capability.
		return current_user_can( 'publish_posts' );
	}

	/**
	 * Callback to validate individual shortcode matches.
	 *
	 * If the user cannot read the target post, strips the post_id attribute
	 * but keeps the rest of the shortcode intact.
	 *
	 * @since 1.5.0
	 *
	 * @param array<int, string> $matches Regex matches.
	 * @return string The shortcode, potentially with post_id removed.
	 */
	private static function validate_shortcode_callback( array $matches ): string {
		$full_shortcode    = $matches[0];
		$attributes_string = isset( $matches[1] ) ? $matches[1] : '';

		// Parse attributes using WordPress function.
		$atts = shortcode_parse_atts( $attributes_string );

		// shortcode_parse_atts returns empty string if no attributes.
		if ( ! is_array( $atts ) ) {
			return $full_shortcode;
		}

		// If no post_id attribute, shortcode is safe (uses current post).
		if ( ! isset( $atts['post_id'] ) || '' === $atts['post_id'] ) {
			return $full_shortcode;
		}

		$post_id_value = $atts['post_id'];
		if ( ! is_string( $post_id_value ) && ! is_numeric( $post_id_value ) ) {
			return self::rebuild_shortcode_without_post_id( $atts );
		}

		$post_id = absint( $post_id_value );

		// If post_id is 0 or invalid, strip it.
		if ( 0 === $post_id ) {
			return self::rebuild_shortcode_without_post_id( $atts );
		}

		// Check if current user can read this post.
		if ( current_user_can( 'read_post', $post_id ) ) {
			return $full_shortcode;
		}

		// User cannot read this post - rebuild without post_id.
		return self::rebuild_shortcode_without_post_id( $atts );
	}

	/**
	 * Rebuild the shortcode without the post_id attribute.
	 *
	 * @since 1.5.0
	 *
	 * @param array<int|string, mixed> $atts The parsed shortcode attributes.
	 * @return string The rebuilt shortcode.
	 */
	private static function rebuild_shortcode_without_post_id( array $atts ): string {
		$id_value = isset( $atts['id'] ) ? $atts['id'] : 0;
		$id       = is_numeric( $id_value ) ? absint( $id_value ) : 0;

		if ( 0 === $id ) {
			return '';
		}

		return "[custom-template id='{$id}']";
	}

	/**
	 * Check if the current user can render a post_id in a shortcode.
	 *
	 * In preview mode, always checks if the previewing user can read the target post.
	 * For published posts, allows rendering (an authorized user published it).
	 *
	 * @since 1.5.0
	 *
	 * @param int $target_post_id The post ID being rendered via the shortcode.
	 * @return bool True if rendering is allowed, false otherwise.
	 */
	public static function can_render_post_in_shortcode( int $target_post_id ): bool {
		// If no post_id specified, allow (will use current post context).
		if ( 0 === $target_post_id ) {
			return true;
		}

		// Check if we're in preview mode.
		if ( self::is_preview_mode() ) {
			// In preview, check if the current user can read the target post.
			return current_user_can( 'read_post', $target_post_id );
		}

		// Not in preview - if the post is published, an authorized user approved it.
		return true;
	}

	/**
	 * Check if we're currently in preview mode.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if in preview mode.
	 */
	private static function is_preview_mode(): bool {
		// WordPress is_preview() function.
		if ( is_preview() ) {
			return true;
		}

		// Check for preview query parameter (used by block editor).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Just checking if preview mode.
		if ( isset( $_GET['preview'] ) && 'true' === $_GET['preview'] ) {
			return true;
		}

		// Check the post status of the containing post.
		$post = get_post();
		if ( $post && 'publish' !== $post->post_status ) {
			return true;
		}

		return false;
	}
}

// Initialize on load.
Permissions::init();
