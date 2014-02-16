<?php 

/**
 * Shortcode to display the rating form
 */
function mr_display_rating_form( $atts = array() ) {
	
	extract( shortcode_atts( array(
			'post_id' => null,
			'title' => '',
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => null
	), $atts ) );

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
	
	return Rating_Form_View::get_rating_form($rating_items, $post_id, $title, $before_title, $after_title, $submit_button_text);
}
add_shortcode( 'display_rating_form', 'mr_display_rating_form' );


/**
 * Shortcode to display the rating result
 */
function mr_display_rating_result( $atts = array() ) {

	extract( shortcode_atts( array(
			'post_id' => null,
			'no_rating_results_text' => null
	), $atts ) );

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
	return Rating_Result_View::get_rating_result_html($rating_result, $no_rating_results_text);
	
}
add_shortcode( 'display_rating_result', 'mr_display_rating_result' );



/**
 * Shortcode function for displaying the top ratingresults
 *
 * @param unknown_type $atts
 * @return string
 */
function mr_display_top_rating_results( $atts = array() ) {

	extract( shortcode_atts( array(
			'count' => 10,
			'title' => '',
			'before_title' => '<h4>',
			'after_title' => '</h4>'
	), $atts ) );
	
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
	return $html;
}
add_shortcode( 'display_top_rating_results', 'mr_display_top_rating_results' );

?>