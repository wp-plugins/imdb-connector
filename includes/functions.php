<?php
	/**
	 * Kolja Nolte
	 * kolja.nolte@gmail.com
	 * http://www.koljanolte.com
	 * Created on 2015-08-17 02:18 UTC+7
	 */

	/** Prevents this file from being called directly */
	if(!function_exists("add_action")) {
		return;
	}

	/**
	 * Returns the URL to the plugin directory.
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	function imdb_connector_get_url() {
		return plugin_dir_url(dirname(__FILE__));
	}

	/**
	 * Displays the URL to the plugin directory.
	 *
	 * @since 0.2
	 */
	function imdb_connector_the_url() {
		echo imdb_connector_get_url();
	}

	/**
	 * Returns the absolute path to the plugin directory.
	 *
	 * @return string
	 *
	 * @since 0.2
	 */
	function imdb_connector_get_path() {
		return plugin_dir_path(dirname(__FILE__));
	}

	/**
	 * Displays the absolute path to the plugin directory.
	 *
	 * @since 0.2
	 */
	function imdb_connector_the_path() {
		echo imdb_connector_get_cache_path();
	}

	/**
	 * Returns the absolute path to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 *
	 * @return mixed
	 */
	function imdb_connector_get_cache_path() {
		$path = str_replace("\\", "/", plugin_dir_path(dirname(__FILE__))) . "cache";

		return (string)$path;
	}

	/**
	 * Displays the absolute path to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 */
	function imdb_connector_the_cache_path() {
		echo imdb_connector_get_cache_path();
	}

	/**
	 * Returns the URL to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	function imdb_connector_get_cache_url() {
		$cache_url = plugin_dir_url(dirname(__FILE__)) . "cache";

		return $cache_url;
	}

	/**
	 * Displays the URL to IMDb Connector's cache directory.
	 *
	 * @since 0.2
	 */
	function imdb_connector_the_cache_url() {
		echo imdb_connector_get_cache_url();
	}

	/**
	 * @param array $user_options
	 * @param array $default_options
	 * @param bool  $allow_empty
	 *
	 * @return array
	 */
	function imdb_connector_merge_options(array $user_options = array(), array $default_options = array(), $allow_empty = true) {
		$merged_options = array();
		foreach($default_options as $default_option_name => $default_option_value) {
			/** Check if the user has set the current option  */
			if(isset($user_options[$default_option_name])) {
				$user_option_value = $user_options[$default_option_name];
				/** Use default option value if $allow_empty is off */
				$merged_option_value = $user_option_value;
				if(!$allow_empty && !$user_option_value) {
					$merged_option_value = $default_option_value;
				}
			}
			/** Use default option value if user option is not set  */
			else {
				if(!isset($default_option_value)) {
					$default_option_value = "";
				}
				$merged_option_value = $default_option_value;
			}
			/** Add current option and its value to the output array */
			$merged_options[$default_option_name] = $merged_option_value;
		}

		return $merged_options;
	}

	/**
	 * @param array $attributes
	 * @param bool  $allow_empty
	 *
	 * @return string
	 */
	function imdb_connector_get_html_attributes(array &$attributes, $allow_empty = false) {
		$html_attributes = "";

		foreach((array)$attributes as $attribute => $value) {
			if(!$allow_empty && !$value) {
				continue;
			}
			$html_attributes .= " " . $attribute . '="' . $value . '"';
		}

		return $html_attributes;
	}

	/**
	 * Sanitizes the movie details.
	 *
	 * @since 0.3
	 *
	 * @param $movie_details
	 *
	 * @return array
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
			if($movie_detail == "released" && $value != "N/A" && phpversion() > 5.2) {
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
				$timestamp = 0;
				if($value != "N/A") {
					$timestamp = mktime(0, $minutes);
				}
				$value = array(
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

		return (array)$movie_details;
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
	function imdb_connector_delete_cache($cache_location = "all") {
		$success = false;
		/** Stop script if cache location has not been defined */
		if(!$cache_location) {
			return false;
		}
		/** Delete local cache */
		if($cache_location == "all" || $cache_location == "local") {
			$cache_path    = imdb_connector_get_cache_path();
			$movie_details = glob("$cache_path/*.tmp");
			$posters       = glob("$cache_path/*.jpg");
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
			$table = $wpdb->prefix . imdb_connector_get_setting("database_table");
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
	function imdb_connector_get_cached_movies($cache_location = "all", $type = "array") {
		$movies = array();
		$movie  = "";
		if($cache_location == "all" || $cache_location == "local") {
			foreach(glob(imdb_connector_get_cache_path() . "/*.tmp") as $file) {
				$movie    = json_decode(file_get_contents($file), true);
				$movies[] = $movie;
			}
		}
		if($cache_location == "all" || $cache_location == "database") {
			global $wpdb;
			$table           = $wpdb->prefix . imdb_connector_get_setting("database_table");
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
			$movies[] = $movie;
		}
		/** Convert array to stdClass object if set */
		if($type == "object") {
			$movies = json_decode(json_encode($movies));
		}

		return $movies;
	}

	/**
	 * @param $json
	 *
	 * @since 1.3.1
	 *
	 * @return mixed
	 */
	function imdb_connector_fix_json_bug($json) {
		return str_replace('"movie,', '"movie",', $json);
	}

	/**
	 * @param           $api_url
	 * @param bool|true $decode
	 *
	 * @param string    $type
	 *
	 * @since 1.3.1
	 *
	 * @return array|bool
	 */
	function imdb_connector_process_json($api_url, $decode = true, $type = "array") {
		$data = wp_remote_get($api_url);
		if(is_wp_error($data)) {
			return false;
		}

		$array = true;
		if($type !== "array") {
			$array = false;
		}

		$data = $data["body"];
		$data = str_replace('"movie,', '"movie",', $data);
		if($decode) {
			$data = json_decode($data, $array);
			$data = imdb_connector_sanitize_movie_details($data);
		}

		return (array)$data;
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
	 * Returns a movie including all details provided by
	 * the unofficial API at omdbapi.com.
	 *
	 * @param       $id_or_title
	 * @param array $options
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	function imdb_connector_get_movie($id_or_title, array $options = array()) {
		/** Define default function options */
		$default_options = array(
			"format"         => "array",
			"allow_caching"  => imdb_connector_get_setting("allow_caching"),
			"cache_location" => imdb_connector_get_setting("cache_location")
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

		$movie_details = (array)array();
		$found         = true;

		/** When caching feature has been activated */
		if(imdb_connector_get_setting("allow_caching") !== "off") {
			$file_name            = substr(md5($id_or_title), 0, 8);
			$cache_directory_path = imdb_connector_get_cache_path();
			$cache_directory_url  = imdb_connector_get_cache_url();
			$cache_file_path      = $cache_directory_path . "/" . $file_name . ".tmp";

			if($options["cache_location"] == "local") {
				/** Display error message if the directory doesn't exist and can't be created automatically */
				if(!is_dir($cache_directory_path) && !mkdir($cache_directory_path)) {
					return false;
				}
				/** Display error message if the directory exists but isn't writable */
				elseif(!is_writable($cache_directory_path)) {
					return false;
				}
				/** Get details from cached file if it exists */
				if(file_exists($cache_file_path)) {
					$handle        = fopen($cache_file_path, "r");
					$data          = fread($handle, 9999);
					$movie_details = json_decode($data, true);
				}
				/** Get movie details online and create cache file */
				else {
					$handle = fopen($cache_file_path, "a");
					$data   = wp_remote_get($api_url);
					if(is_wp_error($data)) {
						return false;
					}

					$data          = $data["body"];
					$data          = imdb_connector_fix_json_bug($data);
					$movie_details = json_decode($data, true);
					$movie_details = imdb_connector_sanitize_movie_details($movie_details);

					fwrite($handle, json_encode($movie_details));
					fclose($handle);
				}
			}
			elseif($options["cache_location"] == "database") {
				global $wpdb;
				$table = $wpdb->prefix . imdb_connector_get_setting("database_table");
				imdb_connector_install(false, false);
				$query = "SELECT * FROM $table ";
				if($type == "i") {
					$query .= "WHERE imdbid = '" . $id_or_title . "'";
				}
				else {
					$query .= "WHERE title = '$id_or_title'";
				}
				$movie_details = (array)$wpdb->get_row($query, "ARRAY_A");
				/** Read row and convert serialized strings back to array */
				if($movie_details) {
					foreach($movie_details as $movie_detail => $value) {
						if(is_serialized($value)) {
							$movie_details[$movie_detail] = unserialize($value);
						}
					}
				}
				/** Movie doesn't exist in the database, so we add it */
				elseif(imdb_connector_get_setting("create_database_table") == "on") {
					$movie_details = imdb_connector_process_json($api_url);
					if(!is_array($movie_details)) {
						return false;
					}

					$data = array(
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
						$formats[] = $format;
					}

					$wpdb->insert($table, $data, $formats);
				}
			}
			/** Create movie poster if it doesn't exist yet */
			$poster_path = $cache_directory_path . "/" . $file_name . ".jpg";
			if(imdb_connector_get_setting("allow_caching") != "on_no_poster") {
				if(array_key_exists("title", $movie_details)) {
					$movie_details = (array)$movie_details;
					if($movie_details["poster"] != "N/A" && !file_exists($poster_path)) {
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
			/** Fetch JSON data */
			$data = wp_remote_get($api_url);

			/** Stop if downloading JSON fails */
			if(is_wp_error($data)) {
				return false;
			}

			/** Specify JSON data and turn it into a proper movie details array */
			$data          = $data["body"];
			$data          = imdb_connector_fix_json_bug($data);
			$movie_details = json_decode($data, true);
			$movie_details = imdb_connector_sanitize_movie_details($movie_details);

			/** Quick check if the array contains the necessary keys */
			if(!array_key_exists("title", $movie_details)) {
				$found = false;
			}
		}

		/** Display error message in case the movie could not be found */
		if(!$found) {
			return false;
		}

		/** Convert movie details into object if set */
		if($options["format"] == "object") {
			$movie_details = json_decode(json_encode($movie_details));
		}

		return $movie_details;
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
	function imdb_connector_search_movie($id_or_title) {
		$api_url = "http://www.omdbapi.com/?s=" . imdb_connector_sanitize_url_title($id_or_title);
		$results = file_get_contents($api_url);
		$results = (array)json_decode($results, true);
		if(array_key_exists("Response", $results) && $results["Response"] == "False") {
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
	function imdb_connector_search_movies(array $ids_or_titles) {
		$results = array();
		foreach($ids_or_titles as $id_or_title) {
			$result = imdb_connector_search_movie($id_or_title);
			if(!$result) {
				continue;
			}
			$results[] = $result;
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
	function imdb_connector_has_movie($id_or_title) {
		if(!imdb_connector_get_movie($id_or_title)) {
			return false;
		}

		return (boolean)true;
	}

	/**
	 * @param array $titles_or_ids
	 *
	 * @since 0.2
	 *
	 * @return array|bool
	 */
	function imdb_connector_get_movies(array $titles_or_ids) {
		$movies    = array();
		$not_found = array();
		foreach($titles_or_ids as $title_or_id) {
			$movie = imdb_connector_get_movie($title_or_id);
			if(!$movie) {
				$not_found[] = $title_or_id;
				continue;
			}
			$movies[] = $movie;
		}
		/** Display error message if one or more movies could not be found */
		if(count($not_found) >= 1) {
			echo " " . implode(", ", $not_found);
		}

		return (array)$movies;
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
	function imdb_connector_get_movie_detail($id_or_title, $detail) {
		$movie = imdb_connector_get_movie($id_or_title);
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
			return false;
		}

		return $movie[$detail];
	}

	/**
	 * @param $attributes
	 *
	 * @return string
	 */
	function imdb_connector_shortcode_movie_detail($attributes) {
		if(!isset($attributes["title"], $attributes["detail"])) {
			return "";
		}

		$attribute_title  = $attributes["title"];
		$attribute_detail = $attributes["detail"];

		$movie_details = imdb_connector_get_movie($attribute_title);
		if(!$movie_details) {
			return "";
		}

		$output = "";

		if($attribute_detail === "poster_image") {
			$img_default_attributes = array(
				"src"    => $movie_details["poster"],
				"width"  => 0,
				"height" => 0,
				"alt"    => "",
				"class"  => ""
			);

			$img_attributes = imdb_connector_merge_options($attributes, $img_default_attributes);
			$img_attributes = imdb_connector_get_html_attributes($img_attributes);
			$img            = "<img $img_attributes />";

			$output = $img;

			if(isset($attributes["href"]) || (isset($attributes["linked"]) && $attributes["linked"] === "true")) {
				$a_default_attributes = array(
					"href"   => "http://www.imdb.com/title/" . $movie_details["imdbid"] . "/",
					"target" => "_blank"
				);

				$a_attributes = imdb_connector_merge_options($attributes, $a_default_attributes, false);
				$a_attributes = imdb_connector_get_html_attributes($a_attributes);
				$output       = "<a $a_attributes>$output</a>";
			}
		}
		elseif(strstr($attribute_detail, "runtime")) {
			if($attribute_detail === "runtime") {
				$output = $movie_details["runtime"]["hours"];
				if(isset($attributes["format"])) {
					$output = date($attributes["format"], $movie_details["runtime"]["timestamp"]);
				}
			}
			elseif($attribute_detail === "runtime-minutes") {
				$output = $movie_details["runtime"]["minutes"];
			}
		}
		elseif(array_key_exists($attribute_detail, $movie_details)) {
			$output = $movie_details[$attribute_detail];
			if(is_array($output)) {
				$output = implode(", ", $output);
			}
		}

		return (string)$output;
	}

