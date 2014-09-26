<?php
	/**
	 * Plugin name:  IMDb Connector
	 * Plugin URI:   http://www.koljanolte.com/wordpress/plugins/imdb-connector/
	 * Description:  A neat plugin that allows you to get movie details from IMDb.com.
	 * Version:      0.2
	 * Author:       Kolja Nolte
	 * Author URI:   http://www.koljanolte.com
	 * License:      GPLv2 or later
	 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
	 */

	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	/** Include plugin files */
	$widget_files  = glob(dirname(__FILE__) . "/widgets/*.php");
	$include_files = glob(dirname(__FILE__) . "/includes/*.php");
	$filenames     = array_merge($widget_files, $include_files);
	foreach($filenames as $filename) {
		if(strstr($filename, "remote-actions.php")) {
			continue;
		}
		/** @noinspection PhpIncludeInspection */
		include $filename;
	}