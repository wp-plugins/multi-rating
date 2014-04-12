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
				'submit_button_text' => '' 
		)));
	
		Rating_Form_View::$sequence++;

		$selected_option_lookup = null;
		$rating_item_entry_id = null ;
		
		$html = '<form class="rating-form" name="rating-form-' . $post_id . '-' . Rating_Form_View::$sequence . '" action="#">';
	
		if ( !empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
	
		$html .= '<table>';
			
		foreach ($rating_items as $rating_item) {
			$rating_item_id = $rating_item['rating_item_id'];
			$description = $rating_item['description'];
			$default_option_value = $rating_item['default_option_value'];
			$max_option_value = $rating_item['max_option_value'];
	
			$select_id = 'rating-form-' . $post_id . '-' . Rating_Form_View::$sequence . '-item-value-' . $rating_item_id;
	
			$html .= '<tr>';
			$html .= '<td><label for="' . $select_id . '">' . $description . '</label></td>';
			$html .= '<td class="value"><select id="' . $select_id . '">';
				
			// option values
			for ($index=0; $index<=$max_option_value; $index++) {
				$html .= '<option value="' . $index . '"';
	
				
				if ($default_option_value == $index) {
					$html .= ' selected="selected"';
				}
					
				$text = $index;

				$html .= '">' . $text . '</option>';
			}
			$html .= '</select>';
				
			$html .= '<input type="hidden" value="' . $rating_item_id . '" class="rating-form-' . $post_id . '-' . Rating_Form_View::$sequence . '-item" id="hidden-rating-item-id-' . $rating_item_id .'" />';
			$html .= '</td></tr>';
		}
	
		$html .= '<tr><td class="action" colspan="2">';
		$button_text = $submit_button_text;

		$html .= '<input type="button" class="btn btn-default save-rating" id="' . $post_id . '-' . Rating_Form_View::$sequence . '" value="' . $button_text . '"></input>';
		$html .= '<input type="hidden" value="' . $rating_item_entry_id . '" id="rating-item-entry-id-' . $post_id . '-' . Rating_Form_View::$sequence . '" />';
		$html .= '</td></tr>';
	
		$html .= '</table>';
		$html .= '</form>';
	
		return $html;
	}
	
}

?>