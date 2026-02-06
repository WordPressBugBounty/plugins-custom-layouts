<?php
/**
 * Handles the frontend display + data of the published date
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
 * Renders the published date
 */
class Published_Date extends Element_Base {

	/**
	 * Render the published date element.
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

		$this->post = $post;

		$date_format = $instance_data['dateFormat'];
		if ( 'custom' === $date_format ) {
			$date_format = $instance_data['customDateFormat'];
		}

		$date   = $this->get_data( $post );
		$output = esc_html( wp_date( $date_format, strtotime( $date ), new \DateTimeZone( 'UTC' ) ) );

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return_output ) {
			return $output;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by esc_html() and parent::run_post_render_hooks()
		echo $output;
	}

	/**
	 * Get the published date for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string The post published date.
	 */
	public function get_data( $post ) {
		return $post->post_date;
	}
}
