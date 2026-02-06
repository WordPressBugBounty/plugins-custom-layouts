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

	/**
	 * Render the link element.
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

		if ( $return_output ) {
			return $output;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by esc_html(), esc_attr() and parent::run_post_render_hooks()
		echo $output;
	}

	/**
	 * Get the permalink for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string The post permalink.
	 */
	public function get_data( $post ) {
		return get_permalink( $post->ID );
	}


	/**
	 * Get CSS for the link element.
	 *
	 * @param array  $instance Element instance data.
	 * @param string $template_class Template CSS class.
	 * @param array  $template Template data.
	 * @return string The generated CSS.
	 */
	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$child_selector = '.cl-element-link__anchor'; // Anchor selector.

		// Grab the prop.
		$parent_properties = array();
		// Move margin on to the parent.
		$parent_properties['justifyContent'] = $this->get_align_justify( $instance['data']['align'] );
		$parent_properties['align']          = $instance['data']['align'];
		unset( $instance['data']['align'] ); // Keep the rest.

		$css      = '/* ' . $instance['elementId'] . ' */';
		$html_tag = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';

		// Parent node.
		$css  = $template_class . ' ' . $html_tag . $instance_class . '{';
		$css .= CSS_Loader::parse_css_settings( $parent_properties );
		$css .= '}';

		// Child / inline node.
		$full_child_selector = $template_class . ' ' . $html_tag . $instance_class . ' ' . $child_selector;
		$css                .= $full_child_selector . '{';
		$width_mode          = isset( $instance['data']['widthMode'] ) ? $instance['data']['widthMode'] : 'full';
		if ( $width_mode === 'full' ) {
			$css .= 'width: 100%;';
		}
		$css .= CSS_Loader::parse_css_settings( $instance['data'] );
		$css .= '}';

		// Now add styles to link hover.

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
