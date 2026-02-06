<?php
/**
 * Handles the frontend display + data of the link
 *
 * @link       http://codeamp.com
 * @since      1.3.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

namespace Custom_Layouts\Template\Elements;

use Custom_Layouts\Core\CSS_Loader;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the link
 */
class Text extends Element_Base {

	/**
	 * Render the text element.
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

		$text   = $instance_data['text'];
		$output = nl2br( $text );

		// In gutenberg, shortcodes are already processed, so lets just enforce this
		// for consistent behaviour everywhere ( ie, when using our shortcodes ).
		$output = wp_kses_post( do_shortcode( $output ) );
		// $enable_shortcodes = $instance['data']['enableShortcodes'];
		/*
		if ( $enable_shortcodes === 'yes' ) {
			$output = do_shortcode( $output );
		}*/

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return_output ) {
			return $output;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by wp_kses_post() and parent::run_post_render_hooks()
		echo $output;
	}

	/**
	 * Get text data for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string Empty string.
	 */
	public function get_data( $post ) {
		return '';
	}
}
