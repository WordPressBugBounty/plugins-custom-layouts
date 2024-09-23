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

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$date_format = $instance_data['dateFormat'];
		if ( 'custom' === $date_format ) {
			$date_format = $instance_data['customDateFormat'];
		}

		$date   = $this->get_data( $post );
		$output = esc_html( wp_date( $date_format, strtotime( $date ) ) );

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		return $post->post_date;
	}

}
