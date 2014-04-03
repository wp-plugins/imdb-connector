<?php
	/**
	 * Plugin name:  IMDb Connector
	 * Plugin URI:   http://www.koljanolte.com/wordpress/plugins/imdb-connector/
	 * Description:  A neat plugin that allows you to get movie details from IMDb.com.
	 * Version:      0.1
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

	function include_imdb_connector_files() {
		/** Include widgets */
		$widgets_directory = dirname(__FILE__) . "/widgets";
		foreach(glob($widgets_directory . "/*.php") as $filename) {
			/** @noinspection PhpIncludeInspection */
			include $filename;
		}
		/** Include functions */
		include("functions.php");
	}

	include_imdb_connector_files();

	/**
	 * Defines the plugin's settings and their default values.
	 *
	 * @param string $setting
	 *
	 * @return array
	 */
	function get_imdb_connector_default_settings($setting = "") {
		/** Build the settings and their default values */
		$settings = array(
			"allow_caching"        => "on",
			"allow_shortcodes"     => "on",
			"allow_default_styles" => "on",
		);
		/** Check if the selected setting exists */
		if(!empty($setting) && isset($settings[$setting])) {
			$settings = $settings[$setting];
		}
		else {
			$settings = false;
		}
		return $settings;
	}

	/**
	 * Sets the default settings when plugin is activated.
	 */
	function init_imdb_connector_default_settings() {
		/** Uses update_option() to create the default options  */
		foreach(get_imdb_connector_default_settings() as $setting => $value) {
			add_option($setting, $value);
		}
	}

	register_activation_hook(__FILE__, "init_imdb_connector_default_settings");

	/**
	 * @param $setting
	 *
	 * @return array|mixed|string|void
	 */
	function get_imdb_connector_setting($setting) {
		$setting = "imdb_connector_" . $setting;
		if(get_option($setting) == "") {
			$setting = get_imdb_connector_default_settings($setting);
		}
		else {
			$setting = get_option($setting);
		}
		return $setting;
	}

	/**
	 * Loads the text domain for localization from languages/ directory.
	 */
	function init_imdb_connector_languages() {
		load_textdomain("imdb_connector", false, dirname(plugin_basename(__FILE__)) . "/languages/");
	}

	add_action("init", "init_imdb_connector_languages");

	/**
	 * Load plugin's styles (if allowed in admin settings).
	 */
	function load_imdb_connector_styles() {
		wp_enqueue_style("imdb-connector-style", plugin_dir_url(__FILE__) . "style.css", array());
	}

	if(get_imdb_connector_setting("allow_default_styles") == "on") {
		add_action("wp_enqueue_scripts", "load_imdb_connector_styles");
	}

	/**
	 * Returns the plugin's cache directory for the covers.
	 *
	 * @return string
	 */
	function get_imdb_connector_cache_directory() {
		return dirname(__FILE__) . "/cache";
	}

	/**
	 * Returns the plugin's cache directory as URL.
	 *
	 * @return string
	 */
	function get_imdb_connector_cache_directory_url() {
		$plugin_directory = explode("\\", dirname(__FILE__));
		$plugin_directory = $plugin_directory[count($plugin_directory) - 1];
		$url              = plugin_dir_url("") . $plugin_directory . "/cache";
		return $url;
	}

	/**
	 * Returns the movie details.
	 *
	 * @param $id_or_title
	 *
	 * @return array|mixed|string
	 */
	function get_imdb_movie($id_or_title) {
		if(!isset($id_or_title[0])) {
			return false;
		}
		/** Check whether selector is an ID or a movie title */
		if($id_or_title[0] == "t" && $id_or_title[1] == "t" && strlen($id_or_title) == 9) {
			$type = "i";
		}
		else {
			$type = "t";
			/** Sanitize title to be URL friendly */
			$id_or_title = rawurlencode($id_or_title);
		}
		$md5_name         = md5($id_or_title);
		$cache_directory  = str_replace("\\", "/", get_imdb_connector_cache_directory());
		$movie_cache_file = $cache_directory . "/" . $md5_name . ".tmp";
		/** Get movie details from cache file if exists */
		if(file_exists($movie_cache_file)) {
			$handle        = fopen($movie_cache_file, "r");
			$movie_details = json_decode(fread($handle, strlen(file_get_contents($movie_cache_file))), true);
			fclose($handle);
		}
		/** Create movie details and cover cache files */
		else {
			$movie_details       = file_get_contents("http://www.omdbapi.com/?" . $type . "=" . $id_or_title);
			$movie_details_array = json_decode($movie_details, true);
			if(get_imdb_connector_setting("allow_caching") == "on") {
				$cover_cache_file = $cache_directory . "/" . get_url_filename($movie_details_array["Poster"]);
				$handle           = fopen($movie_cache_file, "w+");
				if(!file_exists($cover_cache_file)) {
					/** Download cover and replace the URL  */
					$cover_handle = fopen($cover_cache_file, "w+");
					fwrite($cover_handle, file_get_contents($movie_details_array["Poster"]));
					fclose($cover_handle);
					$movie_details_array["Poster"] = get_imdb_connector_cache_directory_url() . "/" . get_url_filename($cover_cache_file);
					fwrite($handle, json_encode($movie_details_array));
					fclose($handle);
				}
			}
			else {
				$movie_details = $movie_details_array;
			}
		}
		return array_change_key_case_recursive($movie_details, CASE_LOWER);
	}

	/**
	 * Returns if the set query returns valid movie details.
	 *
	 * @param $id_or_title
	 *
	 * @return bool
	 */
	function has_imdb_movie($id_or_title) {
		if(get_imdb_movie($id_or_title)) {
			$has = true;
		}
		else {
			$has = false;
		}
		return $has;
	}

	/**
	 * Returns an array with the found movies details.
	 *
	 * @param $title
	 *
	 * @return array
	 */
	function get_imdb_movies($title) {
		$movies     = file_get_contents("http://www.omdbapi.com/?s=" . rawurldecode($title));
		$movies     = array_change_key_case_recursive(json_decode($movies, true));
		$new_movies = array();
		foreach($movies["search"] as $movie) {
			array_push($new_movies, $movie);
		}
		return $new_movies;
	}

	/**
	 * Returns - if available - a certain movie detail.
	 *
	 * @param        $id_or_title
	 * @param string $detail
	 *
	 * @return bool
	 */
	function get_imdb_movie_detail($id_or_title, $detail = "title") {
		$output = "";
		$movie  = get_imdb_movie($id_or_title);
		if(!$movie) {
			$output = false;
		}
		if(isset($movie[$detail])) {
			$output = $movie[$detail];
		}
		return $output;
	}

	/**
	 * @param $attributes
	 *
	 * @return bool|string
	 */
	function imdb_connector_shortcode_movie_detail($attributes) {
		print_r(get_imdb_movie("Seven"));
		extract($attributes);
		if(!isset($title)) {
			$title = "";
		}
		if(!isset($detail)) {
			$detail = "";
		}
		$movie_detail = "";
		if(isset($title) || isset($id) && isset($detail)) {
			if(isset($id)) {
				$title = $id;
			}
			$movie_detail = get_imdb_movie_detail($title, $detail);
		}
		return $movie_detail;
	}

	/** Only add if set on settings page */
	if(get_imdb_connector_setting("allow_shortcodes") == "on") {
		add_shortcode("imdb_movie_detail", "imdb_connector_shortcode_movie_detail");
	}

	/**
	 * Checks if option has a specific value and makes HTML input checked/unchecked.
	 *
	 * @param $setting
	 * @param $check_value
	 */
	function imdb_check_setting($setting, $check_value) {
		if(get_imdb_connector_setting($setting) == $check_value) {
			echo ' checked="checked"';
		}
	}

	/**
	 * Initializes plugin's setting page.
	 */
	function init_admin_settings_page() {
		/** Creates a new page on the admin interface */
		add_options_page(__("IMDb Connector settings"), "IMDb Connector", "manage_options", "imdb-connector", "build_admin_settings_page");
	}

	add_action("admin_menu", "init_admin_settings_page");

	/**
	 * Builds the plugin's settings page.
	 */
	function build_admin_settings_page() {
		if(isset($_POST["saved"])) {
			$fields = array(
				"allow_caching",
				"allow_shortcodes",
				"allow_default_styles",
			);
			foreach($fields as $field) {
				update_option("imdb_connector_" . $field, $_POST[$field]);
			}
		}

		?>
		<div class="wrap">
			<h2><?php echo __("Settings", "imdb_connector") . " › " . __("IMDb Connector", "imdb_connector"); ?></h2>

			<form method="post" action="">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="allow_caching_on"><?php _e("Caching", "imdb_connector"); ?></label>
							</th>
							<td>
								<input type="radio" name="allow_caching" id="allow_caching_on" value="on"<?php imdb_check_setting("allow_caching", "on"); ?> />
								<label for="allow_caching_on"><?php _e("On", "imdb_connector"); ?></label>
								<input type="radio" name="allow_caching" id="allow_caching_off" value="off"<?php imdb_check_setting("allow_caching", "off"); ?> />
								<label for="allow_caching_off"><?php _e("Off", "imdb_connector"); ?></label>
								<small>(<?php _e("Default", "imdb_connector"); ?>
								       : <?php echo get_imdb_connector_default_settings("allow_caching"); ?>)
								</small>

								<p class="description"><?php _e("Allows IMDb Connector to cache movie details and covers for faster access.", "imdb_connector"); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="allow_shortcodes_on"><?php _e("Shortcodes", "imdb_connector"); ?></label>
							</th>
							<td>
								<input type="radio" name="allow_shortcodes" id="allow_shortcodes_on" value="on"<?php imdb_check_setting("allow_shortcodes", "on"); ?> />
								<label for="allow_shortcodes_on"><?php _e("On", "imdb_connector"); ?></label>
								<input type="radio" name="allow_shortcodes" id="allow_shortcodes_off" value="off"<?php imdb_check_setting("allow_shortcodes", "off"); ?> />
								<label for="allow_shortcodes_off"><?php _e("Off", "imdb_connector"); ?></label>
								<small>(<?php _e("Default", "imdb_connector"); ?>
								       : <?php echo get_imdb_connector_default_settings("allow_shortcodes"); ?>)
								</small>

								<p class="description"><?php _e('Provides <a href="http://codex.wordpress.org/Shortcode_API" target="_blank">shortcodes</a> to easily insert movie details into your posts or pages.<br />To learn how, please see the <a href="www.koljanolte.com/wordpress/plugins/imdb-connector/" target="_blank" title="See online documentary">online documentary.</a>', "imdb_connector"); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="allow_default_styles_on"><?php _e("Use default styles", "imdb_connector"); ?></label>
							</th>
							<td>
								<input type="radio" name="allow_default_styles" id="allow_default_styles_on" value="on"<?php imdb_check_setting("allow_default_styles", "on"); ?> />
								<label for="allow_default_styles_on"><?php _e("On", "imdb_connector"); ?></label>
								<input type="radio" name="allow_default_styles" id="allow_default_styles_off" value="off"<?php imdb_check_setting("allow_default_styles", "off"); ?> />
								<label for="allow_default_styles_off"><?php _e("Off", "imdb_connector"); ?></label>
								<small>(<?php _e("Default", "imdb_connector"); ?>
								       : <?php echo get_imdb_connector_default_settings("allow_default_styles"); ?>)
								</small>

								<p class="description"><?php _e("Makes IMDb Connector use its default CSS styles for widgets etc.<br />defined in <code>styles.css</code>.", "imdb_connector"); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<p><?php _e('Do you speak more than one language? Help making IMDb Connector<br />easier to use for foreign users by <a href="https://www.transifex.com/projects/p/plugin-imdb-connector/" target="_blank">translating the plugin on Transifex.</a>', "imdb_connector"); ?></p>

				<p>
					<br />
					<input type="hidden" name="saved" value="true" />
					<input type="submit" name="save_settings" class="button-primary" value="<?php _e("Save changes", "imdb_connector"); ?>" />
				</p>
			</form>
		</div>
	<?php
	}