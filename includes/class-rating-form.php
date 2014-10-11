<?php 

/**
 * Rating form class
 * 
 * @author dpowney
 *
 */
class MR_Rating_Form {
	
	/**
	 * Used to uniquely identify a rating form
	 */
	private static $sequence = 0;
	
	/**
	 * Generates the rating form html
	 * 
	 * @param $rating_items
	 * @param $post_id
	 * @param $params
	 * @return html
	 */
	public static function do_rating_form_html( $rating_items, $post_id, $params = array() ) {
		
		extract( wp_parse_args( $params, array(
				'title' => '',
				'before_title' => '<h4>',
				'after_title' => '</h4>',
				'submit_button_text' => '',
				'class' => ''
		)));
		
		MR_Rating_Form::$sequence++;

		$html = '<div class="rating-form ' . $class . '">';
		
		if ( !empty( $title ) ) {
			$html .=  $before_title . $title . $after_title;
		}
		
		$html .= '<form name="rating-form-' . $post_id . '-' . MR_Rating_Form::$sequence . '" action="#">';
		
		// add the rating items
		foreach ( $rating_items as $rating_item ) {
			
			$rating_item_id = $rating_item['rating_item_id'];
			$element_id = 'rating-item-' . $rating_item_id . '-' . MR_Rating_Form::$sequence ;
				
			$html .= MR_Rating_Form::get_rating_item_html( $rating_item, $element_id );
				
			// hidden field to identify the rating item
			// this is used in the JavaScript to construct the AJAX call when submitting the rating form
			$html .= '<input type="hidden" value="' . $rating_item_id . '" class="rating-form-' . $post_id . '-' . MR_Rating_Form::$sequence . '-item" id="hidden-rating-item-id-' . $rating_item_id .'" />';
		}

		$html .= '<input type="button" class="btn btn-default save-rating" id="saveBtn-' . $post_id . '-' . MR_Rating_Form::$sequence . '" value="' . $submit_button_text . '"></input>';
		
		$html .= '</form>';
		
		$html .= '</div>';
		
		echo $html;
	}
	
	/**
	 * Returns HTML for the rating items in the rating form
	 *
	 * @param unknown_type $rating_item
	 * @param unknown_type $element_id
	 */
	public static function get_rating_item_html( $rating_item, $element_id ) {
		
		$rating_item_id = $rating_item['rating_item_id'];
		$description = stripslashes($rating_item['description']);
		$default_option_value = $rating_item['default_option_value'];
		$max_option_value = $rating_item['max_option_value'];
		$rating_item_type = $rating_item['type'];
	
		$html = '<p class="rating-item mrp"><label class="description" for="' . $element_id . '">' . $description . '</label>';
	
		if ( $rating_item_type == "star_rating" ) {
			
			$style_settings = (array) get_option( Multi_Rating::STYLE_SETTINGS );
			$star_rating_colour = $style_settings[Multi_Rating::STAR_RATING_COLOUR_OPTION];
			$font_awesome_version = $style_settings[Multi_Rating::FONT_AWESOME_VERSION_OPTION];
			$icon_classes = MR_Utils::get_icon_classes( $font_awesome_version );
				
			$html .= '<span class="star-rating star-rating-select">';
	
			// add star icons
			$index = 0;
			for ( $index; $index <= $max_option_value; $index++ ) {
				
				if ( $index == 0 ) {
					$html .= '<i class="' .  $icon_classes['minus'] . ' index-' . $index . '-' . $element_id . '"></i>';
					continue;
				}
				
				$class = $icon_classes['star_full'];
				// if default is less than current icon, it must be empty
				if ( $default_option_value < $index ) {
					$class = $icon_classes['star_empty'];
				}
				
				$html .= '<i class="' . $class . ' index-' . $index . '-' . $element_id . '"></i>';
			}
			
			$html .= '</span>';
	
			// hidden field for storing selected star rating value
			$html .= '<input type="hidden" name="' . $element_id . '" id="' . $element_id . '" value="' . $default_option_value . '">';
				
		} else {
			
			if ( $rating_item_type == 'select' ) {
				$html .= '<select name="' . $element_id . '" id="' . $element_id . '">';
			}
	
			// option values
			for ($index=0; $index <= $max_option_value; $index++) {
	
				$is_selected = false;
				if ( $default_option_value == $index ) {
					$is_selected = true;
				}
	
				$text = $index;
				if ( $rating_item_type == 'select' ) {
					$html .= '<option value="' . $index . '"';
					
					if ( $is_selected ) {
						$html .= ' selected="selected"';
					}
					
					$html .= '>' . $text . '</option>';
				} else {
					$html .= '<span class="radio-option">';
					$html .= '<input type="radio" name="' . $element_id . '" id="' . $element_id . '-' . $index . '" value="' . $index . '"';
					
					if ( $is_selected ) {
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
	
	
	/**
	 * Saves a rating form entry.
	 */
	public static function save_rating() {
	
		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce($ajax_nonce, Multi_Rating::ID.'-nonce' ) ) {
				
			global $wpdb;
	
			$rating_items = $_POST['ratingItems'];
			$post_id = $_POST['postId'];
			$ip_address = MR_Utils::get_ip_address();
			$entry_date_mysql = current_time( 'mysql' );
			$sequence = isset($_POST['sequence']) ? $_POST['sequence'] : '';
	
			$data = array(
					'sequence' => $sequence,
					'post_id' => $post_id
			);				
	
			$general_settings = (array) get_option( Multi_Rating::GENERAL_SETTINGS );
			$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
			// get user id
			global $wp_roles;
			$user = wp_get_current_user();
			$user_id = $user->ID;
	
			// stores any validation results, custom validation results can be added through filters
			$validation_results = array();
			
			$validation_results = MR_Utils::validate_ip_address_datetime_check( $validation_results, $post_id );
			
			$validation_results = apply_filters( 'mrp_after_rating_form_validation_save', $validation_results, $data );
			
			if ( MR_Utils::has_validation_error( $validation_results ) ) {
				echo json_encode( array (
						'status' => 'error',
						'data' => $data,
						'validation_results' => $validation_results
				) );
				die();
			}
	
			// everything is OK so now insert the rating form entry and entry values into the database tables
			$wpdb->insert( $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
					'post_id' => $post_id,
					'entry_date' => $entry_date_mysql,
					'ip_address' => $ip_address,
					'user_id' => $user_id,
			), array('%d', '%s', '%s', '%d') );
	
			$rating_item_entry_id = $wpdb->insert_id;
	
			foreach ( $rating_items as $rating_item ) {
	
				$rating_item_id = $rating_item['id'];
				$rating_item_value = $rating_item['value'];
	
				$wpdb->insert( $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_item_entry_id,
						'rating_item_id' => $rating_item_id,
						'value' => $rating_item_value
				), array('%d', '%d', '%d') );
			}
			
			$rating_items = Multi_Rating_API::get_rating_items( array( 'post_id' => $post_id ) );
			
			$rating_result  = Multi_Rating_API::calculate_rating_result( array(
					'post_id' => $post_id,
					'rating_items' => $rating_items
			) );

			$rating_results_cache = $general_settings[Multi_Rating::RATING_RESULTS_CACHE_OPTION];
			if ($rating_results_cache == true) {
				// update rating results cache
				update_post_meta( $post_id, Multi_Rating::RATING_RESULTS_POST_META_KEY, $rating_result );
			}
			
			$data['html'] = stripslashes( MR_Rating_Result::get_rating_result_type_html( $rating_result, array(
					'class' => 'rating-result-' . $post_id . ' mr-filter'
			) ) );
	
			// if the custom text does not contain %, then there's no need to substitute the message
			$message = $custom_text_settings[ Multi_Rating::RATING_FORM_SUBMIT_SUCCESS_MESSAGE_OPTION];
			if (strpos($message, '%') !== false) {
				$message = MR_Utils::substitute_message( $message, $user, 
						Multi_Rating_API::calculate_rating_item_entry_result($rating_item_entry_id, $rating_items ) );
			}
	
			$data['rating_result'] = $rating_result;
				
			$data['hide_rating_form'] = $general_settings[Multi_Rating::HIDE_RATING_FORM_AFTER_SUBMIT_OPTION];
			
			echo json_encode( array(
					'status' => 'success',
					'data' => $data,
					'message' => $message,
					'validation_results' => $validation_results
			) );
		}
			
		die();
	}
}
?>