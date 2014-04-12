<?php 

/**
 * Shortcode to display the rating form
 */
function mr_display_rating_form( $atts = array() ) {
	
	if (is_admin())
		return;
	
	global $post;
	
	$post_id = null;
	if (isset( $post ) ) {
		$post_id = $post->ID;
	}
	
	$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => $custom_text_settings[Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION]
	), $atts ) );
	
	if ($post_id == null) {
		return; // No post Id available
	}

	return Multi_Rating_API::display_rating_form(
			array(
					'post_id' => $post_id,
					'title' => $title,
					'before_title' => $before_title,
					'after_title' => $after_title,
					'submit_button_text' => $submit_button_text,
					'echo' => false
			));
}
add_shortcode( 'display_rating_form', 'mr_display_rating_form' );


/**
 * Shortcode to display the rating result
 */
function mr_display_rating_result( $atts = array() ) {

	if (is_admin())
		return;
	
	global $post;
	
	$post_id = null;
	if (isset( $post ) ) {
		$post_id = $post->ID;
	}
	
	$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'no_rating_results_text' =>  $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_rich_snippets' => false,
			'show_title' => false,
			'show_count' => true
	), $atts ) );
	
	if ($post_id == null) {
		return; // No post Id available
	}
	
	if (is_string($show_rich_snippets)) {
		$show_rich_snippets = $show_rich_snippets == "true" ? true : false;
	}
	if (is_string($show_title)) {
		$show_title = $show_title == "true" ? true : false;
	}
	if (is_string($show_count)) {
		$show_count = $show_count == "true" ? true : false;
	}
	
	// TODO result types: percentage, star and aggregate
	
	return Multi_Rating_API::display_rating_result(
			array(
					'post_id' => $post_id,
					'no_rating_results_text' => $no_rating_results_text,
					'show_rich_snippets' => $show_rich_snippets,
					'show_title' => $show_title,
					'show_date' => false,
					'show_count' => $show_count,
					'echo' => false
			));
	
}
add_shortcode( 'display_rating_result', 'mr_display_rating_result' );



/**
 * Shortcode function for displaying the top ratingresults
 *
 * @param unknown_type $atts
 * @return string
 */
function mr_display_top_rating_results( $atts = array() ) {

	if (is_admin())
		return;
	
	$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	extract( shortcode_atts( array(
			'count' => 10,
			'title' => $custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'no_rating_results_text' => $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_title' => true,
			'show_count' => true,
	), $atts ) );
	
	if (is_string($show_title)) {
		$show_title = $show_title == "true" ? true : false;
	}
	if (is_string($show_count)) {
		$show_count = $show_count == "true" ? true : false;
	}
	
	// TODO result types: percentage, star and aggregate

	return Multi_Rating_API::display_top_rating_results(
			array(
					'show_title' => true,
					'no_rating_results_text' => $no_rating_results_text,
					'show_title' => $show_title,
					'show_rich_snippets' => false,
					'show_count' => $show_count,
					'show_date' => false,
					'title' => $title,
					'echo' => false
			));
}
add_shortcode( 'display_top_rating_results', 'mr_display_top_rating_results' );

?>