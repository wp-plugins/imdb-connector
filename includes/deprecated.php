<?php
	/**
	 * Kolja Nolte
	 * kolja.nolte@gmail.com
	 * http://www.koljanolte.com
	 * Created on 2015-08-17 02:27 UTC+7
	 */

	/**
	 * @deprecated
	 */
	function get_imdb_connector_url() {
		_deprecated_function("get_imdb_connector_url", "1.3.2", "imdb_connector_get_url()");
	}

	/**
	 * @deprecated
	 */
	function the_imdb_connector_url() {
		_deprecated_function("the_imdb_connector_url", "1.3.2", "imdb_connector_the_url()");
	}

	/**
	 * @deprecated
	 */
	function get_imdb_connector_path() {
		_deprecated_function("get_imdb_connector_path", "1.3.2", "imdb_connector_get_path()");
	}

	/**
	 * @deprecated
	 */
	function the_imdb_connector_path() {
		_deprecated_function("the_imdb_connector_path", "1.3.2", "imdb_connector_the_path()");
	}

	/**
	 * @deprecated
	 */
	function get_imdb_connector_cache_path() {
		_deprecated_function("get_imdb_connector_cache_path", "1.3.2", "imdb_connector_get_cache_path()");
	}

	/**
	 * @deprecated
	 */
	function the_imdb_connector_cache_path() {
		_deprecated_function("the_imdb_connector_cache_path", "1.3.2", "imdb_connector_the_cache_path()");
	}

	/**
	 * @deprecated
	 */
	function the_imdb_connector_cache_url() {
		_deprecated_function("the_imdb_connector_cache_url", "1.3.2", "imdb_connector_the_cache_url()");
	}

	/**
	 * @deprecated
	 */
	function get_imdb_connector_cached_movies() {
		_deprecated_function("get_imdb_connector_cached_movies", "1.3.2", "imdb_connector_get_cached_movies(\$cache_location, \$type)");
	}

	/**
	 * @param string $cache_location
	 *
	 * @since 1.3.2
	 *
	 * @deprecated
	 */
	function delete_imdb_connector_cache($cache_location = "all") {
		_deprecated_function("delete_imdb_connector_cache", "1.3.2", "delete_imdb_connector_cache(\$cache_location)");
	}

	/**
	 * @param       $id_or_title
	 * @param array $options
	 *
	 * @deprecated
	 */
	function get_imdb_connector_movie($id_or_title, array $options = array()) {
		_deprecated_function("imdb_connector_get_movie", "1.3.2", "get_imdb_connector_movie(\$id_or_title, array \$options)");
	}

	/**
	 * @param $id_or_title
	 *
	 * @deprecated
	 */
	function search_imdb_connector_movie($id_or_title) {
		_deprecated_function("search_imdb_connector_movie", "1.3.2", "imdb_connector_search_movie(\$id_or_title)");
	}

	/**
	 * @param array $ids_or_titles
	 *
	 * @deprecated
	 */
	function search_imdb_connector_movies(array $ids_or_titles) {
		_deprecated_function("search_imdb_connector_movies", "1.3.2", "imdb_connector_search_movies(array \$ids_or_titles)");
	}

	/**
	 * @param $id_or_title
	 *
	 * @deprecated
	 */
	function has_imdb_connector_movie($id_or_title) {
		_deprecated_function("has_imdb_connector_movie", "1.3.2", "imdb_connector_has_movie(\$id_or_title)");
	}

	/**
	 * @param array $ids_or_titles
	 *
	 * @deprecated
	 */
	function get_imdb_connector_movies(array $ids_or_titles) {
		_deprecated_function("get_imdb_connector_movies", "1.3.2", "imdb_connector_get_movies(array \$ids_or_titles)");
	}

	/**
	 * @param $id_or_title
	 * @param $detail
	 */
	function get_imdb_connector_movie_detail($id_or_title, $detail) {
		_deprecated_function("get_imdb_connector_movies", "1.3.2", "imdb_connector_get_movie_detail(\$id_or_title)");
	}

	/**
	 * @deprecated
	 */
	function get_imdb_connector_default_settings() {
		_deprecated_function("get_imdb_connector_default_settings", "1.3.2", "imdb_connector_get_default_settings()");
	}

	/**
	 * @param $setting
	 *
	 * @deprecated
	 */
	function get_imdb_connector_default_setting($setting) {
		_deprecated_function("get_imdb_connector_default_setting", "1.3.2", "imdb_connector_get_default_setting(\$setting)");
	}

	/**
	 * @deprecated
	 */
	function get_imdb_connector_settings() {
		_deprecated_function("get_imdb_connector_settings", "1.3.2", "imdb_connector_get_settings()");
	}

	/**
	 * @param $setting
	 *
	 * @deprecated
	 */
	function get_imdb_connector_setting($setting) {
		_deprecated_function("get_imdb_connector_setting", "1.3.2", "imdb_connector_get_setting(\$setting)");
	}