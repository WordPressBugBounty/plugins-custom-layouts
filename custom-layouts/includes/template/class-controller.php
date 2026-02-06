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

	/**
	 * The template ID.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * The template name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * The WordPress post object for this template.
	 *
	 * @var \WP_Post|null
	 */
	private $post;

	/**
	 * Processed template settings.
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * The data type for this template.
	 *
	 * @var string
	 */
	private $data_type = '';

	/**
	 * The data source for this template.
	 *
	 * @var string
	 */
	private $data_source = '';

	/**
	 * The input type for this template.
	 *
	 * @var string
	 */
	protected $input_type = '';

	/**
	 * Query parameters for this template.
	 *
	 * @var array
	 */
	protected $query = array();

	/**
	 * Template elements array.
	 *
	 * @var object
	 */
	protected $elements;

	/**
	 * Whether a global post copy has been made.
	 *
	 * @var bool
	 */
	private $has_global_post_copy = false;

	/**
	 * Copy of the last global post.
	 *
	 * @var \WP_Post|null
	 */
	private $last_global_post;

	/**
	 * Whether the template has been initialized.
	 *
	 * @var bool
	 */
	private $has_init = false;

	/**
	 * Template values.
	 *
	 * @var mixed
	 */
	private $values;

	/**
	 * HTML attributes for the template container.
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Constructor - initializes the template controller.
	 *
	 * @since    1.0.0
	 * @param    int $id  The template ID.
	 */
	public function __construct( $id ) {
		$this->init( $id );
	}

	/**
	 * Initializes the template controller with settings and elements.
	 *
	 * @since    1.0.0
	 * @param    int $id  The template ID.
	 */
	private function init( $id ) {

		$this->id       = $id;
		$this->id       = apply_filters( 'custom-layouts/template/id', $this->id );
		$this->settings = Settings::get_template_data( $this->id );
		// $this->data_type = $this->settings['data_type'];

		// Init elements.
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

	/**
	 * Temporarily override the global $post with the current post.
	 *
	 * This ensures template functions like get_the_permalink() continue to work
	 * in hooks attached to read more links.
	 *
	 * @since    1.0.0
	 */
	protected function set_global_post() {
		if ( ! $this->has_global_post_copy ) {
			global $post;
			$this->last_global_post     = $post;
			$this->has_global_post_copy = true;
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentional temporary override for template rendering.
			$post                       = $this->post;
		}
	}

	/**
	 * Reverts the global $post to its previous value.
	 *
	 * @since    1.0.0
	 */
	protected function revert_global_post() {
		if ( $this->has_global_post_copy ) {
			global $post;
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentional restore after template rendering.
			$post = $this->last_global_post;
			unset( $this->last_global_post );
			$this->has_global_post_copy = false;
		}
	}

	/**
	 * Gets an element by its ID.
	 *
	 * @since    1.0.0
	 * @param    string $element_id  The element ID.
	 * @return   object|false        The element object or false if not found.
	 */
	public function element( $element_id ) {

		if ( isset( $this->elements->{ $element_id } ) ) {
			return $this->elements->{ $element_id };
		}
		return false;
	}

	/**
	 * Sets the current post for the template.
	 *
	 * @since    1.0.0
	 * @param    object $post  The post object.
	 */
	public function set_post( $post ) {
		// Using get_post will ensure we are using a valid post object, using either ID or post object.
		// $this->post = get_post( $post );
		$this->post = $post; // Better performance.
	}

	/**
	 * Sets the background image style attribute.
	 *
	 * @since    1.0.0
	 */
	private function set_attribute_background_image_style() {
		// Add background image.
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

	/**
	 * Sets the template wrapper attributes.
	 *
	 * @since    1.0.0
	 */
	private function set_attributes() {
		$base_class  = 'cl-template';
		$type_class  = ' cl-template--post';
		$image_class = ' ' . $this->get_image_postion_class();
		$id_class    = '';
		if ( $this->id ) {
			$id_class = ' cl-template--id-' . $this->id;
		} else {
			$id_class = ' cl-template--id-0'; // Then it must be "default" (which has its own css).
		}
		$this->attributes['class'] = $base_class . $type_class . $id_class . $image_class;
	}

	/**
	 * Gets the CSS class for the image position.
	 *
	 * @since    1.0.0
	 * @return   string  The image position class.
	 */
	private function get_image_postion_class() {
		// Image wrapper classes.
		$show_featured_image = isset( $this->settings['template']['showFeaturedImage'] ) ? $this->settings['template']['showFeaturedImage'] : 'no';
		$image_position      = isset( $this->settings['template']['imagePosition'] ) ? $this->settings['template']['imagePosition'] : 'top';

		$display_elements = array();
		$post_image_class = '';
		if ( $show_featured_image === 'yes' ) {

			$image_positions = array( 'top', 'right', 'bottom', 'left' );
			if ( in_array( $image_position, $image_positions, true ) ) {
				$post_image_class = 'cl-template--image-' . $image_position;
			}
		}
		return $post_image_class;
	}

	/**
	 * Checks if the template has been initialized.
	 *
	 * @since    1.0.0
	 * @return   bool  True if initialized, false otherwise.
	 */
	protected function has_init() {
		if ( ! $this->has_init ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'If you are extending the Template constructor, make sure to call `parent::_construct()` at the top of the child constructor.', 'custom-layouts' ), '1.0.0' );
			return false;
		}
		return true;
	}

	/**
	 * Adds custom CSS classes to the template wrapper.
	 *
	 * @since    1.0.0
	 * @param    string $class_names  Space-separated class names.
	 */
	protected function add_class( $class_names ) {

		if ( ! $this->has_init() ) {
			return;
		}

		if ( empty( $class_names ) ) {
			return;
		}

		$this->attributes['class'] .= ' ' . $class_names;
	}

	/**
	 * Adds a custom attribute to the template wrapper.
	 *
	 * @since    1.0.0
	 * @param    string $attribute_name   The attribute name.
	 * @param    string $attribute_value  The attribute value.
	 */
	protected function add_attribute( $attribute_name, $attribute_value ) {

		if ( ! $this->has_init() ) {
			return;
		}

		$this->attributes[ $attribute_name ] = $attribute_value;
	}

	/**
	 * Gets all template wrapper attributes.
	 *
	 * @since    1.0.0
	 * @return   array  Array of attributes.
	 */
	protected function get_attributes() {

		if ( ! $this->has_init() ) {
			return array();
		}
		$this->set_attribute_background_image_style();

		return $this->attributes;
	}

	/**
	 * Gets the template values.
	 *
	 * @since    1.0.0
	 * @return   mixed  The template values or null if not initialized.
	 */
	protected function get_values() {

		if ( ! $this->has_init() ) {
			return;
		}
		return $this->values;
	}

	/**
	 * Display the HTML output of the template.
	 *
	 * @since    1.0.0
	 * @param    int|object $post           The post ID or post object.
	 * @param    bool       $return_output  Whether to return the output or echo it.
	 * @return   string|void                The rendered output if $return_output is true.
	 */
	public function render( $post, $return_output = false ) {

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
		// 90% of the args can't be modified, because they are used to compile the CSS on save
		// having this in here now would be very confusing.
		// $settings = apply_filters( 'custom-layouts/template/render_args', $settings, $this->name );

		// TODO: store rendered output as transients, based on $settings + $post_id values.
		ob_start();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped by Util::get_attributes_html()
		echo '<div ' . Util::get_attributes_html( $this->get_attributes() ) . '>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped in build() method
		echo $this->build( $settings );
		echo '</div>';
		$output = ob_get_clean();

		// Modify output html.
		$output = apply_filters( 'custom-layouts/template/render_output', $output, $this->name, $settings );

		// Trigger action when finished.
		do_action( 'custom-layouts/template/after_render', $settings, $this->name );

		$this->revert_global_post();
		if ( ! $return_output ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped and filtered
			echo $output;
		}

		if ( $return_output ) {
			return $output;
		}
	}

	/**
	 * Validates the template settings structure.
	 *
	 * @since    1.0.0
	 * @param    array $settings  The settings array to validate.
	 * @return   bool             True if valid, false otherwise.
	 */
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
	 * The main function that constructs the main part of the template.
	 *
	 * @since    1.0.0
	 * @param    array $settings  The template settings.
	 * @return   string           The built template HTML.
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
		$this->render_instance( $instances['section'], $template, $elements_list ); // Add the elements list to the section.
		$section = ob_get_clean();
		$output  = $section;

		$show_featured_image = $template['showFeaturedImage'];

		if ( 'yes' === $show_featured_image ) {

			$image_postion = $template['imagePosition'];

			if ( 'background' !== $image_postion ) {
				ob_start();
				$this->render_instance( $instances['featured_media'], $template, false, false ); // Render the featured media.
				$featured_media = ob_get_clean();

				$first_positions = array( 'top', 'left' );
				if ( in_array( $image_postion, $first_positions, true ) ) {
					$output = $featured_media . $section;
				} else {
					$output = $section . $featured_media;
				}
			}
		}

		return $output;
	}

	/**
	 * Renders a template element instance.
	 *
	 * @since    1.0.0
	 * @param    array $instance         The instance data.
	 * @param    array $template_data    The template data.
	 * @param    mixed $children         The child elements.
	 * @param    bool  $render_if_empty  Whether to render if output is empty.
	 */
	public function render_instance( $instance, $template_data, $children = false, $render_if_empty = true ) {

		$element_id = $instance['elementId'];
		$element    = $this->element( $element_id );

		if ( ! $element ) {
			return;
		}

		if ( $children ) {
			$element_output = $element->render( $this->post, $instance, $template_data, $children, true );
		} else {
			$element_output = $element->render( $this->post, $instance, $template_data, true );
		}

		if ( ! $render_if_empty ) {
			if ( empty( $element_output ) ) {
				return;
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is pre-escaped by element render() method
		echo $element_output;
	}
}
