<?php
	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	/**
	 * Defines the plugin's settings and their default values.
	 *
	 * @internal param string $setting
	 *
	 * @since    0.1
	 *
	 * @return array
	 */
	function get_imdb_connector_default_settings() {
		/** Build the settings and their default values */
		$settings = array(
			"imdb_connector_allow_caching"         => "on",
			"imdb_connector_cache_location"        => "database",
			"imdb_connector_database_table"        => "imdb_connector",
			"imdb_connector_create_database_table" => "on",
			"imdb_connector_allow_shortcodes"      => "on",
			"imdb_connector_auto_delete"           => "off",
			"imdb_connector_deactivation_actions"  => array(),
			"imdb_connector_debug_mode"            => "off"
		);

		return $settings;
	}

	/**
	 * Returns plugin's settings names and their default values.
	 *
	 * @param $setting
	 *
	 * @since 0.1
	 *
	 * @return bool
	 */
	function get_imdb_connector_default_setting($setting) {
		if(!strstr($setting, "imdb_connector_")) {
			$setting = "imdb_connector_" . $setting;
		}
		$settings = get_imdb_connector_default_settings();
		if(!array_key_exists($setting, $settings, false)) {
			return false;
		}

		return $settings[$setting];
	}

	/**
	 * Returns plugin's settings names and their set values; uses default value if not set.
	 *
	 * @return array
	 *
	 * @since 0.2
	 */
	function get_imdb_connector_settings() {
		$settings = array();
		foreach(get_imdb_connector_default_settings() as $setting => $default_value) {
			$option = get_option($setting);
			$value  = $option;
			if(!$option) {
				$value = $default_value;
			}
			if(!is_array($value) && strstr($value, "%imdb_connector_path%")) {
				$value = str_replace("%imdb_connector_path%", plugin_dir_path(dirname(__FILE__)), $value);
			}
			$settings[$setting] = $value;
		}

		return $settings;
	}

	/**
	 * Returns a specific plugin setting; uses default value if not set.
	 *
	 * @param $setting
	 *
	 * @since 0.1
	 *
	 * @return array|mixed|string|void
	 */
	function get_imdb_connector_setting($setting) {
		$setting  = "imdb_connector_" . $setting;
		$settings = get_imdb_connector_settings();
		/** Use default value if setting is not set */
		if($settings[$setting] == "") {
			$setting = get_imdb_connector_default_settings($setting);
		}
		else {
			$setting = $settings[$setting];
		}

		return $setting;
	}

	/**
	 * Sets the default settings when plugin is activated and no
	 * settings have been (previously) set.
	 *
	 * @since    0.1
	 *
	 * @param bool $default_settings
	 * @param bool $overwrite
	 *
	 * @internal param bool $create_table
	 * @internal param bool $table
	 * @return bool
	 */
	function imdb_connector_install($default_settings = true, $overwrite = false) {
		/** Uses update_option() to create the default options  */
		if($default_settings) {
			foreach(get_imdb_connector_default_settings() as $setting_name => $default_value) {
				if($overwrite || get_option($setting_name) == "") {
					update_option($setting_name, $default_value);
				}
			}
		}
		global $wpdb;
		$table = $wpdb->prefix . get_imdb_connector_setting("database_table");
		if(get_imdb_connector_setting("create_database_table") == "on") {
			if($overwrite || !$wpdb->get_var("SHOW TABLES LIKE '$table'")) {
				$query = "DROP TABLE IF EXISTS `$table`;";
				$wpdb->query($query);
			}
			else {
				return false;
			}
			/** Create plugin table */
			$query = "
				CREATE TABLE IF NOT EXISTS `$table` (
					`ID`        bigint(20)  NOT NULL AUTO_INCREMENT,
					`title`     text        NOT NULL,
					`imdbid`    text        NOT NULL,
					`year`      bigint(4)   NOT NULL,
					`rated`     text        NOT NULL,
					`released`  bigint(4)   NOT NULL,
					`runtime`   text        NOT NULL,
					`genres`    text        NOT NULL,
					`directors` text        NOT NULL,
					`writers`   text        NOT NULL,
					`actors`    text        NOT NULL,
					`languages` text        NOT NULL,
					`countries` text        NOT NULL,
					`plot`      longtext    NOT NULL,
					`awards`    text        NOT NULL,
					`poster`    text        NOT NULL,
					`metascore` text        NOT NULL,
					`imdbvotes` text        NOT NULL,
					`imdbrating`text        NOT NULL,
					`type`      text        NOT NULL,
					PRIMARY KEY (`ID`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				";

			return $wpdb->query($query);
		}
		update_option("imdb_connector_cleared_cache_date", date("Y-m-d"));

		return true;
	}

	/**
	 * Removes generated cache files.
	 *
	 * @since    0.4
	 *
	 * @return bool
	 */
	function imdb_connector_uninstall() {
		$option = get_imdb_connector_setting("deactivation_actions");
		/** Delete MySQL table */
		if(in_array("database", $option, false)) {
			global $wpdb;
			$table = $wpdb->prefix . get_imdb_connector_setting("database_table");
			$wpdb->query("DROP TABLE $table");
		}
		/** Delete cached posters */
		if(in_array("posters", $option, false)) {
			$posters = glob(get_imdb_connector_cache_path() . "/*.jpg");
			foreach($posters as $poster) {
				unlink($poster);
			}
		}
		/** Delete cached movie details in cache directory */
		if(in_array("movie_details", $option, false)) {
			$movie_details = glob(get_imdb_connector_cache_path() . "/*.tmp");
			foreach($movie_details as $movie_detail) {
				unlink($movie_detail);
			}
		}
		/** Deletes plugin settings */
		if(in_array("settings", $option, false)) {
			foreach(get_imdb_connector_settings() as $setting_name => $setting_value) {
				delete_option($setting_name);
			}
		}

		return true;
	}

	/**
	 * Returns the absolute path to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 *
	 * @return mixed
	 */
	function get_imdb_connector_cache_path() {
		$path = str_replace("\\", "/", plugin_dir_path(dirname(__FILE__))) . "cache";

		return $path;
	}

	/**
	 * Displays the absolute path to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 */
	function the_imdb_connector_cache_path() {
		echo get_imdb_connector_cache_path();
	}

	/**
	 * Returns the URL to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	function get_imdb_connector_cache_url() {
		$cache_url = plugin_dir_url(dirname(__FILE__)) . "cache";

		return $cache_url;
	}

	/**
	 * Displays the URL to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 */
	function the_imdb_connector_cache_url() {
		echo get_imdb_connector_cache_url();
	}

	/**
	 * Sanitizes the given title for the API URL.
	 *
	 * @param $title
	 *
	 * @since 0.2
	 *
	 * @return mixed
	 */
	function imdb_connector_sanitize_url_title($title) {
		/** Sanitizes wptexturized'ed characters */
		if(strstr($title, "%26%238217%3B")) {
			$title = urlencode($title);
			$title = str_replace("%26%238217%3B", "'", $title);
			$title = urldecode($title);
		}
		/** Transform characters to URL characters */
		$title = rawurlencode($title);

		return $title;
	}

	/**
	 * Strips the filename out of a URL.
	 *
	 * @param      $url
	 * @param bool $no_ending
	 *
	 * @since 0.1
	 *
	 * @return array|bool|mixed|string
	 */
	function imdb_connector_get_url_filename($url, $no_ending = false) {
		$url = explode("/", $url);
		$url = $url[count($url) - 1];
		$url = str_replace("\\", "/", $url);
		if(!strstr($url, ".")) {
			return false;
		}
		if($no_ending) {
			$url = substr($url, 0, strlen($url) - 4);
		}

		return $url;
	}

	/**
	 * Returns the movie details.
	 *
	 * @param       $id_or_title
	 * @param array $options
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	function get_imdb_connector_movie($id_or_title, array $options = array()) {
		/** Define default function options */
		$default_options = array(
			"format"         => "array",
			"allow_caching"  => get_imdb_connector_setting("allow_caching"),
			"cache_location" => get_imdb_connector_setting("cache_location")
		);
		/** Use default option value if option is not set */
		foreach($default_options as $option_name => $default_value) {
			if(!array_key_exists($option_name, $options) || !$options[$option_name]) {
				$options[$option_name] = $default_value;
			}
		}

		/** Define variables */
		$api_url = "http://www.omdbapi.com/?";
		$type    = "t";

		/** Check whether $id_or_title is an IMDb ID */
		if(substr($id_or_title, 0, 2) == "tt") {
			$type = "i";
		}
		/** Sanitize $id_or_title to be URL friendly */
		$id_or_title_url = imdb_connector_sanitize_url_title($id_or_title);
		/** Build request API URL */
		$api_url .= $type . "=" . $id_or_title_url;

		$movie_details = array();
		$found         = true;

		if(get_imdb_connector_setting("allow_caching") != "off") {
			$file_name            = substr(md5($id_or_title), 0, 8);
			$cache_directory_path = get_imdb_connector_cache_path();
			$cache_directory_url  = get_imdb_connector_cache_url();
			$cache_file_path      = $cache_directory_path . "/" . $file_name . ".tmp";

			if($options["cache_location"] == "local") {
				/** Display error message if the directory doesn't exist and can't be created automatically */
				if(!is_dir($cache_directory_path) && !mkdir($cache_directory_path)) {
					the_imdb_connector_debug_message(__("The cache directory does not exists and could not be created:") . " " . $cache_directory_path);

					return false;
				}
				/** Display error message if the directory exists but isn't writable */
				elseif(!is_writable($cache_directory_path)) {
					return false;
				}
				/** Get details from cached file if it exists */
				if(file_exists($cache_directory_path . "/" . $file_name . ".tmp")) {
					$movie_details = json_decode(file_get_contents($cache_file_path), true);
				}
				/** Get movie details online and create cache file */
				else {
					$handle        = fopen($cache_file_path, "a");
					$data          = wp_remote_get($api_url);
					$movie_details = json_decode($data["body"], true);
					$movie_details = imdb_connector_sanitize_movie_details($movie_details);
					fwrite($handle, stripslashes(json_encode($movie_details)));
					fclose($handle);
				}
			}
			elseif($options["cache_location"] == "database") {
				global $wpdb;
				$table = $wpdb->prefix . get_imdb_connector_setting("database_table");
				imdb_connector_install(false, false);
				$query = "SELECT * FROM $table ";
				if($type == "i") {
					$query .= "WHERE imdbid = '" . $id_or_title . "'";
				}
				else {
					$query .= "WHERE title = '$id_or_title'";
				}
				$movie_details = $wpdb->get_row($query, "ARRAY_A");
				/** Read row and convert serialized strings back to array */
				if($movie_details) {
					foreach($movie_details as $movie_detail => $value) {
						if(is_serialized($value)) {
							$movie_details[$movie_detail] = unserialize($value);
						}
					}
				}
				/** Movie doesn't exist in the database, so we add it */
				elseif(get_imdb_connector_setting("create_database_table") == "on") {
					$data          = wp_remote_get($api_url);
					$movie_details = json_decode($data["body"], true);
					$movie_details = imdb_connector_sanitize_movie_details($movie_details);
					$data          = array(
						"title"      => $movie_details["title"],
						"imdbid"     => $movie_details["imdbid"],
						"year"       => $movie_details["year"],
						"released"   => $movie_details["released"],
						"runtime"    => serialize($movie_details["runtime"]),
						"genres"     => serialize($movie_details["genres"]),
						"writers"    => serialize($movie_details["writers"]),
						"directors"  => serialize($movie_details["directors"]),
						"actors"     => serialize($movie_details["actors"]),
						"languages"  => serialize($movie_details["languages"]),
						"countries"  => serialize($movie_details["countries"]),
						"rated"      => $movie_details["rated"],
						"poster"     => $movie_details["poster"],
						"awards"     => $movie_details["awards"],
						"plot"       => $movie_details["plot"],
						"metascore"  => $movie_details["metascore"],
						"imdbrating" => $movie_details["imdbrating"],
						"imdbvotes"  => $movie_details["imdbvotes"],
						"type"       => $movie_details["type"]
					);

					$formats = array();
					foreach((array)$data as $key => $value) {
						$format = "%s";
						if(is_int($value)) {
							$format = "%d";
						}
						elseif(is_float($value)) {
							$format = "%f";
						}
						array_push($formats, $format);
					}

					$wpdb->insert($table, $data, $formats);
				}
			}
			/** Create movie poster if it doesn't exist yet */
			$poster_path = $cache_directory_path . "/" . $file_name . ".jpg";
			if(get_imdb_connector_setting("allow_caching") != "on_no_poster") {
				if(!array_key_exists("title", $movie_details)) {
					$found = false;
				}
				else {
					if(!file_exists($poster_path)) {
						$handle = fopen($poster_path, "a");
						fwrite($handle, file_get_contents($movie_details["poster"]));
						fclose($handle);
					}
					/** Change poster URL to cache file */
					$movie_details["poster"] = $cache_directory_url . "/" . $file_name . ".jpg";
				}
			}
		}
		/** Get online movie details if cache is deactivated */
		else {
			$movie_details = json_encode(file_get_contents($api_url), true);
			if(!array_key_exists("title", $movie_details)) {
				$found = false;
			}
		}

		if(!$found) {
			the_imdb_connector_debug_message(sprintf(__("The movie <strong>%s</strong> could not be found. Please check your spelling.", "imdb_connector"), $id_or_title));

			return false;
		}

		/** Convert movie details into object if set */
		if($options["format"] == "object") {
			$movie_details = json_decode(json_encode($movie_details));
		}

		return $movie_details;
	}

	/**
	 * Deprecated version of get_imdb_connector_movie().
	 *
	 * @param $id_or_title
	 *
	 * @since 0.2
	 *
	 * @return array|mixed|string
	 */
	function get_imdb_movie($id_or_title) {
		_deprecated_function("get_imdb_movie", "0.2", "get_imdb_connector_movie($id_or_title)");

		return get_imdb_connector_movie($id_or_title);
	}

	/**
	 * Searches for movies that contain the set title or ID.
	 *
	 * @param $id_or_title
	 *
	 * @since 0.2
	 *
	 * @return array
	 */
	function search_imdb_connector_movie($id_or_title) {
		$api_url = "http://www.omdbapi.com/?s=" . imdb_connector_sanitize_url_title($id_or_title);
		$results = file_get_contents($api_url);
		$results = json_decode($results, true);
		if(array_key_exists("Response", $results) && $results["Response"] == "False") {
			if(is_array($id_or_title)) {
				$id_or_title = explode(", ", $id_or_title);
			}
			the_imdb_connector_debug_message(sprintf(__('No movies could be found with the term(s) <strong>%s</strong>.', "imdb_connector"), $id_or_title));

			return false;
		}
		$results = imdb_connector_sanitize_movie_details($results);

		return (array)$results["search"];
	}

	/**
	 * Searches for movies that contain the set titles or IDs.
	 *
	 * @param array $ids_or_titles
	 *
	 * @internal param int $count
	 *
	 * @since    0.2
	 *
	 * @return array
	 */
	function search_imdb_connector_movies(array $ids_or_titles) {
		$results = array();
		foreach($ids_or_titles as $id_or_title) {
			$result = search_imdb_connector_movie($id_or_title);
			if(!$result) {
				continue;
			}
			array_push($results, $result);
		}

		return (array)$results;
	}

	/**
	 * Returns if the set query returns valid movie details.
	 *
	 * @param $id_or_title
	 *
	 * @since 0.1
	 *
	 * @return bool
	 */
	function has_imdb_connector_movie($id_or_title) {
		if(!get_imdb_connector_movie($id_or_title)) {
			return false;
		}

		return (boolean)true;
	}

	/**
	 * Deprecated version of has_imdb_connector_movie().
	 *
	 * @param $id_or_title
	 *
	 * @since 0.2
	 *
	 * @return array|mixed|string
	 */
	function has_imdb_movie($id_or_title) {
		_deprecated_function("has_imdb_movie", "0.2", "has_imdb_connector_movie($id_or_title)");

		return get_imdb_connector_movie($id_or_title);
	}

	/**
	 * @param array $titles_or_ids
	 *
	 * @since 0.2
	 *
	 * @return array|bool
	 */
	function get_imdb_connector_movies(array $titles_or_ids) {
		$movies    = array();
		$not_found = array();
		foreach($titles_or_ids as $title_or_id) {
			$movie = get_imdb_connector_movie($title_or_id);
			if(!$movie) {
				array_push($not_found, $title_or_id);
				continue;
			}
			array_push($movies, $movie);
		}
		/** Display error message if one or more movies could not be found */
		if(count($not_found) >= 1) {
			the_imdb_connector_debug_message(__("The following movie(s) could not be found. Please verify spelling:", "imdb_connector"));
			echo " " . implode(", ", $not_found);
		}

		return (array)$movies;
	}

	/**
	 * Deprecated version of get_imdb_connector_movies().
	 *
	 * @param $title
	 *
	 * @since 0.2
	 *
	 * @return array
	 */
	function get_imdb_movies($title) {
		_deprecated_function("get_imdb_movies", "0.2", "get_imdb_connector_movies($title)");

		return get_imdb_connector_movies($title);
	}

	/**
	 * Returns - if available - a certain movie detail.
	 *
	 * @param        $id_or_title
	 * @param string $detail
	 *
	 * @since 0.1
	 *
	 * @return bool
	 */
	function get_imdb_connector_movie_detail($id_or_title, $detail) {
		$movie = get_imdb_connector_movie($id_or_title);
		if(!$movie) {
			return false;
		}
		$deprecated = array(
			"genre",
			"country",
			"language",
			"director",
			"writer"
		);
		if(in_array($detail, $deprecated, false)) {
			$new_detail = $detail . "s";
			if($detail == "country") {
				$new_detail = "countries";
			}
			_deprecated_argument("get_imdb_connector_movie_detail", "0.4", "Use <strong>$new_detail</strong> instead.");
			$detail = $new_detail;
		}
		elseif(!array_key_exists($detail, $movie)) {
			the_imdb_connector_debug_message(sprintf(__('The parameter <strong>%s</strong> was not found among the movie details.', "imdb_connector"), $detail));

			return false;
		}

		return $movie[$detail];
	}

	/**
	 * Deprecated version of get_imdb_connector_movie_detail().
	 *
	 * @param        $id_or_title
	 * @param string $detail
	 *
	 * @since 0.2
	 *
	 * @return bool
	 */
	function get_imdb_movie_detail($id_or_title, $detail = "title") {
		_deprecated_function("get_imdb_movie_detail", "0.2", "get_imdb_connector_movie_detail($id_or_title, $detail)");

		return get_imdb_connector_movie_detail($id_or_title, $detail);
	}

	/**
	 * @param $attributes
	 *
	 * @since 0.1
	 *
	 * @return bool|string
	 */
	function imdb_connector_shortcode_movie_detail($attributes) {
		$title  = "";
		$detail = "";
		$id     = "";

		extract($attributes);

		$movie_detail = "";
		if($title || ($id && $detail)) {
			if($id) {
				$title = $id;
			}
			$movie_detail = get_imdb_connector_movie_detail($title, $detail);
			if(is_array($movie_detail)) {
				$movie_detail = implode(", ", $movie_detail);
			}
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
	 * @param        $setting
	 * @param        $check_value
	 *
	 * @param string $type
	 *
	 * @since 0.1
	 */
	function imdb_connector_check_setting($setting, $check_value, $type = "checked") {
		if(get_imdb_connector_setting($setting) == $check_value) {
			echo " $type=\"$type\"";
		}
	}

	/**
	 * Returns the URL to the plugin directory.
	 *
	 * @since 0.2
	 *
	 * @return string
	 *
	 */
	function get_imdb_connector_url() {
		return plugin_dir_url(dirname(__FILE__));
	}

	/**
	 * Displays the URL to the plugin directory.
	 *
	 * @since 0.2
	 */
	function the_imdb_connector_url() {
		echo get_imdb_connector_url();
	}

	/**
	 * Returns the absolute path to the plugin directory.
	 *
	 * @return string
	 *
	 * @since 0.2
	 */
	function get_imdb_connector_path() {
		return plugin_dir_path(dirname(__FILE__));
	}

	/**
	 * Displays the absolute path to the plugin directory.
	 *
	 * @since 0.2
	 */
	function the_imdb_connector_path() {
		echo get_imdb_connector_cache_path();
	}

	/**
	 * Returns an error/warning message and writes it into debug.log.
	 *
	 * @param        $message
	 * @param string $type
	 *
	 * @since 0.2
	 *
	 * @return bool
	 */
	function get_imdb_connector_debug_message($message, $type = "error") {
		if(get_imdb_connector_setting("debug_mode") != "on") {
			return false;
		}
		if($type == "error") {
			$type = __("ERROR", "imdb_connector");
		}
		elseif($type == "warning") {
			$type = __("WARNING", "imdb_connector");
		}
		$debug_file = get_imdb_connector_path() . "debug.log";
		if(!$handle = fopen($debug_file, "a+", false)) {
			return false;
		}
		$message_full = "[" . date("Y-m-d H:i:s") . "] " . $type . ": " . $message . "\n";
		fwrite($handle, $message_full);
		fclose($handle);

		return $message;
	}

	/**
	 * Displays an error/warning message and writes it into debug.log.
	 *
	 * @since 0.2
	 *
	 * @param        $message
	 * @param string $type
	 */
	function the_imdb_connector_debug_message($message, $type = "error") {
		echo get_imdb_connector_debug_message($message, $type);
	}

	/**
	 * Sanitizes the movie details.
	 *
	 * @since 0.3
	 *
	 * @param $movie_details
	 *
	 * @return array|mixed|string|void
	 */
	function imdb_connector_sanitize_movie_details($movie_details) {
		$sanitized_movie_details = array();
		$is_object               = false;
		/** Convert JSON to array */
		if(!is_array($movie_details)) {
			$is_object     = true;
			$movie_details = json_decode($movie_details, true);
		}
		foreach($movie_details as $movie_detail => $value) {
			/** Convert detail identifiers to lowercase */
			$movie_detail = strtolower($movie_detail);
			/** Rename fields that contain more than one value */
			if($movie_detail == "genre") {
				$movie_detail = "genres";
			}
			elseif($movie_detail == "director") {
				$movie_detail = "directors";
			}
			elseif($movie_detail == "country") {
				$movie_detail = "countries";
			}
			elseif($movie_detail == "writer") {
				$movie_detail = "writers";
			}
			elseif($movie_detail == "language") {
				$movie_detail = "languages";
			}
			/** Escape "dangerous" characters */
			/** Convert keys with multiple values into an array */
			$to_array = array(
				"genres",
				"directors",
				"countries",
				"writers",
				"actors",
				"languages"
			);
			/** Split multiple values into arrays */
			if(in_array($movie_detail, $to_array, false)) {
				$value = explode(", ", trim($value));
			}

			/** Format release date */
			if($movie_detail == "released" && phpversion() > 5.2) {
				$value = new DateTime($value);
				$value = $value->format("Y-m-d");
				/*	echo $value;
					$date  = date_create_from_format("Y-m-d", $value);
					$value = $date->format("Y-m-d");
				*/
			}

			/** Create runtime */
			if($movie_detail == "runtime") {
				$minutes   = preg_replace("'[^0-9]'", "", $value);
				$timestamp = mktime(0, $minutes);
				$value     = array(
					"timestamp" => $timestamp,
					"minutes"   => $minutes,
					"hours"     => date("G:i", $timestamp)
				);
			}
			/** Remove everything but numbers from imdbvotes */
			if($movie_detail == "imdbvotes") {
				$value = preg_replace("'[^0-9]'", "", $value);
			}
			$sanitized_movie_details[$movie_detail] = $value;
		}
		$movie_details = $sanitized_movie_details;
		/** Convert array back to JSON */
		if($is_object) {
			$movie_details = json_encode($movie_details);
		}

		return $movie_details;
	}

	/**
	 * Deletes the cache generated by IMDb Connector.
	 *
	 * @param string $cache_location
	 *
	 * @since 0.4
	 *
	 * @return bool
	 */
	function delete_imdb_connector_cache($cache_location = "all") {
		$success = false;
		/** Stop script if cache location has not been defined */
		if(!$cache_location) {
			return false;
		}
		/** Delete local cache */
		if($cache_location == "all" || $cache_location == "local") {
			$movie_details = glob(get_imdb_connector_cache_path() . "/*.tmp");
			$posters       = glob(get_imdb_connector_cache_path() . "/*.jpg");
			$files         = array_merge($movie_details, $posters);
			foreach($files as $file) {
				if(is_writable($file) && unlink($file)) {
					$success = true;
				}
			}
		}
		/** Delete database cache */
		if($cache_location == "all" || $cache_location == "database") {
			global $wpdb;
			$table = $wpdb->prefix . get_imdb_connector_setting("database_table");
			if($wpdb->query("TRUNCATE $table")) {
				$success = true;
			}
		}

		return $success;
	}

	/**
	 * Retrieves all movies cached by IMDb Connector.
	 *
	 * @param string $cache_location
	 * @param string $type
	 *
	 * @since 0.4
	 *
	 * @return array|mixed
	 */
	function get_imdb_connector_cached_movies($cache_location = "all", $type = "array") {
		$movies = array();
		if($cache_location == "all" || $cache_location == "local") {
			foreach(glob(get_imdb_connector_cache_path() . "/*.tmp") as $file) {
				$movie = json_decode(file_get_contents($file), true);
				array_push($movies, $movie);
			}
		}
		if($cache_location == "all" || $cache_location == "database") {
			global $wpdb;
			$table           = $wpdb->prefix . get_imdb_connector_setting("database_table");
			$selected_movies = $wpdb->get_results("SELECT * FROM $table", "ARRAY_A");
			if(!count($selected_movies)) {
				return $movies;
			}

			foreach((array)$selected_movies as $movie_details) {
				$movie = array();
				foreach($movie_details as $movie_detail => $value) {
					if(is_serialized($value)) {
						$value = unserialize($value);
					}
					$movie[$movie_detail] = $value;
				}
			}
			array_push($movies, $movie);
		}
		/** Convert array to stdClass object if set */
		if($type == "object") {
			$movies = json_decode(json_encode($movies));
		}

		return $movies;
	}