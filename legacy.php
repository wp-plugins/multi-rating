<?php

/**
 * Legacy and moved deprecated functions go here. These will be removed over time.
 */

/**
 * @deprecated Use mr_display_rating_form instead
 */
function display_rating_form($atts = array()) {
	return mr_display_rating_form($atts);
}
/**
 * @deprecated Use mr_display_rating_result instead
 */
function display_rating_result($atts = array()) {
	return mr_display_rating_result($atts);
}
/**
 * @deprecated Use mr_display_top_rating_results instead
 */
function display_rating_top_results($atts = array()) {
	return mr_display_top_rating_results($atts);
}

add_shortcode( 'displayRatingForm', 'display_rating_form' );
add_shortcode( 'displayRatingResult', 'display_rating_result' );
add_shortcode( 'displayRatingTopResults', 'display_rating_top_results' );