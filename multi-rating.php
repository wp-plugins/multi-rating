<?php 
/*
Plugin Name: Multi Rating
Plugin URI: http://wordpress.org/plugins/multi-rating/
Description: A simple star rating plugin which allows visitors to rate a post based on multiple criteria and questions
Version: 1.1.1
Author: Daniel Powney
Author URI: danielpowney.com
License: GPL2
*/


require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'multi-rating-table.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'shortcodes.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'utils.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates.php';

/**
 * Multi_Rating class
 */
class Multi_Rating {

	// constants
	const
	VERSION = '1.1',
	ID = 'multi_rating',
	RATING_SUBJECT_TBL_NAME = 'rating_subject',
	RATING_ITEM_TBL_NAME = 'rating_item',
	RATING_ITEM_ENTRY_TBL_NAME = 'rating_item_entry',
	RATING_ITEM_ENTRY_VALUE_TBL_NAME = 'rating_item_entry_value';
	
	public $general_settings = array();
	
	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		// FIXME do we need a table for subject?
		// Subject can be a post type
		$sql_create_rating_subject_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_SUBJECT_TBL_NAME.' (' .
		'rating_id bigint(20) NOT NULL AUTO_INCREMENT,' .
		'post_type varchar(20) NOT NULL,' .
		'PRIMARY KEY (rating_id)' .
		') ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_subject_tbl);
		
		// TODO
		
		// Subject are rated by multiple rating items
		$sql_create_rating_item_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME.' ('.
		'rating_item_id bigint(20) NOT NULL AUTO_INCREMENT,'.
		'rating_id bigint(20) NOT NULL,'.
		'description varchar(255) NOT NULL,'.
		'max_rating_value int(11),'.
		'default_rating_value int(11),'.
		'active tinyint(1) DEFAULT 1,'.
		'weight double precision DEFAULT 1.0,'.
		'PRIMARY KEY (rating_item_id)'.
		') ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_item_tbl);
		
		// The rating item entries are saved
		$sql_create_rating_item_entry_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' ('.
		'rating_item_entry_id bigint(20) NOT NULL AUTO_INCREMENT,'.
		'post_id bigint(20) NOT NULL,'.
		'entry_date datetime NOT NULL,'.
		'ip_address varchar(100),'.
		'user_id bigint(20),'.
		'PRIMARY KEY (rating_item_entry_id)'.
		') ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_item_entry_tbl);
			
		$sql_create_rating_item_entry_value_tbl = 'CREATE TABLE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME.' ('.
		'rating_item_entry_value_id bigint(20) NOT NULL AUTO_INCREMENT,'.
		'rating_item_entry_id bigint(20) NOT NULL,'.
		'rating_item_id bigint(20) NOT NULL,'.
		'value int(11) NOT NULL,'.
		'PRIMARY KEY (rating_item_entry_value_id)'.
		') ENGINE=InnoDB AUTO_INCREMENT=1;';
		dbDelta($sql_create_rating_item_entry_value_tbl);
		
	}
	
	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {
		
		// TODO make option name unique
		delete_option('general-settings');
		
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
		add_action('init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		
		add_action('admin_enqueue_scripts', array($this, 'assets'));
		
		add_action('wp_head', array($this, 'custom_css_style'));
		
		// Create settings page, add JavaScript and CSS
		if( is_admin() ) {
			add_action('admin_menu', array($this, 'create_options_page'));
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		} else {

			add_filter( 'the_content', array(&$this, 'filter_the_content' ) );
			add_filter( 'the_title', array(&$this, 'filter_the_title' ) );
			
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		}
				
		add_action( 'widgets_init', array($this, 'register_widgets') );

		Multi_Rating::add_ajax_actions();
	}
	
	
	/**
	 * Retrieve settings from DB and sets default options if not set
	 */
	function load_settings() {
		$this->general_settings = (array) get_option( 'general-settings' );
		
		$default_css = addslashes(
"form.ratingForm {
	/* i.e. rating form custom CSS:
	border: 3px solid #BABABA;
	background-color: #A1BED4;
	-webkit-border-radius: 10px;
	-moz-border-radius: 10px;
	border-radius: 10px;
	padding: 10px;
	margin-top: 20px;
	width: auto; */
}");
		
		// Merge with defaults
		$this->general_settings = array_merge( array(
				'rating_results_display' => '',
				'rating_form_display' => '',
				'ip_address_datetime_validation' => true,
				'post_types' => 'post',
				'custom_css' => $default_css,
				'stars_image_height' => '25',
				'character_encoding' => ''
		), $this->general_settings );
		
		update_option('general-settings', $this->general_settings);
	}
	
	
	/**
	 * Register the General settings
	 */
	function register_general_settings() {
		register_setting( 'general-settings', 'general-settings', array( &$this, 'sanitize_general_settings' ) );
	
		add_settings_section( 'section_general', 'General Settings', array( &$this, 'section_general_desc' ), 'general-settings' );
	
		add_settings_field( 'rating_results_display', 'Rating results display', array( &$this, 'field_rating_results_display' ), 'general-settings', 'section_general' );
		add_settings_field( 'rating_form_display', 'Rating form display', array( &$this, 'field_rating_form_display' ), 'general-settings', 'section_general' );
		add_settings_field( 'ip_address_datetime_validation', 'Rating form IP address date time validation', array( &$this, 'field_ip_address_datetime_validation' ), 'general-settings', 'section_general' );
		
		add_settings_field( 'post_types', 'Post types', array( &$this, 'field_post_types' ), 'general-settings', 'section_general' );
		
		add_settings_field( 'custom_css', 'Custom CSS', array( &$this, 'field_custom_css' ), 'general-settings', 'section_general' );
		
		add_settings_field( 'stars_image_height', 'Stars image height', array( &$this, 'field_stars_image_height' ), 'general-settings', 'section_general' );
		
		add_settings_field( 'character_encoding', 'Character encoding', array( &$this, 'field_character_encoding' ), 'general-settings', 'section_general' );
	}
	
		
	/**
	 * General settings description
	 */
	function section_general_desc() {
		echo "";
	}
	
	function field_ip_address_datetime_validation() {
		?>
		<input type="checkbox" name="general-settings[ip_address_datetime_validation]" value="true" <?php checked(true, $this->general_settings['ip_address_datetime_validation'], true); ?> />
		<p class="description">Restrict a unique IP address from submitting a rating form only once in a 24 hour period.</p>
		<?php 
	}
	
	function field_custom_css() {
	?>
		<textarea cols="100" rows="10" name="general-settings[custom_css]"><?php echo stripslashes($this->general_settings['custom_css']); ?></textarea>
		<p class="description">Enter custom CSS to change the default style of the rating form and rating results</p>
		<?php 
	}
	
	function field_stars_image_height() {
		$stars_image_height = $this->general_settings['stars_image_height'];
		?>
			<select name="general-settings[stars_image_height]">
				<option value="15" <?php if ($stars_image_height == "15") { echo ' selected="selected"'; } ?>>Small 15px</option>
				<option value="20" <?php if ($stars_image_height == "20") { echo ' selected="selected"'; } ?>>Medium 20px</option>
				<option value="25" <?php if ($stars_image_height == "25") { echo ' selected="selected"'; } ?>>Large 25px</option>
			</select>
			<p class="description">Set the height of the stars image.</p>
			<?php 
		}
	
	function field_post_types() {
		$post_types = get_post_types( '', 'names' );
		$post_types_checked = $this->general_settings['post_types'];
		
		foreach ( $post_types as $post_type ) {
			echo '<input type="checkbox" name="general-settings[post_types][]" value="' . $post_type . '"';
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
		<p class="description">Check the post types to apply multi rating to.</p>
		<?php 
	}
	function field_rating_results_display() {
		?>
		<select name="general-settings[rating_results_display]">
			<option value="" <?php selected('', $this->general_settings['rating_results_display'], true); ?>>None</option>
			<option value="before_title" <?php selected('before_title', $this->general_settings['rating_results_display'], true); ?>>Before title</option>
			<option value="after_title" <?php selected('after_title', $this->general_settings['rating_results_display'], true); ?>>After title</option>
		</select>
		<p class="description">Select the display position of the rating results for all posts. You can also use a custom shortcode <code>[displayRatingResult]</code> or function <code>display_rating_result()</code>.</p>
		<?php
	}
	
	function field_rating_form_display() {
		?>
		<select name="general-settings[rating_form_display]">
			<option value="" <?php selected('', $this->general_settings['rating_form_display'], true); ?>>None</option>
			<option value="before_content" <?php selected('before_content', $this->general_settings['rating_form_display'], true); ?>>Before content</option>
			<option value="after_content" <?php selected('after_content', $this->general_settings['rating_form_display'], true); ?>>After content</option>
		</select>
		<p class="description">Select the display position of the rating form for all posts. You can also use a custom shortcode <code>[displayRatingForm]</code> or function <code>display_rating_form()</code>.</p>
		<?php
	}
	
	function field_character_encoding() {
		?>
			<select name="general-settings[character_encoding]">
				 <option value="" <?php selected('', $this->general_settings['character_encoding'], true); ?>>Keep current charset (Recommended)</option>
		        <option value="utf8_general_ci" <?php selected('utf8_general_ci', $this->general_settings['character_encoding'], true); ?>>UTF-8 (try this first)</option>
		        <option value="latin1_swedish_ci" <?php selected('latin1_swedish_ci', $this->general_settings['character_encoding'], true); ?>>latin1_swedish_ci</option>
			</select>
			<p class="description"></p>
			<?php
		}
	
	/**
	 * Sanitize and validate General settings
	 *
	 * @param unknown_type $input
	 * @return boolean
	 */
	function sanitize_general_settings($input) {
		
		if ( isset( $input['ip_address_datetime_validation'] ) && $input['ip_address_datetime_validation'] == "true")
			$input['ip_address_datetime_validation'] = true;
		else
			$input['ip_address_datetime_validation'] = false;
		
		$input['custom_css'] = addslashes($input['custom_css']);
		
		global $wpdb;
		$character_encoding = $input["character_encoding"];
		
		$old_character_set = $this->general_settings['character_encoding'];
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

	/**
	 * Saves column edit in rating item table
	 *
	 * @since 1.0
	 */
	public function save_column() {
		global $wpdb;
		
		$ajax_nonce = $_POST['nonce'];
		if (wp_verify_nonce($ajax_nonce, self::ID.'-nonce')) {
			$column = $_POST['column'];
			
			// prevent SQL injection
			if (! ( $column == Multi_Rating_Table::DESCRIPTION_COLUMN || $column == Multi_Rating_Table::MAX_RATING_VALUE_COLUMN 
					|| $column == Multi_Rating_Table::DEFAULT_RATING_VALUE_COLUMN || $column == Multi_Rating_Table::WEIGHT_COLUMN ) ) {
				echo 'An error occured';
				die();
			}
			
			$value = isset($_POST['value']) ? addslashes($_POST['value']) : '';
			$rating_item_id = isset($_POST['ratingItemId']) ? $_POST['ratingItemId'] : '';
			$result = $wpdb->query('UPDATE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME.' SET '. $column . ' = "' . $value . '" WHERE ' . Multi_Rating_Table::RATING_ITEM_ID_COLUMN .' = ' .$rating_item_id) ;
			if ($result === FALSE) {
				echo "An error occured";
			}
			echo $result;
		}
		die();
	}

	/**
	 * Creates the admin options page
	 *
	 * @since 0.1
	 */
	public function create_options_page() {
		add_options_page(__('Multi Rating', self::ID), __('Multi Rating', self::ID), 'manage_options', self::ID, array($this, 'settings_page'));
	}

	/**
	 * Shows the admin options page
	 *
	 * @since 0.1
	 */
	public function settings_page() {
		?>
	<div class="wrap">
		<!--  <div id="icon" class="icon32" style="background: url('<?php // echo plugins_url( 'multi-rating-16.ico', __FILE__ ); ?>') no-repeat left top; width: 32px; height: 32px; background-size: 100%;"></div> -->
		<h2>Multi Rating
			<form id="paypal-form" style="display: inline-block;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="display: inline-block;">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="5BDJEA4KANDZW">
				<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" width="1" height="1">
			</form>
		</h2>
		<?php 
		
		$error_message = "";
		$success_message = "";
		
		// Save rating item form submit
		if ( isset( $_POST['formSubmitted'] ) && $_POST['formSubmitted'] === "true" ) {
			global $wpdb;
			if (isset($_POST['ratingItemDesc']) && isset($_POST['maxRatingValue']) && isset($_POST['defaultRatingValue'])) {
				// TODO check description and validate empty fields
				
				$description = $_POST['ratingItemDesc'];
				if (strlen(trim($description)) == 0)
					$error_message .= 'Description cannot be empty. ';
				
				if (is_numeric($_POST['maxRatingValue']) == false)
					$error_message .= 'Max rating value must be a whole number. ';
				
				if (is_numeric($_POST['defaultRatingValue']) == false)
					$error_message .= 'Default rating value must be a whole number. ';

				if (strlen($error_message) == 0) {
					$max_rating_value = intval($_POST['maxRatingValue']);
					$default_rating_value = intval($_POST['defaultRatingValue']);
					$weight = doubleval($_POST['weight']);
					$results = $wpdb->insert(  $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME, array( 'description' => $description, 'max_rating_value' => $max_rating_value, 'default_rating_value' => $default_rating_value, 'weight' => $weight) );
					$success_message .= 'New rating item added successfully. ';
				}
			} else {
				$error_message .= 'An error occured. Rating item could not be added.';
			}
		}
		
		// check clear database
		else if ( isset( $_POST['clear-database-flag'] ) && $_POST['clear-database-flag'] === "true" ) {
			global $wpdb;
			try {
				$rows = $wpdb->get_results( 'DELETE FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME.' WHERE 1' );
				$rows = $wpdb->get_results( 'DELETE FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME.' WHERE 1' );
				$success_message .= 'Database rating item entries cleared successfully.';
			} catch ( Exception $e ) {
				$error_message .= 'An error has occured. ' . $e->getMessage();
			}
		}
		
		if ( strlen( $error_message ) > 0)
			echo '<div class="error"><p>' . $error_message . '</p></div>';
		if ( strlen( $success_message ) > 0)
			echo '<div class="updated"><p>' . $success_message . '</p></div>';
		
		?>
		
		<form method="post" action="options.php">
			<?php
			wp_nonce_field( 'update-options' );
			settings_fields( 'general-settings' );
			do_settings_sections( 'general-settings' );
			submit_button();
			?>
		</form>
		
		<h3>Rating Items</h3>
		<form method="post" id="rating-items-form">
			
			<?php 
			$multi_rating_table = new Multi_Rating_Table();
			$multi_rating_table->prepare_items();
			$multi_rating_table->display();
			?>
		</form>
		
		<br /><h3>New Rating Item</h3>
		<form method="post" id="add-new-form">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">Description</th>
						<td>
							<textarea id="ratingItemDesc" name="ratingItemDesc" type="text" maxlength="255" cols="50"></textarea>	
							<p class="description">Enter the rating item description.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Max rating value</th>
						<td>
							<select id="maxRatingValue" name="maxRatingValue">
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
							</select>	
							<p class="description">Select a maximum rating value which the user can choose.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Default rating value</th>
						<td>
							<select id="defaultRatingValue" name="defaultRatingValue">
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
							</select>
							<p class="description">Select a default rating value.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Weight</th>
						<td>
							<input id="weight" name="weight" type="text" value="1.0" />
							<p class="description">Enter a weight for the rating item.</p>	
						</td>
					</tr>
				</tbody>
			</table>
			
			<input id="createRatingItemBtn" class="button button-primary" value="Add New" type="submit" />
			<input type="hidden" id="formSubmitted" name="formSubmitted" value="false" />
		</form>
		
		<br /><h3>Rating Results</h3>
		
		<form method="post">
			<input type="hidden" name="clear-database-flag" id="clear-database-flag" value="false" />
			<?php 
			submit_button( $text = 'Clear database', $type = 'delete', $name = 'clear-database-btn', $wrap = false, $other_attributes = null );
			?>
			<p class="description">Clear all rating results from the database..</p>
		</form>

	</div>
	<?php 
	}
	
	/**
	 * Registers multi rating widgets
	 */
	function register_widgets() {
		register_widget( 'Multi_Rating_Widget' );
		register_widget( 'Multi_Rating_Top_Results_Widget' );
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function admin_assets(){
		wp_enqueue_script('jquery');
		$config_array = array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'ajaxNonce' => wp_create_nonce(self::ID.'-nonce')
		);

		wp_enqueue_script('multi-rating-admin-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'multi-rating-admin.js', __FILE__), array('jquery'), self::VERSION, true);
		wp_localize_script('multi-rating-admin-script', 'multiRatingLocalData', $config_array);

		wp_enqueue_script('multi-rating-form-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'multi-rating-form.js', __FILE__), array('jquery'), self::VERSION, true);
		wp_localize_script('multi-rating-form-script', 'multiRatingLocalData', $config_array);
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'multi-rating-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'multi-rating.css', __FILE__ ) );
		
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function assets(){
		wp_enqueue_script('jquery');
		$config_array = array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'ajaxNonce' => wp_create_nonce(self::ID.'-nonce')
		);
		
		wp_enqueue_script('multi-rating-form-script', plugins_url('js' . DIRECTORY_SEPARATOR . 'multi-rating-form.js', __FILE__), array('jquery'), self::VERSION, true);
		wp_localize_script('multi-rating-form-script', 'multiRatingLocalData', $config_array);
		
		// Add simple table CSS for rating form
		wp_enqueue_style( 'multi-rating-style', plugins_url( 'css' . DIRECTORY_SEPARATOR . 'multi-rating.css', __FILE__ ) );
		
	}
	
	
	/**
	 * Register AJAX call actions
	 */
	public function add_ajax_actions() {
		add_action('wp_ajax_delete_rating_item', array($this, 'delete_rating_item'));
		add_action('wp_ajax_nopriv_delete_rating_item', array($this, 'delete_rating_item'));

		add_action('wp_ajax_save_column', array($this, 'save_column'));
		// add_action('wp_ajax_nopriv_save_column', array($this, 'save_column'));
		
		// client ajax call
		add_action('wp_ajax_submit_rating', array($this, 'submit_rating'));
		add_action('wp_ajax_nopriv_submit_rating', array($this, 'submit_rating'));
	}
	
	
	/**
	 * Submits the rating for a post
	 * 
	 */
	public function submit_rating() {
		
		$ajax_nonce = $_POST['nonce'];
		if (wp_verify_nonce($ajax_nonce, self::ID.'-nonce')) {
			global $wpdb;
			
			$rating_items = $_POST['ratingItems'];
			$post_id = $_POST['postId'];
			$ip_address = get_ip_address();
			$entry_date_mysql = current_time('mysql');
			$user_id = '';
			
			$ip_address_datetime_validation = $this->general_settings['ip_address_datetime_validation'];
			$submit_rating_check = true;
			if ($ip_address_datetime_validation == true) {
				// check IP address has not submitted a rating for the post ID within the last 24 hours
				$previous_day_date = strtotime( $entry_date_mysql ) - (1 * 24 * 60 * 60);
				$previous_day_date_mysql = date( 'Y-m-d H:i:s', $previous_day_date );
				$ip_address_check_query = 'SELECT * FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE ip_address = "' . $ip_address . '" AND post_id =' . $post_id . ' AND entry_date >= "' . $previous_day_date_mysql . '"';
					
				$rows = $wpdb->get_results($ip_address_check_query);

				if (count($rows) > 0)
					$submit_rating_check = false;
			}				
			
			if ($submit_rating_check == true) {
				$wpdb->insert( $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
						'post_id' => $post_id,
						'entry_date' => $entry_date_mysql,
						'ip_address' => $ip_address,
						'user_id' => $user_id
				), array('%s', '%s', '%s', '%d') );
				
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
				
				echo 'Your rating has been submitted successfully.';
			} else {
				echo 'You cannot submit a rating for the same post multiple times in a 24 hour period.';
			}
		}
		die();
	
	}
	
	/**
	 * Filters the_content()
	 *
	 * @param $content
	 * @return filtered content
	 */
	function filter_the_content($content) {
	
		if (!in_the_loop())
			return $content;
	
		$post_types_checked = $this->general_settings['post_types'];
		if (!isset($post_types_checked) || (isset($post_types_checked) && !is_array($post_types_checked)))
			return $content;
		$post_type = get_post_type();
		if (!in_array($post_type, $post_types_checked))
			return $content;
	
		$rating_results_display = $this->general_settings['rating_results_display'];
		$rating_form_display = $this->general_settings['rating_form_display'];
	
		$filtered_content = '';
	
		if ($rating_results_display == 'before_content') {
			$filtered_content .= display_rating_result(array('show_no_result_text' => false));
		}
		if ($rating_form_display == 'before_content') {
			$filtered_content .= display_rating_form('');
		}
	
		$filtered_content .= $content;
	
		if ($rating_results_display == 'after_content') {
			$filtered_content .= display_rating_result(array('show_no_result_text' => false));
		}
		if ($rating_form_display == 'after_content') {
			$filtered_content .= display_rating_form('');
		}
	
		return $filtered_content;
	}
	
	
	/**
	 * Filters the_title()
	 *
	 * @param $title
	 * @return filtered title
	 */
	function filter_the_title($title) {
	
		if (!in_the_loop())
			return $title;
	
		$post_types_checked = $this->general_settings['post_types'];
		if (!isset($post_types_checked) || (isset($post_types_checked) && !is_array($post_types_checked)))
			return $title;
		$post_type = get_post_type();
		if (!in_array($post_type, $post_types_checked))
			return $title;
	
		$rating_results_display = $this->general_settings['rating_results_display'];
		$rating_form_display = $this->general_settings['rating_form_display'];
	
		$filtered_title = '';
	
		if ($rating_results_display == 'before_title') {
			$filtered_title .= display_rating_result(array('show_no_result_text' => true));
		}
		if ($rating_form_display == 'before_title') {
			$filtered_title .= display_rating_form('');
		}
	
		$filtered_title .= $title;
	
		if ($rating_results_display == 'after_title') {
			$filtered_title .= display_rating_result(array('show_no_result_text' => true));
		}
		if ($rating_form_display == 'after_title') {
			$filtered_title .= display_rating_form('');
		}
	
		return $filtered_title;
	}
	
	function custom_css_style() {
		// TODO do we need to check if HTML5 is supported?
		?>
		<style type="text/css">
			<?php echo $this->general_settings['custom_css']; ?>
		</style>
		<?php 
	}
}


// Activation and deactivation
register_activation_hook( __FILE__, 'mr_activate_plugin');
register_uninstall_hook( __FILE__, 'mr_uninstall_plugin' );
//register_deactivation_hook( __FILE__, 'mr_uninstall_plugin' );
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

$multi_rating = new Multi_Rating();