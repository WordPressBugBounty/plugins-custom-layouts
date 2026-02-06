<?php
/**
 * Handles the frontend display of the post title
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

namespace Custom_Layouts\Template\Elements;

use Custom_Layouts\Settings;
use Custom_Layouts\Util;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the post title
 */
class Content extends Element_Base {

	/**
	 * Render the content element.
	 *
	 * @param \WP_Post $post Post object.
	 * @param array    $instance Element instance data.
	 * @param array    $template Template data.
	 * @param bool     $return_output Whether to return the output instead of echoing.
	 * @return string|void The output if $return_output is true, void otherwise.
	 */
	public function render( $post, $instance, $template, $return_output = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		// $this->post = $post;
		$output = wp_kses_post( $this->get_data( $post ) );

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return_output ) {
			return $output;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by wp_kses_post() and parent::run_post_render_hooks()
		echo $output;
	}

	/**
	 * Get content data for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string The post content with all tags stripped.
	 */
	public function get_data( $post ) {
		$post_content = wp_strip_all_tags( $post->post_content );
		return $post_content;
	}
}
