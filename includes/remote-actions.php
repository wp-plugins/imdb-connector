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
		$deleted_files = 0;
		/** Get all .jpg and .tmp files in the cache directory */
		$temp_files  = glob(get_imdb_connector_cache_path() . "/*.tmp");
		$cover_files = glob(get_imdb_connector_cache_path() . "/*.jpg");
		$files       = array_merge($temp_files, $cover_files);
		/** Delete local cache */
		foreach($files as $file) {
			/** Skip file if it can't be deleted */
			if(!unlink($file)) {
				continue;
			}
			$deleted_files++;
		}
		/** Delete database cache */
		$cache = get_option("imdb_connector_cache");
		if(update_option("imdb_connector_cache", array())) {
			$deleted_files = $deleted_files + count($cache);
		}
		echo $deleted_files;
	}