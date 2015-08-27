<?php
	/** Prevents this file from being called directly */
	if(!function_exists("add_action")) {
		return;
	}

	/**
	 * Loads the text domain for localization from languages/ directory.
	 *
	 * @since 0.1
	 */
	function init_imdb_connector_languages() {
		$translation_path = plugin_dir_path(__FILE__) . "../languages/" . get_site_option("WPLANG") . ".mo";
		load_textdomain("imdb_connector", $translation_path);
	}

	add_action("init", "init_imdb_connector_languages");

	/**
	 * Load plugin's styles.
	 *
	 * @since 0.1
	 */
	function load_imdb_connector_styles_and_scripts() {
		/** Default widgets styles */
		wp_enqueue_style(
			"imdb-connector-style-widget",
			imdb_connector_get_url() . "styles/widgets.css",
			array(),
			imdb_connector_get_plugin_version()
		);
	}

	add_action("wp_enqueue_scripts", "load_imdb_connector_styles_and_scripts");

	/**
	 * Loads plugin's scripts and styles used on the Dashboard.
	 *
	 * @since 0.1
	 */
	function load_imdb_connector_admin_styles_and_scripts() {
		/** Styles */
		wp_enqueue_style(
			"imdb-connector-style-admin",
			imdb_connector_get_url() . "styles/admin.css",
			array(),
			imdb_connector_get_plugin_version()
		);

		wp_enqueue_style(
			"imdb-connector-style-font-awesome",
			"//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css",
			array(),
			"4.4.0"
		);

		/** Scripts */
		wp_enqueue_script(
			"imdb-connector-script-admin",
			imdb_connector_get_url() . "scripts/admin.js",
			array("jquery"),
			imdb_connector_get_plugin_version()
		);
	}

	add_action("admin_enqueue_scripts", "load_imdb_connector_admin_styles_and_scripts");

	/**
	 * Initializes plugin's setting page.
	 *
	 * @since 0.1
	 */
	function init_admin_settings_page() {
		/** Creates a new page on the admin interface */
		add_options_page(__("Settings", "imdb_connector"), "IMDb Connector", "manage_options", "imdb-connector", "build_admin_settings_page");
	}

	add_action("admin_menu", "init_admin_settings_page");

	/**
	 * Checks if the current date is over the set limit
	 * and deletes the cache accordingly.
	 *
	 * @since 0.4
	 *
	 * @return bool
	 */
	function init_imdb_connector_auto_delete() {
		$setting = imdb_connector_get_setting("auto_delete");
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
			imdb_connector_delete_cache();
			update_option("imdb_connector_last_deleted_date", $date_now);
		}

		return true;
	}

	add_action("plugins_loaded", "init_imdb_connector_auto_delete");

	function imdb_connector_add_shortcode() {
		/** Only add if set on settings page */
		if(imdb_connector_get_setting("allow_shortcodes") == "on") {
			add_shortcode("imdb_movie_detail", "imdb_connector_shortcode_movie_detail");
		}
	}

	add_action("init", "imdb_connector_add_shortcode");