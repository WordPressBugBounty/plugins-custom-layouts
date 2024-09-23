<?php
/**
 * Handles the frontend display + data of the link
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

namespace Custom_Layouts\Template\Elements;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Core\Validation;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the link
 */
class Link extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$label              = $instance_data['label'];
		$open_in_new_window = $instance_data['openNewWindow'];

		$permalink = $this->get_data( $post );

		$target = '';
		if ( $open_in_new_window === 'yes' ) {
			$target = ' target="_blank"';
		}
		$output = '<a class="cl-element-link__anchor" href="' . esc_attr( $permalink ) . '"' . $target . '>' . esc_html( $label ) . '</a>';

		$output = $this->wrap_container( $output, $instance, false );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		return get_permalink( $post->ID );
	}


	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$child_selector = '.cl-element-link__anchor'; // anchor selector

		// grab the prop
		$parent_properties = array();
		// move margin on to the parent
		$parent_properties['justifyContent'] = $this->get_align_justify( $instance['data']['align'] );
		$parent_properties['align']          = $instance['data']['align'];
		unset( $instance['data']['align'] ); // keep the rest

		$css      = '/* ' . $instance['elementId'] . ' */';
		$html_tag = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';

		// parent node
		$css  = $template_class . ' ' . $html_tag . $instance_class . '{';
		$css .= CSS_Loader::parse_css_settings( $parent_properties );
		$css .= '}';

		// child / inline node
		$full_child_selector = $template_class . ' ' . $html_tag . $instance_class . ' ' . $child_selector;
		$css                .= $full_child_selector . '{';
		$width_mode          = isset( $instance['data']['widthMode'] ) ? $instance['data']['widthMode'] : 'full';
		if ( $width_mode === 'full' ) {
			$css .= 'width: 100%;';
		}
		$css .= CSS_Loader::parse_css_settings( $instance['data'] );
		$css .= '}';

		// now add styles to link hover

		$hover_styles = array(
			'fontFormatBold'      => $instance['data']['fontFormatBoldHover'],
			'fontFormatItalic'    => $instance['data']['fontFormatItalicHover'],
			'fontFormatUnderline' => $instance['data']['fontFormatUnderlineHover'],
			'backgroundColor'     => isset( $instance['data']['backgroundColor'] ) ? $instance['data']['backgroundColor'] : '',
			'backgroundGradient'  => isset( $instance['data']['backgroundGradient'] ) ? $instance['data']['backgroundGradient'] : '',
			'textColor'           => isset( $instance['data']['textColor'] ) ? $instance['data']['textColor'] : '',
		);

		$css .= $full_child_selector . ':hover, ' . $full_child_selector . ':active, ' . $full_child_selector . ':focus {';
		$css .= CSS_Loader::parse_css_settings( $hover_styles );
		$css .= '}';

		return $css;
	}

}
