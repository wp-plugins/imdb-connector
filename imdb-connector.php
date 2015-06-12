<?php
	/**
	 * Plugin name:  IMDb Connector
	 * Plugin URI:   http://www.koljanolte.com/wordpress/plugins/imdb-connector/
	 * Description:  A simple plugin that allows you to easily get movie details from IMDb.com.
	 * Version:      1.2.0
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
	$file_names    = array_merge($widget_files, $include_files);

	foreach($file_names as $file_name) {
		if(strstr($file_name, "remote-actions.php")) {
			continue;
		}
		include($file_name);
	}

	register_activation_hook(__FILE__, "imdb_connector_install");
	register_deactivation_hook(__FILE__, "imdb_connector_uninstall");