<?php 

/**
 * View class for rating results
 * 
 * @author dpowney
 *
 */
class Rating_Result_View {
	
	public static function get_rating_result_html($rating_result, $params = array()) {
			
		extract( wp_parse_args( $params, array(
				'no_rating_results_text' => null,
				'show_title' => false,
				'show_date' => false,
				'show_rich_snippets' => true,
				'show_count' => true
				)));
		
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
			
			$html .= '<div class="rating-result" itemscope itemtype="http://schema.org/Article">';
			$html .= '<div class="rating-result-row" style="font-size: ' . $stars_image_height . 'px !important;">';
			
			$html .= '
			<span class="rating-result-stars" style="text-align: left; display: inline-block; width: ' . $stars_image_width . 'px; max-height: ' . $stars_image_height . 'px; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 0;">
			<span style="display: inline-block; width: ' . $rating_result_percentage . '%; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 -' .  $stars_image_height . 'px;"></span>
			</span>
			';
			
			$html .= '</div>';
			
			// only add rich snippets for single post types
			if (is_singular() && $show_rich_snippets == true) {
				
				$html .= '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="rating-result-summary">';
				$html .= '<span itemprop="ratingValue">' . round($rating_result_star, 2) . '</span>/<span itemprop="bestRating">5</span>';
				if ($show_count == true) {
					$html .= ' (<span itemprop="ratingCount">' . $entries . '</span>)';
				} else {
					$html .= '<span itemprop="ratingCount" display="none">' . $entries . '</span>';
				}
				$html .= '</div>';
			} else {
				$html .= '<div class="rating-result-summary">' . round($rating_result_star, 2) . '/5';
				if ($show_count == true) {
					$html .= ' (' . $entries . ')';
				}
				$html .= '</div>';
			}
			
			$post_id = $rating_result['post_id'];
			$post = get_post($post_id);
			
			if ($show_title == true) {
				$html .= '&nbsp;<a href="' . get_permalink($post_id) . '">' . $post->post_title . '</a>';
			}
			if ($show_date == true) {
				$html .= ' ' . mysql2date(get_option('date_format'), $post->post_date);
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