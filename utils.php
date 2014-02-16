<?php 
/**
 * Gets the client ip address
 *
 * @since 2.1
 */
function get_ip_address() {
	$client_IP_address = '';
	if ( isset($_SERVER['HTTP_CLIENT_IP']) )
		$client_IP_address = $_SERVER['HTTP_CLIENT_IP'];
	else if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
		$client_IP_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if ( isset($_SERVER['HTTP_X_FORWARDED']) )
		$client_IP_address = $_SERVER['HTTP_X_FORWARDED'];
	else if ( isset($_SERVER['HTTP_FORWARDED_FOR']) )
		$client_IP_address = $_SERVER['HTTP_FORWARDED_FOR'];
	else if ( isset($_SERVER['HTTP_FORWARDED']) )
		$client_IP_address = $_SERVER['HTTP_FORWARDED'];
	else if ( isset($_SERVER['REMOTE_ADDR']) )
		$client_IP_address = $_SERVER['REMOTE_ADDR'];

	return $client_IP_address;
}

function sort_rating_results($a, $b) {
	if ($a['rating_result'] == $b['rating_result']) {
		return 0;
	}
	return ($a['rating_result'] > $b['rating_result']) ? -1 : 1;
}

/**
 * Calculate subject rating result. Typically a subject is a post.
 * @param unknown_type $post_id
 * @param unknown_type $rating_items
 */
function calculate_subject_rating_result($post_id, $rating_items) {
	global $wpdb;

	$rating_item_entry_query = 'SELECT * FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE post_id = ' . $post_id;
	$rating_item_entry_rows = $wpdb->get_results($rating_item_entry_query);

	$total_weight = get_total_weight($rating_items);
	$rating_entry_result_total = 0;
	$entries = 0;
	$count = count($rating_items);
	// process all entries for the post and construct a rating result for each post
	foreach ($rating_item_entry_rows as $rating_item_entry_row) {
		$total_value = 0;

		// retrieve the entry values for each rating item
		$rating_item_entry_id = $rating_item_entry_row->rating_item_entry_id;
		$rating_item_entry_value_query = 'SELECT * FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE rating_item_entry_id = ' . $rating_item_entry_id;
		$rating_item_entry_value_rows = $wpdb->get_results($rating_item_entry_value_query);

		$rating_item_entries = count($rating_item_entry_value_rows);
		$rating_item_entry_result_total = 0;
		foreach ($rating_item_entry_value_rows as $rating_item_entry_value_row) {
			$rating_item_id = $rating_item_entry_value_row->rating_item_id;

			// check rating item is available
			if (isset($rating_items[$rating_item_id]) && isset($rating_items[$rating_item_id]['max_rating_value'])) {

				// add value and max rating values
				$value = $rating_item_entry_value_row->value;
				$max_rating_value = $rating_items[$rating_item_id]['max_rating_value'];
				// make adjustments to the rating for weights
				$weight = $rating_items[$rating_item_id]['weight'];
				$adjustment = ($weight / $total_weight) * $count;
				
				$rating_item_entry_result = round(doubleval($value) / doubleval($max_rating_value), 1);
				$rating_item_entry_result_total += $rating_item_entry_result * $adjustment;

			} else {
				
				break; // skip
			}
		}

		$entries++;

		$rating_entry_result = round(doubleval($rating_item_entry_result_total) / doubleval($rating_item_entries), 2);
		$rating_entry_result_total += $rating_entry_result;
	}
	
	$rating_result = 0;
	if ($entries > 0)
		$rating_result = round(doubleval($rating_entry_result_total) / doubleval($entries), 2);

	return array('post_id' => $post_id, 'rating_result' => $rating_result, 'rating_entry_result_total' => $rating_entry_result_total, 'entries' => $entries);

}

/**
 * Get all rating item details used for calculating the rating results
 *
 * @param unknown_type $post_type
 * @return multitype:multitype:NULL
 */
function get_rating_items($post_type) {
	global $wpdb;

	$rating_subject_query = 'SELECT rating_id FROM '.$wpdb->prefix.Multi_Rating::RATING_SUBJECT_TBL_NAME;
	if (isset($post_type)) {
		$rating_subject_query .= ' WHERE post_type = "' . $post_type . '"';
	}

	// We only use one post type for now
	$rating_id = $wpdb->get_var( $rating_subject_query, 0, 0 );

	$rating_items_query = 'SELECT rating_item_id, max_rating_value, weight FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE rating_id = "' . $rating_id . '" AND active = 1';
	$rating_item_rows = $wpdb->get_results($rating_items_query);

	$rating_items = array();
	foreach ($rating_item_rows as $rating_item_row) {
		$rating_item_id = $rating_item_row->rating_item_id;
		$weight = $rating_item_row->weight;
		$rating_items[$rating_item_id] = array(
				'max_rating_value' => $rating_item_row->max_rating_value,
				'weight' => $weight
		);
	}

	return $rating_items;
}

function get_total_weight($rating_items) {
	$total_weight = 0;
	
	foreach ($rating_items as $rating_item => $rating_item_array) {
		$total_weight += $rating_item_array['weight'];
	}
	return $total_weight;
}

?>