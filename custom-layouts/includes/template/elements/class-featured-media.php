<?php
/**
 * Handles the frontend display + data of the featured image
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
 * Renders the featured image
 */
class Featured_Media extends Element_Base {

	private $post;

	private function featured_media_axis( $image_position ) {
		if ( $image_position === 'top' || $image_position === 'bottom' ) {
			return 'vertical';
		}
		return 'horizontal';
	}
	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;
		// $image_data = $this->get_data( $post ); // TODO - double check this
		$output     = '';
		$image_size = $instance_data['imageSourceSize'];
		// $image_alignment = $instance_data['imageAlign'];
		$image_fit_mode   = $instance_data['imageFitMode'];
		$container_sizing = $instance_data['containerSizing'];
		$link_to_post     = $instance_data['linkToPost'];

		$image_position      = $template['imagePosition'];
		$featured_image_axis = $this->featured_media_axis( $image_position );

		$post_attachment_id = get_post_thumbnail_id( $post );
		$image_data         = Util::get_image_by_size( $post_attachment_id, $image_size );
		$image_url          = '';
		if ( $image_data ) {
			$image_url = $image_data['url'];
		}

		if ( $image_url === '' ) {
			ob_start();
			Util::load_svgs( array( 'image-placeholder' ) );
			$svg_template = ob_get_clean();
			$placeholder  = '<svg class="cl-element-featured_media__placeholder-image"><use xlink:href="#cl-svg-image-placeholder" /></svg>';
			$output       = $svg_template . $placeholder;

		} elseif ( $image_url !== '' ) {
			// now we need to figure out if we want to show a regular image, or create div / container and use background-image
			$use_img_tag = true; // use img tag
			if ( $featured_image_axis === 'vertical' ) {
				// then the image is above or below
				if ( $container_sizing !== 'natural' ) {
					$use_img_tag = false;
				}
			} elseif ( $featured_image_axis === 'horizontal' ) {
				// then the image is left or right
				if ( $container_sizing !== 'natural' ) {
					if ( $image_fit_mode !== 'full_width' && $image_fit_mode !== 'auto' ) {
						$use_img_tag = false;
					}
				}
			}

			$alt_text         = trim( strip_tags( get_post_meta( $post_attachment_id, '_wp_attachment_image_alt', true ) ) );
			$image_attributes = array(
				'class' => 'cl-element-featured_media__image',
			);

			// get alt text
			if ( $use_img_tag ) {
				$image_attributes['src'] = esc_url( $image_url );
				$image_attributes['alt'] = $alt_text;
				$attribute_html          = Util::get_attributes_html( $image_attributes );
				$output                  = '<img ' . $attribute_html . ' />';
				// todo add alt text
			} else {
				$image_attributes['style'] = 'background-image: url(' . esc_url( $image_url ) . ');';
				$image_attributes['role']  = 'img';

				$image_attributes['aria-label'] = $alt_text;
				// todo - add alt text as aria-label - https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Roles/Role_Img
				$attribute_html = Util::get_attributes_html( $image_attributes );
				$output         = wp_kses_post( '<div ' . $attribute_html . '></div>' );
			}
		} else {
			$output = '';
		}

		if ( 'yes' === $link_to_post ) {
			$open_in_new_window = $instance_data['openNewWindow'];

			$target = '';
			if ( $open_in_new_window === 'yes' ) {
				$target = ' target="_blank"';
			}
			$output = '<a class="cl-element-featured_media__anchor" href="' . esc_url( get_permalink( $post->ID ) ) . '"' . $target . ' title="' . esc_attr( get_the_title( $post->ID ) ) . '">' . $output . '</a>';
		}

		$output = $this->wrap_container( $output, $instance, $image_url, $featured_image_axis );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}


	public function get_data( $post ) {
		global $_wp_additional_image_sizes;
		$image_data = array();
		$sizes      = get_intermediate_image_sizes();
		// $image_data['sizes'] = $_wp_additional_image_sizes;

		$post_attachment_id = get_post_thumbnail_id( $post );

		if ( ! $post_attachment_id ) {
			return array();
		}

		foreach ( $sizes as $size_name ) {
			$attachment_meta = Util::get_image_by_size( $post_attachment_id, $size_name );
			if ( $attachment_meta ) {
				$image_data[ $size_name ] = $attachment_meta;
			}
		}

		return $image_data;
	}

	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class = $this->get_instance_class( $instance['id'] );
		$instance_data  = $instance['data'];
		$image_position = $template['imagePosition'];
		// $font_family = $instance_data['fontFamily'];

		$full_link_selector              = $template_class . ' ' . $instance_class . ' .cl-element-featured_media__anchor';
		$full_image_selector             = $template_class . ' ' . $instance_class . ' .cl-element-featured_media__image';
		$full_placeholder_image_selector = $template_class . ' ' . $instance_class . ' .cl-element-featured_media__placeholder-image';

		$featured_image_axis = $this->featured_media_axis( $image_position );
		// now we need to figure out if we want to show a regular image, or create div / container and use background-image
		$use_img_tag      = true; // use img tag
		$container_sizing = $instance_data['containerSizing'];
		$image_fit_mode   = $instance_data['imageFitMode'];
		$image_align      = isset( $instance_data['imageAlign'] ) ? $instance_data['imageAlign'] : 'center center';
		if ( $featured_image_axis === 'vertical' ) {
			// then the image is above or below
			if ( $container_sizing !== 'natural' ) {
				$use_img_tag = false;
			}
		} elseif ( $featured_image_axis === 'horizontal' ) {
			// then the image is left or right
			if ( $container_sizing !== 'natural' ) {
				if ( $image_fit_mode !== 'full_width' && $image_fit_mode !== 'auto' ) {
					$use_img_tag = false;
				}
			}
		}

		// container styles
		$css              = '/* ' . $instance['elementId'] . ' */';
		$css              = $template_class . ' ' . $instance_class . '{';
		$css             .= CSS_Loader::parse_css_settings( $instance['data'] );
		$container_styles = array();

		if ( $container_sizing === 'natural' ) {
			$container_styles['display'] = 'flex';

			// figure out alignment

			$alignments = explode( ' ', $image_align );
			if ( count( $alignments ) === 2 ) {

				if ( $featured_image_axis === 'vertical' ) {
					$align = trim( strtolower( $alignments[1] ) ); // left / center / right
					if ( 'left' === $align ) {
						$container_styles['justifyContent'] = 'flex-start';
					} elseif ( 'center' === $align ) {
						$container_styles['justifyContent'] = 'center';
					} if ( 'right' === $align ) {
						$container_styles['justifyContent'] = 'flex-end';
					}
				} else {
					// if horizontal use the first part...

					$align = trim( strtolower( $alignments[0] ) ); // top / center / bottom
					if ( 'top' === $align ) {
						$container_styles['alignItems'] = 'flex-start';
					} elseif ( 'center' === $align ) {
						$container_styles['alignItems'] = 'center';
					} if ( 'bottom' === $align ) {
						$container_styles['alignItems'] = 'flex-end';
					}
				}
			}
		} elseif ( $container_sizing === 'aspect_ratio' ) {
			if ( $featured_image_axis === 'vertical' ) {
				// then we need to set the container to an aspect ratio
				$aspect_ratio = $instance_data['aspectRatio'];
				$aspect_parts = explode( '_', $aspect_ratio );
				if ( 2 === count( $aspect_parts ) ) {
					$container_styles['height']     = '0';
					$container_styles['paddingTop'] = round( ( ( absint( $aspect_parts[1] ) / absint( $aspect_parts[0] ) ) * 100 ), 4 ) . '%';

				}
			}
		} elseif ( $container_sizing === 'width_pixel' ) {
			if ( $featured_image_axis === 'horizontal' ) {
				$width_pixel               = absint( $instance_data['imageWidthPixel'] ) . 'px';
				$container_styles['width'] = $width_pixel;
			}
		} elseif ( $container_sizing === 'width_percentage' ) {
			if ( $featured_image_axis === 'horizontal' ) {
				$width_percentage          = absint( $instance_data['imageWidthPercentage'] ) . '%';
				$container_styles['width'] = $width_percentage;
			}
		}

		$css .= CSS_Loader::parse_css_settings( $container_styles );
		$css .= '}';

		/*
		 * Link styles
		 */
		$link_styles = array();
		if ( ! $use_img_tag ) {
			$link_styles['display']  = 'block';
			$link_styles['height']   = '100%';
			$link_styles['width']    = '100%';
			$link_styles['position'] = 'absolute';
			$link_styles['top']      = '0';
			$link_styles['left']     = '0';
		}
		// if ( $container_sizing === 'natural' ) {
		if ( $image_fit_mode === 'full_width' ) {
			$link_styles['width'] = '100%';
		}
		// }

		$css .= $full_link_selector . ' {';
		$css .= CSS_Loader::parse_css_settings( $link_styles );
		$css .= '}';

		/*
		 * image styles
		 */
		if ( $featured_image_axis === 'vertical' ) {
			// then the image is above or below
			if ( $container_sizing !== 'natural' ) {
				$use_img_tag = false;
			}
		}
		$image_styles = array();

		/*
		if ( $featured_image_axis === 'vertical' ) {
			 else {
				//the it must be "natural"
				$container_styles['flex'] = '0 1 0';
			}

		}*/
		// then we are not using image tag and instead using background-image css
		if ( ! $use_img_tag ) {
			// now add styles to image element
			$image_styles = wp_parse_args(
				array(
					'backgroundPosition' => $image_align,
					'backgroundRepeat'   => 'no-repeat',
					'width'              => '100%',
					'height'             => '100%',
					'display'            => 'block',
					'position'           => 'absolute',
					'top'                => '0',
					'left'               => '0',
				),
				$image_styles
			);

			if ( ( $image_fit_mode === 'cover' ) || ( $image_fit_mode === 'contain' ) ) {
				$image_styles['backgroundSize'] = $image_fit_mode;
			}
		}
		// if ( $container_sizing === 'natural' ) {
		if ( $image_fit_mode === 'full_width' ) {
			$image_styles['width'] = '100%';
		}
		if ( $image_fit_mode === 'auto' ) {
			$image_styles['maxWidth'] = '100%';
		}
		// }

		// image
		$css .= $full_image_selector . ' {';
		$css .= CSS_Loader::parse_css_settings( $image_styles );
		$css .= '}';

		// placeholder image
		$css                     .= $full_placeholder_image_selector . ' {';
		$placeholder_image_styles = array();
		// now add foreground color for the SVG
		if ( isset( $instance_data['foregroundColor'] ) ) {
			$placeholder_image_styles['fill'] = $instance_data['foregroundColor'];
		}

		$css .= CSS_Loader::parse_css_settings( $placeholder_image_styles );
		$css .= '}';

		return $css;
	}

	// override the container
	public function wrap_container( $output, $instance, $has_image = true ) {
		$no_image_class = '';
		if ( ! $has_image ) {
			$no_image_class = ' cl-element-featured_media--no-image';
		}

		$instance_data    = $instance['data'];
		$container_sizing = $instance_data['containerSizing'];
		$sizing_class     = ' cl-element-featured_media--sizing-' . esc_attr( $container_sizing );

		$element_id   = $instance['elementId'];
		$html_tag     = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';
		$custom_class = isset( $instance['data']['customClass'] ) ? trim( $instance['data']['customClass'] ) : '';
		// $allowed_tags = array( 'div', 'span', 'p', 'h2', 'h3', 'h4', 'h5', 'h6');
		$instance_id = str_replace( 'eluid-', '', $instance['id'] );

		ob_start();
		echo '<' . sanitize_key( $html_tag ) . ' class="cl-element cl-element-' . esc_attr( $instance['elementId'] ) . ' cl-element--instance-' . intval( $instance_id ) . ' ' . esc_attr( $custom_class ) . esc_attr( $no_image_class ) . esc_attr( $sizing_class ) . '">';
		echo $output;
		echo '</' . sanitize_key( $html_tag ) . '>';

		return ob_get_clean();

	}
}
