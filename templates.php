<?php

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
		
		if ($post_id != null) {
			$post = get_post($post_id);
			$html .= '&nbsp;<a href="' . get_permalink($post_id) . '">' . $post->post_title . '</a>';
		}
		$html .= '</div>';
	} else if ($show_no_result_text == true) {
		$html .= '<div class="ratingResult">';
		$html .= 'No ratings yet';
		$html .= '</div>';
	}
	return $html;
}