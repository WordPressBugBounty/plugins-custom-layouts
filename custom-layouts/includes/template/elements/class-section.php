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

use Custom_Layouts\Core\CSS_Loader;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a section (which can contain instances)
 */
class Section extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $children, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$output = '';
		if ( ! is_bool( $children ) ) {
			$output = $children;
		}
		$output = $this->wrap_container( $output, $instance );
		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		$post_content = wp_strip_all_tags( $post->post_content );
		return $post_content;
	}


}
