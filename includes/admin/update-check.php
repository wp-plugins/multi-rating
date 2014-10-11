<?php 

/**
 * Performs a check if plugin upgrade requires some changes
 */
function mr_update_check() {
	
	// Check if we need to do an upgrade from a previous version
	$previous_plugin_version = get_option( Multi_Rating::VERSION_OPTION );
	
	if ( $previous_plugin_version != Multi_Rating::VERSION && $previous_plugin_version < 3 ) {
		mr_upgrade_to_3_0();
	}	
	
	if ( $previous_plugin_version != Multi_Rating::VERSION && $previous_plugin_version < 3.1 ) {
		mr_upgrade_from_3_0_to_3_1();
	
		update_option( Multi_Rating::VERSION_OPTION, Multi_Rating::VERSION ); // latest version upgrade complete
	}
}

/**
 * Upgrade to v3.x
 */
function mr_upgrade_to_3_0() {
	
	// activate plugin and db updates will occur
	Multi_Rating::activate_plugin();
	
	try {
		/**
		 * Delete old files that are no longer used from previous version
		 */
			
		$root = dirname(__FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
			
		// PHP files
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'filters.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'filters.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'multi-rating-api.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'multi-rating-api.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'rating-item-entry-table.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'rating-item-entry-table.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'rating-item-table.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'rating-item-table.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'rating-result-view.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'rating-result-view.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'rating-form-view.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'rating-form-view.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'rating-item-entry-value-table.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'rating-item-entry-value-table.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'shortcodes.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'shortcodes.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'update-check.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'update-check.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'utils.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'utils.php');
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'widgets.php'))
			unlink($root . DIRECTORY_SEPARATOR . 'widgets.php');
			
		// Dirs
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'js' ) )
			mr_recursive_rmdir_and_unlink( $root . DIRECTORY_SEPARATOR . 'js' );
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'css' ) )
			mr_recursive_rmdir_and_unlink( $root . DIRECTORY_SEPARATOR . 'css' );
		if (file_exists( $root . DIRECTORY_SEPARATOR . 'img' ) )
			mr_recursive_rmdir_and_unlink( $root . DIRECTORY_SEPARATOR . 'img' );
			
		// JS
			
		// Images
			
		// CSS
			
		/**
		 * Migrate options that have been renamed
		 */
			
	} catch (Exception $e) {
		die( __( 'An error occured.', 'multi-rating' ) );
	}
}

/**
 * Upgrade from 3.0.x to 3.1
 */
function mr_upgrade_from_3_0_to_3_1() {
	
	// activate plugin and db updates will occur
	Multi_Rating::activate_plugin();
	
	// replace username with user ID
	global $wpdb;
	
	$num_column_exists = $wpdb->query( 'SHOW COLUMNS FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' LIKE "username"' );
	
	if ( $num_column_exists > 0) { // if username column exists
		$query = 'SELECT username, rating_item_entry_id FROM ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE username != ""';
		$rows = $wpdb->get_results( $query );
			
		foreach ( $rows as $row ) {
			$query = 'SELECT ID FROM ' . $wpdb->users . ' WHERE user_login = "' . $row->username . '"';
			$user_id = $wpdb->get_var( $query );
			if ( $user_id ) {
				$wpdb->update( $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array( 'user_id' => $user_id ), array( 'rating_item_entry_id' =>  $row->rating_item_entry_id ) );
			}
		}
	
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' DROP COLUMN username' );
	}
	
}

/**
 * Recursive function to remove a directory and all it's sub-directories and contents
 * @param  $dir
 */
function mr_recursive_rmdir_and_unlink( $dir ) {
	
	if ( is_dir( $dir ) ) {
		
		$objects = scandir( $dir );
		
		foreach ( $objects as $object ) {
			if ( $object != '.' && $object != '..' ) {
				
				if ( filetype($dir . DIRECTORY_SEPARATOR . $object ) == 'dir' ) {
					mr_recursive_rmdir_and_unlink( $dir. DIRECTORY_SEPARATOR . $object );
				} else {
					unlink( $dir . DIRECTORY_SEPARATOR . $object );
				}
				
			}
		}
		
		reset( $objects );
		rmdir( $dir );
	}
}
?>