<?php
/**
 * Handles the frontend display + data of the excerpt
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
 * Renders the excerpt
 */
class Excerpt extends Element_Base {

	/**
	 * The excerpt length in words.
	 *
	 * @var int
	 */
	private $excerpt_length = 10;

	/**
	 * Whether to hide the "read more" link.
	 *
	 * @var bool
	 */
	private $hide_read_more = false;

	/**
	 * Get the excerpt length.
	 *
	 * @return int The excerpt length.
	 */
	public function excerpt_length() {
		return $this->excerpt_length;
	}
	/**
	 * Filter the excerpt "read more" string.
	 *
	 * @param string $excerpt The excerpt text.
	 * @return string The filtered excerpt.
	 */
	public function excerpt_more( $excerpt ) {

		if ( $this->hide_read_more ) {
			return '';
		}
		return $excerpt;
	}

	/**
	 * Render the excerpt element.
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

		$use_content          = $instance_data['excerptUseContent'];
		$this->excerpt_length = $instance_data['excerptLength'];
		$trim_excerpt         = $instance_data['excerptTrim'] === 'yes' ? true : false;
		$hide_read_more       = $instance_data['excerptHideReadMore'] === 'yes' ? true : false;
		if ( $use_content === 'yes' ) {
			$output = $this->get_data( $post, $this->excerpt_length, $trim_excerpt, $hide_read_more, true );
		} else {
			$output = $this->get_data( $post, $this->excerpt_length, $trim_excerpt, $hide_read_more );
		}
		$output = wp_kses_post( $output );

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return_output ) {
			return $output;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by wp_kses_post() and parent::run_post_render_hooks()
		echo $output;
	}



	/**
	 * Get excerpt data for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @param int      $excerpt_length Length of the excerpt.
	 * @param bool     $trim_excerpt Whether to trim the excerpt.
	 * @param bool     $hide_read_more Whether to hide the read more link.
	 * @param bool     $auto Whether to auto-generate excerpt from content.
	 * @return string The excerpt text.
	 */
	public function get_data( $post, $excerpt_length = -1, $trim_excerpt = false, $hide_read_more = false, $auto = false ) {
		if ( ! $auto ) {
			$excerpt = $post->post_excerpt;
			$excerpt = html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' );

			if ( $trim_excerpt ) {
				$excerpt = wp_trim_words( $excerpt, $this->excerpt_length );
			}
		} else {
			if ( $excerpt_length ) {
				$this->excerpt_length = $excerpt_length;
			}
			$this->hide_read_more = $hide_read_more;

			// First check to see if a post has an excerpt (so we know if we're using)
			// content as fall back.
			if ( $post->post_excerpt === '' ) {

				if ( $hide_read_more ) {
					// We want to keep the default ellipsis, but not add the text
					// so the best way, is just to pull the full excerpt.
					$excerpt = $post->post_content;
					$excerpt = html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' );
					$excerpt = wp_trim_words( $excerpt, $this->excerpt_length );

				} else {
					add_filter( 'excerpt_length', array( $this, 'excerpt_length' ), 1000 );
					add_filter( 'excerpt_more', array( $this, 'excerpt_more' ), 1000 );
					$excerpt = get_the_excerpt( $post );
					remove_filter( 'excerpt_length', array( $this, 'excerpt_length' ), 1000 );
					remove_filter( 'excerpt_more', array( $this, 'excerpt_more' ), 1000 );
					$this->excerpt_length = 0;
					$excerpt              = html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' );
				}
			} else {
				// Then we are using excerpt.
				$excerpt = $post->post_excerpt;
				$excerpt = html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' );
				if ( $trim_excerpt ) {
					$excerpt = wp_trim_words( $excerpt, $this->excerpt_length );
				}
			}
		}
		return $excerpt;
	}
}
