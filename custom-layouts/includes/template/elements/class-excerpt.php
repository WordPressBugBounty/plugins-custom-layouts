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

	private $post;
	private $excerpt_length = 10;

	public function excerpt_length() {
		return $this->excerpt_length;
	}
	public function excerpt_more( $excerpt ) {

		if ( $this->hide_read_more ) {
			return '';
		}
		return $excerpt;
	}

	public function render( $post, $instance, $template, $return = false ) {

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

		if ( $return ) {
			return $output;
		}
		echo $output;
	}



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

			// first check to see if a post has an excerpt (so we know if we're using )
			// content as fall back
			if ( isset( $post->post_excerpt ) && $post->post_excerpt === '' ) {

				if ( $hide_read_more ) {
					// we want to keep the default ellipsis, but not add the text...
					// so the best way, is just to pull the full excerpt, and
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
				// then we are using excerpt
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
