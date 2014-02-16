<?php 

/**
 * View class for rating form
 * 
 * @author dpowney
 *
 */
class Rating_Form_View {
	
	private static $sequence = 0;
	
	public static function get_rating_form($rating_items, $post_id, $title, $before_title, $after_title, $submit_button_text) {
		
		Rating_Form_View::$sequence++;
		
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
		
			for ($index=0; $index<=$max_option_value; $index++) {
				$html .= '<option value="' . $index . '"';
				if ($default_option_value == $index)
					$html .= ' selected="selected"';
				$html .= '">' . $index . '</option>';
			}
			$html .= '</select>';
		
			$html .= '<input type="hidden" value="' . $rating_item_id . '" class="rating-form-' . $post_id . '-' . Rating_Form_View::$sequence . '-item" id="hidden-rating-item-id-' . $rating_item_id .'" />';
			$html .= '</td></tr>';
		}
		
		$html .= '<tr><td class="action" colspan="2"><button type="button" class="btn btn-default" id="' . $post_id . '-' . Rating_Form_View::$sequence . '">' . $submit_button_text . '</button></td></tr>';
		$html .= '</table>';
		$html .= '</form>';
		
		return $html;
	}
	
}

?>