<?php
	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	/**
	 * Loads the text domain for localization from languages/ directory.
	 *
	 * @since 0.1
	 */
	function init_imdb_connector_languages() {
		load_plugin_textdomain("imdb_connector", false, dirname(plugin_basename(__FILE__)) . "/../languages");
	}

	add_action("init", "init_imdb_connector_languages");

	/**
	 * Load plugin's styles.
	 *
	 * @since 0.1
	 */
	function load_imdb_connector_styles_and_scripts() {
		/** Default widgets styles */
		wp_enqueue_style("imdb-connector-widgets-style", get_imdb_connector_url() . "styles/widgets.css");
	}

	add_action("wp_enqueue_scripts", "load_imdb_connector_styles_and_scripts");

	/**
	 * @since 0.1
	 */
	function load_imdb_connector_admin_styles_and_scripts() {
		wp_enqueue_style("imdb-connector-admin-style", get_imdb_connector_url() . "styles/admin.css");
		wp_enqueue_script("imdb-connector-admin-scripts", get_imdb_connector_url() . "scripts/admin.js");
	}

	add_action("admin_enqueue_scripts", "load_imdb_connector_admin_styles_and_scripts");

	/**
	 * Checks if the current date is over the set limit
	 * and deletes the cache accordingly.
	 *
	 * @since 0.4
	 *
	 * @return bool
	 */
	function init_imdb_connector_auto_delete() {
		$setting = get_imdb_connector_setting("auto_delete");
		if(!$setting || $setting == "off") {
			return false;
		}
		$date_now          = date("Y-m-d H:i:s");
		$date_last_deleted = get_option("imdb_connector_last_deleted_date");
		if(!$date_last_deleted) {
			return update_option("imdb_connector_last_deleted_date", $date_now);
		}
		$difference = date_diff(new DateTime($date_now), new DateTime($date_last_deleted));
		$delete     = false;
		if($setting == "24_hours") {
			if($difference->d || $difference->m || $difference->y) {
				$delete = true;
			}
		}
		elseif($setting == "3_days") {
			if($difference->d >= 3 || $difference->m || $difference->y) {
				$delete = true;
			}
		}
		elseif($setting == "30_days") {
			if($difference->d >= 30 || $difference->m || $difference->y) {
				$delete = true;
			}
		}
		elseif($setting == "6_months") {
			if($difference->m >= 6 || $difference->y) {
				$delete = true;
			}
		}
		/** Delete the cache and update the last deleted date */
		if($delete) {
			delete_imdb_connector_cache();
			update_option("imdb_connector_last_deleted_date", $date_now);
		}
		return true;
	}

	add_action("admin_init", "init_imdb_connector_auto_delete");
	add_action("init", "init_imdb_connector_auto_delete");