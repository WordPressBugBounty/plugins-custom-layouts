<?php
namespace Custom_Layouts;

use Custom_Layouts\Settings;
use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Integrations\Gutenberg;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	const ADMIN_INPUT_PREFIX = 'custom_layouts_';

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The stored value after checking if the current admin screen is a valid S&F admin screen
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $is_custom_layouts_admin_screen = -1;
	private $is_custom_layouts_edit_screen  = -1;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name           = $plugin_name;
		$this->version               = $version;
		$this->should_regenerate_css = false;

		add_action( 'save_post_cl-layout', array( $this, 'save_post_layout' ), 20, 2 );
		add_action( 'save_post_cl-template', array( $this, 'save_post_template' ), 20, 2 );
		add_action( 'delete_post', array( $this, 'delete_post_template' ), 20, 2 );
		add_action( 'wp_trash_post', array( $this, 'delete_post_template' ), 20, 2 );
		add_action( 'shutdown', array( $this, 'shutdown' ), 20, 2 );
		add_filter( 'redirect_post_location', array( $this, 'redirect_post_location' ), 20, 2 );

		// add custom columns to layout edit
		add_filter( 'manage_edit-cl-layout_columns', array( $this, 'set_custom_layout_columns' ) );
		add_action( 'manage_cl-layout_posts_custom_column', array( $this, 'custom_layout_column' ), 10, 2 );
	}

	/**
	 * Add column to admin posts page for Layouts
	 *
	 * @since    1.4.0
	 */
	function set_custom_layout_columns( $columns ) {
		$column  = array(
			'template' => __( 'Template', 'custom-layouts' ),
		);
		$columns = $this->insert_value_at( $columns, $column, 2 );
		return $columns;
	}

	/**
	 * Insert value at a position in an assoc array
	 *
	 * @since    1.4.0
	 */
	function insert_value_at( $arr, $insert, $position ) {
		// TODO - move into a utility class
		$i         = 0;
		$new_array = array();
		foreach ( $arr as $key => $value ) {
			if ( $i == $position ) {
				foreach ( $insert as $ikey => $ivalue ) {
					$new_array[ $ikey ] = $ivalue;
				}
			}
			$new_array[ $key ] = $value;
			$i++;
		}
		return $new_array;
	}

	function custom_layout_column( $column, $post_id ) {
		switch ( $column ) {
			case 'template':
				$layout_settings = Settings::get_section_data( $post_id, 'layout' );

				if ( isset( $layout_settings['template_id'] ) ) {
					if ( $layout_settings['template_id'] === 'default' ) {
						echo 'Default';
					} else {
						echo '<a href="' . esc_url( get_edit_post_link( $layout_settings['template_id'] ) ) . '">' . get_the_title( $layout_settings['template_id'] ) . '</a>';
					}
				}
				break;
		}
	}
	/**
	 * Regenerate the css if necessary, do it only once on shutdown
	 *
	 * @since    1.0.0
	 */
	public function shutdown() {
		if ( $this->should_regenerate_css ) {
			CSS_Loader::save_css();
		}
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$css_file_ext = Util::get_file_ext( '.css' );

		if ( $this->is_custom_layouts_settings() ) {
			wp_enqueue_style( 'wp-components' );
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'custom-layouts-admin', CUSTOM_LAYOUTS_URL . 'assets/css/admin/custom-layouts' . $css_file_ext, array( 'wp-components', 'wp-editor-font', 'wp-block-editor' ), CUSTOM_LAYOUTS_VERSION, 'all' );
			return;
		}
		if ( ! $this->is_custom_layouts_edit_screen() ) {
			return;
		}

		// Fake trigger loading of styles.
		do_action( 'enqueue_block_editor_assets' );
	}

	/**
	 * Update the URL after saving to include the last section the user
	 * was viewing
	 *
	 * @param object $location  A string with the redirect URL.
	 * @param int    $post_id   The post ID that has been updated.
	 *
	 * @since    1.0.0
	 */
	public function redirect_post_location( $location, $post_id ) {

		$last_tab = esc_attr( $this->get_post_last_tab( $post_id ) );
		$this->remove_post_last_tab( $post_id );

		return add_query_arg( 'section', $last_tab, $location );
	}

	/**
	 * Add the admin pages
	 *
	 * @since    1.0.0
	 */

	public function admin_pages() {

		// $icon = "dashicons-search";
		// add_menu_page( 'Custom Layouts', 'Custom Layouts', 'manage_options', 'custom-layouts', array($this, 'custom_layouts_page'), $icon, '100.23243'  );
	}

	public function admin_pages_more_menu_items() {

		$icon = 'dashicons-grid-view';

		// TODO - remove the base64 - and just encode the result
		$icon = 'data:image/svg+xml;base64,' . base64_encode(
			'<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 20 20">
  <path
  style="fill:#a0a5aa;fill-opacity:1;"
     d="M 3.7408088,2 C 2.7755426,2 2,2.7755427 2,3.7408088 v 2.5187868 c 0,0.965266 0.7755427,1.7408088 1.7408088,1.7408088 h 3.5187868 c 0.9652661,0 1.7409667,-0.7755428 1.7408089,-1.7408088 L 8.9999927,3.7408088 C 8.9998349,2.7755426 8.2248618,2 7.2595956,2 Z"
     id="path1187" />
  <path
  style="fill:#a0a5aa;fill-opacity:1;"
     d="M 3.7408088,10.001478 C 2.7755427,10.001413 2,10.777256 2,11.742522 v 4.51667 C 2,17.224458 2.7755427,18 3.7408088,18 h 3.5187868 c 0.9652661,0 1.7408089,-0.775542 1.7408089,-1.740808 v -4.51667 c 0,-0.965266 -0.7755428,-1.740744 -1.7408089,-1.740809 z m 0,0.941412 h 3.5187868 c 0.5170867,0 0.7996324,0.282546 0.7996324,0.799632 v 4.51667 c 0,0.517088 -0.2825457,0.799632 -0.7996325,0.799632 H 3.7408088 c -0.5170868,0 -0.7996323,-0.282544 -0.7996323,-0.799632 v -4.51667 c 0,-0.517086 0.2825456,-0.799632 0.7996323,-0.799632 z"
     id="path1195" />
  <path
  style="fill:#a0a5aa;fill-opacity:1;"
     d="m 12.741107,11.999595 c -0.965267,0 -1.740809,0.775544 -1.740809,1.740809 v 2.518788 C 11.000298,17.224457 11.77584,18 12.741107,18 h 3.517787 c 0.965266,0 1.740808,-0.775543 1.740808,-1.740808 v -2.518788 c 0,-0.965265 -0.775542,-1.740809 -1.740808,-1.740809 z"
     id="path1199" />
  <path
  style="fill:#a0a5aa;fill-opacity:1;"
     d="M 12.741107,2 C 11.77584,2 11.000298,2.7755431 11.000298,3.7408088 v 4.517787 c 0,0.9652644 0.775542,1.7408084 1.740809,1.7408084 h 3.517787 c 0.965266,0 1.740808,-0.775544 1.740808,-1.7408084 V 3.7408088 C 17.999702,2.7755431 17.22416,2 16.258894,2 Z m 0,0.9411765 h 3.517787 c 0.517088,0 0.799632,0.282545 0.799632,0.7996323 v 4.517787 c 0,0.5170868 -0.282544,0.7996314 -0.799632,0.7996314 h -3.517787 c -0.517088,0 -0.799632,-0.2825446 -0.799632,-0.7996314 v -4.517787 c 0,-0.5170873 0.282544,-0.7996323 0.799632,-0.7996323 z"
     id="path1191" />
</svg>
'
		);
		add_menu_page( __( 'Custom Layouts', 'custom-layouts' ), __( 'Custom Layouts', 'custom-layouts' ), 'manage_options', 'custom-layouts', array( $this, 'custom_layouts_page' ), $icon, '100.23242' );
		add_submenu_page( 'custom-layouts', 'Settings', 'Settings', 'manage_options', 'custom-layouts-settings', array( $this, 'custom_layouts_settings_page' ) );
	}

	/**
	 * The main settings page
	 *
	 * @since    1.0.0
	 */
	public function custom_layouts_settings_page() {
		include 'admin/settings-page.php';
	}
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$file_ext = Util::get_file_ext( '.js' );
		if ( $this->is_custom_layouts_settings() ) {
			$preload_paths = array(
				'/custom-layouts/v1/layout/info',
			);
			$this->preload_api_requests( $preload_paths );

			wp_set_script_translations( $this->plugin_name . '-settings', 'custom-layouts' );
			wp_enqueue_script( $this->plugin_name . '-settings', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin/settings/custom-layouts' . $file_ext, array( 'wp-element', 'wp-components', 'wp-date', 'wp-compose', 'wp-data', 'wp-editor', 'wp-edit-post', 'wp-api-fetch', 'wp-plugins' ), $this->version, true );
			return;
		} elseif ( $this->is_custom_layouts_edit_screen() ) {

			global $post;
			$post_id = $post->ID;
			$js_vars = array(
				'homeUrl'  => esc_url_raw( home_url( '/' ) ),
				'api'      => array(
					'url'   => esc_url_raw( rest_url( 'custom-layouts/v1/' ) ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				),
				'settings' => array(),
				'data'     => array(),

				'meta'     => array(
					'id' => $post_id,
				),
			);

			// script dependency list - https://developer.wordpress.org/block-editor/contributors/develop/scripts/

			if ( Util::screen_is_layout_edit() ) {
				wp_set_script_translations( $this->plugin_name . '-layout', 'custom-layouts' );
				wp_enqueue_script( $this->plugin_name . '-layout', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin/layout/custom-layouts' . $file_ext, array( 'wp-element', 'wp-components', 'wp-date', 'wp-compose', 'wp-data', 'wp-editor', 'wp-edit-post', 'wp-api-fetch' ), $this->version, true );

				$js_vars['settings']['query'] = Settings::get_settings_by_section( 'query', true );
				$js_vars['settings']['grid']  = Settings::get_settings_by_section( 'layout', true );

				$js_vars['data']['query'] = Settings::get_section_data( $post_id, 'query' );
				$js_vars['data']['grid']  = Settings::get_section_data( $post_id, 'layout' );

				$js_vars['editorSettings'] = $this->get_editor_settings();

				$preload_paths = array(
					'/custom-layouts/v1/wp/post_types',
					'/custom-layouts/v1/layout/info',
					'/custom-layouts/v1/wp/authors',
					'/custom-layouts/v1/templates/get',
					'/custom-layouts/v1/layout/results?layout_id=' . absint( $post_id ),
				);
				if ( isset( $js_vars['data']['grid']['template_id'] ) ) {
					$template_id = $js_vars['data']['grid']['template_id'];
					array_push( $preload_paths, '/custom-layouts/v1/template/get?id=' . absint( $template_id ) );
				}
				$this->preload_api_requests( $preload_paths );

				// TODO - refactor
				$layout_settings = array();
				if ( $js_vars['data']['query'] ) {
					$layout_settings = array_merge( $layout_settings, $js_vars['data']['query'] );
				}
				if ( $js_vars['data']['grid'] ) {
					$layout_settings = array_merge( $layout_settings, $js_vars['data']['grid'] );
				}
				$layout_defaults           = Settings::get_settings_defaults( array( 'layout', 'query' ) );
				$layout_settings           = wp_parse_args( $layout_settings, $layout_defaults );
				$js_vars['data']['layout'] = Gutenberg::map_attributes( 'php', $layout_settings );

				// $js_vars = apply_filters( 'custom-layouts/admin/js', $js_vars );
				wp_localize_script( $this->plugin_name . '-layout', 'customLayouts', $js_vars );
			} elseif ( Util::screen_is_template_edit() ) {
				$preload_paths = array(
					'/custom-layouts/v1/template/sources',
				);
				$this->preload_api_requests( $preload_paths );

				wp_set_script_translations( $this->plugin_name . '-template', 'custom-layouts' );
				wp_enqueue_script( $this->plugin_name . '-template', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin/template/custom-layouts' . $file_ext, array( 'wp-element', 'wp-components', 'wp-date', 'wp-compose', 'wp-data', 'wp-editor', 'wp-edit-post', 'wp-api-fetch' ), $this->version, true );
				$js_vars['data']['template'] = array(
					'instances'     => Settings::get_section_data( $post_id, 'template-instances' ),
					'instanceOrder' => Settings::get_section_data( $post_id, 'template-instance-order' ),
					'template'      => Settings::get_section_data( $post_id, 'template-data' ),
					'app'           => Settings::get_section_data( $post_id, 'app-data' ),
				);

				$js_vars['meta']['layouts'] = Settings::get_layouts_data();

				$js_vars['editorSettings'] = $this->get_editor_settings();

				// $js_vars = apply_filters( 'custom-layouts/admin/js', $js_vars );
				wp_localize_script( $this->plugin_name . '-template', 'customLayouts', $js_vars );
			}
		}
	}

	/**
	 * New method to load in theme CSS into our editor settings
	 * Adapted from - wp-includes/block-editor.php / get_block_editor_theme_styles()
	 */
	private static function get_editor_styles() {

		global $editor_styles;

		$styles = array();

		// Editor Styles.
		$styles[] = array(
			'css' => 'body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif }',
		);

		if ( $editor_styles && current_theme_supports( 'editor-styles' ) ) {
			foreach ( $editor_styles as $style ) {
				if ( preg_match( '~^(https?:)?//~', $style ) ) {
					$response = wp_remote_get( $style );
					if ( ! is_wp_error( $response ) ) {
						$styles[] = array(
							'css' => wp_remote_retrieve_body( $response ),
						);
					}
				} else {
					$file = get_theme_file_path( $style );
					if ( is_file( $file ) ) {
						$styles[] = array(
							'css'     => file_get_contents( $file ),
							'baseURL' => get_theme_file_uri( $style ),
						);
					}
				}
			}
		}
		return $styles;
	}



	public static function get_editor_settings() {

		$editor_settings = array();

		wp_enqueue_script( 'wp-format-library' );
		wp_enqueue_style( 'wp-format-library' );

		// Need this to trigger so we load theme block editor assets.
		// TODO - we have an issue with using this + Woocommerce...
		$custom_settings = array(
			'siteUrl' => site_url(),
			'styles'  => get_block_editor_theme_styles(),
		);
		$editor_settings = get_block_editor_settings( $custom_settings, null );

		// Non GB, lets just support color palette for now.
		if ( empty( $editor_settings['colors'] ) ) {
			$colors = get_theme_support( 'editor-color-palette' );
			if ( isset( $colors[0] ) ) {
				$editor_settings['colors'] = $colors[0];
			}
		}
		return $editor_settings;
	}

	/**
	 * Remove metaboxes in post edit screens, our post types are not used like regular post types, and therefor
	 * almost all third party metaboxes added to these screens are unwanted, leave just the core, our metaboxes,
	 * and languages -- this was true for S&F, but maybe not CL?
	 *
	 * @since    1.0.0
	 */
	public function remove_metaboxes() {

		global $wp_meta_boxes;

		if ( false === $this->is_custom_layouts_edit_screen() ) {
			return;
		}

		// todo - get post types from settings
		$cl_post_types = array( 'cl-layout', 'cl-template' );

		foreach ( $wp_meta_boxes as $meta_box_page => $meta_box ) {

			if ( in_array( $meta_box_page, $cl_post_types ) ) {

				foreach ( $wp_meta_boxes[ $meta_box_page ] as $context_name => $priority ) {

					foreach ( $priority as $priority_name => $meta_boxes ) {

						if ( $priority_name != 'core' ) {
							foreach ( $meta_boxes as $meta_box_name => $meta_box ) {
								remove_meta_box( $meta_box_name, $meta_box_page, $context_name );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Open the JS app container (wrap it around all content so 1 app can work across multiple metaboxes)
	 *
	 * @since    1.0.0
	 */
	public function admin_head() {

	}
	/**
	 * Close the JS app container in the footer
	 *
	 * @since    1.0.0
	 */
	public function admin_footer() {

	}
	/**
	 * Add metaboxes in post edit screens for editing content
	 *
	 * @since    1.0.0
	 */
	public function add_metaboxes() {

		global $wp_meta_boxes;

		if ( false === $this->is_custom_layouts_edit_screen() ) {
			return;
		}

		Settings::register();

		add_meta_box(
			'custom-layouts-layout',
			__( 'Layout Editor', 'custom-layouts' ),
			array( $this, 'render_layout_metabox' ),
			'cl-layout',
			'normal',
			'high'
		);
		add_meta_box(
			'custom-layouts-shortcode',
			__( 'Shortcode', 'custom-layouts' ),
			array( $this, 'render_layout_shortcode_metabox' ),
			'cl-layout',
			'side',
			'default'
		);

		add_meta_box(
			'custom-layouts-template',
			__( 'Post Template Editor', 'custom-layouts' ),
			array( $this, 'render_template_metabox' ),
			'cl-template',
			'normal',
			'high'
		);
		add_meta_box(
			'custom-layouts-shortcode',
			__( 'Shortcode', 'custom-layouts' ),
			array( $this, 'render_template_shortcode_metabox' ),
			'cl-template',
			'side',
			'default'
		);

	}

	public function render_query_metabox() {

		global $post;
		$post_id = $post->ID;
		echo "<div id='cl-admin-app-query'></div>";
	}
	public function render_layout_metabox() {

		global $post;
		$post_id = $post->ID;
		echo "<div id='cl-admin-app-layout'></div>";
	}
	public function render_layout_shortcode_metabox() {

		global $post;
		$post_id = $post->ID;
		?>
		<div id='cl-admin-app-shortcode'>
			<p><?php echo esc_html__( 'Use this shortcode to display the layout:', 'custom-layouts' ); ?></p>
			<input type='text' value='<?php echo esc_attr( "[custom-layout id='$post_id'] " ); ?>' style='width: 90%;' />
		</div>
		<?php
	}
	public function render_template_shortcode_metabox() {

		global $post;
		$post_id = $post->ID;
		?>
		<div id='cl-admin-app-shortcode'>
			<p><?php echo esc_html__( 'Use this shortcode to display a single instance of this template.', 'custom-layouts' ); ?></p>
			<p><?php echo esc_html__( 'Use the `post_id` argument to specify which post to display (must be Post ID).', 'custom-layouts' ); ?></p>
			<input type='text' value='<?php echo esc_attr( "[custom-template id='$post_id' post_id=''] " ); ?>' style='width: 90%;' />
		</div>
		<?php
	}
	public function render_template_metabox() {

		global $post;
		$post_id = $post->ID;
		echo "<div id='cla-app-template'></div>";
	}


	/**
	 * Checks to see if this is a S&F admin post edit screen, stores the result for reuse later
	 *
	 * @return bool|int
	 */
	private function is_custom_layouts_edit_screen() {

		if ( -1 === $this->is_custom_layouts_edit_screen ) {

			$current_screen               = get_current_screen();
			$valid_custom_layouts_screens = array( 'custom-layouts', 'cl-template', 'cl-layout' );

			if ( in_array( $current_screen->id, $valid_custom_layouts_screens ) ) {
				$this->is_custom_layouts_edit_screen = true;
			} else {
				$this->is_custom_layouts_edit_screen = false;
			}
		}

		return $this->is_custom_layouts_edit_screen;
	}
	/**
	 * Checks to see if this is a S&F admin settings screen
	 *
	 * @return bool|int
	 */
	private function is_custom_layouts_settings() {
		$current_screen = get_current_screen();

		if ( $current_screen->id === 'custom-layouts_page_custom-layouts-settings' ) {
			return true;
		}
		return false;
	}

	public function delete_post_template( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( $post_type === 'cl-template' ) {
			$this->should_regenerate_css = true;
		}
	}
	public function save_post_template( $post_id, $post ) {

		if ( ! $post ) {
			return;
		}

		$post_id = absint( $post_id );

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save for revisions or autosaves.
		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$this->save_template( $post_id );

	}
	public function save_post_layout( $post_id, $post ) {
		// $post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		$post_id = absint( $post_id );

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save for revisions or autosaves.
		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check user has permission to edit.
		/*
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}*/

		$this->save_layout( $post_id );

	}


	public function save_template( $post_id ) {

		$update_meta = true;

		if ( ( ! isset( $_POST['custom-layouts-instances'] ) ) || ( ! isset( $_POST['custom-layouts-template-data'] ) ) || ( ! isset( $_POST['custom-layouts-instance-order'] ) ) ) {
			$update_meta = false;
		}

		if ( $update_meta ) {

			$instances_data = json_decode( stripslashes_deep( $_POST['custom-layouts-instances'] ), true );
			$instance_order = json_decode( stripslashes_deep( $_POST['custom-layouts-instance-order'] ), true );
			$template_data  = json_decode( stripslashes_deep( $_POST['custom-layouts-template-data'] ), true );

			$instances_clean      = Util::deep_clean( $instances_data );
			$instance_order_clean = Util::deep_clean( $instance_order );
			$template_data_clean  = Util::deep_clean( $template_data );

			update_post_meta( $post_id, 'custom-layouts-template-instances', $instances_clean );
			update_post_meta( $post_id, 'custom-layouts-template-instance-order', $instance_order_clean );
			update_post_meta( $post_id, 'custom-layouts-template-data', $template_data_clean );
			update_post_meta( $post_id, 'custom-layouts-version', CUSTOM_LAYOUTS_VERSION );

			if ( isset( $_POST['custom-layouts-app-data'] ) ) {
				$app_data       = json_decode( stripslashes_deep( $_POST['custom-layouts-app-data'] ), true );
				$app_data_clean = Util::deep_clean( $app_data );
				update_post_meta( $post_id, 'custom-layouts-app-data', $app_data_clean );
			}

			CSS_Loader::save_css( array( $post_id ) );
		}
	}

	public function save_layout( $post_id ) {

		if ( ! isset( $_POST['custom-layouts-layout-attributes'] ) ) {
			return;
		}

		$layout_attributes       = json_decode( stripslashes_deep( $_POST['custom-layouts-layout-attributes'] ), true );
		$layout_attributes_clean = Util::deep_clean( $layout_attributes );

		// now we want to map the data back to php format
		$layout_attributes_clean = Gutenberg::map_attributes( 'js', $layout_attributes_clean );

		// and then split into query + layout according to the settings
		$layout_keys = array_keys( Settings::get_settings_defaults( array( 'layout' ) ) );
		$query_keys  = array_keys( Settings::get_settings_defaults( array( 'query' ) ) );

		$layout_data = array();
		$query_data  = array();

		foreach ( $layout_keys as $layout_key ) {
			if ( isset( $layout_attributes_clean[ $layout_key ] ) ) {
				$layout_data[ $layout_key ] = $layout_attributes_clean[ $layout_key ];
			}
		}

		foreach ( $query_keys as $query_key ) {
			if ( isset( $layout_attributes_clean[ $query_key ] ) ) {
				$query_data[ $query_key ] = $layout_attributes_clean[ $query_key ];
			}
		}

		update_post_meta( $post_id, 'custom-layouts-layout', $layout_data );
		update_post_meta( $post_id, 'custom-layouts-query', $query_data );
		update_post_meta( $post_id, 'custom-layouts-version', CUSTOM_LAYOUTS_VERSION );
	}

	/**
	 * Get the last tab from a post ID (stored in post meta)
	 *
	 * @param int $post_id   The post ID.
	 *
	 * @since    1.0.0
	 */
	public function get_post_last_tab( $post_id ) {
		return get_post_meta( $post_id, 'custom-layouts-last-tab', true );
	}

	/**
	 * Get the last tab from a post ID (stored in post meta)
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $tab      The tab name
	 *
	 * @since    1.0.0
	 */
	public function update_post_last_tab( $post_id, $tab ) {
		update_post_meta( $post_id, 'custom-layouts-last-tab', $tab );
	}

	/**
	 * Unset the last tab from a post ID (stored in post meta)
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $tab      The tab name
	 *
	 * @since    1.0.0
	 */
	public function remove_post_last_tab( $post_id ) {
		delete_post_meta( $post_id, 'custom-layouts-last-tab' );
	}


	private function preload_api_requests( $preload_paths ) {
		/*
		 Copied from core - wp-includes/block-editor.php */
		// Keep for a little while until most users are on WP 5.8, then use `block_editor_rest_api_preload( $paths, null )`

		// Restore the global $post as it was before API preloading.
		// Preload common data.
		global $post;
		global $post_id;

		/*
		* Ensure the global $post remains the same after API data is preloaded.
		* Because API preloading can call the_content and other filters, plugins
		* can unexpectedly modify $post.
		*/
		$backup_global_post = ! empty( $post ) ? clone $post : $post;
		$backup_post_id     = $post_id;
		$preload_data       = array_reduce(
			$preload_paths,
			'rest_preload_api_request',
			array()
		);
		$post               = $backup_global_post;
		$post_id            = $backup_post_id;
		wp_add_inline_script(
			'wp-api-fetch',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_data ) ),
			'after'
		);
	}
}
