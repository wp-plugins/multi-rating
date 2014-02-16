<?php

/**
 * This file performs a check to determine whether an update is required
 */

// Check if we need to do an upgrade from a previous version
$previous_plugin_version = get_option( Multi_Rating::VERSION_OPTION );
if ( $previous_plugin_version != Multi_Rating::VERSION ) {
	
	// reactivate plugin and db updates will occur
	Multi_Rating::activate_plugin();
	
	try {
		// Delete old files that are no longer used from previous versions
		
		// PHP files
		if (file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'multi-rating-table.php'))
			unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'multi-rating-table.php');
		if (file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates.php'))
			unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates.php');
		
		// Dirs
		
		// JS
		if (file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'multi-rating-admin.js'))
			unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'multi-rating-admin.js');
		if (file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'multi-rating-form.js'))
			unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'multi-rating-form.js');
		
		// Images
		
		// CSS
		if (file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'multi-rating.css'))
			unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'multi-rating.css');
		
		
	} catch (Exception $e) {
		die('An error occured updating the plugin file structure! Try manually deleting the plugin files to fix the problem.');
	}

	update_option( Multi_Rating::VERSION_OPTION, Multi_Rating::VERSION );
}

/**
 * Recursive function to remove a directory and all it's sub-directories and contents
 * @param unknown_type $dir
 */
function mr_recursive_rmdir_and_unlink($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . DIRECTORY_SEPARATOR . $object) == "dir")
					recursive_rmdir_and_unlink($dir. DIRECTORY_SEPARATOR . $object);
				else unlink($dir . DIRECTORY_SEPARATOR . $object);
			}
		}
		
		reset($objects);
		
		rmdir($dir);
	}
}
 
 ?>