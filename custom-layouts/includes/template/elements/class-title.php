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
 * Renders the post title
 */
class Title extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$link_to_post = $instance_data['linkToPost'];
		$post_title   = $this->get_data( $post );
		$output       = '';
		if ( $link_to_post === 'yes' ) {
			$open_in_new_window = $instance_data['openNewWindow'];

			$target = '';
			if ( $open_in_new_window === 'yes' ) {
				$target = ' target="_blank"';
			}
			$output = '<a class="cl-element-title__anchor" href="' . esc_url( get_permalink( $post->ID ) ) . '"' . $target . '>' . wp_kses_post( $post_title ) . '</a>';
		} else {
			$output = wp_kses_post( $post_title );
		}

		$output = $this->wrap_container( $output, $instance );
		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		return get_the_title( $post->ID );
	}

	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$instance_data  = $instance['data'];

		$font_family = $instance_data['fontFamily'];

		// Container CSS
		$parent_selector = $template_class . ' ' . $instance_class;
		$css             = '/* ' . $instance['elementId'] . ' */';
		$css            .= $this->create_container_css( $parent_selector, $instance_data );

		// now add link styles
		$full_child_selector = $template_class . ' ' . $instance_class . ' .cl-element-title__anchor';
		$link_styles         = $this->take_array_elements(
			array(
				'fontFamily',
				'fontFormatBold',
				'fontFormatItalic',
				'fontFormatUnderline',
				'fontSize',
			),
			$instance_data
		);

		$css .= $full_child_selector . ' {';
		$css .= CSS_Loader::parse_css_settings( $link_styles );
		$css .= 'display:inline-block;line-height:inherit;';
		$css .= '}';

		// add styles to link hover/active/etc
		$hover_settings = $this->take_array_elements(
			array(
				'fontFormatBoldHover',
				'fontFormatItalicHover',
				'fontFormatUnderlineHover',
			),
			$instance_data
		);
		$hover_styles   = array(
			'fontFormatBold'      => $hover_settings['fontFormatBoldHover'],
			'fontFormatItalic'    => $hover_settings['fontFormatItalicHover'],
			'fontFormatUnderline' => $hover_settings['fontFormatUnderlineHover'],
		);
		$css           .= $full_child_selector . ':hover, ' . $full_child_selector . ':active, ' . $full_child_selector . ':focus {';
		$css           .= CSS_Loader::parse_css_settings( $hover_styles );
		$css           .= '}';

		return $css;
	}


}
