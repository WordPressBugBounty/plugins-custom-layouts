<?php
/**
 * Base class for all elements
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
use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Core\Validation;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the post title
 */
class Element_Base {

	private $post;
	private $last_global_post;
	protected $id;
	public function __construct( $parent_id = '' ) {
		$this->parent_id = $parent_id;
	}

	protected function run_pre_render_hooks( $element_type, $instance_data, $post, $template ) {
		// Trigger action when starting.
		do_action( 'custom-layouts/element/before_render', $element_type, $instance_data, $post, $template );

		// Modify args before render.
		// hold off on this until we finalise the apis
		// $instance_data = apply_filters( 'custom-layouts/element/render_args', $instance_data, $element_type, $post, $template );
	}
	protected function run_post_render_hooks( $output, $element_type, $instance_data, $post, $template ) {
		// Modify output html.
		$output = apply_filters( 'custom-layouts/element/render_output', $output, $element_type, $instance_data, $post, $template );
		// Trigger action when finished.
		do_action( 'custom-layouts/element/after_render', $element_type, $instance_data, $post, $template );
		return $output;
	}

	// Temporarily override the global $post, with the current post, so that  template
	// functions like `get_the_permalink()` continue  to work in hooks attached to `read more`
	protected function set_global_post( $current_post ) {

		$this->post = $current_post;
		global $post;
		if ( $current_post->ID !== $post->ID ) {
			$this->last_global_post = $post;
			$post                   = $current_post;
		}
	}

	protected function revert_global_post() {
		if ( $this->post->ID !== $this->last_global_post->ID ) {
			global $post;
			$post = $this->last_global_post;
		}
	}
	protected function get_instance_class( $instance_id ) {

		$instance_id = str_replace( 'eluid-', '', $instance_id );
		return '.cl-element--instance-' . intval( $instance_id );
	}

	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$html_tag       = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';
		$width_mode     = isset( $instance['data']['widthMode'] ) ? $instance['data']['widthMode'] : 'full';
		$css            = '/* ' . $instance['elementId'] . ' */';

		$container_selector = $template_class . ' ' . $html_tag . $instance_class;
		$css               .= $this->create_container_css( $container_selector, $instance['data'] );

		return $css;
	}
	// return the array elements matching the keys, and removes (by ref)
	// the elemenets from the original array
	public function take_array_elements( $keys, &$array ) {
		$new_array = array();
		foreach ( $keys as $key ) {
			$new_array[ $key ] = $array[ $key ];
			// array_push( $new_array, $array[ $key ] );
			unset( $array[ $key ] );
		}

		return $new_array;
	}
	protected function get_align_justify( $align ) {
		if ( $align === 'left' ) {
			return 'flex-start';
		} elseif ( $align === 'right' ) {
			return 'flex-end';
		} elseif ( $align === 'center' ) {
			return 'center';
		} elseif ( $align === 'justify' ) {
			return '';
		}
		return '';
	}

	public function wrap_container( $output, $instance, $use_width_mode = true ) {

		$element_id   = $instance['elementId'];
		$html_tag     = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';
		$width_mode   = isset( $instance['data']['widthMode'] ) ? $instance['data']['widthMode'] : 'full';
		$custom_class = isset( $instance['data']['customClass'] ) ? trim( $instance['data']['customClass'] ) : '';
		// $allowed_tags = array( 'div', 'span', 'p', 'h2', 'h3', 'h4', 'h5', 'h6');
		$instance_id = str_replace( 'eluid-', '', $instance['id'] );

		$container_html_tag = $html_tag;
		$wrapper_html_tag   = 'div';
		if ( $width_mode === 'auto' && $use_width_mode === true ) {
			$container_html_tag = 'div';
			$wrapper_html_tag   = $html_tag;
		}

		ob_start();

		echo '<' . sanitize_key( $container_html_tag ) . ' class="cl-element cl-element-' . esc_attr( $instance['elementId'] ) . ' cl-element--instance-' . intval( $instance_id ) . ' ' . esc_attr( $custom_class ) . '">';
		if ( $width_mode === 'auto' && $use_width_mode === true ) {
			echo '<' . sanitize_key( $wrapper_html_tag ) . ' class="cl-element__container">';
		}
		echo $output;
		if ( $width_mode === 'auto' && $use_width_mode === true ) {
			echo '</' . sanitize_key( $wrapper_html_tag ) . '>';
		}
		echo '</' . sanitize_key( $container_html_tag ) . '>';

		return ob_get_clean();
	}


	public function create_container_css( $selector_name, $instance_data ) {
		$css        = '';
		$width_mode = isset( $instance_data['widthMode'] ) ? $instance_data['widthMode'] : 'full';
		$align      = isset( $instance_data['align'] ) ? $instance_data['align'] : '';

		if ( $width_mode === 'auto' ) {
			$container_selector = $selector_name;
			$container_css      = array(
				'justifyContent' => $this->get_align_justify( $align ),
			);
			$css               .= $container_selector . '{';
			$css               .= CSS_Loader::parse_css_settings( $container_css );
			$css               .= '}';

			$selector_name .= ' .cl-element__container';
		} else {
			$instance_data['justifyContent'] = $this->get_align_justify( $align );
		}

		$css .= $selector_name . '{';
		$css .= CSS_Loader::parse_css_settings( $instance_data );
		$css .= '}';
		return $css;
	}

	public function create_selector_css( $selector_name, $css_rules ) {
		$css  = $selector_name . '{';
		$css .= CSS_Loader::parse_css_settings( $css_rules );
		$css .= '}';
		return $css;
	}
}
