<?php 

/**
 * View class for rating results
 * 
 * @author dpowney
 *
 */
class Rating_Result_View {
	
	public static function get_rating_result_html($rating_result, $no_rating_results_text = null, $show_title = false) {
		
		$entries = $rating_result['entries'];
		$html = '';
		
		if ($entries != 0) {
			$rating_result_percentage = $rating_result['result'] * 100;
			$rating_result_star = $rating_result_percentage / 20;
			
			$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
			
			$aspect_ratio = 5.2; // aspect ratio of stars image constant
			$stars_image_height = $style_settings[ Multi_Rating::STARS_IMG_HEIGHT_OPTION ];
			$stars_image_width = $stars_image_height * $aspect_ratio;
			
			$stars_img_url = plugins_url('img' . DIRECTORY_SEPARATOR . 'stars_sprite_' . $stars_image_height . 'px.png', __FILE__);
			
			$html .= '<div class="rating-result">';
			$html .= '<div itemscope class="rating-result-row" itemtype="http://schema.org/Article" style="font-size: ' . $stars_image_height . 'px !important;">';
			
			$html .= '
			<span class="rating-result-stars" style="text-align: left; display: inline-block; width: ' . $stars_image_width . 'px; max-height: ' . $stars_image_height . 'px; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 0;">
			<span style="display: inline-block; width: ' . $rating_result_percentage . '%; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 -' .  $stars_image_height . 'px;"></span>
			</span>
			';
			
			$html .= '</div>';
			
			// only add rich snippets for single post types
			if (is_singular()) {
				$html .= '
				<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="rating-result-summary">
				<span itemprop="ratingValue">' . round($rating_result_star, 2) . '</span>/<span itemprop="bestRating">5</span> (<span itemprop="ratingCount">' . $entries . '</span>)
				</div>';
			} else {
				$html .= '<div class="rating-result-summary">' . round($rating_result_star, 2) . '/5 (' . $entries . ')</div>';
			}
			
			if ($show_title != null) {
				$post_id = $rating_result['post_id'];
				$post = get_post($post_id);
				$html .= '&nbsp;<a href="' . get_permalink($post_id) . '">' . $post->post_title . '</a>';
			}
			
			$html .= '</div>';
		} else if ($no_rating_results_text != null) {
			$html .= '<div class="rating-result">';
			$html .= $no_rating_results_text;
			$html .= '</div>';
		}
		return $html;
	}
}

?>