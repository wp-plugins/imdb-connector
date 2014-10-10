jQuery(document).ready(function () {
	/**
	 * "Delete cache" function
	 */
	jQuery("#delete-cache").click(function () {
		var file_url = jQuery("#remote-actions-url").attr("value");
		var button = jQuery(this);
		var loading_icon = jQuery("#delete-cache-loading-icon");
		var messages = jQuery("#delete-cache-container").find(".message").fadeOut();

		button.hide();
		messages.hide();
		loading_icon.fadeIn();

		jQuery.ajax({
			type: "get", url: file_url + "?action=delete_cache", success: function (response) {
				button.show();
				loading_icon.hide();
				jQuery("#delete-cache-container").find(".message.success").fadeIn();
				jQuery("#deleted-files-number").text(response);
			}
		});
		setTimeout(function () {
			jQuery("#delete-cache-container").find(".message").fadeOut();
		}, 10000);
	});

	/**
	 * "Show shortcodes" function
	 */
	jQuery("#show-shortcode-examples").click(function () {
		jQuery("#shortcode-examples").slideToggle();
		return false;
	});

	/**
	 * Widget configuration
	 */
	jQuery(".show-poster input").click(function () {
		var show_poster = jQuery(this);
		var poster_options = jQuery(".poster-options");
		if(show_poster.attr("checked") == "checked") {
			poster_options.slideDown();
		}
		else {
			poster_options.slideUp();
		}
	});
	jQuery(".show-movie-title input").click(function () {
		var show_movie_title = jQuery(this);
		var movie_title_position = jQuery(".movie-title-position");
		if(show_movie_title.attr("checked") == "checked") {
			movie_title_position.slideDown();
		}
		else {
			movie_title_position.slideUp();
		}
	});
	jQuery(".poster-target select").change(function () {
		var selected_option = jQuery(this).find("option:selected").attr("value");
		var custom_url = jQuery(".poster-target-custom-url");
		custom_url.hide();
		if(selected_option == "custom") {
			custom_url.slideDown();
		}
	});
});