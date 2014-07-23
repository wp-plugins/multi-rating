<?php 

/**
 * API functions for multi rating
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
	
		$select_rating_items_query = 'SELECT ri.rating_item_id, ri.type, ri.rating_id, ri.description, ri.default_option_value, '
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
			$type = $rating_item_row->type;
	
			$rating_items[$rating_item_id] = array(
					'max_option_value' => $max_option_value,
					'weight' => $weight,
					'rating_item_id' => $rating_item_id,
					'description' => $description,
					'default_option_value' => $default_option_value,
					'type' => $type
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
		$rating_items_query = 'SELECT DISTINCT ri.rating_item_id, ri.type, ri.rating_id, ri.description, ri.default_option_value, '
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
			$type = $rating_item_row->type;
				
			$rating_items[$rating_item_id] = array(
					'max_option_value' => $max_option_value,
					'weight' => $weight,
					'rating_item_id' => $rating_item_id,
					'description' => $description,
					'default_option_value' => $default_option_value,
					'type' => $type
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
	public static function calculate_rating_result( $params = array() ) {
	
		if (!isset($params['rating_items']) || !isset($params['post_id']) ) {
			return;
		}
	
		$rating_items = $params['rating_items'];
		$post_id = $params['post_id'];
	
		$rating_item_entries = Multi_Rating_API::get_rating_item_entries(array('post_id' => $post_id));
			
		$total_weight = Multi_Rating_API::get_total_weight($rating_items);
	
		$score_result_total = 0;
		$adjusted_score_result_total = 0;
		$total_max_option_value = 0;
	
		$count = 0;
		// process all entries for the post and construct a rating result for each post
		foreach ($rating_item_entries as $rating_item_entry) {
			$total_value = 0;
	
			// retrieve the entry values for each rating item
			$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
				
			$rating_result = Multi_Rating_API::calculate_rating_item_entry_result($rating_item_entry_id, $rating_items);
				
				
			$score_result_total += $rating_result['score_result'];
			$adjusted_score_result_total += $rating_result['adjusted_score_result'];
			if ($total_max_option_value == 0) { // no need to set again
				$total_max_option_value = $rating_result['total_max_option_value'];
			}
				
			$count++;
		}
	
		$score_result = 0;
		$adjusted_score_result = 0;
		$star_result = 0;
		$adjusted_star_result = 0;
		$percentage_result = 0;
		$adjusted_percentage_result = 0;
	
		if ($count > 0) {
			// calculate 5 star result
			$score_result = round(doubleval($score_result_total) / $count, 2);
			$adjusted_score_result =round(doubleval($adjusted_score_result_total) / $count, 2);
				
			// calculate star result
			$star_result = round(doubleval($score_result) / doubleval($total_max_option_value), 2) * 5;
			$adjusted_star_result = round(doubleval($adjusted_score_result) / doubleval($total_max_option_value), 2) * 5;
				
			// calculate percentage result
			$percentage_result = round(doubleval($score_result) / doubleval($total_max_option_value), 2) * 100;
			$adjusted_percentage_result = round(doubleval($adjusted_score_result) / doubleval($total_max_option_value), 2) * 100;
		}
	
		return array(
				'adjusted_star_result' => $adjusted_star_result,
				'star_result' => $star_result,
				'total_max_option_value' => $total_max_option_value,
				'adjusted_score_result' => $adjusted_score_result,
				'score_result' => $score_result,
				'percentage_result' => $percentage_result,
				'adjusted_percentage_result' => $adjusted_percentage_result,
				'count' => $count,
				'post_id' => $post_id,
		);
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
		
		extract( wp_parse_args($params, array(
				'post_id' => null,
				'username' => null,
				'limit' => null,
				'from_date' => null,
				'to_date' => null
			)));	
		
	
		global $wpdb;
	
		$query = 'SELECT rie.rating_item_entry_id, rie.username, rie.post_id, rie.entry_date FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie';
	
		$added_to_query = false;
		if ($post_id || $username ||$from_date || $to_date) {
			$query .= ' WHERE';
		}
	
		if ($post_id) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			$query .= ' rie.post_id = "' . $post_id . '"';
			$added_to_query = true;
		}
		
		if ($username) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			$query .= ' rie.username = "' . $username . '"';
			$added_to_query = true;
		}
		
		if ($from_date) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			$query .= ' rie.entry_date >= "' . $from_date . '"';
			$added_to_query = true;
		}
		
		if ($to_date) {
			if ($added_to_query) {
				$query .= ' AND';
			}
			$query .= ' rie.entry_date <= "' . $to_date . '"';
			$added_to_query = true;
		}
	
		$rating_item_entry_rows = $wpdb->get_results($query);
	
		$rating_item_entries = array();
		foreach ($rating_item_entry_rows as $rating_item_entry_row) {
			$rating_item_entry = array(
					'rating_item_entry_id' => $rating_item_entry_row->rating_item_entry_id,
					'username' => $rating_item_entry_row->username,
					'post_id' => $rating_item_entry_row->post_id,
					'entry_date' => $rating_item_entry_row->entry_date
			);
			array_push($rating_item_entries, $rating_item_entry);
		}
	
		return $rating_item_entries;
	}
	
	/**
	 * Calculates the result for a single rating item
	 *
	 * @param array $rating_item
	 * @param int $post_id
	 */
	public static function calculate_rating_item_result($rating_item, $post_id) {
		$max_option_value = $rating_item['max_option_value'];
		$total_value = 0;
	
		global $wpdb;
	
		$rating_item_entry_value_query = 'SELECT * FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME
		. ' as riev, ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie WHERE riev.rating_item_id = "' . $rating_item['rating_item_id']
		. '" AND riev.rating_item_entry_id = rie.rating_item_entry_id AND rie.post_id = "' . $post_id . '"';
	
		$total_max_option_value = 0;
		$star_result = 0;
		$adjusted_star_result = 0;
		$score_result = 0;
		$adjusted_score_result = 0;
		$percentage_result = 0;
		$adjusted_percentage_result = 0;
	
		foreach ($rating_item_entry_value_rows as $rating_item_entry_value_row) {
			$value = $rating_item_entry_value_row->value;
			$score_result += intval($value);
		}
	
		$count = count($rating_item_entry_value_rows);
		if ($count > 0) {
			$score_result = round(doubleval($score_result) / $count, 2);
			$adjusted_score_result = $score_result; // TODO weights
				
			// calculate 5 star result
			$star_result = round(doubleval($score_result) / doubleval($max_option_value), 10) * 5;
			$adjusted_star_result = round(doubleval($adjusted_score_result) / doubleval($max_option_value), 2) * 5;
	
			// calculate percentage result
			$percentage_result = round(doubleval($score_result) / doubleval($max_option_value), 10) * 100;
			$adjusted_percentage_result = round(doubleval($adjusted_score_result) / doubleval($max_option_value), 2) * 100;
		}
	
		return array(
				'adjusted_star_result' => $adjusted_star_result,
				'star_result' => $star_result,
				'total_max_option_value' => $max_option_value,
				'adjusted_score_result' => $adjusted_score_result,
				'score_result' => $score_result,
				'percentage_result' => $percentage_result,
				'adjusted_percentage_result' => $adjusted_percentage_result,
				'count' => $count
		);
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
	
		$count_rating_item_entry_value_rows = count($rating_item_entry_value_rows);
		$count_rating_items = count($rating_items);
	
		$total_max_option_value = 0;
		$star_result = 0;
		$adjusted_star_result = 0;
		$score_result = 0;
		$adjusted_score_result = 0;
		$percentage_result = 0;
		$adjusted_percentage_result = 0;
		$total_weight = Multi_Rating_API::get_total_weight($rating_items);
	
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
				$adjustment = ($weight / $total_weight) * $count_rating_items;
	
				// score result
				$score_result += intval($value);
				$adjusted_score_result += $value * $adjustment;
			} else {
				break; // skip
			}
		}
	
		if (count($rating_item_entry_value_rows) > 0) {
			// calculate 5 star result
			$star_result = round(doubleval($score_result) / doubleval($total_max_option_value), 10) * 5;
			$adjusted_star_result = round(doubleval($adjusted_score_result) / doubleval($total_max_option_value), 2) * 5;
	
			// calculate percentage result
			$percentage_result = round(doubleval($score_result) / doubleval($total_max_option_value), 10) * 100;
			$adjusted_percentage_result = round(doubleval($adjusted_score_result) / doubleval($total_max_option_value), 2) * 100;
		}
	
		return array(
				'adjusted_star_result' => $adjusted_star_result,
				'star_result' => $star_result,
				'total_max_option_value' => $total_max_option_value,
				'adjusted_score_result' => $adjusted_score_result,
				'score_result' => $score_result,
				'percentage_result' => $percentage_result,
				'adjusted_percentage_result' => $adjusted_percentage_result,
		);
	}
	
	
	private static function sort_top_rating_results($a, $b) {
		if ($a['score_result'] == $b['score_result']) {
			return 0;
		}
		return ($a['score_result'] > $b['score_result']) ? -1 : 1;
	}	
	
	
	/**
	 * Get the top rating results
	 *
	 * @param int $count the count of top rating results to return
	 * @return array top rating results
	 */
	public static function get_top_rating_results($limit = 10, $category_id = null) {
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
		$posts = get_posts(array('numberposts' => -1, 'post_type' => $general_settings[Multi_Rating::POST_TYPES_OPTION]));
	
		$rating_items = Multi_Rating_API::get_rating_items(array());
	
		// iterate the post types and calculate rating results
		$rating_results = array();
		foreach ($posts as $current_post) {
			
			if ($category_id != null) {
				// skip if not in category
				if (!in_category($category_id, $current_post->ID)) {
					continue;
				}
			}
				
			$rating_result = Multi_Rating_API::calculate_rating_result(array('post_id' => $current_post->ID, 'rating_items' => $rating_items));
				
			if (intval($rating_result['count']) > 0) {
				array_push($rating_results, $rating_result);
			}
		}
	
		uasort($rating_results, array('Multi_Rating_API' , 'sort_top_rating_results'));
	
		$rating_results = array_slice($rating_results, 0, $limit);
		return $rating_results;
	}
	
	/**
	 * Displays the rating form
	 *
	 * @param unknown_type $params
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
				'echo' => true,
				'class' => ''
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
						'submit_button_text' => $submit_button_text,
						'class' => $class
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
				'echo' => true,
				'result_type' => Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
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
		if (is_string($echo)) {
			$echo = $echo == "true" ? true : false;
		}
	
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
						'show_count' => $show_count,
						'no_rating_results_text' => $no_rating_results_text,
						'result_type' => $result_type,
						'class' => $class
				));
	
		if ($echo == true) {
			echo $html;
		}
	
		return $html;
	}
	
	/**
	 * Displays the top rating results
	 */
	public static function display_top_rating_results( $params = array()) {
	
		$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
	
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
		extract( wp_parse_args( $params, array(
				'title' => $custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION],
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'no_rating_results_text' => $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION ],
				'show_count' => true,
				'echo' => true,
				'show_category_filter' => true,
				'category_id' => 0, // 0 = All,
				'limit' => 10, // modified was count
				'show_rank' => true,
				'result_type' => Multi_Rating::STAR_RATING_RESULT_TYPE,
		        'show_title' => true,
				'class' => ''
		) ) );
	
		if (is_string($show_count)) {
			$show_count = $show_count == "true" ? true : false;
		}
		if (is_string($echo)) {
			$echo = $echo == "true" ? true : false;
		}
		if (is_string($show_category_filter)) {
			$show_category_filter = $show_category_filter == "true" ? true : false;
		}
		if (is_string($show_rank)) {
			$show_rank = $show_rank == "true" ? true : false;
		}
		if (is_string($show_title)) {
			$show_title = $show_title == "true" ? true : false;
		}
	
		// show the filter for categories
		if ($show_category_filter == true) {
			// override category id if set in HTTP request
			if (isset($_REQUEST['category-id'])) {
				$category_id = $_REQUEST['category-id'];
			}
		}
	
		if ($category_id == 0) {
			$category_id = null; // so that all categories are returned
		}
	
		$top_rating_result_rows = Multi_Rating_API::get_top_rating_results($limit, $category_id);
	
		$html = Rating_Result_View::get_top_rating_results_html($top_rating_result_rows,
				array(
						'show_title' => $show_title,
						'show_count' => $show_count,
						'show_category_filter' => $show_category_filter,
						'category_id' => $category_id,
						'before_title' => $before_title,
						'after_title' => $after_title,
						'title' => $title,
						'show_rank' => $show_rank,
						'no_rating_results_text' => $no_rating_results_text,
						'result_type' => $result_type,
						'class' => $class
				));
		if ($echo == true) {
			echo $html;
		}
	
		return $html;
	}
	
	/**
	 * Generates rating results in CSV format
	 *
	 * @returns whether reprt has been successfully generated
	 */
	public static function generate_rating_results_csv_file($file_name, $filters) {
	
		$rating_item_entries = Multi_Rating_API::get_rating_item_entries($filters);
			
		$export_data_rows = array('Entry ID, Entry Date, Post ID, Post Title, '
				. 'Score Rating Result, Adjusted Score Rating Result, Total Max Option Value, Percentage Rating Result, '
				. 'Adjusted Percentage Rating Result, Out of 5 Rating Result, Adjusted Out of 5 Rating Result, '
				. 'Username');
	
		if (count($rating_item_entries) > 0) {
			foreach ($rating_item_entries as $rating_item_entry) {
				$post_id = $rating_item_entry['post_id'];
				$rating_item_entry_id = $rating_item_entry['rating_item_entry_id'];
	
				$rating_items = Multi_Rating_API::get_rating_items(array('post' => $post_id, 'rating_item_entry_id' => $rating_item_entry_id));
				$rating_result = Multi_Rating_API::calculate_rating_item_entry_result($rating_item_entry_id,  $rating_items);
	
				$current_row = $rating_item_entry_id .', ' . $rating_item_entry['entry_date'] . ', '
				. $post_id . ', ' . get_the_title($post_id) . ', ' . $rating_result['score_result'] . ', '
				. $rating_result['adjusted_score_result'] . ', ' . $rating_result['total_max_option_value'] . ', '
				. $rating_result['percentage_result'] . ', ' . $rating_result['adjusted_percentage_result'] . ', '
				. $rating_result['star_result'] . ', ' . $rating_result['adjusted_star_result'] . ', '
				. $rating_item_entry['username'];
	
				array_push($export_data_rows, $current_row);
			}
		}
	
		$file = null;
		try {
			$file = fopen( $file_name, 'w' );
			foreach ( $export_data_rows as $row ) {
				fputcsv( $file, explode(',', $row ) );
			}
			fclose($file);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
}
?>