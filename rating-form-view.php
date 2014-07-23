<?php 

/**
 * View class for rating form
 * 
 * @author dpowney
 *
 */
class Rating_Form_View {
	
	private static $sequence = 0;
	
	public static function get_rating_form($rating_items, $post_id, $params = array()) {
		
		extract( wp_parse_args( $params, array(
				'title' => '',
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'submit_button_text' => '',
				'class' => ''
		)));
		
		Rating_Form_View::$sequence++;
		
		$rating_item_entry_id = null ;

		$html = '<div class="rating-form ' . $class . '">';
		
		if ( !empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
		
		$html .= '<form name="rating-form-' . $post_id . '-' . Rating_Form_View::$sequence . '" action="#">';
		
		// add the rating items
		foreach ($rating_items as $rating_item) {
			$rating_item_id = $rating_item['rating_item_id'];
			$element_id = 'rating-item-' . $rating_item_id . '-' . Rating_Form_View::$sequence ;
				
			$html .= Rating_Form_View::get_rating_item_html($rating_item, $element_id);
				
			// hidden field to identify the rating item
			// this is used in the JavaScript to construct the AJAX call when submitting the rating form
			$html .= '<input type="hidden" value="' . $rating_item_id . '" class="rating-form-' . $post_id . '-' . Rating_Form_View::$sequence . '-item" id="hidden-rating-item-id-' . $rating_item_id .'" />';
		}


		$html .= '<input type="button" class="btn btn-default save-rating" id="' . $post_id . '-' . Rating_Form_View::$sequence . '" value="' . $submit_button_text . '"></input>';
		$html .= '<input type="hidden" value="' . $rating_item_entry_id . '" id="rating-item-entry-id-' . $post_id . '-' . Rating_Form_View::$sequence . '" />';

		$html .= '</form>';
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Returns HTML for the rating items in the rating form
	 *
	 * @param unknown_type $rating_item
	 * @param unknown_type $element_id
	 */
	public static function get_rating_item_html($rating_item, $element_id) {
		$rating_item_id = $rating_item['rating_item_id'];
		$description = stripslashes($rating_item['description']);
		$default_option_value = $rating_item['default_option_value'];
		$max_option_value = $rating_item['max_option_value'];
		$rating_item_type = $rating_item['type'];
	
		$html = '<p class="rating-item"><label class="description" for="' . $element_id . '">' . $description . '</label>';
	
		if ($rating_item_type == "star_rating") {
			$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
			$star_rating_colour = $style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
				
			$html .= '<span class="star-rating star-rating-select">';
	
			// add star icons
			$index = 0;
			for ($index; $index<=$max_option_value; $index++) {
				if ($index == 0) {
					$html .= '<i class="fa fa-minus-circle starIndex-' . $index . '-' . $element_id . '"></i>';
					continue;
				}
				$class = 'fa fa-star';
				// if default is less than current icon, it must be empty
				if ($default_option_value < $index) {
					$class = 'fa fa-star-o';
				}
				$html .= '<i class="' . $class . ' starIndex-' . $index . '-' . $element_id . '" style="color: ' . $star_rating_colour . '"></i>';
			}
			$html .= '</span>';
	
			// hidden field for storing selected star rating value
			$html .= '<input type="hidden" name="' . $element_id . '" id="' . $element_id . '" value="' . $default_option_value . '">';
				
		} else {
			if ($rating_item_type == 'select') {
				$html .= '<select name="' . $element_id . '" id="' . $element_id . '">';
			}
	
			// option values
			for ($index=0; $index<=$max_option_value; $index++) {
	
				$is_selected = false;
				if ($default_option_value == $index) {
					$is_selected = true;
				}
	
				$text = $index;
				if ($rating_item_type == 'select') {
					$html .= '<option value="' . $index . '"';
					if ($is_selected) {
						$html .= ' selected="selected"';
					}
					$html .= '>' . $text . '</option>';
				} else {
					$html .= '<span class="radio-option">';
					$html .= '<input type="radio" name="' . $element_id . '" id="' . $element_id . '-' . $index . '" value="' . $index . '"';
					if ($is_selected) {
						$html .= ' checked="checked"';
					}
					$html .= '>' . $text . '</input></span>';
				}
			}
	
			if ($rating_item_type == 'select') {
				$html .= '</select>';
			}
		}
	
		$html .= '</p>';
		return $html;
	}
	
}

?>