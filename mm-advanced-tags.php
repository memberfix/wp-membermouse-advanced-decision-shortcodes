<?php

/**
 * Plugin Name: MemberMouse Advanced Decision Shortcodes
 * Plugin URI: https://memberfix.rocks/
 * Description: MemberMouse advanced decision shortcodes based on user's membership or associated bundles with options for current access, future access or forbidden access. The shortcodes content is displayed considering either the date when user subscribed or the number of days as member.
 * Version: 1.0.0
 * Author: MemberFix
 * Author URI: https://memberfix.rocks/
 */

add_shortcode('mm_adv_access_decision', 'mf_mm_adv_access_decision');

function mf_mm_adv_access_decision($attr, $content)
{

	global $wpdb;

	$now = strtotime("now");

	$myDate = date("Y/m/d");

	$membership_level = mm_member_data(array("name" => "membershipId"));; // will be used for "membershipid" shortcode parameter

	$registration_date = mm_member_data(array("name" => "registrationDate")); // will be used to compare with "date" shortcode parameter ( YYYY-MM-DD HH:MM:SS )

	$registration_date = strtotime($registration_date);

	$days_as_member = mm_member_data(array("name" => "daysAsMember")); // will be used to compare with "days" shortcode parameter

	$is_member = (mm_member_decision(array("isMember" => "true")) == true) ? "Yes" : "No";

	$membership_status = (mm_member_decision(array("status" => "active")) == true) ? "Active" : "Inactive";

	$attr['access'] = preg_replace("/[^a-z]/", "", $attr['access']); // just in case that wp editor messes with apostrophes - it adds this ’ instead of this '

	if ((isset($attr['date']))) {
		$date = preg_replace("/[^0-9\/]/", "", $attr['date']); // just in case that wp editor messes with apostrophes
		$date = strtotime($date);
	}
	$days = intval(preg_replace("/[^0-9]/", "", $attr['days']));

	$all_membershipids = preg_replace("/[^0-9|]/", "", $attr["membershipid"]); // get all memberships from membershipid shortcode attribute ( it can be just one or multiple like 7|12|3 )
	$all_membershipids_array = explode("|", $all_membershipids); // create an array with all memberships from shortcode attribute membershipid

	if (in_array($membership_level, $all_membershipids_array))  // check if user membership is find in the array with all memberships from membershipid shortcode attribute
	{
		$membership_level = "Yes";
	} else {
		$membership_level = "No";
	};


	$mm_purchase_link = mm_purchase_link(array("membershipId" => $all_membershipids_array[0]));

	$membership_name = $wpdb->get_var("SELECT name FROM mm_membership_levels WHERE id = '$all_membershipids_array[0]'"); 	//get membership level name 

	$membership_link = '<a href="' . $mm_purchase_link . '">' . $membership_name . ' Membership</a>';

	// BUNDLE CODE========================================================================================

	if ((isset($attr["bundleid"]))) {
		$bundle_id = preg_replace("/[^0-9]/", "", $attr["bundleid"]); // get bundle id from shortcode - it needs to have just one bundle id !
	}
	$current_user = wp_get_current_user();

	$user_id = $current_user->ID;

	$days_calc_method = $wpdb->get_var("SELECT days_calc_method FROM mm_applied_bundles WHERE access_type_id = '$user_id' AND bundle_id = '$bundle_id'");

	if ($days_calc_method == "join_date") {

		$bundle_apply_date = $wpdb->get_var("SELECT apply_date FROM mm_applied_bundles WHERE access_type_id = '$user_id' AND bundle_id = '$bundle_id'");

		if (isset($bundle_apply_date)) {
			$bundle_apply_date = strtotime($bundle_apply_date);
			$days_with_bundle = (($now - $bundle_apply_date) / 60 / 60 / 24);
			$days_with_bundle = (int) $days_with_bundle;
		}
	} else if ($days_calc_method == "custom_date") {


		$bundle_apply_date = $wpdb->get_var("SELECT days_calc_value FROM mm_applied_bundles WHERE access_type_id = '$user_id' AND bundle_id = '$bundle_id'");

		if (isset($bundle_apply_date)) {
			$bundle_apply_date = strtotime($bundle_apply_date);
			$days_with_bundle = (($now - $bundle_apply_date) / 60 / 60 / 24);
			$days_with_bundle = (int) $days_with_bundle;
		}
	} else if ($days_calc_method == "fixed") {
		$days_with_bundle = $wpdb->get_var("SELECT days_calc_value FROM mm_applied_bundles WHERE access_type_id = '$user_id' AND bundle_id = '$bundle_id'");
	}


	// for when bundleid is used:

	if (($is_member == "Yes") && ($membership_status == "Active")  && isset($bundle_apply_date) && isset($bundle_id)) {

		if ((isset($attr['date'])) && (isset($attr['days']))) {

			if (($date > $bundle_apply_date) || ($days_with_bundle >= $days)) {

				if (($attr['access'] == "true")) {

					return do_shortcode($content);	  //if conditions are met then show shortcode content

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}

			if (($date < $bundle_apply_date) && ($days_with_bundle < $days)) {

				$days_to_access = $days - $days_with_bundle; // if the number of days as member is less than the "days" attribute, then determine the number of days till access ($days - $days_with_bundle)
				$access_date = date('F j, Y', strtotime($myDate . '+ ' . $days_to_access . ' days'));  // the access date

				$new_content = str_replace("[X]", $access_date, $content); // replace X with number of days in shortcode content

				if (($attr['access'] == "true")) {

					return NULL;  //if there are more shortcodes on page , hide them

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return do_shortcode($new_content);	//if conditions are met then show shortcode content

				}
			}
		} else if ((!isset($attr['date'])) && (isset($attr['days']))) {


			if (($days_with_bundle >= $days)) {


				if (($attr['access'] == "true")) {

					return do_shortcode($content);	  //if conditions are met then show shortcode content

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}

			if (($days_with_bundle < $days)) {

				$days_to_access = $days - $days_with_bundle; // if the number of days as member is less than the "days" attribute, then determine the number of days till access ($days - $days_with_bundle)
				$access_date = date('F j, Y', strtotime($myDate . '+ ' . $days_to_access . ' days'));  // the access date

				$new_content = str_replace("[X]", $access_date, $content); // replace X with number of days in shortcode content

				if (($attr['access'] == "true")) {

					return NULL;  //if there are more shortcodes on page , hide them

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return do_shortcode($new_content);	//if conditions are met then show shortcode content

				}
			}
		} else if ((isset($attr['date'])) && (!isset($attr['days']))) {


			if (($date > $bundle_apply_date)) {

				if (($attr['access'] == "true")) {

					return do_shortcode($content);	  //if conditions are met then show shortcode content

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}

			if (($date < $bundle_apply_date)) {

				if (($attr['access'] == "true")) {

					return NULL;	  //if there are more shortcodes on page , hide them

				}

				if (($attr['access'] == "false")) {

					return do_shortcode($content);	//if conditions are met then show shortcode content
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}
		} else if ((!isset($attr['date'])) && (!isset($attr['days']))) {


			if (($attr['access'] == "true")) {

				return do_shortcode($content);	  //if conditions are met then show shortcode content

			}

			if (($attr['access'] == "false")) {

				return NULL;	//if there are more shortcodes on page , hide them
			}

			if (($attr['access'] == "future")) {

				return NULL;	//if there are more shortcodes on page , hide them

			}
		}
	} else if (($is_member == "Yes") && (!isset($bundle_apply_date) && isset($bundle_id))) { // for this case, for bundle based shortcodes, will return NULL for content

		if (($attr['access'] == "true")) {

			return NULL;
		}

		if (($attr['access'] == "false")) {

			return NULL;
		}

		if (($attr['access'] == "future")) {

			return NULL;
		}
	} else if (($membership_status !== "Active") && isset($bundle_id)) { // for this case, for bundle based shortcodes, will return NULL for content


		if (($attr['access'] == "true")) {

			return NULL;
		}

		if (($attr['access'] == "false")) {

			return NULL;
		}

		if (($attr['access'] == "future")) {

			return NULL;
		}
	} else if (($is_member !== "Yes") && isset($bundle_id)) { // for this case, for bundle based shortcodes, will return NULL for content


		if (($attr['access'] == "true")) {

			return NULL;
		}

		if (($attr['access'] == "false")) {

			return NULL;
		}

		if (($attr['access'] == "future")) {

			return NULL;
		}
	}


	// for when membershipid is used:

	if (($is_member == "Yes") && ($membership_status == "Active")  && ($membership_level == "Yes")) {

		if ((isset($attr['date'])) && (isset($attr['days']))) {

			if (($date > $registration_date) || ($days_as_member >= $days)) {

				if (($attr['access'] == "true")) {

					return do_shortcode($content);	  //if conditions are met then show shortcode content

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}

			if (($date < $registration_date) && ($days_as_member < $days)) {

				$days_to_access = $days - $days_as_member; // if the number of days as member is less than the "days" attribute, then determine the number of days till access ($days - $days_as_member)
				$access_date = date('F j, Y', strtotime($myDate . '+ ' . $days_to_access . ' days'));  // the access date

				$new_content = str_replace("[X]", $access_date, $content); // replace X with number of days in shortcode content

				if (($attr['access'] == "true")) {

					return NULL;  //if there are more shortcodes on page , hide them

				}

				if (($attr['access'] == "false")) {

					return NULL;	 //if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return do_shortcode($new_content);	//if conditions are met then show shortcode content

				}
			}
		} else if ((!isset($attr['date'])) && (isset($attr['days']))) {


			if (($days_as_member >= $days)) {


				if (($attr['access'] == "true")) {

					return do_shortcode($content);	  //if conditions are met then show shortcode content

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}

			if (($days_as_member < $days)) {

				$days_to_access = $days - $days_as_member; // if the number of days as member is less than the "days" attribute, then determine the number of days till access ($days - $days_as_member)
				$access_date = date('F j, Y', strtotime($myDate . '+ ' . $days_to_access . ' days'));  // the access date

				$new_content = str_replace("[X]", $access_date, $content); // replace X with number of days in shortcode content

				if (($attr['access'] == "true")) {

					return NULL;  //if there are more shortcodes on page , hide them

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return do_shortcode($new_content);	//if conditions are met then show shortcode content

				}
			}
		} else if ((isset($attr['date'])) && (!isset($attr['days']))) {


			if (($date > $registration_date)) {

				if (($attr['access'] == "true")) {

					return do_shortcode($content);	  //if conditions are met then show shortcode content

				}

				if (($attr['access'] == "false")) {

					return NULL;	//if there are more shortcodes on page , hide them
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}

			if (($date < $registration_date)) {

				if (($attr['access'] == "true")) {

					return NULL;	  //if there are more shortcodes on page , hide them

				}

				if (($attr['access'] == "false")) {

					return do_shortcode($content);	//if conditions are met then show shortcode content
				}

				if (($attr['access'] == "future")) {

					return NULL;	//if there are more shortcodes on page , hide them

				}
			}
		} else if ((!isset($attr['date'])) && (!isset($attr['days']))) {


			if (($attr['access'] == "true")) {

				return do_shortcode($content);	  //if conditions are met then show shortcode content

			}

			if (($attr['access'] == "false")) {

				return NULL;	//if there are more shortcodes on page , hide them
			}

			if (($attr['access'] == "future")) {

				return NULL;	//if there are more shortcodes on page , hide them

			}
		}
	} else if (($is_member == "Yes") && ($membership_level == "No")) {

		$new_content = str_replace("[X]", $membership_link, $content); // replace X with membership link in shortcode content

		if (($attr['access'] == "true")) {

			return NULL;
		}

		if (($attr['access'] == "false")) {

			return do_shortcode($new_content);
		}

		if (($attr['access'] == "future")) {

			return NULL;
		}
	} else if (($membership_status !== "Active")) {

		$new_content = str_replace("[X]", $membership_link, $content); // replace X with membership link in shortcode content

		if (($attr['access'] == "true")) {

			return NULL;
		}

		if (($attr['access'] == "false")) {

			return do_shortcode($new_content);
		}

		if (($attr['access'] == "future")) {

			return NULL;
		}
	} else if (($is_member !== "Yes")) {

		$new_content = str_replace("[X]", $membership_link, $content); // replace X with membership link in shortcode content

		if (($attr['access'] == "true")) {

			return NULL;
		}

		if (($attr['access'] == "false")) {

			return do_shortcode($new_content);
		}

		if (($attr['access'] == "future")) {

			return NULL;
		}
	}
}
