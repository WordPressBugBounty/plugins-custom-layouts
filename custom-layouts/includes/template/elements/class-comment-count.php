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

	/**
	 * Render the comment count element.
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

		$label_none     = $instance_data['labelNone'];
		$label_single   = $instance_data['labelSingle'];
		$label_multiple = $instance_data['labelMultiple'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$link_to_comments = $instance_data['linkToComments'];

		// The % is not auto replaced by get_comments_number when supplying the single label.
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

		if ( $return_output ) {
			return $output;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by esc_html(), esc_url() and parent::run_post_render_hooks()
		echo $output;
	}

	/**
	 * Get the comment count for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return int The number of comments.
	 */
	public function get_data( $post ) {
		return get_comments_number( $post->ID );
	}

	/**
	 * Get CSS for the comment count element.
	 *
	 * @param array  $instance Element instance data.
	 * @param string $template_class Template CSS class.
	 * @param array  $template Template data.
	 * @return string The generated CSS.
	 */
	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$instance_data  = $instance['data'];

		$font_family = $instance_data['fontFamily'];

		// Container CSS.
		$parent_selector = $template_class . ' ' . $instance_class;
		$css             = '/* ' . $instance['elementId'] . ' */';
		$css            .= $this->create_container_css( $parent_selector, $instance_data );

		// Now add link styles.
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

		// Add styles to link hover/active/etc.
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
