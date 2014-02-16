<?php 

/**
 * API functions for multi rating:
 * <ul>
 * <li>get_rating_items( $post_type? )</li>
 * <li>get_rating_items_by_rating_item_entry( $rating_item_entry_id )</li>
 * <li>get_total_weight( $rating_items )</li>
 * <li>calculate_rating_result( $post_id, $rating_items )</li>
 * <li>calculate_rating_item_entry_result( $rating_item_entry_id, $rating_items? )</li>
 * <li> get_top_rating_results( $count )</li>
 * <li>display_rating_form( $post_id? , $title? , $before_title? , $after_title? , $submit_button_text? )</li>
 * <li>display_rating_result( $post_id?, $no_rating_results_text?  )</li>
 * <li>display_top_rating_results( $count?, $title?, $before_title?, $after_title? )</li>
 * </ul>
 * 
 * ? = optional
 * 
 * @author dpowney
 *
 */
class Multi_Rating_API {
	
	/**
	 * Get all rating items
	 *
	 * @param unknown_type $post_type
	 * @return multitype:multitype:NULL
	 */
	public static function get_rating_items($post_type = null) {
		global $wpdb;
	
		$rating_subject_query = 'SELECT rating_id FROM '.$wpdb->prefix.Multi_Rating::RATING_SUBJECT_TBL_NAME;
		if (isset($post_type)) {
			$rating_subject_query .= ' WHERE post_type = "' . $post_type . '"';
		}
	
		$rating_id = $wpdb->get_var( $rating_subject_query, 0, 0 );
	
		$rating_items_query = 'SELECT rating_item_id, default_option_value, description, max_option_value, weight FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE rating_id = "' . $rating_id . '" AND active = 1';
		$rating_item_rows = $wpdb->get_results($rating_items_query);
	
		$rating_items = array();
		foreach ($rating_item_rows as $rating_item_row) {
			
			$rating_item_id = $rating_item_row->rating_item_id;
			$weight = $rating_item_row->weight;
			$description = $rating_item_row->description;
			$default_option_value = $rating_item_row->default_option_value;
			$max_option_value = $rating_item_row->max_option_value;
			
			$rating_items[$rating_item_id] = array(
					'max_option_value' => $max_option_value,
					'weight' => $weight,
					'rating_item_id' => $rating_item_id,
					'description' => $description,
					'default_option_value' => $default_option_value
			);
		}
	
		return $rating_items;
	}
	
	/**
	 * Get rating items ny rating item entry id
	 *
	 * @param unknown_type $post_type
	 * @return multitype:multitype:NULL
	 */
	public static function get_rating_items_by_rating_item_entry($rating_item_entry_id) {
		global $wpdb;

		// TODO optimize db query for performance
		$rating_items_query = 'SELECT DISTINCT ri.rating_item_id, ri.rating_id, ri.description, ri.default_option_value, '
		. 'ri.max_option_value, ri.weight, ri.active FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . ' AS ri, '
		. $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' AS rie, ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME 
		. ' AS riev WHERE riev.rating_item_entry_id = rie.rating_item_entry_id AND rie.rating_item_entry_id =  "' . $rating_item_entry_id . '"';
		$rating_item_rows = $wpdb->get_results($rating_items_query);
		
		$rating_items = array();
		foreach ($rating_item_rows as $rating_item_row) {
				
			$rating_item_id = $rating_item_row->rating_item_id;
			$weight = $rating_item_row->weight;
			$description = $rating_item_row->description;
			$default_option_value = $rating_item_row->default_option_value;
			$max_option_value = $rating_item_row->max_option_value;
				
			$rating_items[$rating_item_id] = array(
					'max_option_value' => $max_option_value,
					'weight' => $weight,
					'rating_item_id' => $rating_item_id,
					'description' => $description,
					'default_option_value' => $default_option_value
			);
		}
		
		return $rating_items;
	}
	
	/**
	 * Calculates the total weight of rating items
	 * 
	 * @param unknown_type $rating_items
	 */
	public static function get_total_weight($rating_items) {
		
		$total_weight = 0;
	
		foreach ($rating_items as $rating_item => $rating_item_array) {
			$total_weight += $rating_item_array['weight'];
		}
		return $total_weight;
	}
	
	/**
	 * Calculates rating result. 
	 * 
	 * @param unknown_type $post_id
	 * @param unknown_type $rating_items
	 */
	public static function calculate_rating_result($post_id, $rating_items) {
		global $wpdb;
		
		if ($rating_items == null)
			return;
		
		$rating_item_entry_query = 'SELECT * FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE post_id = ' . $post_id;
		$rating_item_entry_rows = $wpdb->get_results($rating_item_entry_query);
	
		$total_weight = Multi_Rating_API::get_total_weight($rating_items);
		$rating_item_entry_result_total = 0;
		$entries = 0;
		// process all entries for the post and construct a rating result for each post
		foreach ($rating_item_entry_rows as $rating_item_entry_row) {
			$total_value = 0;
	
			// retrieve the entry values for each rating item
			$rating_item_entry_id = $rating_item_entry_row->rating_item_entry_id;
			
			$rating_item_entry_result = Multi_Rating_API::calculate_rating_item_entry_result($rating_item_entry_id, $rating_items);
			$rating_item_entry_result_total += $rating_item_entry_result['rating_item_entry_result'];
			
			$entries++;

		}
	
		$result = 0;
		if ($entries > 0) {
			$result = round(doubleval($rating_item_entry_result_total) / doubleval($entries), 2);
		}
		
		return array('result' => $result, 'rating_item_entry_result_total' => $rating_item_entry_result_total, 'entries' => $entries, 'post_id' => $post_id);
	
	}
	
	/**
	 * Calculates the rating item entry result. Optionally pass rating items to the function if it has 
	 * already been loaded to save an extra db call
	 *
	 * @param unknown_type $rating_item_entry_id
	 * @param unknown_type $rating_items
	 */
	public static function calculate_rating_item_entry_result($rating_item_entry_id, $rating_items = null) {
		global $wpdb;
		
		if ($rating_items == null) {
			$rating_items = Multi_Rating_API::get_rating_items_by_rating_item_entry($rating_item_entry_id);
		}
		
		$rating_item_entry_value_query = 'SELECT * FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE rating_item_entry_id = ' . $rating_item_entry_id;
		$rating_item_entry_value_rows = $wpdb->get_results($rating_item_entry_value_query);
		
		$rating_item_entries = count($rating_item_entry_value_rows);
		
		$rating_item_entry_result_total = 0;
		$total_max_option_value = 0;
		
		$total_weight = Multi_Rating_API::get_total_weight($rating_items);
		$count = count($rating_items);
		
		foreach ($rating_item_entry_value_rows as $rating_item_entry_value_row) {
			$rating_item_id = $rating_item_entry_value_row->rating_item_id;
		
			// check rating item is available
			if (isset($rating_items[$rating_item_id]) && isset($rating_items[$rating_item_id]['max_option_value'])) {
		
				// add value and max option values
				$value = $rating_item_entry_value_row->value;
				$max_option_value = $rating_items[$rating_item_id]['max_option_value'];
				$total_max_option_value = $total_max_option_value + intval($max_option_value);
				// make adjustments to the rating for weights
				$weight = $rating_items[$rating_item_id]['weight'];
				$adjustment = ($weight / $total_weight) * $count;
		
				$rating_item_entry_result = round(doubleval($value) / doubleval($max_option_value), 10);
				$rating_item_entry_result_total += $rating_item_entry_result * $adjustment;
		
			} else {
				break; // skip
			}
		}
		
		$rating_item_entry_result = round(doubleval($rating_item_entry_result_total) / doubleval($rating_item_entries), 10);
		
		return array('rating_item_entry_result' => $rating_item_entry_result, 'total_max_option_value' => $total_max_option_value);
		
	}
	
	private static function sort_rating_results($a, $b) {
		if ($a['result'] == $b['result']) {
			return 0;
		}
		return ($a['result'] > $b['result']) ? -1 : 1;
	}
	
	
	/**
	 * Get the top rating results
	 * 
	 * @param unknown_type $count
	 */
	public static function get_top_rating_results($count) {

		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$posts = get_posts(array('numberposts' => -1, 'post_type' => $general_settings[Multi_Rating::POST_TYPES_OPTION]));
		
		$rating_items = Multi_Rating_API::get_rating_items();

		// iterate the post types and calculate rating results
		$rating_results = array();
		foreach ($posts as $current_post) {
			$rating_result = Multi_Rating_API::calculate_rating_result($current_post->ID, $rating_items);
			array_push($rating_results, $rating_result);
		}
		
		uasort($rating_results, array('Multi_Rating_API' , 'sort_rating_results'));
		
		$rating_results = array_slice($rating_results, 0, $count);
		return $rating_results;
	}
	
	/**
	 * Displays the rating form
	 * 
	 * @param unknown_type $post_id
	 * @param unknown_type $title
	 * @param unknown_type $before_title
	 * @param unknown_type $after_title
	 * @param unknown_type $submit_button_text
	 */
	function display_rating_form( $post_id = null, $title = '', $before_title = '<h4>', $after_title = '</h4>', $submit_button_text = null ) {
	
		global $post;
	
		if ( !isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( !isset($post) && !isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
	
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		if (!isset($atts['title'])) {
			$title = $custom_text_settings[Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION];
		}
	
		if ($submit_button_text == null) {
			$submit_button_text = $custom_text_settings[Multi_Rating::RATING_FORM_BUTTON_TEXT_OPTION];
		}
	
		$rating_items = Multi_Rating_API::get_rating_items();
	
		echo Rating_Form_View::get_rating_form($rating_items, $post_id, $title, $before_title, $after_title, $submit_button_text);
	}
	
	/**
	 * Displays the rating result
	 * 
	 * @param unknown_type $atts
	 * @return void|string
	 */
	function display_rating_result( $post_id = null, $no_rating_results_text = null  ) {
	
		global $post;
	
		if ( !isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( !isset($post) && !isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
	
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		if (!isset($atts['no_rating_results_text'])) {
			$no_rating_results_text = $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION];
		}
	
		$rating_items = Multi_Rating_API::get_rating_items();
		$rating_result = Multi_Rating_API::calculate_rating_result($post_id, $rating_items);
		echo Rating_Result_View::get_rating_result_html($rating_result, $no_rating_results_text);
	}
	
	/**
	 * Displays the top rating results
	 * 
	 * @param unknown_type $count
	 * @param unknown_type $title
	 * @param unknown_type $before_title
	 * @param unknown_type $after_title
	 */
	function display_top_rating_results( $count = 10, $title = '', $before_title = '<h4>', $after_title = '</h4>' ) {
	
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		if (!isset($atts['title'])) {
			$title = $custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION];
		}
	
		global $wpdb;
	
		$html = '<div class="top-rating-results">';
	
		if ( !empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
	
		$top_rating_results = Multi_Rating_API::get_top_rating_results($count);
	
		foreach ($top_rating_results as $rating_result_obj) {
			$html .= Rating_Result_View::get_rating_result_html($rating_result_obj, null, true);
		}
	
		$html .= '</div>';
		echo $html;
	}
}
?>