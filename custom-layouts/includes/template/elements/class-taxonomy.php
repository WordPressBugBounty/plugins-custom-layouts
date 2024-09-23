<?php
/**
 * Handles the frontend display + data of the taxonomy
 *
 * @link       http://codeamp.com
 * @since      1.3.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

namespace Custom_Layouts\Template\Elements;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Core\Validation;
use Custom_Layouts\Settings;
use Custom_Layouts\Util;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the taxonomy element
 */
class Taxonomy extends Element_Base {

	public function render( $post, $instance, $template, $return = false ) {

		$instance_data = $instance['data'];
		$element_type  = $instance['elementId'];

		parent::run_pre_render_hooks( $element_type, $instance_data, $post, $template );

		$this->post = $post;

		$taxonomies     = $instance_data['taxonomies'];
		$before_text    = $instance_data['beforeText'];
		$after_text     = $instance_data['afterText'];
		$seperator_text = $instance_data['seperatorText'];
		$term_order_by  = $instance_data['termOrderBy'];
		$term_order_dir = $instance_data['termOrderDir'];
		// $restrict_terms = $instance_data['restrictTerms'];
		// $max_number = $restrict_terms === 'yes' ? $instance_data['maxTerms'] : 0;
		$max_number = $instance_data['maxTerms'];

		if ( ! is_array( $taxonomies ) ) {
			return '';
		}

		if ( count( $taxonomies ) === 0 ) {
			return '';
		}
		$term_tag        = 'div';
		$term_attributes = array(
			'class' => 'cl-element-taxonomy__term',
		);
		if ( $instance_data['linkToArchives'] === 'yes' ) {
			$term_tag = 'a';
		}

		$post_terms = $this->get_data( $post, $taxonomies, $term_order_by, $term_order_dir, $max_number );

		// $output = '';
		$terms_output = array();
		foreach ( $post_terms as $post_term ) {
			$term_attributes['href'] = '';
			if ( $instance_data['linkToArchives'] === 'yes' ) {
				$term_attributes['href'] = get_term_link( absint( $post_term['term_id'] ) );
			}
			ob_start();
			?>
			<<?php echo $term_tag; ?> <?php echo Util::get_attributes_html( $term_attributes ); ?>>
				<?php echo esc_html( $post_term['name'] ); ?>
			</<?php echo $term_tag; ?>>
			<?php
			$term_output = ob_get_clean();
			array_push( $terms_output, trim( $term_output ) );
		}
		$output = implode( esc_html( $seperator_text ), $terms_output );

		if ( $before_text !== '' || $after_text !== '' ) {
			$output = esc_html( $before_text ) . '<div class="cl-element-taxonomy__terms">' . $output . '</div>' . esc_html( $after_text );
		}

		$output = $this->wrap_container( $output, $instance );

		$output = parent::run_post_render_hooks( $output, $element_type, $instance_data, $post, $template );

		if ( ! $return ) {
			echo $output;
		}

		if ( $return ) {
			return $output;
		}
		echo $output;

	}

	public function get_data( $post, $taxonomies, $order_by = 'name', $order_dir = 'asc', $max_number = 0 ) {
		$args = array(
			'taxonomy'   => $taxonomies,
			'object_ids' => $post->ID,
			'orderby'    => $order_by,
			'order'      => strtoupper( $order_dir ),
		);

		$max_number = absint( $max_number );
		if ( 0 !== $max_number ) {
			$args['number'] = $max_number;
		}

		$term_result      = new \WP_Term_Query( $args );
		$taxonomies_terms = array();
		if ( $term_result->terms && is_array( $term_result->terms ) ) {
			foreach ( $term_result->terms as $taxonomy_term ) {

				array_push(
					$taxonomies_terms,
					array(
						'name'    => $taxonomy_term->name,
						'slug'    => $taxonomy_term->slug,
						'term_id' => $taxonomy_term->term_id,
						'count'   => $taxonomy_term->count,
					)
				);
			}
		}
		return $taxonomies_terms;
	}


	public function get_css( $instance, $template_class, $template = array() ) {
		$instance_class  = $this->get_instance_class( $instance['id'] );
		$html_tag        = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';
		$parent_selector = $template_class . ' ' . $html_tag . $instance_class;
		$child_selector  = '.cl-element-taxonomy__term'; // anchor selector

		// parent node
		$css  = '/* ' . $instance['elementId'] . ' */';
		$css .= $this->create_container_css( $parent_selector, $instance['data'] );

		// child / inline node
		$full_child_selector = $template_class . ' ' . $html_tag . $instance_class . ' ' . $child_selector;
		$css                .= $full_child_selector . '{';

		$instance_data = $instance['data'];
		$term_styles   = array(
			'textColor'           => $instance_data['termTextColor'],
			'backgroundColor'     => $instance_data['termBackgroundColor'],
			'backgroundGradient'  => isset( $instance_data['termBackgroundGradient'] ) ? $instance_data['termBackgroundGradient'] : '',

			'marginSize'          => $instance_data['termMarginSize'],
			'paddingSize'         => $instance_data['termPaddingSize'],
			'borderRadius'        => $instance_data['termBorderRadius'],
			'borderWidth'         => $instance_data['termBorderWidth'],

			'fontFormatBold'      => $instance_data['termFontFormatBold'],
			'fontFormatItalic'    => $instance_data['termFontFormatItalic'],
			'fontFormatUnderline' => $instance_data['termFontFormatUnderline'],
			'lineHeight'          => $instance_data['termLineHeight'],

			'fontSize'            => $instance_data['termFontSize'],
			'fontFamily'          => $instance_data['termFontFamily'],
		);

		$css .= CSS_Loader::parse_css_settings( $term_styles );
		$css .= '}';

		if ( $instance_data['linkToArchives'] === 'yes' ) {
			// now add styles to link hover
			$hover_styles = array(
				'fontFormatBold'      => $instance['data']['termFontFormatBoldHover'],
				'fontFormatItalic'    => $instance['data']['termFontFormatItalicHover'],
				'fontFormatUnderline' => $instance['data']['termFontFormatUnderlineHover'],
				'backgroundColor'     => isset( $instance['data']['termBackgroundColor'] ) ? $instance['data']['termBackgroundColor'] : '',
				'textColor'           => isset( $instance['data']['textColor'] ) ? $instance['data']['textColor'] : '',
			);
			$css         .= $full_child_selector . ':hover, ' . $full_child_selector . ':active, ' . $full_child_selector . ':focus {';
			$css         .= CSS_Loader::parse_css_settings( $hover_styles );
			$css         .= '}';

		}

		return $css;
	}
}
