<?php 

/**
 * Shortcode function for displaying the rating form. This function can also be called explicitly
 * 
 * e.g. [displayRatingForm post_id="1" title="Please rate this"]
 */
function display_rating_form( $atts = array() ) {
	
	$general_settings = (array) get_option( 'general-settings' );
	
	$default_rating_form_title = '';
	if (!isset($atts['title'])) {
		$default_rating_form_title = $general_settings[ 'default_rating_form_title' ];
	}
	
	extract( shortcode_atts( array(
			'post_id' => null,
			'title' => $default_rating_form_title,
			'before_title' => '<h4>',
			'after_title' => '</h4>'
	), $atts ) );

	global $wpdb;
	global $post;

	if ( !isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( !isset($post) && !isset( $post_id ) ) {
		return '<p class="error">No post ID available to display multi rating form</p>';
	}
	
	$query = "SELECT * FROM ".$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME;
	$rows = $wpdb->get_results($query);
	
	$html = '<form class="ratingForm" name="ratingForm" action="#">';
	
	if ( !empty( $title ) ) {
		$html .=  $before_title . $title . $after_title;
	}
	
	$html .= '<table>';
	foreach ($rows as $row) {
		$html .= '<tr>';
		
		$select_id = 'ratingForm' . $post_id . 'ItemValue' . $row->rating_item_id;
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
		$html .= '<input type="hidden" value="' . $row->rating_item_id . '" class="ratingForm' . $post_id . 'Item" id="hiddenRatingItemId' . $row->rating_item_id .'" />';
		
		$html .= '</td></tr>';
	}
	
	$html .= '<tr><td class="action" colspan="2"><button type="button" class="btn btn-default" id="' . $post_id . '">Submit</button></td></tr>';
	$html .= '</table>';
	$html .= '</form>';
	
	return $html;
}
add_shortcode( 'displayRatingForm', 'display_rating_form' );



/**
 * Shortcode function for displaying the rating result
 * 
 * e.g. [displayRatingResult post_id="1" show_no_result_text="false"]
 * 
 * @param unknown_type $atts
 */
function display_rating_result( $atts = array() ) {
	
	extract( shortcode_atts( array(
			'post_id' => null,
			'show_no_result_text' => true
	), $atts ) );
	
	global $wpdb;
	global $post;
	
	// Use post id from the loop if not passed
	if (!isset($post_id) && isset($post)) {
		$post_id = $post->ID;
	} else if (!isset($post) && !isset($post_id)) {
		return '<p class="error">No post ID available to display multi rating form</p>';
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
 * e.g. [displayRatingTopResults count="10" title="Top Rating Results"]
 * 
 * 
 * @param unknown_type $atts
 * @return string
 */
function display_rating_top_results( $atts = array() ) {
	
	$general_settings = (array) get_option( 'general-settings' );
	
	$default_top_rating_results_title = '';
	if (!isset($atts['title'])) {
		$default_top_rating_results_title = $general_settings[ 'default_top_rating_results_title' ];
	}
	extract( shortcode_atts( array(
			'count' => 10,
			'title' => $default_top_rating_results_title,
			'before_title' => '<h4>',
			'after_title' => '</h4>'
	), $atts ) );
	
	global $wpdb;

	$html = '<div class="ratingTopResults">';
	if ( !empty( $title ) ) {
		$html .=  $before_title . $title . $after_title;
	}
	
	// iterate the post types and calculate ratings, keep top count
	$general_settings = (array) get_option( 'general-settings' );
	$posts = get_posts(array('numberposts' => -1, 'post_type' => $general_settings['post_types']));
	
	$rating_items = get_rating_items( 'post' );
	
	$rating_results = array();
	foreach ($posts as $current_post) {
		$subject_rating_result = calculate_subject_rating_result($current_post->ID, $rating_items);
		array_push($rating_results, $subject_rating_result);
	}
	
	uasort($rating_results, 'sort_rating_results');
	
	$index = 0;
	foreach ($rating_results as $rating_result_obj) {

		if ($index++ < $count) {		
			$entries = $rating_result_obj['entries'];
			$current_post_id =  $rating_result_obj['post_id'];
			$rating_result = $rating_result_obj['rating_result'];
			$html .= generate_rating_result_html($entries, $rating_result, false, $current_post_id);
		}
	}

	
	$html .= '</div>';	
	return $html;
}
add_shortcode( 'displayRatingTopResults', 'display_rating_top_results' );

?>