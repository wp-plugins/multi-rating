<?php 
/*
Plugin Name: Multi Rating
Plugin URI: http://wordpress.org/plugins/multi-rating/
Description: A simple rating plugin which allows visitors to rate a post based on multiple criteria and questions.
Version: 2.3
Author: Daniel Powney
Author URI: http://danielpowney.com
License: GPL2
Text Domain: multi-rating
*/

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'shortcodes.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'utils.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'multi-rating-api.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-form-view.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-result-view.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'filters.php';
if (is_admin()) {
	require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'update-check.php';
	require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-item-table.php';
	require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-item-entry-table.php';
	require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-item-entry-value-table.php';
}

/**
 * Multi_Rating plugin class
 */
class Multi_Rating {

	// constants
	const
	VERSION = '2.3',
	ID = 'multi-rating',

	// tables
	RATING_SUBJECT_TBL_NAME 					= 'mr_rating_subject',
	RATING_ITEM_TBL_NAME 						= 'mr_rating_item',
	RATING_ITEM_ENTRY_TBL_NAME					= 'mr_rating_item_entry',
	RATING_ITEM_ENTRY_VALUE_TBL_NAME 			= 'mr_rating_item_entry_value',
	
	// settings
	CUSTOM_TEXT_SETTINGS 						= 'mr_custom_text_settings',
	STYLE_SETTINGS 								= 'mr_style_settings',
	POSITION_SETTINGS 							= 'mr_position_settings',
	GENERAL_SETTINGS 							= 'mr_general_settings',
	
	// options
	CUSTOM_CSS_OPTION 							= 'mr_custom_css',
	STAR_RATING_COLOUR_OPTION					= 'mr_star_rating_colour',
	STAR_RATING_HOVER_COLOUR_OPTION				= 'mr_star_rating_hover_colour',
	RATING_RESULTS_POSITION_OPTION				= 'mr_rating_results_position',
	RATING_FORM_POSITION_OPTION 				= 'mr_rating_form',
	CHAR_ENCODING_OPTION 						= 'mr_char_encoding',
	RATING_FORM_TITLE_TEXT_OPTION 				= 'mr_rating_form_title_text',
	TOP_RATING_RESULTS_TITLE_TEXT_OPTION 		= 'mr_top_rating_results_title_text',
	IP_ADDRESS_DATE_VALIDATION_OPTION			= 'mr_ip_address_date_validation',
	POST_TYPES_OPTION							= 'mr_post_types',
	SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION		= 'mr_rating_form_button_text',
	RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION 	= 'mr_rating_form_submit_success_message',
	DATE_VALIDATION_FAIL_MESSAGE_OPTION			= 'mr_date_validation_fail_message',
	NO_RATING_RESULTS_TEXT_OPTION				= 'mr_no_rating_results_text',
	INCLUDE_FONT_AWESOME_OPTION					= 'mr_include_font_awesome',
	FONT_AWESOME_VERSION_OPTION					= 'mr_font_awesome_version',
	VERSION_OPTION								= 'mr_version_option',
	DO_ACTIVATION_REDIRECT_OPTION				= 'mr_do_activiation_redirect',
	
	//values
	SCORE_RESULT_TYPE							= 'score',
	STAR_RATING_RESULT_TYPE						= 'star_rating',
	PERCENTAGE_RESULT_TYPE						= 'percentage',
	DO_NOT_SHOW									= 'do_not_show',
	SELECT_ELEMENT								= 'select',
	
	// pages
	SETTINGS_PAGE_SLUG							= 'mr_settings_page',
	ABOUT_PAGE_SLUG								= 'mr_about_page',
	RATING_ITEMS_PAGE_SLUG						= 'mr_rating_items',
	RATING_RESULTS_PAGE_SLUG					= 'mr_rating_results',
	ADD_NEW_RATING_ITEM_PAGE_SLUG				= 'mr_add_new_rating_item',
	REPORTS_PAGE_SLUG							= 'mr_reports',
	
	// tabs
	ENTRIES_TAB									= 'mr_ENTRIES_TAB',
	ENTRY_VALUES_TAB							= 'mr_ENTRY_VALUES_TAB',
	
	// post meta box
	RATING_FORM_POSITION_POST_META				= 'rating_form_position',
	RATING_RESULTS_POSITION_POST_META			= 'rating_results_position';
	
	public $custom_text_settings = array();
	public $style_settings = array();
	public $position_settings = array();
	public $general_settings = array();
	
	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {
		
		global $wpdb;	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		// subjects can be a post type
		$sql_create_rating_subject_tbl = 'CREATE TABLE ' . $wpdb->prefix . Multi_Rating::RATING_SUBJECT_TBL_NAME . ' (
				rating_id bigint(20) NOT NULL AUTO_INCREMENT,
				post_type varchar(20) NOT NULL,
				PRIMARY KEY  (rating_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_subject_tbl );
		
		// subjects are rated by multiple rating items
		$sql_create_rating_item_tbl = 'CREATE TABLE '. $wpdb->prefix . Multi_Rating::RATING_ITEM_TBL_NAME . ' (
				rating_item_id bigint(20) NOT NULL AUTO_INCREMENT,
				rating_id bigint(20) NOT NULL,
				description varchar(255) NOT NULL,
				default_option_value int(11),
				max_option_value int(11),
				active tinyint(1) DEFAULT 1,
				weight double precision DEFAULT 1.0,
				type varchar(20) NOT NULL DEFAULT "select",
				PRIMARY KEY  (rating_item_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_item_tbl );
		
		// rating item entries and results are saved
		$sql_create_rating_item_entry_tbl = 'CREATE TABLE ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' (
				rating_item_entry_id bigint(20) NOT NULL AUTO_INCREMENT,
				post_id bigint(20) NOT NULL,
				entry_date datetime NOT NULL,
				ip_address varchar(100),
				username varchar(50),
				PRIMARY KEY  (rating_item_entry_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_item_entry_tbl );

		$sql_create_rating_item_entry_value_tbl = 'CREATE TABLE ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' (
				rating_item_entry_value_id bigint(20) NOT NULL AUTO_INCREMENT,
				rating_item_entry_id bigint(20) NOT NULL,
				rating_item_id bigint(20) NOT NULL,
				value int(11) NOT NULL,
				PRIMARY KEY  (rating_item_entry_value_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta( $sql_create_rating_item_entry_value_tbl );
		
	}
	
	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {
		
		delete_option( Multi_Rating::GENERAL_SETTINGS );
		delete_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		delete_option( Multi_Rating::POSITION_SETTINGS );
		delete_option( Multi_Rating::STYLE_SETTINGS );
		
		// Drop tables
		global $wpdb;
		
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_TBL_NAME );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Multi_Rating::RATING_SUBJECT_TBL_NAME );
	}
	
	
	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	function __construct() {
		
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_admin_menus') );
			add_action( 'admin_init', array( $this, 'do_admin_actions') );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		}
		
		add_action( 'init', array( &$this, 'load_settings' ) );
		
		add_action( 'init', array( &$this, 'load_textdomain' ) );
		
		add_action( 'admin_init', array( &$this, 'register_custom_text_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_style_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_position_settings' ) );
		
		add_action( 'admin_init', array( $this, 'redirect_about_page' ) );
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta' ) );
		
		add_action('wp_head', array( $this, 'add_custom_css'));

		$this->add_ajax_actions();
	}
	
	/**
	 * Redirects to about page on activation
	 */
	function redirect_about_page() {
		if ( get_option( MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, false ) ) {
			delete_option( MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION );
			wp_redirect( 'admin.php?page=' . MULTI_RATING::ABOUT_PAGE_SLUG );
		}
	}
	
	/**
	 * Loads plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'multi-rating', false, dirname( plugin_basename( __FILE__) ) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR );
	}
	
	
	/**
	 * Adds the meta box container
	 */
	public function add_meta_box( $post_type ) {
		
		$post_types = $this->general_settings[Multi_Rating::POST_TYPES_OPTION];
		
		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array($post_types);
		}
		if ( $post_types != null && in_array( $post_type, $post_types )) {
			add_meta_box( 'mr_meta_box', __('Multi Rating', 'multi-rating'), array( $this, 'display_meta_box_content' ), $post_type, 'side', 'high');
		}
	}
	
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_post_meta( $post_id ) {
			
		if ( ! isset( $_POST['meta_box_nonce_action'] ) )
			return $post_id;
	
		if ( ! wp_verify_nonce( $_POST['meta_box_nonce_action'], 'meta_box_nonce' ) )
			return $post_id;
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
	
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
	
		$rating_form_position = $_POST['rating-form-position'];
		$rating_results_position = $_POST['rating-results-position'];
	
		// Update the meta field.
		update_post_meta( $post_id, Multi_Rating::RATING_FORM_POSITION_POST_META, $rating_form_position );
		update_post_meta( $post_id, Multi_Rating::RATING_RESULTS_POSITION_POST_META, $rating_results_position );
	}
	
	
	/**
	 * Displays the meta box content
	 *
	 * @param WP_Post $post The post object.
	 */
	public function display_meta_box_content( $post ) {
	
		wp_nonce_field( 'meta_box_nonce', 'meta_box_nonce_action' );
	
		$rating_form_position = get_post_meta( $post->ID, Multi_Rating::RATING_FORM_POSITION_POST_META, true );
		$rating_results_position = get_post_meta( $post->ID, Multi_Rating::RATING_RESULTS_POSITION_POST_META, true );
	
		?>
		<p>
			<label for="rating-form-position"><?php _e( 'Rating form position', 'multi-rating' ); ?></label>
			<select class="widefat" name="rating-form-position">
				<option value="<?php echo Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_form_position, true );?>><?php _e( 'Do not show', 'multi-rating' ); ?></option>
				<option value="" <?php selected('', $rating_form_position, true );?>><?php _e( 'Use default settings', 'multi-rating' ); ?></option>
				<option value="before_content" <?php selected('before_content', $rating_form_position, true );?>><?php _e( 'Before content', 'multi-rating' ); ?></option>
				<option value="after_content" <?php selected('after_content', $rating_form_position, true );?>><?php _e( 'After content', 'multi-rating' ); ?></option>
			</select>
		</p>
		
		<p>
			<label for="rating-results-position"><?php _e( 'Rating result position', 'multi-rating' ); ?></label>
			<select class="widefat" name="rating-results-position">
				<option value="<?php echo Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_results_position, true );?>><?php _e('Do not show', 'multi-rating' ); ?></option>
				<option value="" <?php selected('', $rating_results_position, true );?>><?php _e( 'Use default settings', 'multi-rating' ); ?></option>
				<option value="before_title" <?php selected('before_title', $rating_results_position, true );?>><?php _e( 'Before title', 'multi-rating' ); ?></option>
				<option value="after_title" <?php selected('after_title', $rating_results_position, true );?>><?php _e( 'After title', 'multi-rating' ); ?></option>
			</select>
		</p>
		<?php
	}
	
	/**
	 * Retrieve settings and applies default option values if not set
	 */
	function load_settings() {
		
		$this->style_settings 			= (array) get_option( Multi_Rating::STYLE_SETTINGS );
		$this->custom_text_settings 	= (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$this->position_settings 		= (array) get_option( Multi_Rating::POSITION_SETTINGS );
		$this->general_settings 		= (array) get_option( Multi_Rating::GENERAL_SETTINGS );	
		
		$default_css = addslashes(".top-rating-results .rank { font-weight: bold; }");
		
		// Merge with defaults
		$this->style_settings = array_merge( array(
				Multi_Rating::CUSTOM_CSS_OPTION => $default_css,
				Multi_Rating::STAR_RATING_COLOUR_OPTION => '#ffd700',
				Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION => '#ffba00',
				Multi_Rating::INCLUDE_FONT_AWESOME_OPTION => true,
				Multi_Rating::FONT_AWESOME_VERSION_OPTION => '4.0.3'
		), $this->style_settings );
		
		$this->position_settings = array_merge( array(
				Multi_Rating::RATING_RESULTS_POSITION_OPTION => '',
				Multi_Rating::RATING_FORM_POSITION_OPTION => ''
		), $this->position_settings );
		
		$this->custom_text_settings = array_merge( array(
				Multi_Rating::CHAR_ENCODING_OPTION => '',
				Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION => __( 'Please rate this', 'multi-rating' ),
				Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION => __( 'Top Rating Results', 'multi-rating' ),
				Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION => __( 'Submit Rating', 'multi-rating' ),
				Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION => __( 'Rating form submitted successfully.', 'multi-rating'),
				Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION => __( 'You cannot submit a rating form for the same post multiple times.', 'multi-rating' ),
				Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION => __( 'No rating results yet', 'multi-rating' )
		), $this->custom_text_settings );
		
		$this->general_settings = array_merge( array(
				Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION => true,
				Multi_Rating::POST_TYPES_OPTION => 'post',
		), $this->general_settings );
		
		update_option( Multi_Rating::STYLE_SETTINGS, $this->style_settings);
		update_option( Multi_Rating::POSITION_SETTINGS, $this->position_settings);
		update_option( Multi_Rating::CUSTOM_TEXT_SETTINGS, $this->custom_text_settings);
		update_option( Multi_Rating::GENERAL_SETTINGS, $this->general_settings);
	}
	
	
	/**
	 * Register general settings
	 */
	function register_general_settings() {
		
		register_setting( Multi_Rating::GENERAL_SETTINGS, Multi_Rating::GENERAL_SETTINGS, array( &$this, 'sanitize_general_settings' ) );
	
		add_settings_section( 'section_general', __( 'General Settings', 'multi-rating' ), array( &$this, 'section_general_desc' ), Multi_Rating::GENERAL_SETTINGS );
	
		add_settings_field( Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION, __( 'IP address & date validation check', 'multi-rating' ), array( &$this, 'field_ip_address_date_validation' ), Multi_Rating::GENERAL_SETTINGS, 'section_general' );
		add_settings_field( Multi_Rating::POST_TYPES_OPTION, __( 'Post types', 'multi-rating' ), array( &$this, 'field_post_types' ), Multi_Rating::GENERAL_SETTINGS, 'section_general' );
	
	}
	/**
	 * General section desciption
	 */
	function section_general_desc() {
		echo '';
	}
	/**
	 * IP address & date validation setting
	 */
	function field_ip_address_date_validation() {
		?>
		<input type="checkbox" name="<?php echo Multi_Rating::GENERAL_SETTINGS; ?>[<?php echo Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION], true); ?> />
		<p class="description"><?php _e( 'Restrict the same IP address from submitting the same rating form for the same post multiple times.', 'multi-rating' ); ?></p>
		<?php 
	}
	/**
	 * Post types enabled setting
	 */
	function field_post_types() {
		$post_types = get_post_types( '', 'names' );
		$post_types_checked = $this->general_settings[Multi_Rating::POST_TYPES_OPTION];
	
		foreach ( $post_types as $post_type ) {
			echo '<input type="checkbox" name="' . Multi_Rating::GENERAL_SETTINGS . '[' . Multi_Rating::POST_TYPES_OPTION . '][]" value="' . $post_type . '"';
			if (is_array($post_types_checked)) {
				if (in_array($post_type, $post_types_checked)) {
					echo 'checked="checked"';
				}
			} else {
				checked($post_type, $post_types_checked, true );
			}
			echo ' />&nbsp;<label class="checkbox-label">' . $post_type . '</label>';
		}
	
		?>
		<p class="description"><?php _e('Select the post types to be enabled.', 'multi-rating'); ?></p>
		<?php 
	}
	/**
	 * Sanitize the general settings
	 * 
	 * @param $input
	 * @return boolean
	 */
	function sanitize_general_settings( $input ) {
		
		if ( isset( $input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] ) && $input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] == 'true' ) {
			$input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = true;
		} else {
			$input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = false;
		}
	
		return $input;
	}
	
	/**
	 * Register position settings
	 */
	function register_position_settings() {
		register_setting( Multi_Rating::POSITION_SETTINGS, Multi_Rating::POSITION_SETTINGS, array( &$this, 'sanitize_position_settings' ) );
	
		add_settings_section( 'section_position', __( 'Auto Placement Settings', 'multi-rating' ), array( &$this, 'section_position_desc' ), Multi_Rating::POSITION_SETTINGS );
	
		add_settings_field( Multi_Rating::RATING_RESULTS_POSITION_OPTION, __( 'Rating results position', 'multi-rating' ), array( &$this, 'field_rating_results_position' ), Multi_Rating::POSITION_SETTINGS, 'section_position' );
		add_settings_field( Multi_Rating::RATING_FORM_POSITION_OPTION, __( 'Rating form position', 'multi-rating' ), array( &$this, 'field_rating_form_position' ), Multi_Rating::POSITION_SETTINGS, 'section_position' );
	}
	
	/**
	 * Position section description
	 */
	function section_position_desc() {
		echo '<p>' . _e( 'These settings allow you to automatically place the rating form and rating results on every post or page in default positions. You can override these settings for a particular page or post using the Multi Rating meta box in the edit post page.', 'multi-rating' ) . '</p>';
	}
	
	/**
	 * Rating results auto placement setting
	 */
	function field_rating_results_position() {
		?>
		<select name="<?php echo Multi_Rating::POSITION_SETTINGS; ?>[<?php echo Multi_Rating::RATING_RESULTS_POSITION_OPTION; ?>]">
			<option value="" <?php selected( '', $this->position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION], true ); ?>><?php _e( 'None', 'multi-rating' ); ?></option>
			<option value="before_title" <?php selected( 'before_title', $this->position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION], true ); ?>><?php _e( 'Before title', 'multi-rating' ); ?></option>
			<option value="after_title" <?php selected( 'after_title', $this->position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION], true ); ?>><?php _e( 'After title', 'multi-rating' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Choose to automatically display the rating result before or after the post title for all enabled post types.', 'multi-rating' ); ?></p>
		<?php
	}
	
	/**
	 * Rating form auto placement settings
	 */
	function field_rating_form_position() {
		?>
		<select name="<?php echo Multi_Rating::POSITION_SETTINGS; ?>[<?php echo Multi_Rating::RATING_FORM_POSITION_OPTION; ?>]">
			<option value="" <?php selected( '', $this->position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>><?php _e( 'None', 'multi-rating' ); ?></option>
			<option value="before_content" <?php selected('before_content', $this->position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>><?php _e( 'Before content', 'multi-rating' ); ?></option>
			<option value="after_content" <?php selected('after_content', $this->position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>><?php _e( 'After content', 'multi-rating' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Choose to automatically display the rating form before or after the post content for all enabled post types.', 'multi-rating' ); ?></p>
		<?php
	}
	
	/**
	 * Sanitize auto placement settings
	 * 
	 * @param $input
	 * @return unknown
	 */
	function sanitize_position_settings( $input ) {
		
		return $input;
		
	}
	
	
	/**
	 * Register style settings
	 */
	function register_style_settings() {
		register_setting( Multi_Rating::STYLE_SETTINGS, Multi_Rating::STYLE_SETTINGS, array( &$this, 'sanitize_style_settings' ) );
	
		add_settings_section( 'section_style', __( 'Style Settings', 'multi-rating' ), array( &$this, 'section_style_desc' ), Multi_Rating::STYLE_SETTINGS );

		add_settings_field( Multi_Rating::CUSTOM_CSS_OPTION, __( 'Custom CSS', 'multi-rating' ), array( &$this, 'field_custom_css' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( Multi_Rating::STAR_RATING_COLOUR_OPTION, __( 'Star rating color', 'multi-rating' ), array( &$this, 'field_star_rating_colour' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION, __( 'Star rating on hover color', 'multi-rating' ), array( &$this, 'field_star_rating_hover_colour' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( Multi_Rating::INCLUDE_FONT_AWESOME_OPTION, __( 'Include loading Font Awesome', 'multi-rating' ), array( &$this, 'field_include_font_awesome' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( Multi_Rating::FONT_AWESOME_VERSION_OPTION, __( 'Font Awesome version', 'multi-rating' ), array( &$this, 'field_font_awesome_version' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
	}
	
	/**
	 * Style section description
	 */
	function section_style_desc() {
	}
	
	/**
	 * Include plugin loading Font Awesome CSS
	 */
	function field_include_font_awesome() {
		?>
		<input type="checkbox" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::INCLUDE_FONT_AWESOME_OPTION; ?>]" value="true" <?php checked(true, $this->style_settings[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION], true); ?> />
		<p class="description"><?php _e( 'Do you want the plugin to include loading of the Font Awesome CSS?', 'multi-rating' ); ?></p>
		<?php
	}
	
	/**
	 * Which version of Font Awesome to use
	 */
	function field_font_awesome_version() {
		?>
		<select name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::FONT_AWESOME_VERSION_OPTION; ?>]">
			<option value="4.1.0" <?php selected( '4.1.0', $this->style_settings[Multi_Rating::FONT_AWESOME_VERSION_OPTION], true); ?>>4.1.0</option>
			<option value="4.0.3" <?php selected( '4.0.3', $this->style_settings[Multi_Rating::FONT_AWESOME_VERSION_OPTION], true); ?>>4.0.3</option>
			<option value="3.2.1" <?php selected( '3.2.1', $this->style_settings[Multi_Rating::FONT_AWESOME_VERSION_OPTION], true); ?>>3.2.1</option>
		</select>
		<?php
	}
	
	/**
	 * Customer CSS settings
	 */
	function field_custom_css() {
		?>
		<textarea cols="50" rows="10" class="large-text" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::CUSTOM_CSS_OPTION; ?>]"><?php echo stripslashes($this->style_settings[Multi_Rating::CUSTOM_CSS_OPTION]); ?></textarea>
		<?php 
	}	
	
	/**
	 * Star rating colour setting
	 */
	function field_star_rating_colour() {	
		$star_rating_colour = $this->style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
		?>
   	 	<input class="color-picker" type="text" id="star-rating-colour" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::STAR_RATING_COLOUR_OPTION; ?>]; ?>" value="<?php echo $star_rating_colour; ?>" />
		<?php 
	}
	
	/**
	 * Star rating on hover colour
	 */
	function field_star_rating_hover_colour() {
		$star_rating_hover_colour = $this->style_settings[Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
		?>
	 	 	<input class="color-picker" type="text" id="star-rating-hover-colour" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION; ?>]; ?>" value="<?php echo $star_rating_hover_colour; ?>" />
		<?php 
	}
	
	/**
	 * Sanitize style settings
	 * 
	 * @param $input
	 * @return string
	 */
	function sanitize_style_settings( $input ) {
		
		$input[Multi_Rating::CUSTOM_CSS_OPTION] = addslashes($input[Multi_Rating::CUSTOM_CSS_OPTION]);
		
		if ( isset( $input[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] ) && $input[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] == 'true' ) {
			$input[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] = true;
		} else {
			$input[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION] = false;
		}
		
		return $input;
	}

	/**
	 * Register custom text settings
	 */
	function register_custom_text_settings() {
		
		register_setting( Multi_Rating::CUSTOM_TEXT_SETTINGS, Multi_Rating::CUSTOM_TEXT_SETTINGS, array( &$this, 'sanitize_custom_text_settings' ) );
	
		add_settings_section( 'section_custom_text', __('Custom Text Settings', 'multi-rating' ), array( &$this, 'section_custom_text_desc' ), Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		add_settings_field( Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION, __( 'Rating form title', 'multi-rating' ), array( &$this, 'field_rating_form_title_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION, __( 'Top Rating Results title', 'multi-rating' ), array( &$this, 'field_top_rating_results_title_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION, __( 'Rating form submit button text', 'multi-rating' ), array( &$this, 'field_rating_form_submit_button_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION, __( 'Rating form submit success message', 'multi-rating' ), array( &$this, 'field_rating_form_submit_message' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION, __( 'Date validation failure message', 'multi-rating' ), array( &$this, 'field_date_validation_fail_message' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION, __( 'No rating results text' , 'multi-rating' ), array( &$this, 'field_no_rating_results_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::CHAR_ENCODING_OPTION, __( 'Character encoding', 'multi-rating' ), array( &$this, 'field_char_encoding' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		
	}
	
	/**
	 * Custom text section description
	 */
	function section_custom_text_desc() {
		echo '<p class="description">' . _e('Modify the default text and messages.', 'multi-rating' ) . '</p>';
	}
	
	/**
	 * Rating form submit button text setting
	 */
	function field_rating_form_submit_button_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION]; ?>" />
		<?php
	}	
	
	/**
	 * Rating form submit message setting
	 */
	public function field_rating_form_submit_message() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION]; ?>" />
		<?php
	}
	
	/**
	 * Date validation failure message setting
	 */
	public function field_date_validation_fail_message() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION]; ?>" />
		<?php
	}
	
	/**
	 * Rating form title text setting
	 */
	function field_rating_form_title_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}
	
	/**
	 * Top rating results title text setting
	 */
	function field_top_rating_results_title_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}	
	
	/**
	 * No rating results text setting
	 */
	function field_no_rating_results_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION]; ?>" />
		<?php
	}	

	/**
	 * Char encoding setting
	 */
	function field_char_encoding() {
		?>	
		<select name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::CHAR_ENCODING_OPTION; ?>]">
			<option value="" <?php selected( '', $this->custom_text_settings[Multi_Rating::CHAR_ENCODING_OPTION], true); ?>><?php _e('Keep current charset (Recommended)', 'multi-rating' ); ?></option>
	        <option value="utf8_general_ci" <?php selected('utf8_general_ci', $this->custom_text_settings[Multi_Rating::CHAR_ENCODING_OPTION], true); ?>><?php _e( 'UTF-8 (try this first)', 'multi-rating' ); ?></option>
	        <option value="latin1_swedish_ci" <?php selected('latin1_swedish_ci', $this->custom_text_settings[Multi_Rating::CHAR_ENCODING_OPTION], true); ?>><?php _e( 'latin1_swedish_ci' , 'multi-rating' ); ?></option>
		</select>
		<?php
	}
	
	/**
	 * Sanitize custom text settings
	 * 
	 * @param $input
	 * @return unknown
	 */
	function sanitize_custom_text_settings( $input ) {
		
		global $wpdb;
		
		$character_encoding = $input[Multi_Rating::CHAR_ENCODING_OPTION];
		$old_character_set = $this->general_settings[Multi_Rating::CHAR_ENCODING_OPTION];
		
		if ($character_encoding != $old_character_set) {
			
			$tables = array( $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME );
			
			foreach ( $tables as $table ) {
				$rows = $wpdb->get_results( "DESCRIBE {$table}" );
				
				foreach ( $rows as $row ) {
					
					$name = $row->Field;
					$type = $row->Type;
					
					if ( preg_match( "/^varchar\((\d+)\)$/i", $type, $mat ) || ! strcasecmp( $type, "CHAR" )
							|| !strcasecmp( $type, "TEXT" ) || ! strcasecmp( $type, "MEDIUMTEXT" ) ) {
						$wpdb->query( 'ALTER TABLE ' . $table .' CHANGE ' . $name . ' ' . $name . ' ' . $type . ' COLLATE ' . $character_encoding );
					}
				}
			}
		}
	
		return $input;
	}

	/**
	 * Adds admin menus
	 */
	public function add_admin_menus() {
		add_menu_page( __( 'Multi Rating', 'multi-rating' ), __( 'Multi Rating', 'multi-rating' ), 'manage_options', Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ), '', null );
	
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, '', '', 'manage_options', Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ) );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Results', 'multi-rating' ), __( 'Rating Results', 'multi-rating' ), 'manage_options', Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ) );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Items', 'multi-rating' ), __( 'Rating Items', 'multi-rating' ),'manage_options', Multi_Rating::RATING_ITEMS_PAGE_SLUG, array( &$this, 'rating_items_page' ) );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Add New Rating Item', 'multi-rating' ), __( 'Add New Rating Item', 'multi-rating' ), 'manage_options', Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG, array( &$this, 'add_new_rating_item_page' ) );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Settings', 'multi-rating' ), __( 'Settings', 'multi-rating' ), 'manage_options', Multi_Rating::SETTINGS_PAGE_SLUG, array( &$this, 'settings_page' ) );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Reports', 'multi-rating' ), __( 'Reports', 'multi-rating' ), 'manage_options', Multi_Rating::REPORTS_PAGE_SLUG, array( &$this, 'reports_page' ) );
		add_submenu_page( Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'About', 'multi-rating' ), __( 'About', 'multi-rating' ), 'manage_options', Multi_Rating::ABOUT_PAGE_SLUG, array( &$this, 'about_page' ) );
	}
	
	/**
	 * Reports page
	 */
	public function reports_page() {
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'reports';
				$page = Multi_Rating::REPORTS_PAGE_SLUG;
				$tabs = array (
						'reports' => __( 'Reports', 'multi-rating' ),
						'export-import' => __('Export / Import', 'multi-rating' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} ?>
			</h2>
			
			<?php 
			if ( $current_tab == 'export-import' ) {?>	
				<div class="metabox-holder">
					<div class="postbox">
					
						<h3><span><?php _e( 'Export Rating Results', 'multi-rating' ); ?></span></h3>
						
						<div class="inside">
							<p><?php _e( 'Export Rating Results to a CSV file.', 'multi-rating' ); ?></p>
							
							<form method="post" id="export-rating-results-form">
							
								<p>
									<input type="text" name="username" id="username" class="" autocomplete="off" placeholder="Username">
									<input type="text" class="date-picker" autocomplete="off" name="from-date" placeholder="<?php printf( __('From - %s', 'multi-rating' ), 'dd/MM/yyyy' ); ?>" id="from-date">
									<input type="text" class="date-picker" autocomplete="off" name="to-date" placeholder="<?php printf( __('To - %s', 'multi-rating' ), 'dd/MM/yyyy' ); ?>" id="to-date">
									
									<select name="post-id" id="post-id">
										<option value=""><?php _e( 'All posts / pages', 'multi-rating' ); ?></option>
										<?php	
										global $wpdb;
										$query = 'SELECT DISTINCT post_id FROM ' .$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;
										
										$rows = $wpdb->get_results($query, ARRAY_A);
					
										foreach ( $rows as $row ) {
											$post = get_post($row['post_id']);
											?>
											<option value="<?php echo $post->ID; ?>">
												<?php echo get_the_title( $post->ID ); ?>
											</option>
										<?php } ?>
									</select>
								</p>
								
								<p>
									<input type="hidden" name="export-rating-results" id="export-rating-results" value="false" />
									<?php 
									submit_button( __( 'Export', 'multi-rating' ), 'secondary', 'export-btn', false, null );
									?>
								</p>
							</form>
						</div><!-- .inside -->
					</div>
				</div>
			<?php } else { ?>
			
				<h3><?php _e( 'Number of entries per day', 'multi-rating' ); ?></h3>
				
				<?php 
				global $wpdb;
				
				// Time graph
				$query = 'SELECT DISTINCT DATE(entry_date ) AS day, count(*) as count FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' GROUP BY day ORDER BY entry_date DESC';
				$rows = $wpdb->get_results($query);
					
				$time_data = array();
				foreach ( $rows as $row ) {
					$day = $row->day;
					$count = $row->count;
					// TODO if a day has no data, then make it 0 visitors.
					// Otherwise, it is not plotted on the graph as 0.
			
					array_push( $time_data, array( ( strtotime( $day ) * 1000 ), intval( $count ) ) );
				}
				?>
				
				<div class="flot-container">
					<div class="report-wrapper" style="height: 300px;">
						<div id="entry-count-placeholder" class="report-placeholder"></div>
					</div>
				</div>
				
				<div class="flot-container">
					<div class="report-wrapper" style="height: 100px;">
						<div id="entry-count-overview-placeholder" class="report-placeholder"></div>
					</div>
				</div>
										
				<script type="text/javascript">
					// Time graph
					jQuery(document).ready(function() {
						// add markers for weekends on grid
						function weekendAreas(axes) {
							var markings = [];
							var d = new Date(axes.xaxis.min);
							// go to the first Saturday
							d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
							d.setUTCSeconds(0);
							d.setUTCMinutes(0);
							d.setUTCHours(0);
							var i = d.getTime();
							// when we don't set yaxis, the rectangle automatically
							// extends to infinity upwards and downwards
							do {
								markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
								i += 7 * 24 * 60 * 60 * 1000;
							} while (i < axes.xaxis.max);
							return markings;
						}
	
						var options = {
							xaxis: {
								mode: "time",
								tickLength: 5
							},
							selection: {
								mode: "x"
							},
							grid: {
								markings: weekendAreas,
								hoverable : true,
								show: true,
								aboveData: false,
								color: '#BBB',
								backgroundColor: '#f9f9f9',
								borderColor: '#ccc',
								borderWidth: 2,
							},
							series : {
								lines: {
									show: true,
									lineWidth: 1
								},
								points: { show: true }
							}
						};
						
						var plot = jQuery.plot("#entry-count-placeholder", [<?php echo json_encode( $time_data ); ?>], options);
						
						var overview = jQuery.plot("#entry-count-overview-placeholder", [<?php echo json_encode( $time_data ); ?>], {
							series: {
								lines: {
									show: true,
									lineWidth: 1
								},
								shadowSize: 0
							},
							xaxis: {
								ticks: [],
								mode: "time"
							},
							yaxis: {
								ticks: [],
								min: 0,
								autoscaleMargin: 0.1
							},
							selection: {
								mode: "x"
							},
							grid: {
								markings: weekendAreas,
								hoverable : true,
								show: true,
								aboveData: false,
								color: '#BBB',
								backgroundColor: '#f9f9f9',
								borderColor: '#ccc',
								borderWidth: 2,
								
							},
						});

						function flot_tooltip(x, y, contents) {
							jQuery('<div id="flot-tooltip">' + contents + '</div>').css( {
								position: 'absolute',
								display: 'none',
								top: y + 5,
								left: x + 5,
								border: '1px solid #fdd',
								padding: '2px',
								'background-color': '#fee',
								opacity: 0.80
							}).appendTo("body").fadeIn(200);
						}
							
						jQuery("#entry-count-placeholder").bind("plotselected", function (event, ranges) {
							// do the zooming
									
							plot = jQuery.plot("#entry-count-placeholder", [<?php echo json_encode( $time_data ); ?>], jQuery.extend(true, {}, options, {
								xaxis: {
									min: ranges.xaxis.from,
									max: ranges.xaxis.to
								}
							}));
									
							// don't fire event on the overview to prevent eternal loop
							overview.setSelection(ranges, true);
						});
												
						jQuery("#entry-count-overview-placeholder").bind("plotselected", function (event, ranges) {
							plot.setSelection(ranges);
						});

						jQuery("#entry-count-placeholder").bind("plothover", function (event, pos, item) {
							if (item) {
						   		jQuery("#flot-tooltip").remove();
								var x = item.datapoint[0].toFixed(2),
								y = item.datapoint[1].toFixed(2);

								flot_tooltip( item.pageX - 30, item.pageY - 20, item.datapoint[1] );
						    } else {
						    	jQuery("#flot-tooltip").remove();
						    }
						});
					});
				</script>	
			<?php } ?>
		</div>
		
		<?php
	}
	
	/**
	 * Executes custom admin actions on init depending on HTTP request
	 */
	public function do_admin_actions() {
	
		// if downloading the rating results csv export
		if ( isset( $_POST['export-rating-results'] ) && $_POST['export-rating-results'] === "true" ) {
				
			$file_name = 'rating-results-' . date( 'YmdHis' ) . '.csv';
				
			$username = isset( $_POST['username'] ) ? $_POST['username'] : null;
			$from_date = isset( $_POST['from-date'] ) ? $_POST['from-date'] : null;
			$to_date = isset( $_POST['to-date'] ) ? $_POST['to-date'] : null;
			$post_id = isset( $_POST['post-id'] ) ? $_POST['post-id'] : null;
				
			$filters = array();
			
			if ( $username != null && strlen($username) > 0 ) {
				$filters['username'] = $username;
			}
			
			if ( $post_id != null && strlen($post_id) > 0 ) {
				$filters['post_id'] = $post_id;
			}

			if ( $from_date != null && strlen($from_date) > 0 ) {
				list($year, $month, $day) = explode('/', $from_date); // default yyyy/mm/dd format
				if ( checkdate( $month , $day , $year )) {
					$filters['from_date'] = $from_date;
				}
			}
			
			if ( $to_date != null && strlen($to_date) > 0 ) {
				list($year, $month, $day) = explode('/', $to_date); // default yyyy/mm/dd format
				if ( checkdate( $month , $day , $year )) {
					$filters['to_date'] = $to_date;
				}
			}
				
			if ( Multi_Rating_API::generate_rating_results_csv_file( $file_name, $filters ) ) {
					
				header('Content-type: text/csv');
				header('Content-Disposition: attachment; filename="' . $file_name . '"');
				readfile($file_name);
	
				// delete file
				unlink($file_name);
			}
				
			die();
		}
	}
	
	/**
	 * Shows the settings page
	 *
	 * @since 0.1
	 */
	public function settings_page() {
		?>
		<div class="wrap">
		
			<h2><?php _e( 'Settings', 'multi-rating' ); ?></h2>
			
			<?php 
			if ( isset( $_GET['updated'] ) && isset( $_GET['page'] ) ) {
				add_settings_error('general', 'settings_updated', __( 'Settings saved.', 'multi-rating' ), 'updated');
			}
			
			settings_errors();
			
			if ( isset( $_POST['clear-database'] ) && $_POST['clear-database'] === "true" ) {
				global $wpdb;
				try {
					$rows = $wpdb->get_results( 'DELETE FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' WHERE 1' );
					$rows = $wpdb->get_results( 'DELETE FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME.' WHERE 1' );
					echo '<div class="updated"><p>' . __( 'Database cleared successfully.', 'multi-rating' ) . '</p></div>';
				} catch ( Exception $e ) {
					echo '<div class="error"><p>' . sprintf( __( 'An error has occured. %s', 'multi-rating' ), $e->getMessage() ) . '</p></div>';
				}
			}
			?>

			<form method="post" name="<?php echo Multi_Rating::GENERAL_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( Multi_Rating::GENERAL_SETTINGS );
				do_settings_sections( Multi_Rating::GENERAL_SETTINGS );
				submit_button(null, 'primary', 'submit', true, null);
				?>
			</form>
			
			<form method="post" name="<?php echo Multi_Rating::POSITION_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( Multi_Rating::POSITION_SETTINGS );
				do_settings_sections( Multi_Rating::POSITION_SETTINGS );
				submit_button(null, 'primary', 'submit', true, null);
				?>
			</form>
			
			<form method="post" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( Multi_Rating::CUSTOM_TEXT_SETTINGS );
				do_settings_sections( Multi_Rating::CUSTOM_TEXT_SETTINGS );
				submit_button(null, 'primary', 'submit', true, null);
				?>
			</form>
			
			<form method="post" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( Multi_Rating::STYLE_SETTINGS );
				do_settings_sections( Multi_Rating::STYLE_SETTINGS );
				submit_button(null, 'primary', 'submit', true, null);
				?>
			</form>
			
			<h3>Database</h3>
			<form method="post">
				<input type="hidden" name="clear-database" id="clear-database" value="false" />
				<?php 
				submit_button( $text = __( 'Clear Database', 'multi-rating' ), $type = 'delete', $name = 'clear-database-btn', $wrap = false, $other_attributes = null );
				?>
				<p class="description"><?php _e( 'Delete all rating results.', 'multi-rating' ); ?></p>
			</form>
			
		</div>
		<?php 
	}
	
	/**
	 * Shows the rating results page
	 *
	 * @since 0.1
	 */
	public function rating_results_page() {
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : Multi_Rating::ENTRIES_TAB;
				$page = Multi_Rating::RATING_RESULTS_PAGE_SLUG;
				$tabs = array (
						Multi_Rating::ENTRIES_TAB => __( 'Entries', 'multi-rating' ),
						Multi_Rating::ENTRY_VALUES_TAB => __( 'Entry Values', 'multi-rating' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} 
				?>
			</h2>
			<?php 
			
			if ( $current_tab == Multi_Rating::ENTRIES_TAB ) {
				?>
				<form method="post" id="rating-item-entry-table-form">
					<?php 
					$rating_item_entry_table = new Rating_Item_Entry_Table();
					$rating_item_entry_table->prepare_items();
					$rating_item_entry_table->display();
					?>
				</form>
				<?php 
			} else if ( $current_tab == Multi_Rating::ENTRY_VALUES_TAB ) {
				?>
				<form method="post" id="rating-item-entry-value-table-form">
					<?php 
					$rating_item_entry_value_table = new Rating_Item_Entry_Value_Table();
					$rating_item_entry_value_table->prepare_items();
					$rating_item_entry_value_table->display();
					?>
				</form>
				<?php
			}
			?>
			
		</div>
		<?php 
	}
		
	/**
	 * Shows the rating items page
	 *
	 * @since 0.1
	 */
	public function rating_items_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Rating Items' , 'multi-rating' ); ?><a class="add-new-h2" href="admin.php?page=<?php echo Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG; ?>"><?php _e( 'Add New', 'multi-rating' ); ?></a></h2>
			<form method="post" id="rating-item-table-form">
				<?php 
				$rating_item_table = new Rating_Item_Table();
				$rating_item_table->prepare_items();
				$rating_item_table->display();
				?>
			</form>
			
		</div>
		<?php 
	}
	
	public function add_new_rating_item_page() {
		
		if ( isset( $_POST['form-submitted'] ) && $_POST['form-submitted'] === "true" ) {
			$error_message = '';
			$success_message = '';
			
			if ( isset($_POST['desciption'] ) && isset( $_POST['max-option-value'] ) 
					&& isset( $_POST['default-option-value'] ) ) {
	
				$description = $_POST['desciption'];
				if ( strlen( trim( $description ) ) == 0) {
					$error_message .= __( 'Description cannot be empty. ', 'multi-rating' );
				}
				
				$type = $_POST['type'];
				if ( strlen( trim( $type ) ) == 0) {
					$type = Multi_Rating::SELECT_ELEMENT;
				}
				
				if ( is_numeric( $_POST['max-option-value'] ) == false ) {
					$error_message .= __( 'Max option value cannot be empty and must be a whole number. ', 'multi-rating' );
				}
				
				if ( is_numeric( $_POST['default-option-value'] ) == false ) {
					$error_message .= __( 'Default option value cannot be empty and must be a whole number. ', 'multi-rating' );
				}
				
				if (strlen($error_message) == 0) {
					
					global $wpdb;
					
					$max_option_value = intval($_POST['max-option-value']);
					$default_option_value = intval($_POST['default-option-value']);
					$weight = doubleval($_POST['weight']);
					
					$results = $wpdb->insert( $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME, array( 
							'description' => $description,
							'max_option_value' => $max_option_value,
							'default_option_value' => $default_option_value,
							'weight' => $weight,
							'type' => $type
					) );
					
					$success_message .= __('Rating item added successfully.', 'multi-rating' );
				}
			} else {
				$error_message .= __( 'An error occured. Rating item could not be added.', 'multi-rating' );
			}
			
			if ( strlen( $error_message ) > 0) {
				echo '<div class="error"><p>' . $error_message . '</p></div>';
			}
			if ( strlen( $success_message ) > 0) {
				echo '<div class="updated"><p>' . $success_message . '</p></div>';
			}
		}
		
		?>
		<div class="wrap">
			<h2><?php _e( 'Add New Rating Item', 'multi-rating' ); ?></h2>
		
			<form method="post" id="add-new-rating-item-form">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><?php _e( 'Description', 'multi-rating' ); ?></th>
							<td>
								<textarea id="desciption" name="desciption" type="text" maxlength="255" cols="100" placeholder="<?php _e( 'Enter description' , 'multi-rating' ); ?>"></textarea>	
								<p class="description"><?php _e( 'Enter a description.' , 'multi-rating' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Type', 'multi-rating' ); ?></th>
							<td>
								<select name="type" id="type">
									<option value="select"><?php _e( 'Select', 'multi-rating' ); ?></option>
									<option value="radio"><?php _e( 'Radio', 'multi-rating' ); ?></option>
									<option value="star_rating"><?php _e( 'Star Rating', 'multi-rating' ); ?></option>
								</select>
								<p class="description"><?php _e( 'Do you want to use a select drop-down list, radio buttons or star rating icons from Font Awesome?', 'multi-rating' );?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Max Option Value', 'multi-rating' ); ?></th>
							<td>
								<input id="max-option-value" name="max-option-value" type="text" value="5" placeholder="<?php _e( 'Enter max option value' , 'multi-rating' ); ?>"/>
								<p class="description"><?php _e( 'If the max option value is set to 5, then the rating item options would be 0, 1, 2, 3, 4 and 5.', 'multi-rating' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Default Option Value', 'multi-rating' ); ?></th>
							<td>
								<input id="default-option-value" name="default-option-value" type="text" value="5" placeholder="<?php _e( 'Enter default option value' , 'multi-rating' ); ?>"/>
								<p class="description"><?php _e( 'This is used to default the selected option value.' , 'multi-rating' ); ?></p>	
							</td>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Weight', 'multi-rating' ); ?></th>
							<td>
								<input id="weight" name="weight" type="text" value="1" placeholder="<?php _e( 'Enter weight', 'multi-rating' ); ?>" />
								<p class="description"><?php _e( 'All rating items are rated equally by default. Modifying the weight of a rating item will adjust the rating results accordingly. Decimal values can be used.', 'multi-rating' ); ?></p>	
							</td>
						</tr>
					</tbody>
				</table>
				
				<input id="add-new-rating-item-btn" class="button button-primary" value="<?php _e( 'Add New Rating Item', 'multi-rating' ); ?>" type="submit" />
				<input type="hidden" id="form-submitted" name="form-submitted" value="false" />
			</form>
		</div>
		<?php
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function admin_assets() {
		
		wp_enqueue_script( 'jquery' );
		
		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( Multi_Rating::ID.'-nonce' )
		);

		wp_enqueue_script( 'mr-admin-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'admin.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true );
		wp_localize_script( 'mr-admin-script', 'mr_admin_data', $config_array );

		wp_enqueue_script( 'mr-frontend-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'frontend.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true );
		wp_localize_script( 'mr-frontend-script', 'mr_frontend_data', $config_array );
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mr-frontend-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'frontend.css', __FILE__ ) );
		wp_enqueue_style( 'mr-admin-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'admin.css', __FILE__ ) );
		
		// flot
		wp_enqueue_script( 'flot', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'flot-categories', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.categories.js', __FILE__ ), array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-time', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.time.js', __FILE__ ), array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-selection', plugins_url( 'js' . DIRECTORY_SEPARATOR . 'flot' . DIRECTORY_SEPARATOR . 'jquery.flot.selection.js', __FILE__ ), array( 'jquery', 'flot', 'flot-time' ) );
		
		// color picker
		wp_enqueue_style( 'wp-color-picker' );          
    	wp_enqueue_script( 'wp-color-picker' );
		
    	// date picker
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function assets() {
		
		wp_enqueue_script('jquery');
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mr-frontend-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'frontend.css', __FILE__ ) );
		
		// Allow support for other versions of Font Awesome
		$include_font_awesome = $this->style_settings[Multi_Rating::INCLUDE_FONT_AWESOME_OPTION];
		$font_awesome_version = $this->style_settings[Multi_Rating::FONT_AWESOME_VERSION_OPTION];
		
		$icon_classes = mr_get_icon_classes( $font_awesome_version );
		
		if ( $include_font_awesome ) {
			if ( $font_awesome_version == '4.0.3' ) {
				wp_enqueue_style( 'fontawesome', 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
			} else if ( $font_awesome_version == '3.2.1' ) {
				wp_enqueue_style( 'fontawesome', 'http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' );
			} else if ( $font_awesome_version == '4.1.0' ) {
				wp_enqueue_style( 'fontawesome', 'http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' );
			}
		}
		
		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( Multi_Rating::ID.'-nonce' ),
				'icon_classes' => json_encode( $icon_classes )
		);
		
		wp_enqueue_script( 'mr-frontend-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'frontend.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true );
		wp_localize_script( 'mr-frontend-script', 'mr_frontend_data', $config_array );
	}
	
	
	/**
	 * Register AJAX actions
	 */
	public function add_ajax_actions() {
		add_action( 'wp_ajax_save_rating', array( $this, 'save_rating' ) );
		add_action( 'wp_ajax_nopriv_save_rating', array( $this, 'save_rating' ) );
		
		add_action( 'wp_ajax_save_rating_item_table_column', array( 'Rating_Item_Table', 'save_rating_item_table_column' ) );
	}
	
	/**
	 * Submits the rating for a post
	 * 
	 */
	public function save_rating() {
		$ajax_nonce = $_POST['nonce'];
		
		if ( wp_verify_nonce( $ajax_nonce, self::ID.'-nonce' ) ) {
			
			global $wpdb;
				
			$rating_items = $_POST['ratingItems'];
			$post_id = $_POST['postId'];
			$ip_address = mr_get_ip_address();
			$entry_date_mysql = current_time('mysql');
				
			$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
				
			// get username
			global $wp_roles;
			$current_user = wp_get_current_user();
			$username = $current_user->user_login;
		
			// check ip address date/time validation option
			$ip_address_datetime_validation = $this->general_settings[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION];
			if ( $ip_address_datetime_validation == true ) {
				
				// check IP address has not submitted a rating for the post ID within a duration of time
				$ip_address_datetime_validation_days_duration = 1 * 24 * 60 * 60; // 24 hous
				$previous_day_date = strtotime( $entry_date_mysql ) - $ip_address_datetime_validation_days_duration;
				$previous_day_date_mysql = date( 'Y-m-d H:i:s', $previous_day_date );
				
				$ip_address_check_query = 'SELECT * FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE ip_address = "' . $ip_address . '" AND post_id ='
						. $post_id . ' AND entry_date >= "' . $previous_day_date_mysql . '"';
	
				$rows = $wpdb->get_results($ip_address_check_query);
	
				if ( count( $rows ) > 0) {
					echo $custom_text_settings[ Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION ];
					
					die();
				}
			}
	
	
			// everything is OK so now insert the rating form entry and entry values into the database tables
			$wpdb->insert( $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
					'post_id' => $post_id,
					'entry_date' => $entry_date_mysql,
					'ip_address' => $ip_address,
					'username' => $username
			), array('%s', '%s', '%s', '%s') );
	
			$rating_item_entry_id = $wpdb->insert_id;
	
			foreach ( $rating_items as $rating_item ) {
				
				$rating_item_id = $rating_item['id'];
				$rating_item_value = $rating_item['value'];
					
				$wpdb->insert( $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_item_entry_id,
						'rating_item_id' => $rating_item_id,
						'value' => $rating_item_value
				), array('%d', '%d', '%d') );
			}
	
			echo $custom_text_settings[ Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION ];
		}
		
		die();
	}
	
	function add_custom_css() {
		?>
		<style type="text/css">
			<?php echo $this->style_settings[Multi_Rating::CUSTOM_CSS_OPTION]; ?>
			
			<?php 
			$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
			$star_rating_colour = $style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$star_rating_hover_colour = $style_settings[Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
			?>
			
			.mr-star-hover {
				color: <?php echo $star_rating_hover_colour; ?> !important;
			}
			.mr-star-full, .mr-star-half, .mr-star-empty {
				color: <?php echo $star_rating_colour; ?>;
			}
		</style>
		<?php 
	}
	
	/**
	 * About page
	 */
	function about_page() {
		
		// if version is less than 3.8 then manually add the necessary css missing from about.css
		if ( ! version_compare( get_bloginfo( 'version' ), '3.8', '>=' ) ) {
			?>
			<style type="text/css">
				.about-wrap .changelog .feature-section {
				    overflow: hidden;
				}
				.about-wrap .feature-section {
				    margin-top: 20px;
				}
				.about-wrap .feature-section.two-col > div {
				    position: relative;
				    width: 47.5%;
				    margin-right: 4.999999999%;
				    float: left;
				}
				.about-wrap .feature-section.col .last-feature {
				    margin-right: 0;
				}
				 .about-wrap hr {
				  	border: 0;
					border-top: 1px solid #DFDFDF;
				}
				.about-wrap {
					position: relative;
					margin: 25px 40px 0 20px;
					max-width: 1050px;
					font-size: 15px;
				}
				.about-wrap img {
					margin: 0;
					max-width: 100%;
					vertical-align: middle;
				}
				.about-wrap .changelog h2.about-headline-callout {
					margin: 1.1em 0 0.2em;
					font-size: 2.4em;
					font-weight: 300;
					line-height: 1.3;
					text-align: center;
				}
				.about-wrap .feature-section img {
				    margin-bottom: 20px !important;
				}
				.about-wrap h3 {
					margin: 1em 0 .6em;
					font-size: 1.5em;
					line-height: 1.5em;
				}
				.about-wrap .feature-section.three-col div {
					width: 29.75%;
				}
				.about-wrap .feature-section.two-col > div {
					margin-right: 4.8%;
				}
			</style>
		<?php 
		}
		?>
		
		<div class="wrap about-wrap">

			<h1><?php printf( __( 'Multi Rating v%s', 'multi-rating' ), Multi_Rating::VERSION ); ?></h1>
			
			<div class="about-text"><?php _e( 'The simplest rating plugin which allows visitors to rate a post based on multiple criteria and questions.', 'multi-rating' ); ?></div>

			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'getting_started';
				$page = Multi_Rating::ABOUT_PAGE_SLUG;
				$tabs = array (
						'getting_started' => __( 'Getting Started', 'multi-rating' ),
						'support' => __( 'Support', 'multi-rating' ),
						'multi_rating_pro' => __( 'Multi Rating Pro', 'multi-rating' )
				);
				
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} ?>
			</h2>
			
			<?php 
			if ( $current_tab == 'getting_started' ) { ?>	
			
				<div class="changelog">

					
					<p class="about-description"><?php _e( 'Use the tips below to help you get started.', 'multi-rating' ); ?></p>
					
					<div class="feature-section col three-col">
						<div class="col-1">
							<img src="<?php echo plugins_url( 'img' . DIRECTORY_SEPARATOR . 'add-new-rating-items.png' , __FILE__ ); ?>" />
							<h4><?php _e( 'Add New Rating Items', 'multi-rating' ); ?></h4>
							<p><?php printf( __( 'Setup your criteria and questions by <a href="admin.php?page=%s">adding new rating items</a>.', 'multi-rating' ), Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG ); ?></p>
						</div>
						<div class="col-2">
							<img src="<?php echo plugins_url( 'img' . DIRECTORY_SEPARATOR . 'auto-placement.png' , __FILE__ ); ?>" />
							<h4><?php _e( 'Auto Placement Settings' ); ?></h4>
							<p><?php printf( __( 'Use the <a href="admin.php?page=%s">automatic placement settings</a> to set the rating form and rating results to display on every post or page in default positions.', 'multi-rating-pro' ), Multi_Rating::SETTINGS_PAGE_SLUG ); ?></p>						
						</div>
						<div class="col-3 last-feature">
							<img src="<?php echo plugins_url( 'img' . DIRECTORY_SEPARATOR . 'view-frontend.png' , __FILE__ ); ?>" />
							<h4><?php _e( 'View the Frontend', 'multi-rating' ); ?></h4>
							<p><?php _e( 'If everything is setup correctly, the rating form and rating results should appear on your website!', 'multi-rating' ); ?></p>
						</div>
					</div>
				
					<hr>
				
					<div class="feature-section col two-col">
						<div class="col-1">
							<img src="<?php echo plugins_url( 'img' . DIRECTORY_SEPARATOR . 'shortcodes.png' , __FILE__ ); ?>" />
							<h4><?php _e( 'Shortcodes', 'multi-rating' ); ?></h4>
							<p><?php _e( '[display_rating_form] - displays the rating form</i>.', 'multi-rating' ); ?><br />
							<?php _e( '[display_top_rating_results] - displays the top rating results and includes an optional category filter', 'multi-rating' ); ?><br />
							<?php _e( '[display_rating_result] - displays the rating result', 'multi-rating' ); ?></p>
							<p><?php printf( __( 'Refer to the <a href="%s">documentation</a> for more information on the attributes available.', 'multi-rating' ), 'http://danielpowney.com/multi-rating/' ); ?></p>
						</div>
						<div class="col-2 last-feature">
							<img src="<?php echo plugins_url( 'img' . DIRECTORY_SEPARATOR . 'view-rating-results.png' , __FILE__ ); ?>" />
							<h4><?php _e( 'View Rating Results', 'multi-rating' ); ?></h4>
							<p><?php printf( __( 'View all <a href="admin.php?page=%s">rating results</a> and entries from the WP-admin.', 'multi-rating' ), Multi_Rating::RATING_RESULTS_PAGE_SLUG ); ?></p>
						</div>
					</div>
				</div>
				
				<hr>
				
				<div class="changelog under-the-hood">
					<div class="feature-section col three-col">
						<div>
							<h4><?php _e( 'Custom Post Types', 'multi-rating' ); ?></h4>
							<p><?php _e( 'If you want to use the plugin for pages and other post types you\'ll need to be enable them in the plugin settings.', 'multi-rating' ); ?></p>
									
							<h4><?php _e( 'Developer API', 'multi-rating' ); ?></h4>
							<p><?php _e( 'The API functions are located in the multi-rating-api.php file which contains a static class called Multi_Rating_API.', 'multi-rating' ); ?></p>
	
							<h4><?php _e( 'Font Awesome', 'multi-rating' ); ?></h4>
							<p><?php _e( 'Font Awesome versions 4.1.0, 4.0.3 and 3.2.1 are supported by the plugin.', 'multi-rating' ); ?></p>	
						</div>
						<div>
							<h4><?php _e( 'I18n & Custom Text', 'multi-rating' ); ?></h4>
							<p><?php _e( 'The plugin has been internationalized and is translation ready (.pot file in the languages directory). You can also modify the default text and messages.', 'multi-rating' ); ?></p>
				
							<h4><?php _e( 'Rich Snippets', 'multi-rating' ); ?></h4>
							<p><?php _e( '<a href="http://schema.org">schema.org</a> structured markup is be added to every page that allow search engines to display the aggregated rating results as rich snippets.', 'multi-rating' ); ?></p>
						</div>
						<div class="last-feature">
							<h4><?php _e( 'Export Rating Results', 'multi-rating' ); ?></h4>
							<p><?php _e( 'You can export the rating results to a CSV file.', 'multi-rating' ); ?></p>
							
							<h4><?php _e( 'Reports' ); ?></h4>
							
							<p><?php _e( 'You can view the number of rating entries per day over time.', 'multi-rating' ); ?></p>
							<h4><?php _e( 'Meta-box', 'multi-rating' ); ?></h4>
							<p><?php _e( 'There\'s a meta-box on the edit post page so that you can override the default settings (e.g. autu placement settings).', 'multi-rating' ); ?></p>
						</div>
				</div>
				
			</div>
		<?php } else if ( $current_tab == 'support') {
			?>
			<p><?php printf( __( 'All support for the free Multi Rating plugin should use the <a href="%s">WordPress.org support forum</a>.', 'multi-rating' ), 'https://wordpress.org/support/plugin/multi-rating' ); ?></p>
			<p><?php printf( __( 'Please use the <a href="%s">contact form</a> to send translation files or to contact me directly.', 'multi-rating' ), 'http://danielpowney.com/contact/' ); ?></p>
			<p><?php printf( __( 'Please <a href="%1$s" target="_blank">rate this plugin</a> on WordPress.org', 'multi-rating' ), 'http://wordpress.org/support/view/plugin-reviews/multi-rating?filter=5#postform' ); ?></p>
			
			<?php
		} else { // Multi Rating Pro
			?>
			<p><?php printf( __( '<a href="%s">Multi Rating Pro</a> provides advanced features to the free Multi Rating plugin.', 'multi-rating' ), 'http://danielpowney.com/downloads/multi-rating-pro/' ); ?></p>
			
			<p><strong><?php _e( 'The following key features are available in the Pro version:', 'multi-rating' ); ?></strong></p>
			<ol style="list-style-type:disc; margin-left: 2.5em;">
			    <li><?php _e( 'Multiple rating forms with different rating items', 'multi-rating' ); ?></li>
			    <li><?php _e( 'WordPress comments system integration', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Logged in users can update or delete their existing ratings', 'multi-rating' ); ?></li>
			    <li><?php _e( 'New shortcodes, API functions and widgets (e.g. display user reviews and individual rating item results)', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Rating forms can optionally include a name, e-mail and comment fields', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Ability to use text descriptions for select and radio options instead of numbers', 'multi-rating' ); ?></li>
			    <li><?php _e( 'View rating results per post and rating form in WP-admin backend', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Post, category and specific page filters to include (whitelist) or exclude (blacklist) automatic placement of the rating form and rating results' ); ?></li>
			    <li><?php _e( 'Options to exclude the home page and archive pages (i.e. Category, Tag, Author or a Date based pages)', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Thumbs up/down rating item type', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Display rating item results breakdown in a poll format', 'multi-rating' ); ?></li>
			</ol>
			
			<p><strong><?php _e( 'Other features available in the Pro version include:', 'multi-rating' ); ?></strong></p>
			<ol style="list-style-type:disc; margin-left: 2.5em;">
			    <li><?php _e( 'Allow/disallow anonymous user ratings option', 'multi-rating' ); ?></li>
			    <li><?php _e( 'Option to display the rating result back to the user when they submit a rating form', 'multi-rating' ); ?></li>
			    <li><?php _e( 'More filters on WP-admin rating results tables', 'multi-rating' ); ?></li>
			</ol>
			<?php
		}
	}
}


/**
 * Activate plugin
 */
function mr_activate_plugin() {
	
	if ( is_admin() ) {
		add_option(MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, true);
		Multi_Rating::activate_plugin();
	}

}
/**
 * Uninstall plugin
 */
function mr_uninstall_plugin() {
	
	if ( is_admin() ) {
		Multi_Rating::uninstall_plugin();
	}
}

// Activation and deactivation
register_activation_hook( __FILE__, 'mr_activate_plugin' );
register_uninstall_hook( __FILE__, 'mr_uninstall_plugin' );

$multi_rating = new Multi_Rating();

/**
 * Add plugin footer to admin dashboard
 *
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function mr_plugin_footer( $footer_text ) {
	
	$current_screen = get_current_screen();
	
	if ( $current_screen->parent_base == Multi_Rating::RATING_RESULTS_PAGE_SLUG ) {
		$plugin_footer = sprintf( __( 'Please <a href="%1$s" target="_blank">rate this plugin</a> on WordPress.org | '
				. 'Check out <a href="%2$s" target="_blank">Multi Rating Pro</a>!', 'multi-rating' ),
				'http://wordpress.org/support/view/plugin-reviews/multi-rating?filter=5#postform',
				'http://danielpowney.com/downloads/multi-rating-pro'
		);
		
		 return $plugin_footer . '<br />' . $footer_text;

	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'mr_plugin_footer' );

/**
 * Add to the WordPress version
 * 
 * @param $default
 */ 
function mr_footer_version ( $default ) {
	
	$current_screen = get_current_screen();
	
	if ( $current_screen->parent_base == Multi_Rating::RATING_RESULTS_PAGE_SLUG ) {
		return 'Multi Rating v' . Multi_Rating::VERSION . '<br />' . $default;
	}
	
	return $default;
}
add_filter ('update_footer', 'mr_footer_version', 999);