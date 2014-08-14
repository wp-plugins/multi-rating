<?php 

/**
 * Settings class
 * 
 * @author dpowney
 */
class MR_Settings {

	public $custom_text_settings = array();
	public $style_settings = array();
	public $position_settings = array();
	public $general_settings = array();
	
	/**
	 * Constructor
	 */
	function __construct() {
	
		if ( is_admin() ) {
			add_action('admin_init', array( &$this, 'register_settings' ) );			
		}
	
		$this->load_settings();
	}
	
	/**
	 * Reisters settings
	 */
	function register_settings() {
	
		$this->register_custom_text_settings();
		$this->register_style_settings();
		$this->register_general_settings();
		$this->register_position_settings();
	
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
				Multi_Rating::CUSTOM_CSS_OPTION 				=> $default_css,
				Multi_Rating::STAR_RATING_COLOUR_OPTION 		=> '#ffd700',
				Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION 	=> '#ffba00',
				Multi_Rating::INCLUDE_FONT_AWESOME_OPTION 		=> true,
				Multi_Rating::FONT_AWESOME_VERSION_OPTION 		=> '4.0.3'
		), $this->style_settings );
	
	
		$this->position_settings = array_merge( array(
				Multi_Rating::RATING_RESULTS_POSITION_OPTION 	=> '',
				Multi_Rating::RATING_FORM_POSITION_OPTION 		=> ''
		), $this->position_settings );
	
	
		$this->custom_text_settings = array_merge( array(
				Multi_Rating::CHAR_ENCODING_OPTION => '',
				Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION 			=> __( 'Please rate this', 'multi-rating' ),
				Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION 		=> __( 'Top Rating Results', 'multi-rating' ),
				Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION		=> __( 'Submit Rating', 'multi-rating' ),
				Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION => __( 'Your rating was %adjusted_star_result%/5.', 'multi-rating'),
				Multi_Rating::DATE_VALIDATION_FAIL_MESSAGE_OPTION 		=> __( 'You cannot submit a rating form for the same post multiple times.', 'multi-rating' ),
				Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION 			=> __( 'No rating results yet', 'multi-rating' )
		), $this->custom_text_settings );
	
	
		$this->general_settings = array_merge( array(
				Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION 		=> true,
				Multi_Rating::POST_TYPES_OPTION 						=> 'post',
				Multi_Rating::RATING_RESULTS_CACHE_OPTION				=> true
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
		add_settings_field( Multi_Rating::RATING_RESULTS_CACHE_OPTION, __( 'Enable rating results cache', 'multi-rating' ), array( &$this, 'field_rating_results_cache' ), Multi_Rating::GENERAL_SETTINGS, 'section_general' );
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
	 * Rating results cache
	 */
	function field_rating_results_cache() {
	?>
		<input type="checkbox" name="<?php echo Multi_Rating::GENERAL_SETTINGS;?>[<?php echo Multi_Rating::RATING_RESULTS_CACHE_OPTION; ?>]" value="true" <?php checked(true, $this->general_settings[Multi_Rating::RATING_RESULTS_CACHE_OPTION], true); ?> />
		<p class="description"><?php printf( __( 'Enable the rating results to be cached in the WordPress post meta table. The cache is refreshed whenever the rating form is submitted. You can also use the <a href="admin.php?page=%s">Tools</a> to clear the rating results cache.', 'multi-rating' ), Multi_Rating::TOOLS_PAGE_SLUG ); ?></p>
		<?php 
	}
	/**
	 * Sanitize the general settings
	 * 
	 * @param $input
	 * @return boolean
	 */
	function sanitize_general_settings( $input ) {
		
		// ip address datetime validation
		if ( isset( $input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] ) && $input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] == 'true' ) {
			$input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = true;
		} else {
			$input[Multi_Rating::IP_ADDRESS_DATE_VALIDATION_OPTION] = false;
		}
		
		// rating reulsts cache
		if ( isset( $input[Multi_Rating::RATING_RESULTS_CACHE_OPTION] )
				&& $input[Multi_Rating::RATING_RESULTS_CACHE_OPTION] == 'true' ) {
			$input[Multi_Rating::RATING_RESULTS_CACHE_OPTION] = true;
		} else {
			$input[Multi_Rating::RATING_RESULTS_CACHE_OPTION] = false;
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
	public function section_custom_text_desc() {
		echo '<p class="description">' . __( 'Modify the default text and messages.' , 'multi-rating' ) . '</p>';
	}
	
	/**
	 * Rating form submit button text setting
	 */
	public function field_rating_form_submit_button_text() {
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
		<p class="description"><?php _e( 'Substitutions: %star_result%, %adjusted_star_result%, %score_result%, %adjusted_score_result%, %percentage_result%, %adjusted_percentage_result% and %total_max_option_value%. e.g. "Your rating was %adjusted_star_result%/5".', 'multi-rating' ); ?>
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
}
?>