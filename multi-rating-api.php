<?php 

/**
 * API functions for multi rating:
 *
 */
class Multi_Rating_API {
	
	/**
	 * Get rating items
	 *
	 * @param array $params	rating_item_entry_id and post_id
	 * @return rating items
	 */
	public static function get_rating_items($params = array()) {
		global $wpdb;
	
		$select_rating_items_query = 'SELECT ri.rating_item_id, ri.rating_id, ri.description, ri.default_option_value, '
		. 'ri.max_option_value, ri.weight, ri.active FROM '
		. $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . ' as ri';
	
		if (isset($params['rating_item_entry_id']) || isset($params['post_id'])) {
			// TODO optimize db query for performance
			$select_rating_items_query .= ', ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' AS rie, '
			. $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME
			. ' AS riev';
		}
	
		$added_to_query = false;
		if (isset($params['rating_item_entry_id']) || isset($params['post_id'])) {
			$select_rating_items_query .= ' WHERE';
			$select_rating_items_query .= ' riev.rating_item_entry_id = rie.rating_item_entry_id';
			$added_to_query = true;
		}
	
		// rating_item_entry_id
		if (isset($params['rating_item_entry_id'])) {
			if ($added_to_query == true) {
				$select_rating_items_query .= ' AND';
				$added_to_query = false;
			}
			$select_rating_items_query .= ' rie.rating_item_entry_id =  "' . $params['rating_item_entry_id'] . '"';
			$added_to_query = true;
		}
	
		// post_id
		if (isset($params['post_id'])) {
			if ($added_to_query == true) {
				$select_rating_items_query .= ' AND';
				$added_to_query = false;
			}
			$select_rating_items_query .= ' rie.post_id = "' . $params['post_id'] . '"';
			$added_to_query = true;
				
			//$post_type = get_post_type( $params['post_id'] );
		}
	
		$rating_item_rows = $wpdb->get_results($select_rating_items_query);
	
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
	 * Calculates the rating result of a rating form for a post with filters for username
	 *
	 * @param array $params post_id, rating_items
	 * @return rating result
	 */
	public static function calculate_rating_result($params = array()) {
	
		if (!isset($params['rating_items']) || !isset($params['post_id']) ) {
			return;
		}
	
		$rating_items = $params['rating_items'];
		$post_id = $params['post_id'];
		
		$rating_item_entries = Multi_Rating_API::get_rating_item_entries(array('post_id' => $post_id));
			
		$total_weight = Multi_Rating_API::get_total_weight($rating_items);
		$rating_item_entry_result_total = 0;
		$entries = 0;
		// process all entries for the post and construct a rating result for each post
		foreach ($rating_item_entries as $rating_item_entry) {
			$total_value = 0;
	
			// retrieve the entry values for each rating item
			$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
				
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
	 * Gets the rating form entries for a given post
	 *
	 * $params post_id
	 * @param unknown_type $params
	 */
	public static function get_rating_item_entry_values($params = array()) {
	
		$rating_item_entry_values = array();
	
		$rating_item_entries = Multi_Rating_API::get_rating_item_entries($params);
	
		$rating_item_entries_array = array();
	
		global $wpdb;
		foreach ($rating_item_entries as $rating_item_entry) {
			global $wpdb;
	
			$query = 'SELECT ri.description AS description, riev.value AS value, ri.max_option_value AS max_option_value, '
			. 'riev.rating_item_entry_id AS rating_item_entry_id, ri.rating_item_id AS rating_item_id '
			. 'FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev, '
			. $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . ' AS ri WHERE ri.rating_item_id = riev.rating_item_id '
			. 'AND riev.rating_item_entry_id = "' . $rating_item_entry['rating_item_entry_id'] . '"';
				
			$rating_item_entry_value_rows = $wpdb->get_results($query, ARRAY_A);
				
			foreach ($rating_item_entry_value_rows as &$rating_item_entry_value_row) {
				
				$value = intval($rating_item_entry_value_row['value']);
				$rating_item_entry_value_row['value_text'] = $value;
			}
				
			array_push($rating_item_entries_array, array('rating_item_entry' => $rating_item_entry, 'rating_item_entry_values' => $rating_item_entry_value_rows));
		}
	
		return $rating_item_entries_array;
	
	}
	
	/**
	 * Gets rating item entries of a post
	 *
	 * @param array $params post_id
	 * @return rating item entries
	 */
	public static function get_rating_item_entries($params = array()) {
		
		$post_id = null;
		if (isset($params['post_id'])) {
			$post_id = $params['post_id'];
		}
	
		global $wpdb;
	
		$query = 'SELECT rie.rating_item_entry_id, rie.post_id, rie.entry_date FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie';
	
		$added_to_query = false;
		if ($post_id) {
			$query .= ' WHERE';
		}
	
		if ($post_id) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			$query .= ' rie.post_id = "' . $post_id . '"';
			$added_to_query = true;
		}
	
		$rating_item_entry_rows = $wpdb->get_results($query);
	
		$rating_item_entries = array();
		foreach ($rating_item_entry_rows as $rating_item_entry_row) {
			$rating_item_entry = array(
					'rating_item_entry_id' => $rating_item_entry_row->rating_item_entry_id,
					'post_id' => $rating_item_entry_row->post_id,
					'entry_date' => $rating_item_entry_row->entry_date
			);
			array_push($rating_item_entries, $rating_item_entry);
		}
	
		return $rating_item_entries;
	}
	
	/**
	 * Calculates the result for single rating item
	 *
	 * @param array $rating_item
	 * @param int $post_id
	 * @return rating item result
	 */
	public static function calculate_rating_item_result($rating_item, $post_id) {
		$max_option_value = $rating_item['max_option_value'];
		$total_value = 0;
		$count = 0;
	
		global $wpdb;
	
		$rating_item_entry_value_query = 'SELECT * FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME
		. ' as riev, ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie WHERE riev.rating_item_id = "' . $rating_item['rating_item_id']
		. '" AND riev.rating_item_entry_id = rie.rating_item_entry_id AND rie.post_id = "' . $post_id . '"';
	
		$rating_item_entry_value_rows = $wpdb->get_results($rating_item_entry_value_query);
	
		foreach ($rating_item_entry_value_rows as $rating_item_entry_value_row) {
			$value = $rating_item_entry_value_row->value;
			$total_value += intval($value);
			$count++;
		}
	
		$total_max_option_value = 0;
		$avg_value = 0;
		if ($count > 0) {
			$total_max_option_value = $count * $max_option_value;
			$avg_value = round(doubleval($total_value / $count), 2);
		}
	
		return array('total_max_option_value' => $total_max_option_value, 'count' => $count, 'total_value' => $total_value, 'max_option_value' => $max_option_value, 'avg_value' => $avg_value);
	}
	
	
	/**
	 * Calculates the rating item entry result.
	 *
	 * @param int $rating_item_entry_id
	 * @param array $rating_items optionally used to save an additional call to the database if the rating items have already been loaded
	 */
	public static function calculate_rating_item_entry_result($rating_item_entry_id, $rating_items = null) {
		global $wpdb;
	
		if ($rating_items == null) {
			$rating_items = Multi_Rating_API::get_rating_items(array('rating_item_entry_id' => $rating_item_entry_id));
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
	
		$rating_item_entry_result = 0;
		if ($rating_item_entries > 0) {
			$rating_item_entry_result = round(doubleval($rating_item_entry_result_total) / doubleval($rating_item_entries), 10);
		}
	
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
	 * @param int $count the count of top rating results to return
	 * @param int $rating_form_id
	 * @return array top rating results
	 */
	public static function get_top_rating_results($count = 10) {
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$posts = get_posts(array('numberposts' => -1, 'post_type' => $general_settings[Multi_Rating::POST_TYPES_OPTION]));
	
		$rating_items = Multi_Rating_API::get_rating_items(array());
	
		// iterate the post types and calculate rating results
		$rating_results = array();
		foreach ($posts as $current_post) {
				
			$rating_result = Multi_Rating_API::calculate_rating_result(array('post_id' => $current_post->ID, 'rating_items' => $rating_items));
				
			if (intval($rating_result['entries']) > 0) {
				array_push($rating_results, $rating_result);
			}
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
	public static function display_rating_form( $params = array()) {
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$position_settings = (array) get_option( Multi_Rating::POSITION_SETTINGS );
	
		extract( wp_parse_args($params, array(
				'post_id' => null,
				'title' => $custom_text_settings[Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'submit_button_text' => $custom_text_settings[Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
				'echo' => true
		) ) );
	
		global $post;
	
		if ( !isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( !isset($post) && !isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
	
		$rating_items = Multi_Rating_API::get_rating_items(array());
		
		$html = Rating_Form_View::get_rating_form($rating_items, $post_id,
				array(
						'title' => $title,
						'before_title' => $before_title,
						'after_title' => $after_title,
						'submit_button_text' => $submit_button_text
				));
	
		if ($echo == true) {
			echo $html;
		}
	
		return $html;
	}
	
	/**
	 * Displays the rating result
	 *
	 * @param unknown_type $atts
	 * @return void|string
	 */
	public static function display_rating_result( $params = array()) {
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		extract( wp_parse_args($params, array(
				'post_id' => null,
				'no_rating_results_text' => $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
				'show_rich_snippets' => false,
				'show_title' => false,
				'show_date' => true,
				'show_count' => true,
				'echo' => true
		) ) );
	
		if (is_string($show_rich_snippets)) {
			$show_rich_snippets = $show_rich_snippets == "true" ? true : false;
		}
		if (is_string($show_title)) {
			$show_title = $show_title == "true" ? true : false;
		}
		if (is_string($show_date)) {
			$show_date = $show_date == "true" ? true : false;
		}
		if (is_string($show_count)) {
			$show_count = $show_count == "true" ? true : false;
		}
	
		// TODO result types: percentage, star and aggregate
	
		global $post;
	
		if ( !isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( !isset($post) && !isset( $post_id ) ) {
			return; // No post Id available to display rating form
		}
	
		$rating_items = Multi_Rating_API::get_rating_items(array());
	
		$rating_result = Multi_Rating_API::calculate_rating_result(
				array(
						'post_id' => $post_id,
						'rating_items' => $rating_items
				));
		$html = Rating_Result_View::get_rating_result_html($rating_result,
				array(
						'no_rating_results_text' => $no_rating_results_text,
						'show_rich_snippets' => $show_rich_snippets,
						'show_title' => $show_title,
						'show_date' => $show_date,
						'show_count' => $show_count
				));
	
		if ($echo == true) {
			echo $html;
		}
	
		return $html;
	}
	
	
	
	/**
	 * Displays the top rating results
	 * 
	 * @param unknown_type $count
	 * @param unknown_type $title
	 * @param unknown_type $before_title
	 * @param unknown_type $after_title
	 */
	static function display_top_rating_results( $params = array() ) {
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		extract( wp_parse_args( $params, array(
				'count' => 10,
				'title' => $custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION],
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'no_rating_results_text' => $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION ],
				'show_title' => true,
				'show_count' => true,
				'echo' => true
		) ) );
	
		global $wpdb;
	
		$html = '<div class="top-rating-results">';
	
		if ( !empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
		
		$top_rating_results = Multi_Rating_API::get_top_rating_results($count);
		
		if (count($top_rating_results) > 0) {
			foreach ($top_rating_results as $rating_result) {
				$html .= Rating_Result_View::get_rating_result_html($rating_result, 
						array(
								'show_title' => true,
								'no_rating_results_text' => $no_rating_results_text,
								'show_title' => $show_title,
								'show_rich_snippets' => false,
								'show_count' => $show_count,
								'show_date' => false
						));
			}
		} else {
			$html .= $no_rating_results_text;
		}
		
		$html .= '</div>';
		
		if ($echo == true) {
			echo $html;
		}
		
		return $html;
	}
}
?>