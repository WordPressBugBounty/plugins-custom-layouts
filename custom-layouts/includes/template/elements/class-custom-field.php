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

use Custom_Layouts\Settings;
use Custom_Layouts\Util;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the modified date
 */
class Custom_Field extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post           = $post;
		$custom_field_key     = $instance_data['customFieldKey'];
		$before_text          = $instance_data['beforeText'];
		$after_text           = $instance_data['afterText'];
		$custom_field_type    = $instance_data['customFieldType'];
		$decimal_places       = $instance_data['formatDecimalPlaces'];
		$decimal_point        = $instance_data['formatDecimalPoint'];
		$thousands_seperator  = $instance_data['formatThousandsSeperator'];
		$date_format          = $instance_data['dateFormat'];
		$custom_date_format   = $instance_data['customDateFormat'];
		$restrict_text        = $instance_data['restrictText'];
		$restrict_text_length = intval( $instance_data['restrictTextLength'] );
		$ellipsis_text        = $instance_data['ellipsisText'];

		if ( empty( $custom_field_key ) ) {
			return '';
		}

		$output = '';
		$value  = get_post_meta( $post->ID, $custom_field_key, true );

		if ( $value !== '' && is_scalar( $value ) ) {
			$formatted_content = $value;
			if ( 'number' === $custom_field_type ) {
				$formatted_content = number_format( $value, $decimal_places, $decimal_point, $thousands_seperator );
			} elseif ( 'text' === $custom_field_type ) {
				if ( $restrict_text === 'words' ) {
					$formatted_content = wp_trim_words( $value, $restrict_text_length, $ellipsis_text );
				} elseif ( $restrict_text === 'chars' ) {
					$formatted_content = $this->trim_string_to_chars( $value, $restrict_text_length, $ellipsis_text );
				}
			} elseif ( 'date' === $custom_field_type ) {
				$date_formatted = $instance_data['dateFormat'];
				if ( 'custom' === $date_format ) {
					$date_formatted = $custom_date_format;
				}
				$formatted_content = wp_date( $date_formatted, strtotime( $value ) );

			} elseif ( 'link' === $custom_field_type ) {
				$url = $value;

				$label              = $instance_data['linkLabel'];
				$open_in_new_window = $instance_data['linkNewWindow'];

				$target = '';
				if ( $open_in_new_window === 'yes' ) {
					$target = ' target="_blank"';
				}
				$formatted_content = '<a class="cl-element-link__anchor" href="' . esc_attr( esc_url( $url ) ) . '"' . $target . '>' . esc_html( $label ) . '</a>';
			}

			$escaped_content = '';
			if ( 'html' === $custom_field_type || 'link' === $custom_field_type ) {
				$escaped_content = wp_kses_post( $formatted_content );
			} else {
				$escaped_content = esc_html( $formatted_content );
			}

			$output .= esc_html( $before_text ) . $escaped_content . esc_html( $after_text );
			$output  = $this->wrap_container( $output, $instance );
		}

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post ) {
		return '';
	}
	// TODO - this is probably not i18n friendly
	private function trim_string_to_chars( $value, $limit = 100, $padding = '&hellip;' ) {
		// $limit = $limit - mb_strlen( $padding ); // Take into account $padding string into the limit
		$valuelen = mb_strlen( $value );
		return $limit < $valuelen ? mb_substr( $value, 0, mb_strrpos( $value, ' ', $limit - $valuelen ) ) . $padding : $value;
	}

	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$html_tag       = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';
		$width_mode     = isset( $instance['data']['widthMode'] ) ? $instance['data']['widthMode'] : 'full';

		$custom_field_type = $instance['data']['customFieldType'];
		if ( $custom_field_type !== 'link' ) {
			// proceed as usual
			$css                = '/* ' . $instance['elementId'] . ' */';
			$container_selector = $template_class . ' ' . $html_tag . $instance_class;
			$css               .= $this->create_container_css( $container_selector, $instance['data'] );

			if ( $container_selector !== 'auto' ) {
				$css .= $container_selector . '{';
				$css .= 'display: block';
				$css .= '}';
			}
		} else {
			// otherwise just call the link css
			$new_instance                          = $instance;
			$new_instance['data']['label']         = $instance['data']['linkLabel'];
			$new_instance['data']['openNewWindow'] = $instance['data']['linkNewWindow'];
			$link_instance                         = new Link();
			$css                                   = $link_instance->get_css( $new_instance, $template_class, $template );
		}

		return $css;
	}

}
