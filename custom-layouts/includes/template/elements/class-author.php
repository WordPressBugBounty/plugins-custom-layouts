<?php
/**
 * Handles the frontend display + data of the author
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
 * Renders the author
 */
class Author extends Element_Base {

	private $post;

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$show_image      = $instance_data['showImage'];
		$image_style     = $instance_data['imageStyle'];
		$display_mode    = $instance_data['displayMode'];
		$image_position  = $instance_data['imagePosition'];
		$link_to_archive = $instance_data['linkToArchive'];
		$output          = '';
		$data            = $this->get_data( $post );
		$output          = '';

		// ob_start();

		$image  = false;
		$author = false;
		if ( $show_image === 'yes' ) {

			ob_start();
			?>
			<div class ='cl-element-author__image cl-element-author__image--<?php echo sanitize_key( $image_style ); ?>'>
				<img src="<?php echo esc_url( $data['avatar_url'] ); ?>" />
			</div>
			<?php
			$image = ob_get_clean();
		}
		if ( 'yes' === $link_to_archive ) {
			$author_link = get_author_posts_url( $post->post_author );
			$author      = '<a href="' . esc_url( $author_link ) . '" class="cl-element-author__text">' . esc_html( $data[ $display_mode ] ) . '</a>';
		} else {
			$author = '<div class="cl-element-author__text">' . esc_html( $data[ $display_mode ] ) . '</div>';
		}

		$output = $author;
		if ( $image ) {
			if ( 'right' === $image_position ) {
				$output = $author . $image;
			} else {
				$output = $image . $author;
			}
		}

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( $return ) {
			return $output;
		}
		echo $output;
	}

	public function get_data( $post, $display_mode = false ) {
		// $post_content = wp_strip_all_tags( apply_filters( 'the_content', $post->post_content ) );
		$author_id   = $post->post_author;
		$author      = get_user_by( 'id', $author_id );
		$author_data = array(
			'id'           => $author->ID,
			'full_name'    => $author->user_firstname . ' ' . $author->user_lastname,
			'display_name' => $author->display_name,
			'first_name'   => $author->user_firstname,
			'last_name'    => $author->user_lastname,
			'nickname'     => get_user_meta( $author_id, 'nickname', true ),
			'avatar_url'   => get_avatar_url( $author_id ),
		);
		return $author_data;
	}

}
