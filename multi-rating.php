<?php 
/*
Plugin Name: Multi Rating
Plugin URI: http://wordpress.org/plugins/multi-rating/
Description: A simple rating plugin which allows visitors to rate a post based on multiple criteria and questions.
Version: 2.2.1
Author: Daniel Powney
Author URI: danielpowney.com
License: GPL2
*/

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'shortcodes.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'utils.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'multi-rating-api.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-form-view.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rating-result-view.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'filters.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'legacy.php';
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
	VERSION = '2.2.1',
	ID = 'mr',
	PRO_HTML = '<p><a href="http://danielpowney.com/downloads/multi-rating-pro/">Multi Rating Pro</a> has extra features including multiple rating forms, displaying reviews, WordPress comments integration, showing individual rating item results, updating/deleting existing ratings and loads more!</p>',
	
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
	VERSION_OPTION								= 'mr_version_option',
	
	//values
	SCORE_RESULT_TYPE							= 'score',
	STAR_RATING_RESULT_TYPE						= 'star_rating',
	PERCENTAGE_RESULT_TYPE						= 'percentage',
	DO_NOT_SHOW									= 'do_not_show',
	SELECT_ELEMENT								= 'select',
	
	// pages
	SETTINGS_PAGE_SLUG							= 'mr_settings_page',
	RATING_ITEMS_PAGE_SLUG						= 'mr_rating_items',
	RATING_RESULTS_PAGE_SLUG					= 'mr_rating_results',
	ADD_NEW_RATING_ITEM_PAGE_SLUG				= 'mr_add_new_rating_item',
	
	// tabs
	RATING_RESULTS_TAB							= 'mr_rating_results_tab',
	RATING_RESULT_DETAILS_TAB					= 'mr_rating_result_details_tab',
	
	// post meta box
	RATING_FORM_POSITION_POST_META				= 'rating_form_position',
	RATING_RESULTS_POSITION_POST_META			= 'rating_results_position'
	;
	
	public $custom_text_settings = array();
	public $style_settings = array();
	public $position_settings = array();
	public $general_settings = array();
	
	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		// subjects can be a post type
		$sql_create_rating_subject_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_SUBJECT_TBL_NAME.' (
				rating_id bigint(20) NOT NULL AUTO_INCREMENT,
				post_type varchar(20) NOT NULL,
				PRIMARY KEY  (rating_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_subject_tbl);
		
		// subjects are rated by multiple rating items
		$sql_create_rating_item_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME.' (
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
		dbDelta($sql_create_rating_item_tbl);
		
		// rating item entries and results are saved
		$sql_create_rating_item_entry_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' (
				rating_item_entry_id bigint(20) NOT NULL AUTO_INCREMENT,
				post_id bigint(20) NOT NULL,
				entry_date datetime NOT NULL,
				ip_address varchar(100),
				username varchar(50),
				PRIMARY KEY  (rating_item_entry_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_item_entry_tbl);

		$sql_create_rating_item_entry_value_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME.' (
				rating_item_entry_value_id bigint(20) NOT NULL AUTO_INCREMENT,
				rating_item_entry_id bigint(20) NOT NULL,
				rating_item_id bigint(20) NOT NULL,
				value int(11) NOT NULL,
				PRIMARY KEY  (rating_item_entry_value_id)
		) ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_item_entry_value_tbl);
		
	}
	
	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {
		
		delete_option(Multi_Rating::GENERAL_SETTINGS);
		delete_option(Multi_Rating::CUSTOM_TEXT_SETTINGS);
		delete_option(Multi_Rating::POSITION_SETTINGS);
		delete_option(Multi_Rating::STYLE_SETTINGS);
		
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
	function __construct(){
		
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		
		if( is_admin() ) {
			add_action( 'admin_menu', array($this, 'add_admin_menus') );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		}
		
		add_action('init', array( &$this, 'load_settings' ) );
		
		add_action( 'admin_init', array( &$this, 'register_custom_text_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_style_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_position_settings' ) );
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta' ) );
		
		add_action('wp_head', array($this, 'add_custom_css'));

		$this->add_ajax_actions();
	}
	
	/**
	 * Adds the meta box container
	 */
	public function add_meta_box( $post_type ) {
		$post_types = $this->general_settings[Multi_Rating::POST_TYPES_OPTION];
		
		if ( !is_array($post_types) && is_string($post_types) ) {
			$post_types = array($post_types);
		}
		if ( $post_types != null && in_array( $post_type, $post_types )) {
			add_meta_box( 'mr_meta_box', 'Multi Rating', array( $this, 'display_meta_box_content' ), $post_type, 'side', 'high');
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
			<label for="rating-form-position">Rating Form Position</label>
			<select class="widefat" name="rating-form-position">
				<option value="<?php echo Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_form_position, true );?>>Do not show</option>
				<option value="" <?php selected('', $rating_form_position, true );?>>Use default settings</option>
				<option value="before_content" <?php selected('before_content', $rating_form_position, true );?>>Before content</option>
				<option value="after_content" <?php selected('after_content', $rating_form_position, true );?>>After content</option>
			</select>
		</p>
		<p>
			<label for="rating-results-position">Rating Result Position</label>
			<select class="widefat" name="rating-results-position">
				<option value="<?php echo Multi_Rating::DO_NOT_SHOW; ?>" <?php selected('do_not_show', $rating_results_position, true );?>>Do not show</option>
				<option value="" <?php selected('', $rating_results_position, true );?>>Use default settings</option>
				<option value="before_title" <?php selected('before_title', $rating_results_position, true );?>>Before title</option>
				<option value="after_title" <?php selected('after_title', $rating_results_position, true );?>>After title</option>
			</select>
		</p>
		<?php
	}
	
	/**
	 * Retrieve settings from DB and sets default options if not set
	 */
	function load_settings() {
		$this->style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
		$this->custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$this->position_settings = (array) get_option( Multi_Rating::POSITION_SETTINGS );
		$this->general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );	
		
		$default_css = addslashes(".top-rating-results .rank { font-weight: bold; }");
		
		// Merge with defaults
		$this->style_settings = array_merge( array(
				Multi_Rating::CUSTOM_CSS_OPTION => $default_css,
				Multi_Rating::STAR_RATING_COLOUR_OPTION => '#ffd700'
		), $this->style_settings );
		
		$this->position_settings = array_merge( array(
				Multi_Rating::RATING_RESULTS_POSITION_OPTION => '',
				Multi_Rating::RATING_FORM_POSITION_OPTION => ''
		), $this->position_settings );
		
		$this->custom_text_settings = array_merge( array(
				Multi_Rating::CHAR_ENCODING_OPTION => '',
				Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION => 'Please rate this post',
				Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION => 'Top Rating Results',
				Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION => 'Submit Rating',
				Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION => 'Rating form has been submitted successfully.',
				Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION => 'You cannot submit a rating form for the same post multiple times in a 24 hour period.',
				Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION => 'No rating results yet'
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
	 * General settings
	 */
	function register_general_settings() {
		register_setting( Multi_Rating::GENERAL_SETTINGS, Multi_Rating::GENERAL_SETTINGS, array( &$this, 'sanitize_general_settings' ) );
	
		add_settings_section( 'section_general', 'General Settings', array( &$this, 'section_general_desc' ), Multi_Rating::GENERAL_SETTINGS );
	
		add_settings_field( Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION, 'Rating form IP address & date validation', array( &$this, 'field_ip_address_date_validation' ), Multi_Rating::GENERAL_SETTINGS, 'section_general' );
		add_settings_field( Multi_Rating::POST_TYPES_OPTION, 'Post types', array( &$this, 'field_post_types' ), Multi_Rating::GENERAL_SETTINGS, 'section_general' );
	}
	function section_general_desc() {
		echo "";
	}
	function field_ip_address_date_validation() {
		?>
		<input type="checkbox" name="<?php echo Multi_Rating::GENERAL_SETTINGS;?>[<?php echo Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION], true); ?> />
		<p class="description">Restrict the same IP address from submitting a rating form within 24 hours for the same post.</p>
		<?php 
	}
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
		<p class="description">Select the post types that are applicable for multi rating.</p>
		<?php 
	}
	function sanitize_general_settings($input) {
		if ( isset( $input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] ) && $input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] == "true")
			$input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = true;
		else
			$input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = false;
	
		return $input;
	}
	
	/**
	 * Position settings
	 */
	function register_position_settings() {
		register_setting( Multi_Rating::POSITION_SETTINGS, Multi_Rating::POSITION_SETTINGS, array( &$this, 'sanitize_position_settings' ) );
	
		add_settings_section( 'section_position', 'Auto Placement Settings', array( &$this, 'section_position_desc' ), Multi_Rating::POSITION_SETTINGS );
	
		add_settings_field( Multi_Rating::RATING_RESULTS_POSITION_OPTION, 'Rating results position', array( &$this, 'field_rating_results_position' ), Multi_Rating::POSITION_SETTINGS, 'section_position' );
		add_settings_field( Multi_Rating::RATING_FORM_POSITION_OPTION, 'Rating form position', array( &$this, 'field_rating_form_position' ), Multi_Rating::POSITION_SETTINGS, 'section_position' );
	}
	function section_position_desc() {
		echo '<p>These settings allow you to automatically place the rating form and rating results on every post or page in default positions. You can override these settings for a particular post using the Multi Rating meta box in edit post page.</p>';
	}
	function field_rating_results_position() {
		?>
		<select name="<?php echo Multi_Rating::POSITION_SETTINGS; ?>[<?php echo Multi_Rating::RATING_RESULTS_POSITION_OPTION; ?>]">
			<option value="" <?php selected('', $this->position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION], true); ?>>None</option>
			<option value="before_title" <?php selected('before_title', $this->position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION], true); ?>>Before title</option>
			<option value="after_title" <?php selected('after_title', $this->position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION], true); ?>>After title</option>
		</select>
		<p class="description">Choose to automatically display the rating result before or after the post title for all selected post types.</p>
		<?php
	}
	function field_rating_form_position() {
		?>
		<select name="<?php echo Multi_Rating::POSITION_SETTINGS; ?>[<?php echo Multi_Rating::RATING_FORM_POSITION_OPTION; ?>]">
			<option value="" <?php selected('', $this->position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>>None</option>
			<option value="before_content" <?php selected('before_content', $this->position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>>Before content</option>
			<option value="after_content" <?php selected('after_content', $this->position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION], true); ?>>After content</option>
		</select>
		<p class="description">Choose to automatically display the rating form before or after the post content for all selected post types.</p>
		<?php
	}
	function sanitize_position_settings($input) {
		return $input;
	}
	
	
	/**
	 * Style settings
	 */
	function register_style_settings() {
		register_setting( Multi_Rating::STYLE_SETTINGS, Multi_Rating::STYLE_SETTINGS, array( &$this, 'sanitize_style_settings' ) );
	
		add_settings_section( 'section_style', 'Style Settings', array( &$this, 'section_style_desc' ), Multi_Rating::STYLE_SETTINGS );

		add_settings_field( Multi_Rating::CUSTOM_CSS_OPTION, 'Custom CSS', array( &$this, 'field_custom_css' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
		add_settings_field( Multi_Rating::STAR_RATING_COLOUR_OPTION, 'Star Rating Colour', array( &$this, 'field_star_rating_colour' ), Multi_Rating::STYLE_SETTINGS, 'section_style' );
		
	}
	function section_style_desc() {
	}
	function field_custom_css() {
		?>
		<textarea cols="50" rows="10" class="large-text" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::CUSTOM_CSS_OPTION; ?>]"><?php echo stripslashes($this->style_settings[Multi_Rating::CUSTOM_CSS_OPTION]); ?></textarea>
		<p class="description">Enter custom CSS to change the default style of the rating form and rating results</p>
		<?php 
	}	
	function field_star_rating_colour() {
		$star_rating_colour = $this->style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
		?>
   	 	<input type="text" id="star-rating-colour" name="<?php echo Multi_Rating::STYLE_SETTINGS; ?>[<?php echo Multi_Rating::STAR_RATING_COLOUR_OPTION; ?>]; ?>" value="<?php echo $star_rating_colour; ?>" />
    	<div id="star-rating-colorpicker"></div>
		<?php 
	}
	function sanitize_style_settings($input) {
		$input[Multi_Rating::CUSTOM_CSS_OPTION] = addslashes($input[Multi_Rating::CUSTOM_CSS_OPTION]);
		return $input;
	}

	/**
	 * Custom Text settings
	 */
	function register_custom_text_settings() {
		register_setting( Multi_Rating::CUSTOM_TEXT_SETTINGS, Multi_Rating::CUSTOM_TEXT_SETTINGS, array( &$this, 'sanitize_custom_text_settings' ) );
	
		add_settings_section( 'section_custom_text', 'Custom Text Settings', array( &$this, 'section_custom_text_desc' ), Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		add_settings_field( Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION, 'Rating form title text', array( &$this, 'field_rating_form_title_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION, 'Top rating results title text', array( &$this, 'field_top_rating_results_title_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION, 'Rating form submit button text', array( &$this, 'field_rating_form_submit_button_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION, 'Rating form submit success message', array( &$this, 'field_rating_form_submit_message' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION, 'Date validation failure message', array( &$this, 'field_date_validation_fail_message' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION, 'No rating results text', array( &$this, 'field_no_rating_results_text' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		add_settings_field( Multi_Rating::CHAR_ENCODING_OPTION, 'Character encoding', array( &$this, 'field_char_encoding' ), Multi_Rating::CUSTOM_TEXT_SETTINGS, 'section_custom_text' );
		
	}
	function section_custom_text_desc() {
		echo '<p class="description">Change the default text and messages.</p>';
	}
	function field_rating_form_submit_button_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION]; ?>" />
		<?php
	}	
	public function field_rating_form_submit_message() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION]; ?>" />
		<?php
	}
	public function field_date_validation_fail_message() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION]; ?>" />
		<?php
	}
	function field_rating_form_title_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}
	function field_top_rating_results_title_text() {
		?>
		<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION]; ?>" />
		<?php
	}	
	function field_no_rating_results_text() {
		?>
			<input type="text" name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION; ?>]" class="regular-text" value="<?php echo $this->custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION]; ?>" />
		<?php
	}		
	function field_char_encoding() {
		?>	
		<select name="<?php echo Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>[<?php echo Multi_Rating::CHAR_ENCODING_OPTION; ?>]">
		<option value="" <?php selected('', $this->custom_text_settings[Multi_Rating::CHAR_ENCODING_OPTION], true); ?>>Keep current charset (Recommended)</option>
	        <option value="utf8_general_ci" <?php selected('utf8_general_ci', $this->custom_text_settings[Multi_Rating::CHAR_ENCODING_OPTION], true); ?>>UTF-8 (try this first)</option>
	        <option value="latin1_swedish_ci" <?php selected('latin1_swedish_ci', $this->custom_text_settings[Multi_Rating::CHAR_ENCODING_OPTION], true); ?>>latin1_swedish_ci</option>
		</select>
		<?php
	}
	function sanitize_custom_text_settings($input) {
		global $wpdb;
		$character_encoding = $input[Multi_Rating::CHAR_ENCODING_OPTION];
	
		$old_character_set = $this->general_settings[Multi_Rating::CHAR_ENCODING_OPTION];
		if ($character_encoding != $old_character_set) {
			$tables = array( $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME );
			foreach ($tables as $table) {
				$rows = $wpdb->get_results( "DESCRIBE {$table}" );
				foreach ($rows as $row) {
					$name = $row->Field;
					$type = $row->Type;
					if (preg_match("/^varchar\((\d+)\)$/i", $type, $mat) || !strcasecmp($type, "CHAR")
							|| !strcasecmp($type, "TEXT") || !strcasecmp($type, "MEDIUMTEXT")) {
						$wpdb->query('ALTER TABLE ' . $table .' CHANGE ' . $name . ' ' . $name . ' ' . $type . ' COLLATE ' . $character_encoding);
					}
				}
			}
		}
	
		return $input;
	}

	public function add_admin_menus() {
		add_menu_page( __( 'Multi Rating', Multi_Rating::ID ), __( 'Multi Rating', Multi_Rating::ID ), 'manage_options', Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ), '', null );
	
		add_submenu_page(Multi_Rating::RATING_RESULTS_PAGE_SLUG,'','','manage_options',Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ));
		add_submenu_page(Multi_Rating::RATING_RESULTS_PAGE_SLUG,'Rating Results','Rating Results','manage_options',Multi_Rating::RATING_RESULTS_PAGE_SLUG, array( &$this, 'rating_results_page' ));
		add_submenu_page(Multi_Rating::RATING_RESULTS_PAGE_SLUG,'Rating Items','Rating Items','manage_options',Multi_Rating::RATING_ITEMS_PAGE_SLUG, array( &$this, 'rating_items_page' ));
		add_submenu_page(Multi_Rating::RATING_RESULTS_PAGE_SLUG,'Add New Rating Item','Add New Rating Item','manage_options',Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG, array( &$this, 'add_new_rating_item_page' ));
		add_submenu_page(Multi_Rating::RATING_RESULTS_PAGE_SLUG,'Settings','Settings','manage_options',Multi_Rating::SETTINGS_PAGE_SLUG, array( &$this, 'settings_page' ));
	}
	/**
	 * Shows the settings page
	 *
	 * @since 0.1
	 */
	public function settings_page() {
		?>
		<div class="wrap">
		
			<h2>Settings</h2>
			<?php echo Multi_Rating::PRO_HTML; ?>
			
			<?php 
			if ( isset( $_GET['updated'] ) && isset( $_GET['page'] ) ) {
				add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
			}
			
			settings_errors();
			
			if ( isset( $_POST['clear-database'] ) && $_POST['clear-database'] === "true" ) {
				global $wpdb;
				try {
					$rows = $wpdb->get_results( 'DELETE FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' WHERE 1' );
					$rows = $wpdb->get_results( 'DELETE FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME.' WHERE 1' );
					echo '<div class="updated"><p>Database cleared successfully.</p></div>';
				} catch ( Exception $e ) {
					echo '<div class="error"><p>An error has occured. ' . $e->getMessage() . '</p></div>';
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
				submit_button( $text = 'Clear database', $type = 'delete', $name = 'clear-database-btn', $wrap = false, $other_attributes = null );
				?>
				<p class="description">Clear all rating results from the database.</p>
			</form>
			
			<div class="clear" />
			<br />
			<hr />
			<?php echo Multi_Rating::PRO_HTML; ?>
			
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
			<?php echo Multi_Rating::PRO_HTML; ?>
			<h2 class="nav-tab-wrapper">
				<?php
				$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : Multi_Rating::RATING_RESULTS_TAB;
				$page = Multi_Rating::RATING_RESULTS_PAGE_SLUG;
				$tabs = array (Multi_Rating::RATING_RESULTS_TAB => 'Rating Results', Multi_Rating::RATING_RESULT_DETAILS_TAB => 'Rating Result Details');
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				} 
				?>
			</h2>
			<?php 
			
			if ($current_tab == Multi_Rating::RATING_RESULTS_TAB) {
				?>
				<form method="post" id="rating-item-entry-table-form">
					<?php 
					$rating_item_entry_table = new Rating_Item_Entry_Table();
					$rating_item_entry_table->prepare_items();
					$rating_item_entry_table->display();
					?>
				</form>
				<?php 
			} else if ($current_tab == Multi_Rating::RATING_RESULT_DETAILS_TAB) {
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
			
			<div class="clear" />
			<br />
			<hr />
			<?php echo Multi_Rating::PRO_HTML; ?>
			
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
			<h2>Rating Items<a class="add-new-h2" href="admin.php?page=<?php echo Multi_Rating::ADD_NEW_RATING_ITEM_PAGE_SLUG; ?>">Add New</a></h2>
			<?php echo Multi_Rating::PRO_HTML; ?>
			<form method="post" id="rating-item-table-form">
				<?php 
				$rating_item_table = new Rating_Item_Table();
				$rating_item_table->prepare_items();
				$rating_item_table->display();
				?>
			</form>
			
			<div class="clear" />
			<br />
			<hr />
			<?php echo Multi_Rating::PRO_HTML; ?>
			
		</div>
		<?php 
	}
	
	public function add_new_rating_item_page() {
		
		if ( isset( $_POST['form-submitted'] ) && $_POST['form-submitted'] === "true" ) {
			$error_message = '';
			$success_message = '';
			
			if (isset($_POST['desciption']) && isset($_POST['max-option-value']) && isset($_POST['default-option-value'])) {
	
				$description = $_POST['desciption'];
				if (strlen(trim($description)) == 0) {
					$error_message .= 'Description cannot be empty. ';
				}
				
				$type = $_POST['type'];
				if (strlen(trim($type)) == 0) {
					$type = Multi_Rating::SELECT_ELEMENT;
				}
				
				if (is_numeric($_POST['max-option-value']) == false) {
					$error_message .= 'Max option value cannot be empty and must be a whole number. ';
				}
				
				if (is_numeric($_POST['default-option-value']) == false) {
					$error_message .= 'Default option value cannot be empty and must be a whole number. ';
				}
				
				if (strlen($error_message) == 0) {
					global $wpdb;
					
					$max_option_value = intval($_POST['max-option-value']);
					$default_option_value = intval($_POST['default-option-value']);
					$weight = doubleval($_POST['weight']);
					
					$results = $wpdb->insert( $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME, 
							array( 
									'description' => $description,
									'max_option_value' => $max_option_value,
									'default_option_value' => $default_option_value,
									'weight' => $weight,
									'type' => $type
							));
					
					$success_message .= 'Rating item added successfully.';
				}
			} else {
				$error_message .= 'An error occured. Rating item could not be added.';
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
			<h2>Add New Rating Item</h2>
			<?php echo Multi_Rating::PRO_HTML; ?>
		
			<form method="post" id="add-new-rating-item-form">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">Description</th>
							<td>
								<textarea id="desciption" name="desciption" type="text" maxlength="255" cols="100"></textarea>	
								<p class="description">Enter a rating item description.</p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Type</th>
							<td>
								<select name="type" id="type">
									<option value="select">Select</option>
									<option value="radio">Radio</option>
									<option value="star_rating">Star Rating</option>
								</select>
								<p class="type">Do you want to want to display the rating item with a select, radio or using Fontawesome star rating icons?</p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Max option value</th>
							<td>
								<input id="max-option-value" name="max-option-value" type="text" value="" />
								<p class="description">This is used to determine the max rating item option value. i.e. if max option value is set to 5, the rating item options would be 0, 1, 2, 3, 4, 5.</p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Default option value</th>
							<td>
								<input id="default-option-value" name="default-option-value" type="text" value="" />
								<p class="description">This is used to default the selected option in the drop down.</p>	
							</td>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Weight</th>
							<td>
								<input id="weight" name="weight" type="text" value="1.0" />
								<p class="description">Each rating item can have different weights which effect the rating results. For example, a rating item with a larger weight will influence the rating result more.</p>	
							</td>
						</tr>
					</tbody>
				</table>
				
				<input id="add-new-rating-item-btn" class="button button-primary" value="Add New Rating Item" type="submit" />
				<input type="hidden" id="form-submitted" name="form-submitted" value="false" />
			</form>
			
			<div class="clear" />
			<br />
			<hr />
			<?php echo Multi_Rating::PRO_HTML; ?>
		</div>
		<?php
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function admin_assets(){
		wp_enqueue_script('jquery');
		
		$config_array = array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce(Multi_Rating::ID.'-nonce')
		);

		wp_enqueue_script('mr-admin-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'admin.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true);
		wp_localize_script('mr-admin-script', 'mr_admin_data', $config_array);

		wp_enqueue_script('mr-frontend-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'frontend.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true);
		wp_localize_script('mr-frontend-script', 'mr_frontend_data', $config_array);
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mr-frontend-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'frontend.css', __FILE__ ) );
		wp_enqueue_style( 'mr-admin-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'admin.css', __FILE__ ) );
		
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function assets(){
		wp_enqueue_script('jquery');
		$config_array = array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce(Multi_Rating::ID.'-nonce')
		);
		
		wp_enqueue_script('mr-frontend-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'frontend.js', __FILE__), array('jquery'), Multi_Rating::VERSION, true);
		wp_localize_script('mr-frontend-script', 'mr_frontend_data', $config_array);
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'mr-frontend-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'frontend.css', __FILE__ ) );
		
		wp_enqueue_style( 'fontawesome', "http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" );
		//<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		
	}
	
	
	/**
	 * Register AJAX actions
	 */
	public function add_ajax_actions() {
		add_action('wp_ajax_save_rating', array($this, 'save_rating'));
		add_action('wp_ajax_nopriv_save_rating', array($this, 'save_rating'));
		
		add_action('wp_ajax_save_rating_item_table_column', array('Rating_Item_Table', 'save_rating_item_table_column'));
	}
	
	/**
	 * Submits the rating for a post
	 * 
	 */
	public function save_rating() {
		$ajax_nonce = $_POST['nonce'];
		if (wp_verify_nonce($ajax_nonce, self::ID.'-nonce')) {
			global $wpdb;
				
			$rating_items = $_POST['ratingItems'];
			$post_id = $_POST['postId'];
			$ip_address = mr_get_ip_address();
			$entry_date_mysql = current_time('mysql');
				
		
				
			$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
				
			global $wp_roles;
			$current_user = wp_get_current_user();
			$username = $current_user->user_login;
		
		
			// check ip address date/time validation option
			$ip_address_datetime_validation = $this->general_settings[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION];
			if ($ip_address_datetime_validation == true) {
				// check IP address has not submitted a rating for the post ID within a duration of time
				$ip_address_datetime_validation_days_duration = 1 * 24 * 60 * 60; // 24 hous
				$previous_day_date = strtotime( $entry_date_mysql ) - $ip_address_datetime_validation_days_duration;
				$previous_day_date_mysql = date( 'Y-m-d H:i:s', $previous_day_date );
				$ip_address_check_query = 'SELECT * FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE ip_address = "' . $ip_address . '" AND post_id ='
				. $post_id . ' AND entry_date >= "' . $previous_day_date_mysql . '"';
	
				$rows = $wpdb->get_results($ip_address_check_query);
	
				if (count($rows) > 0) {
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
	
			foreach ($rating_items as $rating_item) {
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
		</style>
		<?php 
	}
}


function mr_activate_plugin() {
	if (is_admin()) {
		Multi_Rating::activate_plugin();
	}

}
function mr_uninstall_plugin() {
	if (is_admin()) {
		Multi_Rating::uninstall_plugin();
	}
}
// Activation and deactivation
register_activation_hook( __FILE__, 'mr_activate_plugin');
register_uninstall_hook( __FILE__, 'mr_uninstall_plugin' );

$multi_rating = new Multi_Rating();