<?php
	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	/**
	 * Sets the default settings when plugin is activated and no
	 * settings have been (previously) set.
	 *
	 * @since 0.1
	 */
	function init_imdb_connector_default_settings() {
		/** Uses update_option() to create the default options  */
		foreach(get_imdb_connector_default_settings() as $setting_name => $default_value) {
			if(get_option($setting_name) == "") {
				update_option($setting_name, $default_value);
			}
		}
		return true;
	}

	register_activation_hook(__FILE__, "init_imdb_connector_default_settings");

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
	 * Load plugin's styles (if allowed in admin settings).
	 *
	 * @since 0.1
	 */
	function load_imdb_connector_styles_and_scripts() {
		/** Default widgets styles */
		wp_enqueue_style("imdb-connector-widgets-style", get_imdb_connector_url() . "styles/widgets.css");
	}

	if(get_imdb_connector_setting("allow_default_styles") == "on") {
		add_action("wp_enqueue_scripts", "load_imdb_connector_styles_and_scripts");
	}

	/**
	 * @since 0.1
	 */
	function load_imdb_connector_admin_styles_and_scripts() {
		wp_enqueue_style("imdb-connector-admin-style", get_imdb_connector_url() . "styles/admin.css");
		wp_enqueue_script("imdb-connector-admin-scripts", get_imdb_connector_url() . "scripts/admin.js");
	}

	add_action("admin_enqueue_scripts", "load_imdb_connector_admin_styles_and_scripts");