<?php

	/**
	 * Global function to define all setting fields and their defaults.
	 *
	 * @return array
	 */
	function get_widget_imdb_movie_default_fields() {
		$fields = array(
			"title"          => __("IMDB movie", "imdb_connector"),
			"movie_title"    => "Apocalypse Now",
			"cover_size"     => "",
			"show_plot"      => "short_plot",
			"show_title"     => "off",
			"show_cover"     => "off",
			"title_position" => "above",
			"cover_link"     => "",
			"bottom_text"    => "",
		);
		return $fields;
	}

	/**
	 * Class widget_imdb_movie
	 */
	class widget_imdb_movie extends WP_Widget {
		function widget_imdb_movie() {
			parent::__construct("imdb_movie", __("IMDB movie", "imdb_connector"), array("description" => __("Displays a specific movie and its details.", "imdb_connector")));
		}

		/**
		 * @param array $sidebar_info
		 * @param array $options
		 *
		 * @return bool|void
		 */
		function widget($sidebar_info, $options) {
			/** Pre-defining variables to make WP_DEBUG (and PhpStorm) happy */
			$movie_title    = "";
			$cover_size     = "";
			$show_plot      = "";
			$show_title     = "";
			$show_cover     = "";
			$title_position = "";
			$cover_link     = "";
			$bottom_text    = "";
			/** Define default options */
			$default_options = get_widget_imdb_movie_default_fields();
			/** Apply default options if option is empty */
			$options = set_default_widget_options($options, $default_options);
			extract($options);
			/** Store the cover dimensions in an array */
			if(explode("x", $cover_size)) {
				$cover_size = explode("x", $cover_size);
			}
			$movie_slug = sanitize_title($movie_title);
			/** Cancel script if the movie is not found */
			if(!has_imdb_movie($movie_title)) {
				return false;
			}
			/** Build widget */
			echo $sidebar_info["before_widget"];
			echo $sidebar_info["before_title"];
			echo $options["title"];
			echo $sidebar_info["after_title"];
			if($show_title == "on" && $title_position == "above") {
				echo '<h2 id="movie-title-' . $movie_slug . '" class="movie-title">' . get_imdb_movie_detail($movie_title, "title") . '</h2>';
			}
			if($show_cover == "on") {
				if(!empty($cover_link)) {
					echo '<a href="' . $cover_link . '" id="movie-link-' . $movie_slug . '">';
				}
				$cover_sizes = "";
				if(!empty($cover_size) && isset($cover_size[0]) && isset($cover_size[1])) {
					$cover_sizes = ' width="' . $cover_size[0] . '" height="' . $cover_size[1] . '"';
				}
				echo '<img src="' . get_imdb_movie_detail($movie_title, "poster") . '"' . $cover_sizes . ' class="cover" id="cover-' . $movie_slug . '" />';
				if(!empty($cover_link)) {
					echo '</a>';
				}
			}
			if($show_title == "on" && $title_position == "below") {
				echo '<h2>' . get_imdb_movie_detail($movie_title, "title") . '</h2>';
			}
			if($show_plot != "") {
				echo '<p class="plot" id="plot-' . $movie_slug . '">';
				if($show_plot == "short_plot") {
					echo get_imdb_movie_detail($movie_title, "plot");
				}
				echo "</p>";
			}
			if(!empty($bottom_text)) {
				echo '<p class="bottom-text" id="bottom-text-' . $movie_slug . '">' . $bottom_text . "</p>";
			}

			echo $sidebar_info["after_widget"];
			return true;
		}

		/**
		 * @param array $new_options
		 * @param array $old_options
		 *
		 * @return array
		 */
		function update($new_options, $old_options) {
			$output_options = array();
			/** Define existing options to be saved */
			$options = get_widget_imdb_movie_default_fields();
			/** Save each option */
			foreach($options as $option => $value) {
				$output_options[$option] = $new_options[$option];
			}
			return $output_options;
		}

		/**
		 * @param array $instance
		 *
		 * @return string|void
		 */
		function form($instance) {
			/** Define widget admin fields */
			$fields = array(
				array(
					"type"  => "text",
					"name"  => $this->get_field_name("title"),
					"id"    => $this->get_field_id("title"),
					"label" => __("Widget title", "imdb_connector"),
					"value" => $instance["title"]
				),
				array(
					"type"        => "text",
					"name"        => $this->get_field_name("movie_title"),
					"id"          => $this->get_field_id("movie_title"),
					"label"       => __("Movie title", "imdb_connector"),
					"value"       => $instance["movie_title"],
					"description" => __("The title of the movie that should be used.", "imdb_connector")
				),
				array(
					"type"    => "checkbox",
					"name"    => $this->get_field_name("show_cover"),
					"id"      => $this->get_field_id("show_cover"),
					"label"   => __("Show cover", "imdb_connector"),
					"value"   => "on",
					"checked" => $instance["show_cover"]
				),
				array(
					"type"        => "text",
					"name"        => $this->get_field_name("cover_size"),
					"id"          => $this->get_field_id("cover_size"),
					"label"       => __("Cover width and height", "imdb_connector"),
					"value"       => $instance["cover_size"],
					"description" => __("E.g. 100x150", "imdb_connector")
				),
				array(
					"type"  => "text",
					"name"  => $this->get_field_name("cover_link"),
					"id"    => $this->get_field_id("cover_link"),
					"label" => __("Cover link", "imdb_connector"),
					"value" => $instance["cover_link"],
				),
				array(
					"type"    => "checkbox",
					"name"    => $this->get_field_name("show_title"),
					"id"      => $this->get_field_id("show_title"),
					"label"   => __("Show title", "imdb_connector"),
					"value"   => "on",
					"checked" => $instance["show_title"]
				),
				array(
					"type"     => "select",
					"name"     => $this->get_field_name("title_position"),
					"id"       => $this->get_field_id("title_position"),
					"label"    => __("Title position", "imdb_connector"),
					"selected" => $instance["title_position"],
					"fields"   => array(
						array(
							"label" => __("Above the cover", "imdb_connector"),
							"value" => "above"
						),
						array(
							"label" => __("Below the cover", "imdb_connector"),
							"value" => "below"
						)
					)
				),
				array(
					"type"    => "radio",
					"name"    => $this->get_field_name("show_plot"),
					"id"      => $this->get_field_id("show_plot"),
					"label"   => __("Show plot", "imdb_connector"),
					"checked" => $instance["show_plot"],
					"fields"  => array(
						array(
							"label" => __("Short plot", "imdb_connector"),
							"value" => "short_plot",
							"id"    => $this->get_field_id("short_plot"),
						),
						array(
							"label" => __("Long plot", "imdb_connector"),
							"value" => "long_plot",
							"id"    => $this->get_field_id("long_plot"),
						),
						array(
							"label" => __("None", "imdb_connector"),
							"value" => "none",
							"id"    => $this->get_field_id("none"),
						),
					)
				),
				array(
					"type"  => "textarea",
					"name"  => $this->get_field_name("bottom_text"),
					"id"    => $this->get_field_id("bottom_text"),
					"value" => $instance["bottom_text"],
					"label" => __("Bottom text", "imdb_connector")
				)
			);
			/** Build widget admin fields */
			the_widget_form_fields($fields);
		}
	}

	function init_widget_imdb_movie() {
		register_widget("widget_imdb_movie");
	}

	add_action("widgets_init", "init_widget_imdb_movie");