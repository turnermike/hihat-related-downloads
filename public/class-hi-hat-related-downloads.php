<?php
/**
 * Hi-hat Related Downloads
 *
 * @package   Hi_Hat_Related_Downloads
 * @author    Mike Turner <turner.mike@gmail.com>
 * @license   GPL-2.0+
 * @link      http://hi-hatconsulting.com
 * @copyright 2014 Hi-hat Consulting
 */

class Hi_Hat_Related_Downloads_Widget extends WP_Widget{

	//constructor
	function Hi_Hat_Related_Downloads_Widget(){

		parent::WP_Widget(false, $name = __('Hi-hat Related Downloads', 'Hi_Hat_Related_Downloads_Widget'));

	}

	//widget form creation
	function form($instance){

		// Check values
		if( $instance) {
		     $title = esc_attr($instance['title']);
		     if(isset($instance['post_qty'])){
		     	$post_qty = esc_attr($instance['post_qty']);
		     }
		     if(isset($instance['readmore_url'])){
		     	$readmore_url = esc_attr($instance['readmore_url']);
		     }
		} else {
		     $title = '';
		     $post_qty = '';
		     $readmore_url = '';
		}
		?>

		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'Hi_Hat_Related_Downloads_Widget'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>This widget will display downloads using the 'Related Downloads' tag.</p>

		<?php
	}

	//widget update
	function update($new_instance, $old_instance){

		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;

	}

	//widget display
	function widget($args, $instance){

		wp_reset_postdata();

		global $post;

		extract( $args );

		//get the terms
		$terms = wp_get_object_terms( $post->ID, array('download-tag'), array('fields' => 'ids') );

		$args = array(
			'post_type'			=> 'downloads',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> 9,
			'orderby'			=> 'rand',
			'tax_query'			=> array(
				'relation'			=> 'OR',
				array(
					'taxonomy'	=> 'download-tag',
					'field' 	=> 'id',
					'terms' 	=> $terms
				)
			),
			'post__not_in' => array($post->ID)
		);
		$results = new WP_Query($args);

		// print('<pre>');
		// print_r($results->posts);
		// print('</pre>');

		if($results->have_posts()){

			$output = $before_widget;
			$output .= "<ul>";
			// get the title
			$title = apply_filters('widget_title', $instance['title']);
			// Check if title is set
			if ( $title ) {
				$output .= $before_title . $title . $after_title;
			}

			while($results->have_posts()) : $results->the_post();

				$output .= "<li>";
				$title = get_the_title();
				$url = get_post_meta($post->ID, 'wpcf-downloads-the-file', true);
				$desc = get_post_meta($post->ID, 'wpcf-downloads-file-description', true);
				if($url && $title){
					$output .= "<a href='$url'>$title</a>";
				}
				if($desc){
					$output .= wpautop($desc);
				}
				// $output .= $url;
				$output .= "</li>";

			endwhile;

			$output .= "</ul>";
			$output .= $after_widget;
		}

		echo $output;

	}

}

add_action('widgets_init', create_function('', 'return register_widget("Hi_Hat_Related_Downloads_Widget");'));




/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Hi_Hat_Related_Downloads
 * @author  Your Name <email@example.com>
 */
class Hi_Hat_Related_Downloads {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'hi-hat-related-downloads';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'initialize' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'add_meta_boxes', array( $this, 'hihat_add_meta_boxes' ) );
		add_action( 'save_post', array($this, 'hihat_save_meta_boxes' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

		//debug
		ob_start();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function initialize() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

		register_post_type( 'related_download',
		array(
		  'labels' => array(
		    'name' => __( 'Related Downloads' ),
		    'singular_name' => __( 'Related Download' ),
		  ),
		  'public' => true,
		  'has_archive' => true,
		  'supports' => array('title', 'thumbnail')
		)
		);

		register_taxonomy(
			'related_download_tag',
			// array('related_download', 'post', 'page'),
			get_post_types(),
			array(
				'label' => __( 'Related Download Tags' ),
				'rewrite' => array( 'slug' => 'related_download_tag' ),
			)
		);

	}

	public function hihat_add_meta_boxes(){

        add_meta_box(
            'related_download',
            __( 'The Download', 'hi-hat-related-downloads' ),
            array( $this, 'hihat_add_meta_boxes_callback' ),
            'related_download',
            'normal'
        );

    }

    public function hihat_add_meta_boxes_callback(){

    	global $post;
    	$attachment_id = get_post_meta($post->ID, 'hihat-attachment-id', true);
    	$attachment_title = get_post_meta($post->ID, 'hihat-attachment-title', true);
    	$attachment_url = get_post_meta($post->ID, 'hihat-attachment-url', true);

    ?>
		<div class="uploader">
		<p>
			<strong>Current file: </strong><br />
			Title: <span class="hihat-attachment-title"><?php echo ($attachment_title != '' ? $attachment_title : 'Not yet set'); ?></span><br />
			URL: <span class="hihat-attachment-url"><?php echo ($attachment_url != '' ? $attachment_url : 'Not yet set'); ?></span>
		</p>

		<p>
		<input type="submit" class="button" name="hihat-upload-button" id="hihat-upload-button" value="<?php _e('Select a File', 'hi-hat-ad-widget'); ?>" onclick="imageWidget.uploader( '<?php echo $this->id; ?>' ); return false;" />
		</p>
		<input type="hidden" id="hihat-attachment-id" name="hihat-attachment-id" value="<?php echo $attachment_id; ?>" />
		<input type="hidden" id="hihat-attachment-title" name="hihat-attachment-title" class="hihat-attachment-title" value="<?php echo $attachment_title; ?>" />
		<input type="hidden" id="hihat-attachment-url" name="hihat-attachment-url" class="hihat-attachment-url" value="<?php echo $attachment_url; ?>" />
		</div>

	<?php

    }

	public function hihat_save_meta_boxes(){

		global $post;

		update_post_meta($post->ID, "hihat-attachment-id", $_POST["hihat-attachment-id"]);
		update_post_meta($post->ID, "hihat-attachment-title", $_POST["hihat-attachment-title"]);
		update_post_meta($post->ID, "hihat-attachment-url", $_POST["hihat-attachment-url"]);


		// if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
		// $uploadedfile = $_FILES['hihat_the_file'];
		// $upload_overrides = array( 'test_form' => false );
		// $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
		// if ( $movefile ) {
		//     echo "File is valid, and was successfully uploaded.\n";
		//     var_dump( $movefile);
		// } else {
		//     echo "Possible file upload attack!\n";
		// }






		// update_post_meta($post->ID, "hihat_the_file", $_POST["hihat_the_file"]);

		// if(!empty($_FILES['hihat_the_file']['name'])){ //New upload

		// 	require_once( ABSPATH . 'wp-admin/includes/file.php' );
		// 	$override['action'] = 'editpost';

		// 	$uploaded_file = wp_handle_upload($_FILES['hihat_the_file'], $override);

		// 	print('<pre>');
		// 	print_r($uploaded_file);
		// 	print('</pre>');

		// 	$post_id = $post->ID;
		// 	$attachment = array(
		// 	'post_title' => $_FILES['hihat_the_file']['name'],
		// 	'post_content' => '',
		// 	'post_type' => 'attachment',
		// 	'post_parent' => $post_id,
		// 	'post_mime_type' => $_FILES['hihat_the_file']['type'],
		// 	'guid' => $uploaded_file['url']
		// 	);
		// 	// Save the data
		// 	$id = wp_insert_attachment( $attachment,$_FILES['hihat_the_file'][ 'file' ], $post_id );
		// 	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $_FILES['hihat_the_file']['file'] ) );

		// 	update_post_meta($post->ID, "hihat_the_file", $uploaded_file['url']);

		// }
	}

    function user_can_save( $post_id, $nonce ) {

        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );

        // Return true if the user is able to save; otherwise, false.
        return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

    } // end user_can_save

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style('thickbox');
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');

		wp_enqueue_media();
		wp_enqueue_script( 'image-widget', plugins_url('/js/hi-hat-related-downloads.js', __FILE__), array( 'jquery', 'media-upload', 'media-views' ), self::VERSION );
		wp_localize_script( 'hi-hat-related-downloads', 'imageWidget', array(
			'frame_title' => __( 'Select an Image', 'image_widget' ),
			'button_title' => __( 'Insert Into Widget', 'image_widget' ),
		) );

	}

}
