<?php 
/**
 * Gets the client ip address
 *
 * @since 2.1
 */
function mr_get_ip_address() {
	
	$client_IP_address = '';
	
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$client_IP_address = $_SERVER['HTTP_CLIENT_IP'];
	} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$client_IP_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
		$client_IP_address = $_SERVER['HTTP_X_FORWARDED'];
	} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
		$client_IP_address = $_SERVER['HTTP_FORWARDED_FOR'];
	} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
		$client_IP_address = $_SERVER['HTTP_FORWARDED'];
	} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$client_IP_address = $_SERVER['REMOTE_ADDR'];
	}
	
	return $client_IP_address;
}

/**
 * Gets the Font Awesome icon classes based on version
 * 
 * @param $font_awesome_version
 * @return array icon classes
 */
function mr_get_icon_classes( $font_awesome_version ) {
	
	$icon_classes = array();
	
	if ( $font_awesome_version == '4.0.3' || $font_awesome_version == '4.1.0' ) {
		$icon_classes['star_full'] = 'fa fa-star mr-star-full';
		$icon_classes['star_half'] = 'fa fa-star-half-o mr-star-half';
		$icon_classes['star_empty'] = 'fa fa-star-o mr-star-empty';
		$icon_classes['minus'] = 'fa fa-minus-circle mr-minus';
	} else if ( $font_awesome_version == '3.2.1' ) {
		$icon_classes['star_full'] = 'icon-star mr-star-full';
		$icon_classes['star_half'] = 'icon-star-half-full mr-star-half';
		$icon_classes['star_empty'] = 'icon-star-empty mr-star-empty';
		$icon_classes['minus'] = 'icon-minus-sign mr-minus';
	}
	
	return $icon_classes;
}
?>