<?php
/**
 * Handles the frontend display of the template
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

namespace Custom_Layouts\Template;

use Custom_Layouts\Settings;
use Custom_Layouts\Util;
use Custom_Layouts\Template\Elements\Title;
use Custom_Layouts\Template\Elements\Excerpt;
use Custom_Layouts\Template\Elements\Content;
use Custom_Layouts\Template\Elements\Author;
use Custom_Layouts\Template\Elements\Published_Date;
use Custom_Layouts\Template\Elements\Modified_Date;
use Custom_Layouts\Template\Elements\Custom_Field;
use Custom_Layouts\Template\Elements\Taxonomy;
use Custom_Layouts\Template\Elements\Link;
use Custom_Layouts\Template\Elements\Comment_Count;
use Custom_Layouts\Template\Elements\Text;
use Custom_Layouts\Template\Elements\Featured_Media;
use Custom_Layouts\Template\Elements\Post_Type;
use Custom_Layouts\Template\Elements\Section;
use stdClass;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core frontend class of a template.
 */
class Controller {

	private $id;
	protected $name = '';
	private $post;
	protected $settings   = array();
	private $data_type    = '';
	private $data_source  = '';
	protected $input_type = '';
	protected $query      = array();
	protected $elements;

	private $has_global_post_copy = false;
	private $last_global_post;

	private $has_init = false;
	private $values;

	private $attributes = array();

	public function __construct( $id ) {
		$this->init( $id );
	}

	/**
	 * Init...
	 *
	 * @since    1.0.0
	 */
	private function init( $id ) {

		$this->id       = $id;
		$this->id       = apply_filters( 'custom-layouts/template/id', $this->id );
		$this->settings = Settings::get_template_data( $this->id );
		// $this->data_type = $this->settings['data_type'];

		// init elements
		$this->elements                 = new stdClass();
		$this->elements->title          = new Title();
		$this->elements->excerpt        = new Excerpt();
		$this->elements->content        = new Content();
		$this->elements->author         = new Author();
		$this->elements->published_date = new Published_Date();
		$this->elements->modified_date  = new Modified_Date();
		$this->elements->custom_field   = new Custom_Field();
		$this->elements->taxonomy       = new Taxonomy();
		$this->elements->post_type      = new Post_Type();
		$this->elements->link           = new Link();
		$this->elements->comment_count  = new Comment_Count();
		$this->elements->text           = new Text();
		$this->elements->featured_media = new Featured_Media();
		$this->elements->section        = new Section();

		// Set init to true, the following functions require it.
		$this->has_init = true;

		// Attributes needs has_init to be true so we can use `get_values`.
		$this->set_attributes();

		// Add user defined custom classes.
		// $this->add_class( $this->settings['add_class'] );
	}
	// Temporarily override the global $post, with the current post, so that  template
	// functions like `get_the_permalink()` continue  to work in hooks attached to `read more`
	protected function set_global_post() {
		if ( ! $this->has_global_post_copy ) {
			global $post;
			$this->last_global_post     = $post;
			$this->has_global_post_copy = true;
			$post                       = $this->post;
		}
	}

	protected function revert_global_post() {
		if ( $this->has_global_post_copy ) {
			global $post;
			$post = $this->last_global_post;
			unset( $this->last_global_post );
			$this->has_global_post_copy = false;
		}
	}
	public function element( $element_id ) {

		if ( isset( $this->elements->{ $element_id } ) ) {
			return $this->elements->{ $element_id };
		}
		return false;
	}

	public function set_post( $post ) {
		// using get_post will ensure we are using a valid post object, using either ID or post object
		// $this->post = get_post( $post );
		$this->post = $post; // better performance...
	}

	private function set_attribute_background_image_style() {
		// add background image
		$background_image_source = isset( $this->settings['template']['backgroundImageSource'] ) ? $this->settings['template']['backgroundImageSource'] : 'none';

		if ( $background_image_source === 'featured_image' ) {
			$image_size         = isset( $this->settings['template']['imageSourceSize'] ) ? $this->settings['template']['imageSourceSize'] : 'medium';
			$post_attachment_id = get_post_thumbnail_id( $this->post );
			if ( $post_attachment_id ) {
				$attachment_meta = Util::get_image_by_size( $post_attachment_id, $image_size );
				if ( $attachment_meta ) {
					$image_url                 = $attachment_meta['url'];
					$this->attributes['style'] = 'background-image:url(' . esc_url( $image_url ) . ');';

				}
			}
		}
	}
	private function set_attributes() {
		$base_class  = 'cl-template';
		$type_class  = ' cl-template--post';
		$image_class = ' ' . $this->get_image_postion_class();
		$id_class    = '';
		if ( $this->id ) {
			$id_class = ' cl-template--id-' . $this->id;
		} else {
			$id_class = ' cl-template--id-0'; // then it must be "default" (which has its own css)
		}
		$this->attributes['class'] = $base_class . $type_class . $id_class . $image_class;
	}

	private function get_image_postion_class() {
		// image wrapper classes
		$show_featured_image = isset( $this->settings['template']['showFeaturedImage'] ) ? $this->settings['template']['showFeaturedImage'] : 'no';
		$image_position      = isset( $this->settings['template']['imagePosition'] ) ? $this->settings['template']['imagePosition'] : 'top';

		$display_elements = array();
		$post_image_class = '';
		if ( $show_featured_image === 'yes' ) {

			$image_positions = array( 'top', 'right', 'bottom', 'left' );
			if ( in_array( $image_position, $image_positions ) ) {
				$post_image_class = 'cl-template--image-' . $image_position;
			}
		}
		return $post_image_class;
	}

	protected function has_init() {
		if ( ! $this->has_init ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'If you are extending the Template constructor, make sure to call `parent::_construct()` at the top of the child constructor.', 'custom-layouts' ), '1.0.0' );
			return false;
		}
		return true;
	}

	protected function add_class( $class_names ) {

		if ( ! $this->has_init() ) {
			return;
		}

		if ( empty( $class_names ) ) {
			return;
		}

		$this->attributes['class'] .= ' ' . $class_names;
	}
	protected function add_attribute( $attribute_name, $attribute_value ) {

		if ( ! $this->has_init() ) {
			return;
		}

		$this->attributes[ $attribute_name ] = $attribute_value;
	}

	protected function get_attributes() {

		if ( ! $this->has_init() ) {
			return array();
		}
		$this->set_attribute_background_image_style();

		return $this->attributes;

	}

	protected function get_values() {

		if ( ! $this->has_init() ) {
			return;
		}
		return $this->values;
	}

	/**
	 * Display the HTML output of the template
	 *
	 * @since    1.0.0
	 */
	public function render( $post, $return = false ) {

		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}
		$this->set_post( $post );
		$this->set_global_post();

		if ( ! $this->has_init() ) {
			return '';
		}

		// We don't want to modify the internal values.
		$settings = $this->settings;

		// Trigger action when starting.
		do_action( 'custom-layouts/template/before_render', $settings, $this->name );

		// Modify args before render.
		// 90% of the args can't be modified, because they are used to compile the CSS on save..
		// having this in here now would be very confusing
		// $settings = apply_filters( 'custom-layouts/template/render_args', $settings, $this->name );

		// TODO: store rendered output as transients, based on $settings + $post_id values
		ob_start();
		echo '<div ' . Util::get_attributes_html( $this->get_attributes() ) . '>';
		echo $this->build( $settings );
		echo '</div>';
		$output = ob_get_clean();

		// Modify output html.
		$output = apply_filters( 'custom-layouts/template/render_output', $output, $this->name, $settings );

		// Trigger action when finished.
		do_action( 'custom-layouts/template/after_render', $settings, $this->name );

		$this->revert_global_post();
		if ( ! $return ) {
			echo $output;
		}

		if ( $return ) {
			return $output;
		}
	}

	/*
	public function get_setting( $setting_name = false ) {

		if ( ! $this->has_init() ) {
			return false;
		}

		if ( ! $setting_name ){
			return false;
		}

		if ( isset( $this->settings[ $setting_name ] ) ) {
			return $this->settings[ $setting_name ];
		}

		return false;
	}*/

	public function validate_settings( $settings ) {
		if ( ( ! isset( $settings['instances'] ) ) || ( ! isset( $settings['instance_order'] ) ) || ( ! isset( $settings['template'] ) ) ) {
			return false;
		}

		if ( ( ! is_array( $settings['instances'] ) ) || ( ! is_array( $settings['instance_order'] ) ) || ( ! is_array( $settings['template'] ) ) ) {
			return false;
		}

		return true;
	}
	/**
	 * The main function that constructs the main part of the template,
	 *
	 * @since    1.0.0
	 */
	public function build( $settings ) {

		if ( ! $this->has_init() ) {
			return '';
		}

		if ( ! $this->validate_settings( $settings ) ) {
			return '';
		}

		$instances      = $settings['instances'];
		$instance_order = $settings['instance_order'];
		$template       = $settings['template'];

		ob_start();
		foreach ( $instance_order as $instance_id ) {
			if ( isset( $instances[ $instance_id ] ) ) {
				$this->render_instance( $instances[ $instance_id ], $template );
			}
		}
		$elements_list = ob_get_clean();

		ob_start();
		$this->render_instance( $instances['section'], $template, $elements_list ); // Add the elements list to the section
		$section = ob_get_clean();
		$output  = $section;

		$show_featured_image = $template['showFeaturedImage'];

		if ( 'yes' === $show_featured_image ) {

			$image_postion = $template['imagePosition'];

			if ( 'background' !== $image_postion ) {
				ob_start();
				$this->render_instance( $instances['featured_media'], $template, false, false ); // render the featured media
				$featured_media = ob_get_clean();

				$first_positions = array( 'top', 'left' );
				if ( in_array( $image_postion, $first_positions ) ) {
					$output = $featured_media . $section;
				} else {
					$output = $section . $featured_media;
				}
			}
		}

		return $output;

	}


	public function render_instance( $instance, $template_data, $children = false, $render_if_empty = true ) {

		$element_id = $instance['elementId'];

		if ( $children ) {
			$element_output = $this->element( $element_id )->render( $this->post, $instance, $template_data, $children, true );
		} else {
			$element_output = $this->element( $element_id )->render( $this->post, $instance, $template_data, true );
		}

		if ( ! $render_if_empty ) {
			if ( empty( $element_output ) ) {
				return;
			}
		}

		echo $element_output;
	}

}
