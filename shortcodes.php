<?php 

/**
 * Shortcode function for displaying the rating form. This function can also be called explicitly
 * 
 * e.g. [displayRatingForm id="1"]
 */
function display_rating_form( $atts ) {
	extract( shortcode_atts( array(
			'id' => null,
			'title' => 'Please rate this',
			'before_title' => '<h4>',
			'after_title' => '</h4>'
	), $atts ) );

	global $wpdb;
	global $post;

	if (!isset($id) && isset($post)) {
		$id = $post->ID;
	} else {
		return '<p class="error">No post ID available to display multi rating form</p>';
	}
	// get table data
	$query = "SELECT * FROM ".$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME;
	$rows = $wpdb->get_results($query);
	
	$html = '<form class="ratingForm" name="ratingForm" action="#">';
	
	
	if ( !empty( $title ) ) {
		$html .=  $before_title . $title . $after_title;
	}
	
	$html .= '<table>';
	foreach ($rows as $row) {
		// TODO use table or css
		$html .= '<tr>';
		
		$select_id = 'ratingForm' . $id . 'ItemValue' . $row->rating_item_id;
		$description = $row->description;
		
		$html .= '<td><label for="' . $select_id . '">' . $description . '</label></td>';

		$html .= '<td class="value"><select id="' . $select_id . '">';
		
		$default_rating_value = $row->default_rating_value;
		$max_rating_value = $row->max_rating_value;
		for ($index=0; $index<=$max_rating_value; $index++) {
			$html .= '<option value="' . $index . '"';
			if ($default_rating_value == $index)
				$html .= ' selected="selected"';
			$html .= '">' . $index . '</option>';
		}
		$html .= '</select>';
		
		// hidden input for rating item id
		$html .= '<input type="hidden" value="' . $row->rating_item_id . '" class="ratingForm' . $id . 'Item" id="hiddenRatingItemId' . $row->rating_item_id .'" />';
		
		$html .= '</td></tr>';
	}
	
	// button
	$html .= '<tr><td class="action" colspan="2"><button type="button" class="btn btn-default" id="' . $id . '">Submit</button></td></tr>';
	$html .= '</table>';
	
	$html .= '</form>';
	
	return $html;
}
add_shortcode( 'displayRatingForm', 'display_rating_form' );



/**
 * Shortcode function for displaying the rating result
 * 
 * e.g. [displayRatingResult id=1]
 * 
 * @param unknown_type $atts
 */
function display_rating_result( $atts ) {
	extract( shortcode_atts( array(
			'post_id' => null,
			'show_no_result_text' => true
	), $atts ) );

	global $wpdb;
	
	// Use post id from the loop if not passed
	global $post;
	if (!isset($post_id)) {
		$post_id = $post->ID;
	}
	
	// get the current rating items that we need to check
	$post_type = get_post_type( $post );
	
	$rating_items = get_rating_items( $post_type ); 
	
	$subject_rating_result = calculate_subject_rating_result($post_id, $rating_items);

	$entries = $subject_rating_result['entries'];
	$rating_result = $subject_rating_result['rating_result'];
	
	$html = generate_rating_result_html($entries, $rating_result, $show_no_result_text, '');
	
	return $html;
}
add_shortcode( 'displayRatingResult', 'display_rating_result' );


/**
 * Shortcode function for displaying the rating top results
 * 
 * e.g. [displayRatingTopResults count=10]
 * 
 * 
 * @param unknown_type $atts
 * @return string
 */
function display_rating_top_results( $atts ) {
	extract( shortcode_atts( array(
			'count' => 10,
			'title' => 'Top Rating Results',
			'before_title' => '<h4>',
			'after_title' => '</h4>'
	), $atts ) );

	global $post;
	global $wpdb;
	
	$html = '<div class="ratingTopResults">';
	if ( !empty( $title ) ) {
		$html .=  $before_title . $title . $after_title;
	}
	
	// iterate all posts and calculate ratings, keep top count
	$posts = get_posts();
	
	$rating_items = get_rating_items( 'post' );
	
	$rating_results = array();
	foreach ($posts as $post) {
	
		$subject_rating_result = calculate_subject_rating_result($post->ID, $rating_items);
		array_push($rating_results, $subject_rating_result);
	}
	
	uasort($rating_results, 'sort_rating_results');
	
	foreach ($rating_results as $rating_result_obj) {

		$entries = $rating_result_obj['entries'];
		$post_id =  $rating_result_obj['post_id'];
		$rating_result = $rating_result_obj['rating_result'];
		
		$html .= generate_rating_result_html($entries, $rating_result, false, $post_id);
	}
	
	
	$html .= '</div>';	
	return $html;
}
add_shortcode( 'displayRatingTopResults', 'display_rating_top_results' );


function sort_rating_results($a, $b) {
	if ($a['rating_result'] == $b['rating_result']) {
		return 0;
	}
	return ($a['rating_result'] > $b['rating_result']) ? -1 : 1;
}


/**
 * Generates HTML code for displaying the rating result
 * @param unknown_type $entries
 */
function generate_rating_result_html($entries, $rating_result, $show_no_result_text, $post_id) {
	$html = '';
	if ($entries != 0) {
		$rating_result_percentage = $rating_result * 100;
		$rating_result_star = $rating_result_percentage / 20;

		$general_settings = (array) get_option( 'general-settings' );
	
		$aspect_ratio = 5.2;
		$stars_image_height = $general_settings['stars_image_height'];
		$stars_image_width = $stars_image_height * $aspect_ratio;
			
		$stars_img_url = plugins_url('img' . DIRECTORY_SEPARATOR . 'stars_sprite_' . $stars_image_height . 'px.png', __FILE__);
		
		$html .= '<div itemscope itemtype="http://schema.org/Article" class="ratingResult" style="font-size: ' . $stars_image_height . 'px !important;">';
		
		$html .= '
		<span class="ratingStars" style="text-align: left; display: inline-block; width: ' . $stars_image_width . 'px; max-height: ' . $stars_image_height . 'px; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 0;">
			<span style="display: inline-block; width: ' . $rating_result_percentage . '%; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 -' .  $stars_image_height . 'px;"></span>
		</span>
		';
		
		// only add rich snippets for single post types
		if (is_singular()) {
			$html .= '
			<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="ratingSummary">
			<span itemprop="ratingValue">' . round($rating_result_star, 2) . '</span>/<span itemprop="bestRating">5</span> (<span itemprop="ratingCount">' . $entries . '</span>)
			</div>';
		} else {
			$html .= '<div class="ratingSummary">' . round($rating_result_star, 2) . '/5 (' . $entries . ')</div>';
		}
		
		if ($post_id != null)
			$html .= '&nbsp;<a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a>';
		
		$html .= '</div>';
	} else if ($show_no_result_text == true) {
		$html .= '<div class="ratingResult">';
		$html .= 'No ratings yet';
		$html .= '</div>';
	}
	return $html;
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

	$rating_entry_result_total = 0;
	$entries = 0;
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

				// TODO weight
				$rating_item_entry_result = round(doubleval($value) / doubleval($max_rating_value), 1);
				$rating_item_entry_result_total += $rating_item_entry_result;

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
		$rating_items[$rating_item_id] = array(
				'max_rating_value' => $rating_item_row->max_rating_value
		);
	}

	return $rating_items;
}

?>