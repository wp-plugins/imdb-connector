=== IMDb Connector ===
Contributors: thaikolja
Tags: imdb, imdb connector, imdb database, movie, movies, movie details, movie database
Tested up to: 4.0
Stable tag: 0.1
Requires at least: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin that allows you to easily get movie details from IMDb.com.

== Description ==

**IMDb Connector** is a simple plugin that allows you to easily access the [IMDb.com](http://ww.imdb.com) database through the [API provided by omdbapi.com](http://www.omdbapi.com) and get details for a specific movie.

So far, the plugin comes with the following features:

* [**Widgets**](http://codex.wordpress.org/Widgets_API) that lets you display the movie details within your sidebar,
* **PHP functions** that allows theme/plugin developers to easily parse information for a specific movie,
* [**Shortcodes**](http://codex.wordpress.org/Shortcode_API) which you can use to display one or more details about a movie inside your post or page,
* and a **settings page** that lets you (de)activate features and customize the way IMDb Connector works.

**For instructions on how to use, examples and additional information, please see [the official documentation](http://www.koljanolte.com/wordpress/plugins/imdb-connector/)**.

In case you like this plugin, please consider [making a donation to Brian Fritz](http://www.omdbapi.com/), the author of omdbapi.com IMDb Connector is based on.

If you have any other ideas for features, please don't hesitate to submit them by [sending me an e-mail](mailto:kolja.nolte@gmail.com) and I'll try my best to implement it in the next version. Your WP.org username will be added to the plugin's contributor list, of course (if you provide one).

*Feel free to make IMDb Connector easier to use for foreign users by [help translating the plugin on Transifex](https://www.transifex.com/projects/p/plugin-imdb-connector/)*.

== Installation ==

1. Install IMDb Connector either through WordPress' native plugin installer found under *Plugins > Install* or copy the *imdb-connector* folder into the */wp-content/plugins/* directory of your WordPress installation.
2. Make sure the folder */cache/* in the plugin's directory is writable (CHMOD 755).
3. Activate the plugin in the plugin section of your admin interface.
4. Go to *Settings > IMDb Connector* to customize the plugin as desired.

**Please see the [official documentary](http://www.koljanolte.com/wordpress/plugins/imdb-connector/) for information and examples related to IMDb Connector.**

== Frequently Asked Questions ==

**The full FAQ can be found on the [official website](http://www.koljanolte.com/wordpress/plugins/imdb-connector/#FAQ).**

== Screenshots ==

1. The plugin's settings page.
2. The standard widget displayed in a sidebar.
3. The widget configuration on the admin interface.

== Changelog ==

= 0.4.1 =
* Fixed shortcode movie details with multiple values in it.

= 0.4 =
* MySQL cache is now stored in a separate table.
* Added feature to select the table name the cache data is being stored.
* Added feature to automatically delete the cache after a certain time.
* Added feature allowing admins to chose what cached files and settings IMDb Connector should keep after disabling the plugin.
* Added "type" movie detail that returns the type (documentary, series, movie, ...) of the movie.
* Renamed movie details "genre", "country", "language", "writer" and "director" to plural names.
* Updated translations.

= 0.3 =
* Added option to chose if the movie detail cache should be stored locally on in MySQL.
* Added an option to the settings page that defines whether the movie poster should be cached or not.
* Added "format" option array to get_imdb_connector_movie() function that defines whether the output should be an "array" or "object".
* Added translations and updated existing ones.
* The movie details "genre", "director", "writer", "actors", "country" and "language" are split up in arrays.
* The movie detail "runtime" is now an array containing "timestamp", "minutes" and "hours".
* Removed "Use default widgets style" from settings page.

= 0.2 =
* Added "Delete cache" function on settings page.
* Added several PHP functions, e.g. search_imdb_connector_movies().
* Added debug mode to display errors and warnings.
* Added several translations and updated existing ones.
* Fixed "headers already sent" bug on plugin activation.
* Fixed bug that prevented translations from being loaded.
* Fixed [bug](https://wordpress.org/support/topic/imdb-connector-dont-import-some-movies-informations) when a string run through `wptexturize()` is used for the IMDb title ([thanks to 7movies](https://wordpress.org/support/profile/7movies)).
* Changed `get_imdb_*` functions to `get_imdb_connector_*` to avoid conflicts with other plugins.
* Updated documentation.
* Rebuild movie widget.
* Restructured plugin files.

= 0.1.2 =
* Hotfix.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 0.4.1 =
Shortcode fix.

= 0.4 =
Major update with many new functions (auto delete, MySQL caching, deactivation actions), bug fixes and corrections.

= 0.3 =
**IMPORTANT:** The array key names have been renamed and partly reformatted. Please see "PHP functions" section in the [official documentation](http://www.koljanolte.com/wordpress/plugins/imdb-connector/#PHP functions) for the new structure.

= 0.2 =
Major update with many bug fixes and new features and functions. See changelog for more information.

= 0.1.2 =
Implemented hotfix.

= 0.1 =
This is the first release of IMDb Connector.