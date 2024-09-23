<?php
/**
 * Handles the frontend display of the post comment count
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
 * Renders the post comment count
 */
class Comment_Count extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		$label_none     = $instance_data['labelNone'];
		$label_single   = $instance_data['labelSingle'];
		$label_multiple = $instance_data['labelMultiple'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$link_to_comments = $instance_data['linkToComments'];

		// The % is noty auto replaced by get_comments_number when supplying the single label
		$comments_number = get_comments_number( $post->ID );
		if ( absint( $comments_number ) === 1 ) {
			if ( false !== strpos( $label_single, '%' ) ) {
				$label_single = str_replace( '%', number_format_i18n( $comments_number ), $label_single );
			}
		}
		$comments_text = get_comments_number_text( $label_none, $label_single, $label_multiple, $post->ID );

		if ( $link_to_comments === 'yes' ) {
			$output = '<a class="cl-element-comment_count__anchor" href="' . esc_url( get_comments_link( $post->ID ) ) . '">' . esc_html( $comments_text ) . '</a>';
		} else {
			$output = esc_html( $comments_text );
		}

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		return get_comments_number( $post->ID );
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
		$full_child_selector = $template_class . ' ' . $instance_class . ' .cl-element-comment_count__anchor';
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
