<?php 
/**
 * Gets the client ip address
 *
 * @since 2.1
 */
function mr_get_ip_address() {
	$client_IP_address = '';
	if ( isset($_SERVER['HTTP_CLIENT_IP']) )
		$client_IP_address = $_SERVER['HTTP_CLIENT_IP'];
	else if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
		$client_IP_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if ( isset($_SERVER['HTTP_X_FORWARDED']) )
		$client_IP_address = $_SERVER['HTTP_X_FORWARDED'];
	else if ( isset($_SERVER['HTTP_FORWARDED_FOR']) )
		$client_IP_address = $_SERVER['HTTP_FORWARDED_FOR'];
	else if ( isset($_SERVER['HTTP_FORWARDED']) )
		$client_IP_address = $_SERVER['HTTP_FORWARDED'];
	else if ( isset($_SERVER['REMOTE_ADDR']) )
		$client_IP_address = $_SERVER['REMOTE_ADDR'];

	return $client_IP_address;
}
?>