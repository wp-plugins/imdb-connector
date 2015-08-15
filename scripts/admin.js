jQuery(document).ready(
	function() {
		"use strict";

		/** Only apply scripts if we're on the IMDb Connector settings page */
		if(jQuery("#imdb-connector-settings").length < 1) {
			return false;
		}

		jQuery("#submit-link").click(
			function() {
				jQuery(this).parents("form").submit();
				return false;
			}
		);

		var updatedSelector;

		updatedSelector = jQuery(".updated");

		if(updatedSelector.length >= 1) {
			setTimeout(
				function() {
					updatedSelector.fadeOut("slow");
				}, 5000
			);
		}

		/**
		 * "Delete cache" function
		 */
		jQuery("#delete-cache").click(
			function() {
				var fileUrl, button, loadingIcon, messages;

				fileUrl = jQuery("#remote-actions-url").attr("value");
				button = jQuery(this);
				loadingIcon = jQuery("#delete-cache-loading-icon");
				messages = jQuery("#delete-cache-container").find(".message").fadeOut();

				button.hide();
				messages.hide();
				loadingIcon.fadeIn();

				jQuery.ajax(
					{
						type: "get", url: fileUrl + "?action=delete_cache" + "&nonce=" + jQuery("#delete_cache_nonce").attr("value"), success: function(response) {
						button.show();
						loadingIcon.hide();
						jQuery("#delete-cache-container").find(".message.success").fadeIn();
						jQuery("#deleted-files-number").text(response);
					}
					}
				);
				setTimeout(
					function() {
						jQuery("#delete-cache-container").find(".message").fadeOut();
					}, 10000
				);
			}
		);

		/**
		 * "Show shortcodes" function
		 */
		jQuery("#show-shortcode-examples").click(
			function() {
				jQuery("#shortcode-examples").slideToggle();
				return false;
			}
		);

		/**
		 * Widget configuration
		 */
		jQuery(".show-poster input").click(
			function() {
				var showPoster, posterOptions;

				showPoster = jQuery(this);
				posterOptions = jQuery(".poster-options");
				if(showPoster.attr("checked") === "checked") {
					posterOptions.slideDown();
				} else {
					posterOptions.slideUp();
				}
			}
		);
		jQuery(".show-movie-title input").click(
			function() {
				var showMovieTitle, movieTitlePosition;

				showMovieTitle = jQuery(this);
				movieTitlePosition = jQuery(".movie-title-position");
				if(showMovieTitle.attr("checked") === "checked") {
					movieTitlePosition.slideDown();
				} else {
					movieTitlePosition.slideUp();
				}
			}
		);
		jQuery(".poster-target select").change(
			function() {
				var selectedOption, customUrl;

				selectedOption = jQuery(this).find("option:selected").attr("value");
				customUrl = jQuery(".poster-target-custom-url");
				customUrl.hide();
				if(selectedOption === "custom") {
					customUrl.slideDown();
				}
			}
		);
		return true;
	}
);