<?php
	/**
	 * This ile stores the functions the plugin needs in order to
	 * work properly. Most functions are taken from the public WordPress
	 * library "klibrary" (http://www.koljanolte.com/wordpress/klibrary/).
	 * For user functions, please see imdb-connector.php.
	 */

	/**
	 * @param        $input
	 * @param string $case
	 *
	 * @return array
	 */
	function array_change_key_case_recursive($input, $case = "") {
		if(empty($case)) {
			$case = CASE_LOWER;
		}
		$input = array_change_key_case($input, $case);
		foreach($input as $key => $array) {
			if(is_array($array)) {
				$input[$key] = array_change_key_case_recursive($array, $case);
			}
		}
		return $input;
	}

	/**
	 * Displays all admin settings fields specified in $fields for widgets.
	 *
	 * This function simplifies and unifies the process of adding settings
	 * for a widget. It allows to create a clearly structured array that stores
	 * all the fields and their information which will automatically be displayed
	 * when viewing the widget settings, saving a lot of code that otherwise
	 * would be needed to write. Please see ... for an example array.
	 *
	 * @param array $fields
	 */
	function the_widget_form_fields($fields = array()) {
		/** Setup default options */
		$default_options = array(
			"name"        => "", // HTML name attribute and identifier of the field
			"id"          => "", // HTML ID attribute
			"class"       => "", // CSS class
			"type"        => "", // Type of the field
			"value"       => "", // HTML value attribute
			"placeholder" => "", // HTML placeholder attribute
			"description" => "", // Additional description
			"input_size"  => ""
		);
		/** Keep track of the rounds */
		$counter = 0;
		/** Take fields one by one */
		foreach($fields as $field) {
			/** Check if the field has a certain option; if not, create it */
			foreach($default_options as $option => $value) {
				if(!isset($field[$option])) {
					$fields[$counter][$option] = $value;
				}
				if(isset($field["type"])) {
					if($option == "type" && $field["type"] == "checkbox" || $field["type"] == "radio") {
						/** Keep track of the rounds */
						$subfield_counter = 0;
						if(isset($field["fields"])) {
							foreach($field["fields"] as $subfield) {
								if(!isset($subfield["name"])) {
									//$fields[$counter]["fields"][$subfield_counter]["name"] = $field["name"];
								}
								/** If checkbox ID is empty, use the sanitized name as ID */
								if(!isset($subfield["id"])) {
									$fields[$counter]["fields"][$subfield_counter]["id"] = sanitize_title_for_query($field["name"]);
								}
								$subfield_counter++;
							}
						}
					}
				}
			}
			$counter++;
		}
		foreach($fields as $field) {
			$value       = "";
			$id          = "";
			$name        = "";
			$placeholder = "";
			$class       = "";
			extract($field);
			if(!isset($type)) {
				$type = "text";
			}
			/** Start building up the settings fields */
			echo "<p>";
			/** Display the label */
			if(isset($field["label"]) && $type != "checkbox") {
				echo '<label for="' . $id . '">' . $field["label"] . ':</label> ';
			}
			/** Text type */
			if($type == "text") {
				if(empty($class)) {
					$class = "widefat";
				}
				echo '<input type="text" id="' . $id . '" class="' . $class . '" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value . '" />';
				if(!empty($description)) {
					echo '<br /><small>' . $description . '</small>';
				}
			}
			/** Checkbox type */
			elseif($type == "checkbox") {
				if(!isset($field["fields"])) {
					$checked = "";
					if($field["checked"] == $field["value"]) {
						$checked = ' checked="checked"';
					}
					echo '<input type="checkbox" class="checkbox" name="' . $field["name"] . '" id="' . $field["id"] . '" value="' . $field["value"] . '"' . $checked . ' /> ';
					echo '<label for="' . $field["id"] . '">' . $field["label"] . '</label><br />';
				}
				else {
					foreach($field["fields"] as $checkbox) {
						/** Define whether the checkbox should be checked or not */
						$checked = "";
						if(!empty($checkbox["checked"])) {
							if(is_array($checkbox["checked"])) {
								if(in_array($checkbox["value"], $checkbox["checked"])) {
									$checked = ' checked="checked"';
								}
							}
							elseif($checkbox["value"] == $checkbox["checked"]) {
								$checked = ' checked="checked"';
							}
						}
						/** If the checkbox doesn't have a name specified, use the field name instead */
						if(empty($checkbox["name"])) {
							$checkbox["name"] = $name;
						}
						/** Turn the result into an array if there're more than one checkboxes */
						$make_array = "";
						if(count($field["fields"]) > 1) {
							$make_array = "[]";
						}
						echo '<input type="checkbox" class="checkbox" name="' . $checkbox["name"] . $make_array . '" id="' . $checkbox["id"] . '" value="' . $checkbox["value"] . '"' . $checked . ' /> ';
						echo '<label for="' . $checkbox["id"] . '">' . $checkbox["label"] . '</label><br />';
					}
				}
			}
			/** Radio type */
			elseif($type == "radio") {
				foreach($fields as $option) {
					$default_options = array(
						"name"  => $field["name"],
						"label" => $option["value"],
						"value" => "",
						"id"    => "",
						"class" => "",
					);
					$option          = set_default_widget_options($option, $default_options);
					$checked         = "";
					if($field["checked"] == $option["value"]) {
						$checked = ' checked="checked"';
					}
					echo '<br /><input type="radio" name="' . $field["name"] . '" value="' . $option["value"] . '" id="' . $option["id"] . '" class="' . $option["class"] . '"' . $checked . ' />';
					echo ' <label for="' . $option["id"] . '">' . $option["label"] . '</label> ';
				}
			}
			/** Select type */
			elseif($type == "select") {
				echo '<select name="' . $name . '" class="' . $class . '" id="' . $id . '">';
				foreach($fields as $item) {
					$selected = "";
					if(isset($field["selected"]) && $field["selected"] == $item["value"]) {
						$selected = ' selected=""';
					}
					echo '<option value="' . $item["value"] . '"' . $selected . '>' . $item["label"] . '</option>';
				}
				echo '</select>';
			}
			/** Textarea type */
			elseif($type == "textarea") {
				echo '<br /><textarea name="' . $name . '" class="' . $class . ' widefat" id="' . $id . '" style="resize:vertical;">' . $value . '</textarea>';
			}
			/** date type */
			elseif($type == "date") {

			}
			echo "</p>";
		}
	}

	/**
	 * @param array $input_options
	 * @param array $default_options
	 *
	 * @return array
	 */
	function set_default_widget_options($input_options = array(), $default_options = array()) {
		foreach($default_options as $option => $value) {
			if(empty($input_options[$option])) {
				$input_options[$option] = $value;
			}
		}
		$output_options = $input_options;
		return $output_options;
	}

	/**
	 * Gets the filename of an URL.
	 *
	 * @param $url
	 *
	 * @return array
	 */
	function get_url_filename($url) {
		$url = explode("/", $url);
		$url = $url[count($url) - 1];
		return $url;
	}

?>