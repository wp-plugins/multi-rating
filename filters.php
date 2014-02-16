<?php 

/**
 * Filters the_content()
 *
 * @param $content
 * @return filtered content
 */
function mr_filter_the_content($content) {

	$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
	
	if (!in_the_loop())
		return $content;

	$post_types_checked = $general_settings[ Multi_Rating::POST_TYPES_OPTION ];
	
	if (!isset($post_types_checked) || (isset($post_types_checked) && !is_array($post_types_checked)))
		return $content;
	
	$post_type = get_post_type();
	if (!in_array($post_type, $post_types_checked))
		return $content;

	$position_settings = (array) get_option( Multi_Rating::POSITION_SETTINGS );
	$rating_form_position = $position_settings[Multi_Rating::RATING_FORM_POSITION_OPTION ];
	
	$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	$rating_form_title_text = $custom_text_settings[Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION];
	$rating_form_button_text = $custom_text_settings[Multi_Rating::RATING_FORM_BUTTON_TEXT_OPTION];
	
	global $post;
	if ( !isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( !isset($post) && !isset( $post_id ) ) {
		return; // No post Id available to display rating form
	}
	
	$rating_items = Multi_Rating_API::get_rating_items();
	$rating_form_view = null;
	if ($rating_form_position == 'before_content' || $rating_form_position == 'after_content') {
		$rating_form_view = Rating_Form_View::get_rating_form($rating_items, $post_id, $rating_form_title_text, '<h4>', '</h4>', $rating_form_button_text);
	}
	
	$filtered_content = '';

	if ($rating_form_position == 'before_content' && $rating_form_view != null) {
		$filtered_content .= $rating_form_view;
	}

	$filtered_content .= $content;

	if ($rating_form_position == 'after_content' && $rating_form_view != null) {
		$filtered_content .= $rating_form_view;
	}

	return $filtered_content;
}
add_filter( 'the_content', 'mr_filter_the_content' );



/**
 * Filters the_title()
 *
 * @param $content
 * @return filtered content
 */
function mr_filter_the_title($title) {

	$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );

	if (!in_the_loop())
		return $title;

	$post_types_checked = $general_settings[ Multi_Rating::POST_TYPES_OPTION ];

	if (!isset($post_types_checked) || (isset($post_types_checked) && !is_array($post_types_checked)))
		return $title;

	$post_type = get_post_type();
	if (!in_array($post_type, $post_types_checked))
		return $title;

	$position_settings = (array) get_option( Multi_Rating::POSITION_SETTINGS );
	$rating_result_position = $position_settings[Multi_Rating::RATING_RESULTS_POSITION_OPTION ];

	$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	$no_rating_results_text = $custom_text_settings[Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION];

	global $post;
	if ( !isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( !isset($post) && !isset( $post_id ) ) {
		return; // No post Id available to display rating form
	}

	$rating_items = Multi_Rating_API::get_rating_items();
	$rating_result_view = null;
	if ($rating_result_position == 'before_title' || $rating_result_position == 'after_title') {
		$rating_result = Multi_Rating_API::calculate_rating_result($post_id, $rating_items);
		$rating_result_view =  Rating_Result_View::get_rating_result_html($rating_result, $no_rating_results_text);
	}

	$filtered_title = '';

	if ($rating_result_position == 'before_title' && $rating_result_view != null) {
		$filtered_title .= $rating_result_view;
	}

	$filtered_title .= $title;

	if ($rating_result_position == 'after_title' && $rating_result_view != null) {
		$filtered_title .= $rating_result_view;
	}
	

	return $filtered_title;
}
add_filter( 'the_title', 'mr_filter_the_title' );