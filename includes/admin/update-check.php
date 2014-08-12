<?php 

/**
 * Performs a check if plugin upgrade requires some changes
 */
function mr_update_check() {
	
	// Check if we need to do an upgrade from a previous version
	$previous_plugin_version = get_option( Multi_Rating::VERSION_OPTION );
	
	if ( $previous_plugin_version != Multi_Rating::VERSION && $previous_plugin_version < 3 ) {
	
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
	
		update_option( Multi_Rating::VERSION_OPTION, Multi_Rating::VERSION );
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