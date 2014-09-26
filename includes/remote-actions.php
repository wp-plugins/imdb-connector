<?php
	/**
	 * This file handles all the requests by jQuery/AJAX.
	 *
	 * @since 0.2
	 */

	/** Stop script if file is called directly or with no valid request */
	if(!isset($_GET) || !count($_GET)) {
		return false;
	}

	/** Include WordPress */
	require("../../../../wp-load.php");

	/** "Delete cache" function */
	if($_GET["action"] == "delete_cache") {
		/** Get all .jpg and .tmp files in the cache directory */
		$temp_files    = glob(get_imdb_connector_cache_path() . "/*.tmp");
		$cover_files   = glob(get_imdb_connector_cache_path() . "/*.jpg");
		$files         = array_merge($temp_files, $cover_files);
		$deleted_files = 0;
		foreach($files as $file) {
			/** Skip file if it can't be deleted */
			if(!unlink($file)) {
				continue;
			}
			$deleted_files++;
		}
		echo $deleted_files;
	}
