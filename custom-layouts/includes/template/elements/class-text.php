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

	private $post;
	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$text   = $instance_data['text'];
		$output = nl2br( $text );

		// In gutenberg, shortcodes are already processed, so lets just enforce this
		// for consistent behaviour everywhere ( ie, when using our shortcodes )
		$output = wp_kses_post( do_shortcode( $output ) );
		// $enable_shortcodes = $instance['data']['enableShortcodes'];
		/*
		if ( $enable_shortcodes === 'yes' ) {
			$output = do_shortcode( $output );
		}*/

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		return '';
	}

}
