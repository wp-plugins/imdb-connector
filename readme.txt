=== IMDb Connector ===
Contributors: thaikolja
Tags: imdb, imdb connector, imdb database, movie, movies, movie details, movie database
Tested up to: 3.8.1
Stable tag: 0.1
Requires at least: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A neat plugin that allows you to easily get movie details from IMDb.com.

== Description ==

**IMDb Connector** is a plugin that allows you to easily access the [IMDb.com](http://ww.imdb.com) database through the [API provided by omdbapi.com](http://www.omdbapi.com) and get details for a specific movie (for a full list of the returned details, please see [Installation > PHP functions](http://wordpress.org/plugins/imdb-connector/installation/)).

So far, the plugin comes with the following features:

* [**Widgets**](http://codex.wordpress.org/Widgets_API) that lets you display the movie details within your sidebar,
* **PHP functions** that allows theme/plugin developers to easily parse information for a specific movie,
* [**Shortcodes**](http://codex.wordpress.org/Shortcode_API) which you can use to display one or more details about a movie inside your post or page,
* and a **settings page** that lets you (de)activate features and customize the way IMDb Connector works.

*For instructions on how to use, please see [Installation](http://wordpress.org/plugins/imdb-connector/installation/)*.

If you have any other ideas for features, please don't hesitate to submit them by [sending me an e-mail](mailto:kolja.nolte@gmail.com) and I'll try my best to implement it in the next version. Your WP.org username will be added to the plugin's contributor list, of course.

*Feel free to make IMDb Connector easier to use for foreign users by [help translating the plugin on Transifex](https://www.transifex.com/projects/p/plugin-imdb-connector/)*.

**Please note that this is an early version, there still may be bugs. If you encounter any problems or misbehaviors while using IMDb Connector, please take a minute to report it via mail to kolja.nolte@gmail.com so that the fix can be implemented in the next version.**

== Installation ==

For more detailed instructions and additional information, please see the official documentary on [www.koljanolte.com/wordpress/plugins/imdb-connector/](http://www.koljanolte.com/wordpress/plugins/imdb-connector/).

= Installation =

1. Install IMDb Connector either through WordPress' native plugin installer (Plugins > Install) or copy the "imdb-connector" folder into your /wp-content/plugins/ directory.
2. Activate the plugin in the plugin section of your admin interface.
3. Go to Settings > IMDb Connector to customize the plugin as desired.

= Usage =
IMDb Connector can be used in several different ways:

* as **widgets**,
* as **PHP functions** and
* as **shortcodes**.

= Widgets =

The plugin comes with a standard widget that displays one or more information about a specific movie inside a sidebar.

To use the widget, go to *Settings* > *Appearance* > *Widgets* and drag and drop the widget *IMDb movie* into the desired sidebar.

Open the widget settings and customize it according to your needs. You can change:

* **Widget title:** The title of the widget that is displayed on top of the widget.
* **Movie title:** The title of the movie whose information should be displayed.
* **Show cover:** Defines whether to embed the movie's cover (if available).
* **Cover width and height:** The desired dimensions of the cover separated by an *x*, e.g. 300x100.
* **Cover link:** Turns the cover into a hyperlink to the set location.
* **Show title:** Whether the movie title should be shown or not.
* **Title position:** The position the title is going to be displayed, either above or below the cover.
* **Show plot:** Displays either a short plot, a long plot or none of them.
* **Bottom text:** A custom text that is being displayed at the bottom of the widget.

= PHP functions =
The plugin provides the following PHP functions that let you easily get information from the IMDb database:

**get_imdb_movie($imdb_movie_id_or_title):** *(array)* Returns the following information for a specific movie:

* title (full movie title)
* year (production year)
* rated (rating according to the [Motion Picture Association of America](http://en.wikipedia.org/wiki/Motion_Picture_Association_of_America)
* released (release year)
* runtime (in minutes)
* genre
* director (one name)
* writer (one name)
* actors (multiple names)
* plot (short plot)
* language (original language)
* country (production country)
* awards
* poster (cover URL)
* metascore
* imdbrating (rating on IMDb e.g. 7.2)
* imdbvotes (number of votes for that movie on IMDb)
* imdbid (official movie ID on IMDb)

**get_imdb_movies($movie_title):** *([array](http://codex.wordpress.org/Glossary#Array))* Returns all movies and their details that contain the $movie_title.

**has_imdb_movie($imdb_movie_id_or_title):** *([bool](http://codex.wordpress.org/Glossary#Boolean))* Checks whether a movie can be found with the set IMDb movie ID or title.

**get_imdb_movie_detail($imdb_movie_id_or_title, $detail):** *([string](http://codex.wordpress.org/Glossary#String))* Returns a specific detail (e.g. actors or genre) of the set movie.

**Examples:**

Displays a text with the movie details:
`<?php
	$movie = get_imdb_movie("Apocalypse Now");
	echo $movie["title"] . " was released in " . $movie["year"] . " and is a " . $movie["genre"] . " movie.";
?>`

List all movies with "Island" in their title and some of their details in a table:
`<?php
	/** Get the movies */
	$movies = get_imdb_movies("Island");
	/** Start building the table */
	echo "<table>";
	echo "<tbody>";
	echo "<tr>";
	echo "<td>Title</td>";
	echo "<td>Year</td>";
	echo "<td>Genre</td>";
	echo "<td>Plot</td>";
	echo "</tr>";
	/** Insert a row for each found movie */
	foreach($movies as $movie) {
		echo "<tr>";
		echo "<td>" . $movie["title"] . "</td>";
		echo "<td>" . $movie["year"] . "</td>";
		echo "<td>" . $movie["genre"] . "</td>";
		echo "<td>" . $movie["plot"] . "</td>";
		echo "</tr>";
	}
	/** Close the table */
	echo "</tbody>";
	echo "</table>";
?>`

Check if IMDb has a the movie that has been submitted through a form and display its year:
`<?php
	$movie_title = $_GET["movie_title"];
	if(has_imdb_movie($movie_title)) {
		echo $movie_title . " was released in " . get_imdb_movie_detail($movie_title, "year");
	}
?>`

= Shortcodes =
IMDb Connector allows you use shortcodes to display movie details within your post or page by using `[imdb_movie_detail title="" detail=""]` whereas *title* is the title of the targeted movie and *detail* the movie detail that is supposed to be displayed.

= Change the settings =
You can customize the way IMDb Connector works by going to *Settings > IMDb Connector* within the admin area of your WordPress installation.

== Frequently Asked Questions ==

= The plugin is not caching the movie details =
Please check whether the option *Caching* within *Settings > IMDb Connector* is set to *On*.

Also make sure that the folder *cache* in the plugin directory is writable ([CHMOD 777](http://en.wikipedia.org/wiki/Chmod)).

== Screenshots ==

1. The standard widget displayed in a sidebar.

== Changelog ==

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 0.1 =
This is the first release of IMDb Connector.