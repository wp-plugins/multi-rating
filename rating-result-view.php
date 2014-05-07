<?php 

/**
 * View class for rating results
 * 
 * @author dpowney
 *
 */
class Rating_Result_View {
	
	/**
	 * Returns the HTML for the Top Rating Results
	 *
	 * @param unknown_type $top_rating_result_rows
	 * @param unknown_type $params
	 */
	public static function get_top_rating_results_html($top_rating_result_rows, $params = array()) {
	
		extract(wp_parse_args($params, array(
				'show_title' => true,
				'show_count' => false,
				'show_category_filter' => true,
				'category_id' => 0,
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'title' => null,
				'show_rank' => true,
				'no_rating_results_text' => '',
				'result_type' => Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
		)));
	
		if ($category_id == null) {
			$category_id = 0; // so that all categories are returned
		}
	
		$html = '<div class="top-rating-results ' . $class . '">';
	
		if ( !empty( $title )) {
			$html .=  $before_title . $title . $after_title;
		}
	
		if ($show_category_filter == true) {
			$html .= '<form action="" class="category-id-filter" method="POST">';
			$html .= '<label for="category-id">Category</label>';
			$html .= wp_dropdown_categories(array('echo' => false, 'class' => 'category-id', 'name' => 'category-id', 'id' => 'category-id', 'selected' => $category_id, 'show_option_all' => 'All'));
			$html .= '<input type="submit" value="Filter" />';
			$html .= '</form>';
		}
	
		if (count($top_rating_result_rows) == 0) {
			$html .= '<p>' . $no_rating_results_text . '</p>';
		} else {
			$html .= '<table>';
			$index = 1;
			foreach ($top_rating_result_rows as $rating_result) {
				$html .= '<tr>';
	
				if ($show_rank) {
					$html .= '<td>';
					$html .= '<span class="rank">' . $index . '</span>';
					$html .= '</td>';
				}
	
				$html .= '<td>';
	
				$html .= Rating_Result_View::get_rating_result_type_html($rating_result,
						array(
								'show_date' => false,
								'show_title' => false,
								'show_count' => true,
								'result_type' => $result_type
						));
				$html .= '</td>';
	
				if ($show_title == true) {
					$html .= '<td>';
					$html .= '<span class="title">';
					$post_id = $rating_result['post_id'];
					$post = get_post($post_id);
					$html .= '<a href="' . get_permalink($post_id) . '">' . $post->post_title . '</a>';
					$html .= '</span>';
					$html .= '</td>';
				}
	
				$html .= '</tr>';
	
				$index++;
			}
	
			$html .= '</table>';
		}
	
		$html .= '</div>';
	
		return $html;
	
	}
	
	/**
	 * Return the HTML for a Rating Result
	 *
	 * @param unknown_type $rating_result
	 * @param unknown_type $params
	 */
	public static function get_rating_result_html($rating_result, $params = array()) {
			
		extract( wp_parse_args( $params, array(
				'no_rating_results_text' => null,
				'show_title' => false,
				'show_date' => false,
				'show_rich_snippets' => false ,
				'show_count' => true,
				'date' => null,
				'before_date' => '(',
				'after_date' => ')',
				'result_type' => Multi_Rating::STAR_RATING_RESULT_TYPE,
				'class' => ''
		)));
	
		$html = Rating_Result_View::get_rating_result_type_html($rating_result, $params);
	
		return $html;
	}
	
	/**
	 * Helper method for returning the HTML for the rating result type
	 *
	 * @param unknown_type $rating_result
	 * @param unknown_type $params
	 */
	public static function get_rating_result_type_html($rating_result, $params = array()) {
		 
		extract( wp_parse_args( $params, array(
				'show_title' => false,
				'show_date' => false,
				'show_rich_snippets' => false,
				'show_count' => true,
				'date' => null,
				'before_date' => '(',
				'after_date' => ')',
				'result_type' => Multi_Rating::STAR_RATING_RESULT_TYPE,
				'no_rating_results_text' => '',
				'ignore_count' => false,
				'class' => ''
		)));
		 
		$html = '<div class="rating-result ' . $class . '"';
		 
		$count = isset($rating_result['count']) ? $rating_result['count'] : 0;
		 
		if ($count == null || $count == 0) {
			$html .= '><p>' . $no_rating_results_text . '</p>';
		} else {
	
			if ($show_rich_snippets && $result_type == Multi_Rating::STAR_RATING_RESULT_TYPE) {
				$html .= ' itemscope itemtype="http://schema.org/Article"';
			}
			$html .= '>';
	
			if ($show_title == true) {
				$post_id = $rating_result['post_id'];
				$post = get_post($post_id);
				$html .= '&nbsp;<a href="' . get_permalink($post_id) . '">' . $post->post_title . '</a>&nbsp;';
			}
				
			if ($result_type == Multi_Rating::SCORE_RESULT_TYPE) {
				$html .= '<span class="score-result">' . $rating_result['adjusted_score_result'] . '/' . $rating_result['total_max_option_value'] . '</span>';
			} else if ($result_type == Multi_Rating::PERCENTAGE_RESULT_TYPE) {
				$html .= '<span class="percentage-result">' . $rating_result['adjusted_percentage_result'] . '%</span>';
			} else { // star rating
				$rating_result_percentage = $rating_result['adjusted_percentage_result'];
					
				$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
				$stars_image_height = $style_settings[ Multi_Rating::STARS_IMG_HEIGHT_OPTION ];
				$stars_img_url = plugins_url('img' . DIRECTORY_SEPARATOR . 'stars_sprite_' . $stars_image_height . 'px.png', __FILE__);
				
				$aspect_ratio = 5.2; // aspect ratio of stars image constant
				$stars_image_width = $stars_image_height * $aspect_ratio;
	
				$html .= '<div class="rating-result-row" style="font-size: ' . $stars_image_height . 'px !important;">';
				$html .= '
				<span class="rating-result-stars" style="text-align: left; display: inline-block; width: ' . $stars_image_width . 'px; max-height: ' . $stars_image_height . 'px; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 0;">
				<span style="display: inline-block; width: ' . $rating_result_percentage . '%; height: ' .  $stars_image_height . 'px; background: url(' . $stars_img_url . ') 0 -' .  $stars_image_height . 'px;"></span>
				</span>
				';
				$html .= '</div>';
				
				if (is_singular() && $show_rich_snippets == true) {
					$html .= '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="rating-result-summary" style="display: none;">';
					$html .= '<span itemprop="ratingValue">' . $rating_result['adjusted_star_result'] . '</span>/<span itemprop="bestRating">5</span>';
					$html .= '<span itemprop="ratingCount" style="display:none;">' . $count . '</span>';
					$html .= '</div>';
				}
	
				$html .= '<span class="star-result">&nbsp;' . $rating_result['adjusted_star_result'] . '/5</span>';
	
			}
				
			if ($show_count && $count != null) {
				$html .= '&nbsp;(' . $count . ')';
			}
				
			if ($show_date == true && $date != null) {
				$html .= ' ' . $before_date . mysql2date(get_option('date_format'), $date) . $after_date;
			}
		}
	
		$html .= '</div>';
		 
		return $html;
	}
}

?>