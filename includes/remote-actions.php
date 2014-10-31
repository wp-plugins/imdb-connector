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
	if($_GET["action"] == "delete_cache" && isset($_GET["nonce"]) && wp_verify_nonce($_GET["nonce"], "delete_cache")) {
		$deleted_files = count(get_imdb_connector_cached_movies());
		delete_imdb_connector_cache();
		echo $deleted_files;
	}