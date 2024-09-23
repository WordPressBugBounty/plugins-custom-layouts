<?php
/**
 * Handles the frontend display + data of the modified date
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

namespace Custom_Layouts\Template\Elements;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Settings;
use Custom_Layouts\Util;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the modified date
 */
class Post_Type extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		$link_to_archive = $instance_data['linkToArchive'];
		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$post_type_label = $this->get_data( $post );
		$output          = esc_html( $post_type_label );
		if ( $link_to_archive === 'yes' ) {
			$post_type_object = get_post_type_object( $post->post_type );
			// only add the link if there is an archive
			if ( $post->post_type === 'post' || ( $post_type_object->has_archive === true && $post_type_object->public === true ) ) {
				$output = '<a class="cl-element-post-type__anchor" href="' . esc_url( get_post_type_archive_link( $post->post_type ) ) . '">' . esc_html( $post_type_label ) . '</a>';
			}
		}

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		$post_type = get_post_type_object( $post->post_type );
		return $post_type->labels->singular_name;
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
		$full_child_selector = $template_class . ' ' . $instance_class . ' .cl-element-post-type__anchor';
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
