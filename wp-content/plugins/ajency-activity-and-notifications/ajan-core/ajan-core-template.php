<?php
/**
 * Core component template tag functions
 *
 * @package ActivityNotifications
 * @subpackage TemplateFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the "options nav", the secondary-level single item navigation menu.
 *
 * Uses the $ajan->ajan_options_nav global to render out the sub navigation for the
 * current component. Each component adds to its sub navigation array within
 * its own setup_nav() function.
 *
 * This sub navigation array is the secondary level navigation, so for profile
 * it contains:
 *      [Public, Edit Profile, Change Avatar]
 *
 * The function will also analyze the current action for the current component
 * to determine whether or not to highlight a particular sub nav item.
 *
 * @global ActivityNotifications $ajan The one true ActivityNotifications instance.
 * @uses ajan_get_user_nav() Renders the navigation for a profile of a currently
 *       viewed user.
 */
function ajan_get_options_nav() {
	global $ajan;

	// If we are looking at a member profile, then the we can use the current component as an
	// index. Otherwise we need to use the component's root_slug
	$component_index = !empty( $ajan->displayed_user ) ? ajan_current_component() : ajan_get_root_slug( ajan_current_component() );

	if ( !ajan_is_single_item() ) {
		if ( !isset( $ajan->ajan_options_nav[$component_index] ) || count( $ajan->ajan_options_nav[$component_index] ) < 1 ) {
			return false;
		} else {
			$the_index = $component_index;
		}
	} else {
		if ( !isset( $ajan->ajan_options_nav[ajan_current_item()] ) || count( $ajan->ajan_options_nav[ajan_current_item()] ) < 1 ) {
			return false;
		} else {
			$the_index = ajan_current_item();
		}
	}

	// Loop through each navigation item
	foreach ( (array) $ajan->ajan_options_nav[$the_index] as $subnav_item ) {
		if ( !$subnav_item['user_has_access'] )
			continue;

		// If the current action or an action variable matches the nav item id, then add a highlight CSS class.
		if ( $subnav_item['slug'] == ajan_current_action() ) {
			$selected = ' class="current selected"';
		} else {
			$selected = '';
		}

		// List type depends on our current component
		$list_type = ajan_is_group() ? 'groups' : 'personal';

		// echo out the final list item
		echo apply_filters( 'ajan_get_options_nav_' . $subnav_item['css_id'], '<li id="' . $subnav_item['css_id'] . '-' . $list_type . '-li" ' . $selected . '><a id="' . $subnav_item['css_id'] . '" href="' . $subnav_item['link'] . '">' . $subnav_item['name'] . '</a></li>', $subnav_item );
	}
}

/**
 * Get the 'ajan_options_title' property from the BP global.
 *
 * Not currently used in ActivityNotifications.
 * @todo Deprecate.
 */
function ajan_get_options_title() {
	global $ajan;

	if ( empty( $ajan->ajan_options_title ) )
		$ajan->ajan_options_title = __( 'Options', 'ajency-activity-and-notifications' );

	echo apply_filters( 'ajan_get_options_title', esc_attr( $ajan->ajan_options_title ) );
}

/**
 * Get the directory title for a component.
 *
 * Used for the <title> element and the page header on the component directory
 * page.
 *
 * @since ActivityNotifications (2.0.0)
 *
 * @return string
 */
function ajan_get_directory_title( $component = '' ) {
	$title = '';

	// Use the string provided by the component
	if ( ! empty( activitynotifications()->{$component}->directory_title ) ) {
		$title = activitynotifications()->{$component}->directory_title;

	// If none is found, concatenate
	} else if ( isset( activitynotifications()->{$component}->name ) ) {
		$title = sprintf( __( '%s Directory', 'ajency-activity-and-notifications' ), activitynotifications()->{$component}->name );
	}

	return apply_filters( 'ajan_get_directory_title', $title, $component );
}

/** Avatars *******************************************************************/

/**
 * Check to see if there is an options avatar.
 *
 * An options avatar is an avatar for something like a group, or a friend.
 * Basically an avatar that appears in the sub nav options bar.
 *
 * Not currently used in ActivityNotifications.
 *
 * @global ActivityNotifications $ajan The one true ActivityNotifications instance.
 * @todo Deprecate.
 *
 * @return bool Returns true if an options avatar has been set, otherwise
 *         false.
 */
function ajan_has_options_avatar() {
	global $ajan;

	if ( empty( $ajan->ajan_options_avatar ) )
		return false;

	return true;
}

/**
 * Output the options avatar.
 *
 * Not currently used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_get_options_avatar() {
	global $ajan;

	echo apply_filters( 'ajan_get_options_avatar', $ajan->ajan_options_avatar );
}

/**
 * Output a comment author's avatar.
 *
 * Not currently used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_comment_author_avatar() {
	global $comment;

	if ( function_exists( 'ajan_core_fetch_avatar' ) )
		echo apply_filters( 'ajan_comment_author_avatar', ajan_core_fetch_avatar( array( 'item_id' => $comment->user_id, 'type' => 'thumb', 'alt' => sprintf( __( 'Avatar of %s', 'ajency-activity-and-notifications' ), ajan_core_get_user_displayname( $comment->user_id ) ) ) ) );
	else if ( function_exists('get_avatar') )
		get_avatar();
}

/**
 * Output a post author's avatar.
 *
 * Not currently used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_post_author_avatar() {
	global $post;

	if ( function_exists( 'ajan_core_fetch_avatar' ) )
		echo apply_filters( 'ajan_post_author_avatar', ajan_core_fetch_avatar( array( 'item_id' => $post->post_author, 'type' => 'thumb', 'alt' => sprintf( __( 'Avatar of %s', 'ajency-activity-and-notifications' ), ajan_core_get_user_displayname( $post->post_author ) ) ) ) );
	else if ( function_exists('get_avatar') )
		get_avatar();
}

/**
 * Output the current avatar upload step.
 */
function ajan_avatar_admin_step() {
	echo ajan_get_avatar_admin_step();
}
	/**
	 * Return the current avatar upload step.
	 *
	 * @return string The current avatar upload step. Returns 'upload-image'
	 *         if none is found.
	 */
	function ajan_get_avatar_admin_step() {
		global $ajan;

		if ( isset( $ajan->avatar_admin->step ) )
			$step = $ajan->avatar_admin->step;
		else
			$step = 'upload-image';

		return apply_filters( 'ajan_get_avatar_admin_step', $step );
	}

/**
 * Output the URL of the avatar to crop.
 */
function ajan_avatar_to_crop() {
	echo ajan_get_avatar_to_crop();
}
	/**
	 * Return the URL of the avatar to crop.
	 *
	 * @return string URL of the avatar awaiting cropping.
	 */
	function ajan_get_avatar_to_crop() {
		global $ajan;

		if ( isset( $ajan->avatar_admin->image->url ) )
			$url = $ajan->avatar_admin->image->url;
		else
			$url = '';

		return apply_filters( 'ajan_get_avatar_to_crop', $url );
	}

/**
 * Output the relative file path to the avatar to crop.
 */
function ajan_avatar_to_crop_src() {
	echo ajan_get_avatar_to_crop_src();
}
	/**
	 * Return the relative file path to the avatar to crop.
	 *
	 * @return string Relative file path to the avatar.
	 */
	function ajan_get_avatar_to_crop_src() {
		global $ajan;

		return apply_filters( 'ajan_get_avatar_to_crop_src', str_replace( WP_CONTENT_DIR, '', $ajan->avatar_admin->image->dir ) );
	}

/**
 * Output the avatar cropper <img> markup.
 *
 * No longer used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_avatar_cropper() {
	global $ajan;

	echo '<img id="avatar-to-crop" class="avatar" src="' . $ajan->avatar_admin->image . '" />';
}

/**
 * Output the name of the BP site. Used in RSS headers.
 */
function ajan_site_name() {
	echo ajan_get_site_name();
}
	/**
	 * Returns the name of the BP site. Used in RSS headers.
	 *
	 * @since ActivityNotifications (1.6.0)
	 */
	function ajan_get_site_name() {
		return apply_filters( 'ajan_site_name', get_bloginfo( 'name', 'display' ) );
	}

/**
 * Format a date.
 *
 * @param int $time The UNIX timestamp to be formatted.
 * @param bool $just_date Optional. True to return only the month + day, false
 *        to return month, day, and time. Default: false.
 * @param bool $localize_time Optional. True to display in local time, false to
 *        leave in GMT. Default: true.
 * @return string|bool $localize_time Optional. A string representation of
 *         $time, in the format "January 1, 2010 at 9:50pm" (or whatever your
 *         'date_format' and 'time_format' settings are). False on failure.
 */
function ajan_format_time( $time, $just_date = false, $localize_time = true ) {
	if ( !isset( $time ) || !is_numeric( $time ) )
		return false;

	// Get GMT offset from root blog
	$root_blog_offset = false;
	if ( $localize_time )
		$root_blog_offset = get_blog_option( ajan_get_root_blog_id(), 'gmt_offset' );

	// Calculate offset time
	$time_offset = $time + ( $root_blog_offset * 3600 );

	// Current date (January 1, 2010)
	$date = date_i18n( get_option( 'date_format' ), $time_offset );

	// Should we show the time also?
	if ( !$just_date ) {
		// Current time (9:50pm)
		$time = date_i18n( get_option( 'time_format' ), $time_offset );

		// Return string formatted with date and time
		$date = sprintf( __( '%1$s at %2$s', 'ajency-activity-and-notifications' ), $date, $time );
	}

	return apply_filters( 'ajan_format_time', $date );
}

/**
 * Select between two dynamic strings, according to context.
 *
 * This function can be used in cases where a phrase used in a template will
 * differ for a user looking at his own profile and a user looking at another
 * user's profile (eg, "My Friends" and "Joe's Friends"). Pass both versions
 * of the phrase, and ajan_word_or_name() will detect which is appropriate, and
 * do the necessary argument swapping for dynamic phrases.
 *
 * @param string $youtext The "you" version of the phrase (eg "Your Friends").
 * @param string $nametext The other-user version of the phrase. Should be in
 *        a format appropriate for sprintf() - use %s in place of the displayed
 *        user's name (eg "%'s Friends").
 * @param bool $capitalize Optional. Force into title case. Default: true.
 * @param bool $echo Optional. True to echo the results, false to return them.
 *        Default: true.
 * @return string|null If ! $echo, returns the appropriate string.
 */
function ajan_word_or_name( $youtext, $nametext, $capitalize = true, $echo = true ) {

	if ( !empty( $capitalize ) )
		$youtext = ajan_core_ucfirst( $youtext );

	if ( ajan_displayed_user_id() == ajan_loggedin_user_id() ) {
		if ( true == $echo ) {
			echo apply_filters( 'ajan_word_or_name', $youtext );
		} else {
			return apply_filters( 'ajan_word_or_name', $youtext );
		}
	} else {
		$fullname = ajan_get_displayed_user_fullname();
		$fullname = (array) explode( ' ', $fullname );
		$nametext = sprintf( $nametext, $fullname[0] );
		if ( true == $echo ) {
			echo apply_filters( 'ajan_word_or_name', $nametext );
		} else {
			return apply_filters( 'ajan_word_or_name', $nametext );
		}
	}
}

/**
 * Do the 'ajan_styles' action, and call wp_print_styles().
 *
 * No longer used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_styles() {
	do_action( 'ajan_styles' );
	wp_print_styles();
}

/** Search Form ***************************************************************/

/**
 * Return the "action" attribute for search forms.
 *
 * @return string URL action attribute for search forms, eg example.com/search/.
 */
function ajan_search_form_action() {
	return apply_filters( 'ajan_search_form_action', trailingslashit( ajan_get_root_domain() . '/' . ajan_get_search_slug() ) );
}

/**
 * Generate the basic search form as used in BP-Default's header.
 *
 * @since ActivityNotifications (1.0.0)
 *
 * @return string HTML <select> element.
 */
function ajan_search_form_type_select() {

	$options = array();

	if ( ajan_is_active( 'xprofile' ) )
		$options['members'] = __( 'Members', 'ajency-activity-and-notifications' );

	if ( ajan_is_active( 'groups' ) )
		$options['groups']  = __( 'Groups',  'ajency-activity-and-notifications' );

	if ( ajan_is_active( 'blogs' ) && is_multisite() )
		$options['blogs']   = __( 'Blogs',   'ajency-activity-and-notifications' );

	if ( ajan_is_active( 'forums' ) && ajan_forums_is_installed_correctly() && ajan_forums_has_directory() )
		$options['forums']  = __( 'Forums',  'ajency-activity-and-notifications' );

	$options['posts'] = __( 'Posts', 'ajency-activity-and-notifications' );

	// Eventually this won't be needed and a page will be built to integrate all search results.
	$selection_box  = '<label for="search-which" class="accessibly-hidden">' . __( 'Search these:', 'ajency-activity-and-notifications' ) . '</label>';
	$selection_box .= '<select name="search-which" id="search-which" style="width: auto">';

	$options = apply_filters( 'ajan_search_form_type_select_options', $options );
	foreach( (array) $options as $option_value => $option_title )
		$selection_box .= sprintf( '<option value="%s">%s</option>', $option_value, $option_title );

	$selection_box .= '</select>';

	return apply_filters( 'ajan_search_form_type_select', $selection_box );
}

/**
 * Output the default text for the search box for a given component.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @see ajan_get_search_default_text()
 *
 * @param string $component See {@link ajan_get_search_default_text()}.
 */
function ajan_search_default_text( $component = '' ) {
	echo ajan_get_search_default_text( $component );
}
	/**
	 * Return the default text for the search box for a given component.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @param string $component Component name. Default: current component.
	 * @return string Placeholder text for search field.
	 */
	function ajan_get_search_default_text( $component = '' ) {
		global $ajan;

		if ( empty( $component ) )
			$component = ajan_current_component();

		$default_text = __( 'Search anything...', 'ajency-activity-and-notifications' );

		// Most of the time, $component will be the actual component ID
		if ( !empty( $component ) ) {
			if ( !empty( $ajan->{$component}->search_string ) ) {
				$default_text = $ajan->{$component}->search_string;
			} else {
				// When the request comes through AJAX, we need to get the component
				// name out of $ajan->pages
				if ( !empty( $ajan->pages->{$component}->slug ) ) {
					$key = $ajan->pages->{$component}->slug;
					if ( !empty( $ajan->{$key}->search_string ) )
						$default_text = $ajan->{$key}->search_string;
				}
			}
		}

		return apply_filters( 'ajan_get_search_default_text', $default_text, $component );
	}

/**
 * Fire the 'ajan_custom_profile_boxes' action.
 *
 * No longer used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_custom_profile_boxes() {
	do_action( 'ajan_custom_profile_boxes' );
}

/**
 * Fire the 'ajan_custom_profile_sidebar_boxes' action.
 *
 * No longer used in ActivityNotifications.
 *
 * @todo Deprecate.
 */
function ajan_custom_profile_sidebar_boxes() {
	do_action( 'ajan_custom_profile_sidebar_boxes' );
}

/**
 * Create and output a button.
 *
 * @see ajan_get_button()
 *
 * @param array $args See {@link AJAN_Button}.
 */
function ajan_button( $args = '' ) {
	echo ajan_get_button( $args );
}
	/**
	 * Create and return a button.
	 *
	 * @see AJAN_Button for a description of arguments and return value.
	 *
	 * @param array $args See {@link AJAN_Button}.
	 * @return string HTML markup for the button.
	 */
	function ajan_get_button( $args = '' ) {
		$button = new AJAN_Button( $args );
		return apply_filters( 'ajan_get_button', $button->contents, $args, $button );
	}

/**
 * Truncate text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * This function is borrowed from CakePHP v2.0, under the MIT license. See
 * http://book.cakephp.org/view/1469/Text#truncate-1625
 *
 * ### Options:
 *
 * - `ending` Will be used as Ending and appended to the trimmed string
 * - `exact` If false, $text will not be cut mid-word
 * - `html` If true, HTML tags would be handled correctly
 * - `filter_shortcodes` If true, shortcodes will be stripped before truncating
 *
 * @param string $text String to truncate.
 * @param int $length Optional. Length of returned string, including ellipsis.
 *        Default: 225.
 * @param array $options {
 *     An array of HTML attributes and options. Each item is optional.
 *     @type string $ending The string used after truncation.
 *           Default: ' [&hellip;]'.
 *     @type bool $exact If true, $text will be trimmed to exactly $length.
 *           If false, $text will not be cut mid-word. Default: false.
 *     @type bool $html If true, don't include HTML tags when calculating
 *           excerpt length. Default: true.
 *     @type bool $filter_shortcodes If true, shortcodes will be stripped.
 *           Default: true.
 * }
 * @return string Trimmed string.
 */
function ajan_create_excerpt( $text, $length = 225, $options = array() ) {
	// Backward compatibility. The third argument used to be a boolean $filter_shortcodes
	$filter_shortcodes_default = is_bool( $options ) ? $options : true;

	$defaults = array(
		'ending'            => __( ' [&hellip;]', 'ajency-activity-and-notifications' ),
		'exact'             => false,
		'html'              => true,
		'filter_shortcodes' => $filter_shortcodes_default
	);
	$r = wp_parse_args( $options, $defaults );
	extract( $r );

	// Save the original text, to be passed along to the filter
	$original_text = $text;

	// Allow plugins to modify these values globally
	$length = apply_filters( 'ajan_excerpt_length', $length );
	$ending = apply_filters( 'ajan_excerpt_append_text', $ending );

	// Remove shortcodes if necessary
	if ( !empty( $filter_shortcodes ) )
		$text = strip_shortcodes( $text );

	// When $html is true, the excerpt should be created without including HTML tags in the
	// excerpt length
	if ( !empty( $html ) ) {
		// The text is short enough. No need to truncate
		if ( mb_strlen( preg_replace( '/<.*?>/', '', $text ) ) <= $length ) {
			return $text;
		}

		$totalLength = mb_strlen( strip_tags( $ending ) );
		$openTags    = array();
		$truncate    = '';

		// Find all the tags and put them in a stack for later use
		preg_match_all( '/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER );
		foreach ( $tags as $tag ) {
			// Process tags that need to be closed
			if ( !preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s',  $tag[2] ) ) {
				if ( preg_match( '/<[\w]+[^>]*>/s', $tag[0] ) ) {
					array_unshift( $openTags, $tag[2] );
				} else if ( preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag ) ) {
					$pos = array_search( $closeTag[1], $openTags );
					if ( $pos !== false ) {
						array_splice( $openTags, $pos, 1 );
					}
				}
			}
			$truncate .= $tag[1];

			$contentLength = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3] ) );
			if ( $contentLength + $totalLength > $length ) {
				$left = $length - $totalLength;
				$entitiesLength = 0;
				if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE ) ) {
					foreach ( $entities[0] as $entity ) {
						if ( $entity[1] + 1 - $entitiesLength <= $left ) {
							$left--;
							$entitiesLength += mb_strlen( $entity[0] );
						} else {
							break;
						}
					}
				}

				$truncate .= mb_substr( $tag[3], 0 , $left + $entitiesLength );
				break;
			} else {
				$truncate .= $tag[3];
				$totalLength += $contentLength;
			}
			if ( $totalLength >= $length ) {
				break;
			}
		}
	} else {
		if ( mb_strlen( $text ) <= $length ) {
			return $text;
		} else {
			$truncate = mb_substr( $text, 0, $length - mb_strlen( $ending ) );
		}
	}

	// If $exact is false, we can't break on words
	if ( empty( $exact ) ) {
		$spacepos = mb_strrpos( $truncate, ' ' );
		if ( isset( $spacepos ) ) {
			if ( $html ) {
				$bits = mb_substr( $truncate, $spacepos );
				preg_match_all( '/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER );
				if ( !empty( $droppedTags ) ) {
					foreach ( $droppedTags as $closingTag ) {
						if ( !in_array( $closingTag[1], $openTags ) ) {
							array_unshift( $openTags, $closingTag[1] );
						}
					}
				}
			}
			$truncate = mb_substr( $truncate, 0, $spacepos );
		}
	}
	$truncate .= $ending;

	if ( $html ) {
		foreach ( $openTags as $tag ) {
			$truncate .= '</' . $tag . '>';
		}
	}

	return apply_filters( 'ajan_create_excerpt', $truncate, $original_text, $length, $options );

}
add_filter( 'ajan_create_excerpt', 'stripslashes_deep' );
add_filter( 'ajan_create_excerpt', 'force_balance_tags' );

/**
 * Output the total member count for the site.
 */
function ajan_total_member_count() {
	echo ajan_get_total_member_count();
}
	/**
	 * Return the total member count in your BP instance.
	 *
	 * Since ActivityNotifications 1.6, this function has used ajan_core_get_active_member_count(),
	 * which counts non-spam, non-deleted users who have last_activity.
	 * This value will correctly match the total member count number used
	 * for pagination on member directories.
	 *
	 * Before ActivityNotifications 1.6, this function used ajan_core_get_total_member_count(),
	 * which did not take into account last_activity, and thus often
	 * resulted in higher counts than shown by member directory pagination.
	 *
	 * @return int Member count.
	 */
	function ajan_get_total_member_count() {
		return apply_filters( 'ajan_get_total_member_count', ajan_core_get_active_member_count() );
	}
	add_filter( 'ajan_get_total_member_count', 'ajan_core_number_format' );

/**
 * Output whether blog signup is allowed.
 *
 * @todo Deprecate. It doesn't make any sense to echo a boolean.
 */
function ajan_blog_signup_allowed() {
	echo ajan_get_blog_signup_allowed();
}
	/**
	 * Is blog signup allowed?
	 *
	 * Returns true if is_multisite() and blog creation is enabled at
	 * Network Admin > Settings.
	 *
	 * @return bool True if blog signup is allowed, otherwise false.
	 */
	function ajan_get_blog_signup_allowed() {
		global $ajan;

		if ( !is_multisite() )
			return false;

		$status = $ajan->site_options['registration'];
		if ( 'none' != $status && 'user' != $status )
			return true;

		return false;
	}

/**
 * Check whether an activation has just been completed.
 *
 * @return bool True if the activation_complete global flag has been set,
 *         otherwise false.
 */
function ajan_account_was_activated() {
	global $ajan;

	$activation_complete = !empty( $ajan->activation_complete ) ? $ajan->activation_complete : false;

	return $activation_complete;
}

/**
 * Check whether registrations require activation on this installation.
 *
 * On a normal ActivityNotifications installation, all registrations require email
 * activation. This filter exists so that customizations that omit activation
 * can remove certain notification text from the registration screen.
 *
 * @return bool True by default.
 */
function ajan_registration_needs_activation() {
	return apply_filters( 'ajan_registration_needs_activation', true );
}

/**
 * Retrieve a client friendly version of the root blog name.
 *
 * The blogname option is escaped with esc_html on the way into the database in
 * sanitize_option, we want to reverse this for the plain text arena of emails.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @see http://buddypress.trac.wordpress.org/ticket/4401
 *
 * @param array $args {
 *     Array of optional parameters.
 *     @type string $before String to appear before the site name in the
 *           email subject. Default: '['.
 *     @type string $after String to appear after the site name in the
 *           email subject. Default: ']'.
 *     @type string $default The default site name, to be used when none is
 *           found in the database. Default: 'Community'.
 *     @type string $text Text to append to the site name (ie, the main text of
 *           the email subject).
 * }
 * @return string Sanitized email subject.
 */
function ajan_get_email_subject( $args = array() ) {

	$r = wp_parse_args( $args, array(
		'before'  => '[',
		'after'   => ']',
		'default' => __( 'Community', 'ajency-activity-and-notifications' ),
		'text'    => ''
	) );

	$subject = $r['before'] . wp_specialchars_decode( ajan_get_option( 'blogname', $r['default'] ), ENT_QUOTES ) . $r['after'] . ' ' . $r['text'];

	return apply_filters( 'ajan_get_email_subject', $subject, $r );
}

/**
 * Allow templates to pass parameters directly into the template loops via AJAX.
 *
 * For the most part this will be filtered in a theme's functions.php for
 * example in the default theme it is filtered via ajan_dtheme_ajax_querystring().
 *
 * By using this template tag in the templates it will stop them from showing
 * errors if someone copies the templates from the default theme into another
 * WordPress theme without coping the functions from functions.php.
 *
 * @param string $object
 * @return string The AJAX querystring.
 */
function ajan_ajax_querystring( $object = false ) {
	global $ajan;

	if ( !isset( $ajan->ajax_querystring ) )
		$ajan->ajax_querystring = '';

	return apply_filters( 'ajan_ajax_querystring', $ajan->ajax_querystring, $object );
}

/** Template Classes and _is functions ****************************************/

/**
 * Return the name of the current component.
 *
 * @return string Component name.
 */
function ajan_current_component() {
	global $ajan;
	$current_component = !empty( $ajan->current_component ) ? $ajan->current_component : false;
	return apply_filters( 'ajan_current_component', $current_component );
}

/**
 * Return the name of the current action.
 *
 * @return string Action name.
 */
function ajan_current_action() {
	global $ajan;
	$current_action = !empty( $ajan->current_action ) ? $ajan->current_action : '';
	return apply_filters( 'ajan_current_action', $current_action );
}

/**
 * Return the name of the current item.
 *
 * @return unknown
 */
function ajan_current_item() {
	global $ajan;
	$current_item = !empty( $ajan->current_item ) ? $ajan->current_item : false;
	return apply_filters( 'ajan_current_item', $current_item );
}

/**
 * Return the value of $ajan->action_variables.
 *
 * @return array|bool $action_variables The action variables array, or false
 *         if the array is empty.
 */
function ajan_action_variables() {
	global $ajan;
	$action_variables = !empty( $ajan->action_variables ) ? $ajan->action_variables : false;
	return apply_filters( 'ajan_action_variables', $action_variables );
}

/**
 * Return the value of a given action variable.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @param int $position The key of the action_variables array that you want.
 * @return string|bool $action_variable The value of that position in the
 *         array, or false if not found.
 */
function ajan_action_variable( $position = 0 ) {
	$action_variables = ajan_action_variables();
	$action_variable  = isset( $action_variables[$position] ) ? $action_variables[$position] : false;

	return apply_filters( 'ajan_action_variable', $action_variable, $position );
}

/**
 * Output the "root domain", the URL of the BP root blog.
 */
function ajan_root_domain() {
	echo ajan_get_root_domain();
}
	/**
	 * Return the "root domain", the URL of the BP root blog.
	 *
	 * @return string URL of the BP root blog.
	 */
	function ajan_get_root_domain() {
		global $ajan;

		if ( isset( $ajan->root_domain ) && !empty( $ajan->root_domain ) ) {
			$domain = $ajan->root_domain;
		} else {
			$domain          = ajan_core_get_root_domain();
			$ajan->root_domain = $domain;
		}

		return apply_filters( 'ajan_get_root_domain', $domain );
	}

/**
 * Output the root slug for a given component.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @param string $component The component name.
 */
function ajan_root_slug( $component = '' ) {
	echo ajan_get_root_slug( $component );
}
	/**
	 * Get the root slug for given component.
	 *
	 * The "root slug" is the string used when concatenating component
	 * directory URLs. For example, on an installation where the Groups
	 * component's directory is located at http://example.com/groups/, the
	 * root slug for the Groups component is 'groups'. This string
	 * generally corresponds to page_name of the component's directory
	 * page.
	 *
	 * In order to maintain backward compatibility, the following procedure
	 * is used:
	 * 1) Use the short slug to get the canonical component name from the
	 *    active component array
	 * 2) Use the component name to get the root slug out of the
	 *    appropriate part of the $ajan global
	 * 3) If nothing turns up, it probably means that $component is itself
	 *    a root slug
	 *
	 * Example: If your groups directory is at /community/companies, this
	 * function first uses the short slug 'companies' (ie the current
	 * component) to look up the canonical name 'groups' in
	 * $ajan->active_components. Then it uses 'groups' to get the root slug,
	 * from $ajan->groups->root_slug.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @global ActivityNotifications $ajan The one true ActivityNotifications instance.
	 *
	 * @param string $component Optional. Defaults to the current component.
	 * @return string $root_slug The root slug.
	 */
	function ajan_get_root_slug( $component = '' ) {
		global $ajan;

		$root_slug = '';

		// Use current global component if none passed
		if ( empty( $component ) )
			$component = ajan_current_component();

		// Component is active
		if ( !empty( $ajan->active_components[$component] ) ) {
			// Backward compatibility: in legacy plugins, the canonical component id
			// was stored as an array value in $ajan->active_components
			$component_name = '1' == $ajan->active_components[$component] ? $component : $ajan->active_components[$component];

			// Component has specific root slug
			if ( !empty( $ajan->{$component_name}->root_slug ) ) {
				$root_slug = $ajan->{$component_name}->root_slug;
			}
		}

		// No specific root slug, so fall back to component slug
		if ( empty( $root_slug ) )
			$root_slug = $component;

		return apply_filters( 'ajan_get_root_slug', $root_slug, $component );
	}

/**
 * Return the component name based on a root slug.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @global ActivityNotifications $ajan The one true ActivityNotifications instance.
 *
 * @param string $root_slug Needle to our active component haystack.
 * @return mixed False if none found, component name if found.
 */
function ajan_get_name_from_root_slug( $root_slug = '' ) {
	global $ajan;

	// If no slug is passed, look at current_component
	if ( empty( $root_slug ) )
		$root_slug = ajan_current_component();

	// No current component or root slug, so flee
	if ( empty( $root_slug ) )
		return false;

	// Loop through active components and look for a match
	foreach ( array_keys( $ajan->active_components ) as $component ) {
		if ( ( !empty( $ajan->{$component}->slug ) && ( $ajan->{$component}->slug == $root_slug ) ) || ( !empty( $ajan->{$component}->root_slug ) && ( $ajan->{$component}->root_slug == $root_slug ) ) ) {
			return $ajan->{$component}->name;
		}
	}

	return false;
}

function ajan_user_has_access() {
	$has_access = ( ajan_current_user_can( 'ajan_moderate' ) || ajan_is_my_profile() ) ? true : false;

	return apply_filters( 'ajan_user_has_access', $has_access );
}

/**
 * Output the search slug.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @uses ajan_get_search_slug()
 */
function ajan_search_slug() {
	echo ajan_get_search_slug();
}
	/**
	 * Return the search slug.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @return string The search slug. Default: 'search'.
	 */
	function ajan_get_search_slug() {
		return apply_filters( 'ajan_get_search_slug', AJAN_SEARCH_SLUG );
	}

/**
 * Get the ID of the currently displayed user.
 *
 * @uses apply_filters() Filter 'ajan_displayed_user_id' to change this value.
 *
 * @return int ID of the currently displayed user.
 */
function ajan_displayed_user_id() {
	$ajan = activitynotifications();
	$id = !empty( $ajan->displayed_user->id ) ? $ajan->displayed_user->id : 0;

	return (int) apply_filters( 'ajan_displayed_user_id', $id );
}

/**
 * Get the ID of the currently logged-in user.
 *
 * @uses apply_filters() Filter 'ajan_loggedin_user_id' to change this value.
 *
 * @return int ID of the logged-in user.
 */
function ajan_loggedin_user_id() {
	$ajan = activitynotifications();
	$id = !empty( $ajan->loggedin_user->id ) ? $ajan->loggedin_user->id : 0;

	return (int) apply_filters( 'ajan_loggedin_user_id', $id );
}

/** is_() functions to determine the current page *****************************/

/**
 * Check to see whether the current page belongs to the specified component.
 *
 * This function is designed to be generous, accepting several different kinds
 * of value for the $component parameter. It checks $component_name against:
 * - the component's root_slug, which matches the page slug in $ajan->pages
 * - the component's regular slug
 * - the component's id, or 'canonical' name
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @param string $component Name of the component being checked.
 * @return bool Returns true if the component matches, or else false.
 */
function ajan_is_current_component( $component ) {
	global $ajan, $wp_query;

	$is_current_component = false;

	// Always return false if a null value is passed to the function
	if ( empty( $component ) ) {
		return false;
	}

	// Backward compatibility: 'xprofile' should be read as 'profile'
	if ( 'xprofile' == $component )
		$component = 'profile';

	if ( ! empty( $ajan->current_component ) ) {

		// First, check to see whether $component_name and the current
		// component are a simple match
		if ( $ajan->current_component == $component ) {
			$is_current_component = true;

		// Since the current component is based on the visible URL slug let's
		// check the component being passed and see if its root_slug matches
		} elseif ( isset( $ajan->{$component}->root_slug ) && $ajan->{$component}->root_slug == $ajan->current_component ) {
			$is_current_component = true;

		// Because slugs can differ from root_slugs, we should check them too
		} elseif ( isset( $ajan->{$component}->slug ) && $ajan->{$component}->slug == $ajan->current_component ) {
			$is_current_component = true;

		// Next, check to see whether $component is a canonical,
		// non-translatable component name. If so, we can return its
		// corresponding slug from $ajan->active_components.
		} else if ( $key = array_search( $component, $ajan->active_components ) ) {
			if ( strstr( $ajan->current_component, $key ) ) {
				$is_current_component = true;
			}

		// If we haven't found a match yet, check against the root_slugs
		// created by $ajan->pages, as well as the regular slugs
		} else {
			foreach ( $ajan->active_components as $id ) {
				// If the $component parameter does not match the current_component,
				// then move along, these are not the droids you are looking for
				if ( empty( $ajan->{$id}->root_slug ) || $ajan->{$id}->root_slug != $ajan->current_component ) {
					continue;
				}

				if ( $id == $component ) {
					$is_current_component = true;
					break;
				}
			}
		}

	// Page template fallback check if $ajan->current_component is empty
	} elseif ( !is_admin() && is_a( $wp_query, 'WP_Query' ) && is_page() ) {
		global $wp_query;
		$page          = $wp_query->get_queried_object();
		$custom_fields = get_post_custom_values( '_wp_page_template', $page->ID );
		$page_template = $custom_fields[0];

		// Component name is in the page template name
		if ( !empty( $page_template ) && strstr( strtolower( $page_template ), strtolower( $component ) ) ) {
			$is_current_component = true;
		}
	}

 	return apply_filters( 'ajan_is_current_component', $is_current_component, $component );
}

/**
 * Check to see whether the current page matches a given action.
 *
 * Along with ajan_is_current_component() and ajan_is_action_variable(), this
 * function is mostly used to help determine when to use a given screen
 * function.
 *
 * In BP parlance, the current_action is the URL chunk that comes directly
 * after the current item slug. E.g., in
 *   http://example.com/groups/my-group/members
 * the current_action is 'members'.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @param string $action The action being tested against.
 * @return bool True if the current action matches $action.
 */
function ajan_is_current_action( $action = '' ) {
	if ( $action == ajan_current_action() )
		return true;

	return false;
}

/**
 * Check to see whether the current page matches a given action_variable.
 *
 * Along with ajan_is_current_component() and ajan_is_current_action(), this
 * function is mostly used to help determine when to use a given screen
 * function.
 *
 * In BP parlance, action_variables are an array made up of the URL chunks
 * appearing after the current_action in a URL. For example,
 *   http://example.com/groups/my-group/admin/group-settings
 * $action_variables[0] is 'group-settings'.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @param string $action_variable The action_variable being tested against.
 * @param int $position Optional. The array key you're testing against. If you
 *        don't provide a $position, the function will return true if the
 *        $action_variable is found *anywhere* in the action variables array.
 * @return bool True if $action_variable matches at the $position provided.
 */
function ajan_is_action_variable( $action_variable = '', $position = false ) {
	$is_action_variable = false;

	if ( false !== $position ) {
		// When a $position is specified, check that slot in the action_variables array
		if ( $action_variable ) {
			$is_action_variable = $action_variable == ajan_action_variable( $position );
		} else {
			// If no $action_variable is provided, we are essentially checking to see
			// whether the slot is empty
			$is_action_variable = !ajan_action_variable( $position );
		}
	} else {
		// When no $position is specified, check the entire array
		$is_action_variable = in_array( $action_variable, (array)ajan_action_variables() );
	}

	return apply_filters( 'ajan_is_action_variable', $is_action_variable, $action_variable, $position );
}

/**
 * Check against the current_item.
 *
 * @param string $item The item being checked.
 * @return bool True if $item is the current item.
 */
function ajan_is_current_item( $item = '' ) {
	if ( !empty( $item ) && $item == ajan_current_item() )
		return true;

	return false;
}

/**
 * Are we looking at a single item? (group, user, etc)
 *
 * @return bool True if looking at a single item, otherwise false.
 */
function ajan_is_single_item() {
	global $ajan;

	if ( !empty( $ajan->is_single_item ) )
		return true;

	return false;
}

/**
 * Is the logged-in user an admin for the current item?
 *
 * @return bool True if the current user is an admin for the current item,
 *         otherwise false.
 */
function ajan_is_item_admin() {
	global $ajan;

	if ( !empty( $ajan->is_item_admin ) )
		return true;

	return false;
}

/**
 * Is the logged-in user a mod for the current item?
 *
 * @return bool True if the current user is a mod for the current item,
 *         otherwise false.
 */
function ajan_is_item_mod() {
	global $ajan;

	if ( !empty( $ajan->is_item_mod ) )
		return true;

	return false;
}

/**
 * Is this a component directory page?
 *
 * @return bool True if the current page is a component directory, otherwise
 *         false.
 */
function ajan_is_directory() {
	global $ajan;

	if ( !empty( $ajan->is_directory ) )
		return true;

	return false;
}

/**
 * Check to see if a component's URL should be in the root, not under a member page.
 *
 *   Yes ('groups' is root): http://domain.com/groups/the-group
 *   No ('groups' is not-root):  http://domain.com/members/andy/groups/the-group
 *
 * @return bool True if root component, else false.
 */
function ajan_is_root_component( $component_name ) {
	global $ajan;

	if ( !isset( $ajan->active_components ) )
		return false;

	foreach ( (array) $ajan->active_components as $key => $slug ) {
		if ( $key == $component_name || $slug == $component_name )
			return true;
	}

	return false;
}

/**
 * Check if the specified ActivityNotifications component directory is set to be the front page.
 *
 * Corresponds to the setting in wp-admin's Settings > Reading screen.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @global ActivityNotifications $ajan The one true ActivityNotifications instance.
 * @global $current_blog WordPress global for the current blog.
 *
 * @param string $component Optional. Name of the component to check for.
 *        Default: current component.
 * @return bool True if the specified component is set to be the site's front
 *         page, otherwise false.
 */
function ajan_is_component_front_page( $component = '' ) {
	global $ajan, $current_blog;

	if ( !$component && !empty( $ajan->current_component ) )
		$component = $ajan->current_component;

	$path = is_main_site() ? ajan_core_get_site_path() : $current_blog->path;

	if ( 'page' != get_option( 'show_on_front' ) || !$component || empty( $ajan->pages->{$component} ) || $_SERVER['REQUEST_URI'] != $path )
		return false;

	return apply_filters( 'ajan_is_component_front_page', ( $ajan->pages->{$component}->id == get_option( 'page_on_front' ) ), $component );
}

/**
 * Is this a blog page, ie a non-BP page?
 *
 * You can tell if a page is displaying BP content by whether the
 * current_component has been defined.
 *
 * @return bool True if it's a non-BP page, false otherwise.
 */
function ajan_is_blog_page() {

	$is_blog_page = false;

	// Generally, we can just check to see that there's no current component. The one exception
	// is single user home tabs, where $ajan->current_component is unset. Thus the addition
	// of the ajan_is_user() check.
	if ( !ajan_current_component() && !ajan_is_user() )
		$is_blog_page = true;

	return apply_filters( 'ajan_is_blog_page', $is_blog_page );
}

/**
 * Is this a ActivityNotifications component?
 *
 * You can tell if a page is displaying BP content by whether the
 * current_component has been defined.
 *
 * Generally, we can just check to see that there's no current component.
 * The one exception is single user home tabs, where $ajan->current_component
 * is unset. Thus the addition of the ajan_is_user() check.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @return bool True if it's a ActivityNotifications page, false otherwise.
 */
function is_activitynotifications() {
	$retval = (bool) ( ajan_current_component() || ajan_is_user() );

	return apply_filters( 'is_buddypress', $retval );
}

/** Components ****************************************************************/

/**
 * Check whether a given component has been activated by the admin.
 *
 * @param string $component The component name.
 * @return bool True if the component is active, otherwise false.
 */
function ajan_is_active( $component ) {
	global $ajan;

	if ( isset( $ajan->active_components[$component] ) || 'core' == $component )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Members component.
 *
 * @return bool True if the current page is part of the Members component.
 */
function ajan_is_members_component() {
	if ( ajan_is_current_component( 'members' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Profile component.
 *
 * @return bool True if the current page is part of the Profile component.
 */
function ajan_is_profile_component() {
	if ( ajan_is_current_component( 'xprofile' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Activity component.
 *
 * @return bool True if the current page is part of the Activity component.
 */
function ajan_is_activity_component() {
	if ( ajan_is_current_component( 'activity' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Blogs component.
 *
 * @return bool True if the current page is part of the Blogs component.
 */
function ajan_is_blogs_component() {
	if ( is_multisite() && ajan_is_current_component( 'blogs' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Messages component.
 *
 * @return bool True if the current page is part of the Messages component.
 */
function ajan_is_messages_component() {
	if ( ajan_is_current_component( 'messages' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Friends component.
 *
 * @return bool True if the current page is part of the Friends component.
 */
function ajan_is_friends_component() {
	if ( ajan_is_current_component( 'friends' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Groups component.
 *
 * @return bool True if the current page is part of the Groups component.
 */
function ajan_is_groups_component() {
	if ( ajan_is_current_component( 'groups' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Forums component.
 *
 * @return bool True if the current page is part of the Forums component.
 */
function ajan_is_forums_component() {
	if ( ajan_is_current_component( 'forums' ) )
		return true;

	return false;
}

/**
 * Check whether the current page is part of the Notifications component.
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @return bool True if the current page is part of the Notifications component.
 */
function ajan_is_notifications_component() {
	if ( ajan_is_current_component( 'notifications' ) ) {
		return true;
	}

	return false;
}

/**
 * Check whether the current page is part of the Settings component.
 *
 * @return bool True if the current page is part of the Settings component.
 */
function ajan_is_settings_component() {
	if ( ajan_is_current_component( 'settings' ) )
		return true;

	return false;
}

/**
 * Is the current component an active core component?
 *
 * Use this function when you need to check if the current component is an
 * active core component of ActivityNotifications. If the current component is inactive, it
 * will return false. If the current component is not part of ActivityNotifications core,
 * it will return false. If the current component is active, and is part of
 * ActivityNotifications core, it will return true.
 *
 * @return bool True if the current component is active and is one of BP's
 *         packaged components.
 */
function ajan_is_current_component_core() {
	$retval            = false;
	$active_components = apply_filters( 'ajan_active_components', ajan_get_option( 'ajan-active-components' ) );

	foreach ( array_keys( $active_components ) as $active_component ) {
		if ( ajan_is_current_component( $active_component ) ) {
			$retval = true;
			break;
		}
	}

	return $retval;
}

/** Activity ******************************************************************/

/**
 * Is the current page the activity directory ?
 *
 * @since ActivityNotifications (2.0.0)
 * 
 * @return True if the current page is the activity directory.
 */
function ajan_is_activity_directory() {
	if ( ! ajan_displayed_user_id() && ajan_is_activity_component() && ! ajan_current_action() )
		return true;

	return false;
}

/**
 * Is the current page a single activity item permalink?
 *
 * @return True if the current page is a single activity item permalink.
 */
function ajan_is_single_activity() {
	if ( ajan_is_activity_component() && is_numeric( ajan_current_action() ) )
		return true;

	return false;
}

/** User **********************************************************************/

/**
 * Is the current page the members directory ?
 *
 * @since ActivityNotifications (2.0.0)
 * 
 * @return True if the current page is the members directory.
 */
function ajan_is_members_directory() {
	if ( ! ajan_is_user() && ajan_is_members_component() )
		return true;

	return false;
}

/**
 * Is the current page part of the profile of the logged-in user?
 *
 * Will return true for any subpage of the logged-in user's profile, eg
 * http://example.com/members/joe/friends/.
 *
 * @return True if the current page is part of the profile of the logged-in user.
 */
function ajan_is_my_profile() {
	if ( is_user_logged_in() && ajan_loggedin_user_id() == ajan_displayed_user_id() )
		$my_profile = true;
	else
		$my_profile = false;

	return apply_filters( 'ajan_is_my_profile', $my_profile );
}

/**
 * Is the current page a user page?
 *
 * Will return true anytime there is a displayed user.
 *
 * @return True if the current page is a user page.
 */
function ajan_is_user() {
	if ( ajan_displayed_user_id() )
		return true;

	return false;
}

/**
 * Is the current page a user's activity stream page?
 *
 * Eg http://example.com/members/joe/activity/ (or any subpages thereof).
 *
 * @return True if the current page is a user's activity stream page.
 */
function ajan_is_user_activity() {
	if ( ajan_is_user() && ajan_is_activity_component() )
		return true;

	return false;
}

/**
 * Is the current page a user's Friends activity stream?
 *
 * Eg http://example.com/members/joe/friends/
 *
 * @return True if the current page is a user's Friends activity stream.
 */
function ajan_is_user_friends_activity() {

	if ( !ajan_is_active( 'friends' ) )
		return false;

	$slug = ajan_get_friends_slug();

	if ( empty( $slug ) )
		$slug = 'friends';

	if ( ajan_is_user_activity() && ajan_is_current_action( $slug ) )
		return true;

	return false;
}

/**
 * Is the current page a user's Groups activity stream?
 *
 * Eg http://example.com/members/joe/groups/
 *
 * @return True if the current page is a user's Groups activity stream.
 */
function ajan_is_user_groups_activity() {

	if ( !ajan_is_active( 'groups' ) )
		return false;

	$slug = ajan_get_groups_slug();

	if ( empty( $slug ) )
		$slug = 'groups';

	if ( ajan_is_user_activity() && ajan_is_current_action( $slug ) )
		return true;

	return false;
}

/**
 * Is the current page part of a user's extended profile?
 *
 * Eg http://example.com/members/joe/profile/ (or a subpage thereof).
 *
 * @return True if the current page is part of a user's extended profile.
 */
function ajan_is_user_profile() {
	if ( ajan_is_profile_component() || ajan_is_current_component( 'profile' ) )
		return true;

	return false;
}

/**
 * Is the current page part of a user's profile editing section?
 *
 * Eg http://example.com/members/joe/profile/edit/ (or a subpage thereof).
 *
 * @return True if the current page is a user's profile edit page.
 */
function ajan_is_user_profile_edit() {
	if ( ajan_is_profile_component() && ajan_is_current_action( 'edit' ) )
		return true;

	return false;
}

function ajan_is_user_change_avatar() {
	if ( ajan_is_profile_component() && ajan_is_current_action( 'change-avatar' ) )
		return true;

	return false;
}

/**
 * Is this a user's forums page?
 *
 * Eg http://example.com/members/joe/forums/ (or a subpage thereof).
 *
 * @return bool True if the current page is a user's forums page.
 */
function ajan_is_user_forums() {

	if ( ! ajan_is_active( 'forums' ) )
		return false;

	if ( ajan_is_user() && ajan_is_forums_component() )
		return true;

	return false;
}

/**
 * Is this a user's "Topics Started" page?
 *
 * Eg http://example.com/members/joe/forums/topics/.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @return bool True if the current page is a user's Topics Started page.
 */
function ajan_is_user_forums_started() {
	if ( ajan_is_user_forums() && ajan_is_current_action( 'topics' ) )
		return true;

	return false;
}

/**
 * Is this a user's "Replied To" page?
 *
 * Eg http://example.com/members/joe/forums/replies/.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @return bool True if the current page is a user's Replied To forums page.
 */
function ajan_is_user_forums_replied_to() {
	if ( ajan_is_user_forums() && ajan_is_current_action( 'replies' ) )
		return true;

	return false;
}

/**
 * Is the current page part of a user's Groups page?
 *
 * Eg http://example.com/members/joe/groups/ (or a subpage thereof).
 *
 * @return bool True if the current page is a user's Groups page.
 */
function ajan_is_user_groups() {
	if ( ajan_is_user() && ajan_is_groups_component() )
		return true;

	return false;
}

/**
 * Is the current page part of a user's Blogs page?
 *
 * Eg http://example.com/members/joe/blogs/ (or a subpage thereof).
 *
 * @return bool True if the current page is a user's Blogs page.
 */
function ajan_is_user_blogs() {
	if ( ajan_is_user() && ajan_is_blogs_component() )
		return true;

	return false;
}

/**
 * Is the current page a user's Recent Blog Posts page?
 *
 * Eg http://example.com/members/joe/blogs/recent-posts/.
 *
 * @return bool True if the current page is a user's Recent Blog Posts page.
 */
function ajan_is_user_recent_posts() {
	if ( ajan_is_user_blogs() && ajan_is_current_action( 'recent-posts' ) )
		return true;

	return false;
}

/**
 * Is the current page a user's Recent Blog Comments page?
 *
 * Eg http://example.com/members/joe/blogs/recent-comments/.
 *
 * @return bool True if the current page is a user's Recent Blog Comments page.
 */
function ajan_is_user_recent_commments() {
	if ( ajan_is_user_blogs() && ajan_is_current_action( 'recent-comments' ) )
		return true;

	return false;
}

/**
 * Is the current page a user's Friends page?
 *
 * Eg http://example.com/members/joe/blogs/friends/ (or a subpage thereof).
 *
 * @return bool True if the current page is a user's Friends page.
 */
function ajan_is_user_friends() {
	if ( ajan_is_user() && ajan_is_friends_component() )
		return true;

	return false;
}

/**
 * Is the current page a user's Friend Requests page?
 *
 * Eg http://example.com/members/joe/friends/requests/.
 *
 * @return bool True if the current page is a user's Friends Requests page.
 */
function ajan_is_user_friend_requests() {
	if ( ajan_is_user_friends() && ajan_is_current_action( 'requests' ) )
		return true;

	return false;
}

/**
 * Is this a user's notifications page?
 *
 * Eg http://example.com/members/joe/notifications/ (or a subpage thereof).
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @return bool True if the current page is a user's Notifications page.
 */
function ajan_is_user_notifications() {
	if ( ajan_is_user() && ajan_is_notifications_component() ) {
		return true;
	}

	return false;
}

/**
 * Is this a user's settings page?
 *
 * Eg http://example.com/members/joe/settings/ (or a subpage thereof).
 *
 * @return bool True if the current page is a user's Settings page.
 */
function ajan_is_user_settings() {
	if ( ajan_is_user() && ajan_is_settings_component() )
		return true;

	return false;
}

/**
 * Is this a user's General Settings page?
 *
 * Eg http://example.com/members/joe/settings/general/.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @return bool True if the current page is a user's General Settings page.
 */
function ajan_is_user_settings_general() {
	if ( ajan_is_user_settings() && ajan_is_current_action( 'general' ) )
		return true;

	return false;
}

/**
 * Is this a user's Notification Settings page?
 *
 * Eg http://example.com/members/joe/settings/notifications/.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @return bool True if the current page is a user's Notification Settings page.
 */
function ajan_is_user_settings_notifications() {
	if ( ajan_is_user_settings() && ajan_is_current_action( 'notifications' ) )
		return true;

	return false;
}

/**
 * Is this a user's Account Deletion page?
 *
 * Eg http://example.com/members/joe/settings/delete-account/.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @return bool True if the current page is a user's Delete Account page.
 */
function ajan_is_user_settings_account_delete() {
	if ( ajan_is_user_settings() && ajan_is_current_action( 'delete-account' ) )
		return true;

	return false;
}

/**
 * Is this a user's profile settings?
 *
 * Eg http://example.com/members/joe/settings/profile/.
 *
 * @since ActivityNotifications (2.0.0)
 *
 * @return bool True if the current page is a user's Profile Settings page.
 */
function ajan_is_user_settings_profile() {
	if ( ajan_is_user_settings() && ajan_is_current_action( 'profile' ) )
		return true;

	return false;
}

/** Groups ********************************************************************/

/**
 * Is the current page the groups directory ?
 *
 * @since ActivityNotifications (2.0.0)
 * 
 * @return True if the current page is the groups directory.
 */
function ajan_is_groups_directory() {
	if ( ajan_is_groups_component() && ! ajan_current_action() && ! ajan_current_item() )
		return true;

	return false;
}

/**
 * Does the current page belong to a single group?
 *
 * Will return true for any subpage of a single group.
 *
 * @return bool True if the current page is part of a single group.
 */
function ajan_is_group() {
	global $ajan;

	if ( ajan_is_groups_component() && isset( $ajan->groups->current_group ) && $ajan->groups->current_group )
		return true;

	return false;
}

/**
 * Is the current page a single group's home page?
 *
 * URL will vary depending on which group tab is set to be the "home". By
 * default, it's the group's recent activity.
 *
 * @return bool True if the current page is a single group's home page.
 */
function ajan_is_group_home() {
	if ( ajan_is_single_item() && ajan_is_groups_component() && ( !ajan_current_action() || ajan_is_current_action( 'home' ) ) )
		return true;

	return false;
}

/**
 * Is the current page part of the group creation process?
 *
 * @return bool True if the current page is part of the group creation process.
 */
function ajan_is_group_create() {
	if ( ajan_is_groups_component() && ajan_is_current_action( 'create' ) )
		return true;

	return false;
}

/**
 * Is the current page part of a single group's admin screens?
 *
 * Eg http://example.com/groups/mygroup/admin/settings/.
 *
 * @return bool True if the current page is part of a single group's admin.
 */
function ajan_is_group_admin_page() {
	if ( ajan_is_single_item() && ajan_is_groups_component() && ajan_is_current_action( 'admin' ) )
		return true;

	return false;
}

/**
 * Is the current page a group's forum page?
 *
 * Only applies to legacy bbPress forums.
 *
 * @return bool True if the current page is a group forum page.
 */
function ajan_is_group_forum() {
	$retval = false;

	// At a forum URL
	if ( ajan_is_single_item() && ajan_is_groups_component() && ajan_is_current_action( 'forum' ) ) {
		$retval = true;

		// If at a forum URL, set back to false if forums are inactive, or not
		// installed correctly.
		if ( ! ajan_is_active( 'forums' ) || ! ajan_forums_is_installed_correctly() ) {
			$retval = false;
		}
	}

	return $retval;
}

/**
 * Is the current page a group's activity page?
 *
 * @return True if the current page is a group's activity page.
 */
function ajan_is_group_activity() {
	if ( ajan_is_single_item() && ajan_is_groups_component() && ajan_is_current_action( 'activity' ) )
		return true;

	return false;
}

/**
 * Is the current page a group forum topic?
 *
 * Only applies to legacy bbPress (1.x) forums.
 *
 * @return bool True if the current page is part of a group forum topic.
 */
function ajan_is_group_forum_topic() {
	if ( ajan_is_single_item() && ajan_is_groups_component() && ajan_is_current_action( 'forum' ) && ajan_is_action_variable( 'topic', 0 ) )
		return true;

	return false;
}

/**
 * Is the current page a group forum topic edit page?
 *
 * Only applies to legacy bbPress (1.x) forums.
 *
 * @return bool True if the current page is part of a group forum topic edit page.
 */
function ajan_is_group_forum_topic_edit() {
	if ( ajan_is_single_item() && ajan_is_groups_component() && ajan_is_current_action( 'forum' ) && ajan_is_action_variable( 'topic', 0 ) && ajan_is_action_variable( 'edit', 2 ) )
		return true;

	return false;
}

/**
 * Is the current page a group's Members page?
 *
 * Eg http://example.com/groups/mygroup/members/.
 *
 * @return bool True if the current page is part of a group's Members page.
 */
function ajan_is_group_members() {
	if ( ajan_is_single_item() && ajan_is_groups_component() && ajan_is_current_action( 'members' ) )
		return true;

	return false;
}

/**
 * Is the current page a group's Invites page?
 *
 * Eg http://example.com/groups/mygroup/send-invites/.
 *
 * @return bool True if the current page is a group's Send Invites page.
 */
function ajan_is_group_invites() {
	if ( ajan_is_groups_component() && ajan_is_current_action( 'send-invites' ) )
		return true;

	return false;
}

/**
 * Is the current page a group's Request Membership page?
 *
 * Eg http://example.com/groups/mygroup/request-membership/.
 *
 * @return bool True if the current page is a group's Request Membership page.
 */
function ajan_is_group_membership_request() {
	if ( ajan_is_groups_component() && ajan_is_current_action( 'request-membership' ) )
		return true;

	return false;
}

/**
 * Is the current page a leave group attempt?
 *
 * @return bool True if the current page is a Leave Group attempt.
 */
function ajan_is_group_leave() {

	if ( ajan_is_groups_component() && ajan_is_single_item() && ajan_is_current_action( 'leave-group' ) )
		return true;

	return false;
}

/**
 * Is the current page part of a single group?
 *
 * Not currently used by ActivityNotifications.
 *
 * @todo How is this functionally different from ajan_is_group()?
 *
 * @return bool True if the current page is part of a single group.
 */
function ajan_is_group_single() {
	if ( ajan_is_groups_component() && ajan_is_single_item() )
		return true;

	return false;
}

/**
 * Is the current page the Create a Blog page?
 *
 * Eg http://example.com/sites/create/.
 *
 * @return bool True if the current page is the Create a Blog page.
 */
function ajan_is_create_blog() {
	if ( ajan_is_blogs_component() && ajan_is_current_action( 'create' ) )
		return true;

	return false;
}

/**
 * Is the current page the blogs directory ?
 *
 * @since ActivityNotifications (2.0.0)
 * 
 * @return True if the current page is the blogs directory.
 */
function ajan_is_blogs_directory() {
	if ( is_multisite() && ajan_is_blogs_component() && ! ajan_current_action() )
		return true;

	return false;
}

/** Messages ******************************************************************/

/**
 * Is the current page part of a user's Messages pages?
 *
 * Eg http://example.com/members/joe/messages/ (or a subpage thereof).
 *
 * @return bool True if the current page is part of a user's Messages pages.
 */
function ajan_is_user_messages() {
	if ( ajan_is_user() && ajan_is_messages_component() )
		return true;

	return false;
}

/**
 * Is the current page a user's Messages Inbox?
 *
 * Eg http://example.com/members/joe/messages/inbox/.
 *
 * @return bool True if the current page is a user's Messages Inbox.
 */
function ajan_is_messages_inbox() {
	if ( ajan_is_user_messages() && ( !ajan_current_action() || ajan_is_current_action( 'inbox' ) ) )
		return true;

	return false;
}

/**
 * Is the current page a user's Messages Sentbox?
 *
 * Eg http://example.com/members/joe/messages/sentbox/.
 *
 * @return bool True if the current page is a user's Messages Sentbox.
 */
function ajan_is_messages_sentbox() {
	if ( ajan_is_user_messages() && ajan_is_current_action( 'sentbox' ) )
		return true;

	return false;
}

/**
 * Is the current page a user's Messages Compose screen??
 *
 * Eg http://example.com/members/joe/messages/compose/.
 *
 * @return bool True if the current page is a user's Messages Compose screen.
 */
function ajan_is_messages_compose_screen() {
	if ( ajan_is_user_messages() && ajan_is_current_action( 'compose' ) )
		return true;

	return false;
}

/**
 * Is the current page the Notices screen?
 *
 * Eg http://example.com/members/joe/messages/notices/.
 *
 * @return bool True if the current page is the Notices screen.
 */
function ajan_is_notices() {
	if ( ajan_is_user_messages() && ajan_is_current_action( 'notices' ) )
		return true;

	return false;
}

/**
 * Is the current page a single Messages conversation thread?
 *
 * @return bool True if the current page a single Messages conversation thread?
 */
function ajan_is_messages_conversation() {
	if ( ajan_is_user_messages() && ( ajan_is_current_action( 'view' ) ) )
		return true;

	return false;
}

/**
 * Not currently used by ActivityNotifications.
 *
 * @return bool
 */
function ajan_is_single( $component, $callback ) {
	if ( ajan_is_current_component( $component ) && ( true === call_user_func( $callback ) ) )
		return true;

	return false;
}

/** Registration **************************************************************/

/**
 * Is the current page the Activate page?
 *
 * Eg http://example.com/activate/.
 *
 * @return bool True if the current page is the Activate page.
 */
function ajan_is_activation_page() {
	if ( ajan_is_current_component( 'activate' ) )
		return true;

	return false;
}

/**
 * Is the current page the Register page?
 *
 * Eg http://example.com/register/.
 *
 * @return bool True if the current page is the Register page.
 */
function ajan_is_register_page() {
	if ( ajan_is_current_component( 'register' ) )
		return true;

	return false;
}

/**
 * Customize the body class, according to the currently displayed BP content.
 *
 * Uses the above is_() functions to output a body class for each scenario.
 *
 * @param array $wp_classes The body classes coming from WP.
 * @param array $custom_classes Classes that were passed to get_body_class().
 * @return array $classes The BP-adjusted body classes.
 */
function ajan_the_body_class() {
	echo ajan_get_the_body_class();
}
	function ajan_get_the_body_class( $wp_classes = array(), $custom_classes = false ) {

		$ajan_classes = array();

		/** Pages *************************************************************/

		if ( is_front_page() )
			$ajan_classes[] = 'home-page';

		if ( ajan_is_directory() )
			$ajan_classes[] = 'directory';

		if ( ajan_is_single_item() )
			$ajan_classes[] = 'single-item';

		/** Components ********************************************************/

		if ( !ajan_is_blog_page() ) :
			if ( ajan_is_user_profile() )
				$ajan_classes[] = 'xprofile';

			if ( ajan_is_activity_component() )
				$ajan_classes[] = 'activity';

			if ( ajan_is_blogs_component() )
				$ajan_classes[] = 'blogs';

			if ( ajan_is_messages_component() )
				$ajan_classes[] = 'messages';

			if ( ajan_is_friends_component() )
				$ajan_classes[] = 'friends';

			if ( ajan_is_groups_component() )
				$ajan_classes[] = 'groups';

			if ( ajan_is_settings_component()  )
				$ajan_classes[] = 'settings';
		endif;

		/** User **************************************************************/

		if ( ajan_is_user() )
			$ajan_classes[] = 'ajan-user';

		if ( !ajan_is_directory() ) :
			if ( ajan_is_user_blogs() )
				$ajan_classes[] = 'my-blogs';

			if ( ajan_is_user_groups() )
				$ajan_classes[] = 'my-groups';

			if ( ajan_is_user_activity() )
				$ajan_classes[] = 'my-activity';
		endif;

		if ( ajan_is_my_profile() )
			$ajan_classes[] = 'my-account';

		if ( ajan_is_user_profile() )
			$ajan_classes[] = 'my-profile';

		if ( ajan_is_user_friends() )
			$ajan_classes[] = 'my-friends';

		if ( ajan_is_user_messages() )
			$ajan_classes[] = 'my-messages';

		if ( ajan_is_user_recent_commments() )
			$ajan_classes[] = 'recent-comments';

		if ( ajan_is_user_recent_posts() )
			$ajan_classes[] = 'recent-posts';

		if ( ajan_is_user_change_avatar() )
			$ajan_classes[] = 'change-avatar';

		if ( ajan_is_user_profile_edit() )
			$ajan_classes[] = 'profile-edit';

		if ( ajan_is_user_friends_activity() )
			$ajan_classes[] = 'friends-activity';

		if ( ajan_is_user_groups_activity() )
			$ajan_classes[] = 'groups-activity';

		/** Messages **********************************************************/

		if ( ajan_is_messages_inbox() )
			$ajan_classes[] = 'inbox';

		if ( ajan_is_messages_sentbox() )
			$ajan_classes[] = 'sentbox';

		if ( ajan_is_messages_compose_screen() )
			$ajan_classes[] = 'compose';

		if ( ajan_is_notices() )
			$ajan_classes[] = 'notices';

		if ( ajan_is_user_friend_requests() )
			$ajan_classes[] = 'friend-requests';

		if ( ajan_is_create_blog() )
			$ajan_classes[] = 'create-blog';

		/** Groups ************************************************************/

		if ( ajan_is_group_leave() )
			$ajan_classes[] = 'leave-group';

		if ( ajan_is_group_invites() )
			$ajan_classes[] = 'group-invites';

		if ( ajan_is_group_members() )
			$ajan_classes[] = 'group-members';

		if ( ajan_is_group_forum_topic() )
			$ajan_classes[] = 'group-forum-topic';

		if ( ajan_is_group_forum_topic_edit() )
			$ajan_classes[] = 'group-forum-topic-edit';

		if ( ajan_is_group_forum() )
			$ajan_classes[] = 'group-forum';

		if ( ajan_is_group_admin_page() ) {
			$ajan_classes[] = 'group-admin';
			$ajan_classes[] = ajan_get_group_current_admin_tab();
		}

		if ( ajan_is_group_create() ) {
			$ajan_classes[] = 'group-create';
			$ajan_classes[] = ajan_get_groups_current_create_step();
		}

		if ( ajan_is_group_home() )
			$ajan_classes[] = 'group-home';

		if ( ajan_is_single_activity() )
			$ajan_classes[] = 'activity-permalink';

		/** Registration ******************************************************/

		if ( ajan_is_register_page() )
			$ajan_classes[] = 'registration';

		if ( ajan_is_activation_page() )
			$ajan_classes[] = 'activation';

		/** Current Component & Action ****************************************/

		if ( !ajan_is_blog_page() ) {
			$ajan_classes[] = ajan_current_component();
			$ajan_classes[] = ajan_current_action();
		}

		/** Clean up ***********************************************************/

		// Add ActivityNotifications class if we are within a ActivityNotifications page
		if ( ! ajan_is_blog_page() ) {
			$ajan_classes[] = 'ajency-activity-and-notifications';
		}

		// Merge WP classes with ActivityNotifications classes and remove any duplicates
		$classes = array_unique( array_merge( (array) $ajan_classes, (array) $wp_classes ) );

		return apply_filters( 'ajan_get_the_body_class', $classes, $ajan_classes, $wp_classes, $custom_classes );
	}
	add_filter( 'body_class', 'ajan_get_the_body_class', 10, 2 );

/**
 * Sort ActivityNotifications nav menu items by their position property.
 *
 * This is an internal convenience function and it will probably be removed in
 * a later release. Do not use.
 *
 * @access private
 * @since ActivityNotifications (1.7.0)
 *
 * @param array $a First item.
 * @param array $b Second item.
 * @return int Returns an integer less than, equal to, or greater than zero if
 *         the first argument is considered to be respectively less than, equal to, or greater than the second.
 */
function _ajan_nav_menu_sort( $a, $b ) {
	if ( $a["position"] == $b["position"] )
		return 0;

	else if ( $a["position"] < $b["position"] )
		return -1;

	else
		return 1;
}

/**
 * Get the items registered in the primary and secondary ActivityNotifications navigation menus.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @return array A multidimensional array of all navigation items.
 */
function ajan_get_nav_menu_items() {
	$menus = $selected_menus = array();

	// Get the second level menus
	foreach ( (array) activitynotifications()->ajan_options_nav as $parent_menu => $sub_menus ) {

		// The root menu's ID is "xprofile", but the Profile submenus are using "profile". See AJAN_Core::setup_nav().
		if ( 'profile' == $parent_menu )
			$parent_menu = 'xprofile';

		// Sort the items in this menu's navigation by their position property
		$second_level_menus = (array) $sub_menus;
		usort( $second_level_menus, '_ajan_nav_menu_sort' );

		// Iterate through the second level menus
		foreach( $second_level_menus as $sub_nav ) {

			// Skip items we don't have access to
			if ( ! $sub_nav['user_has_access'] )
				continue;

			// Add this menu
			$menu         = new stdClass;
			$menu->class  = array();
			$menu->css_id = $sub_nav['css_id'];
			$menu->link   = $sub_nav['link'];
			$menu->name   = $sub_nav['name'];
			$menu->parent = $parent_menu;  // Associate this sub nav with a top-level menu

			// If we're viewing this item's screen, record that we need to mark its parent menu to be selected
			if ( $sub_nav['slug'] == ajan_current_action() ) {
				$menu->class      = array( 'current-menu-item' );
				$selected_menus[] = $parent_menu;
			}

			$menus[] = $menu;
		}
	}

	// Get the top-level menu parts (Friends, Groups, etc) and sort by their position property
	$top_level_menus = (array) activitynotifications()->ajan_nav;
	usort( $top_level_menus, '_ajan_nav_menu_sort' );

	// Iterate through the top-level menus
	foreach ( $top_level_menus as $nav ) {

		// Skip items marked as user-specific if you're not on your own profile
		if ( ! $nav['show_for_displayed_user'] && ! ajan_core_can_edit_settings()  )
			continue;

		// Get the correct menu link. See http://buddypress.trac.wordpress.org/ticket/4624
		$link = ajan_loggedin_user_domain() ? str_replace( ajan_loggedin_user_domain(), ajan_displayed_user_domain(), $nav['link'] ) : trailingslashit( ajan_displayed_user_domain() . $nav['link'] );

		// Add this menu
		$menu         = new stdClass;
		$menu->class  = array();
		$menu->css_id = $nav['css_id'];
		$menu->link   = $link;
		$menu->name   = $nav['name'];
		$menu->parent = 0;

		// Check if we need to mark this menu as selected
		if ( in_array( $nav['css_id'], $selected_menus ) )
			$menu->class = array( 'current-menu-parent' );

		$menus[] = $menu;
	}

	return apply_filters( 'ajan_get_nav_menu_items', $menus );
}

/**
 * Display a navigation menu.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string|array $args {
 *     An array of optional arguments.
 *     @type string $after Text after the link text. Default: ''.
 *     @type string $before Text before the link text. Default: ''.
 *     @type string $container The name of the element to wrap the navigation
 *           with. 'div' or 'nav'. Default: 'div'.
 *     @type string $container_class The class that is applied to the container.
 *           Default: 'menu-ajan-container'.
 *     @type string $container_id The ID that is applied to the container.
 *           Default: ''.
 *     @type int depth How many levels of the hierarchy are to be included. 0
 *           means all. Default: 0.
 *     @type bool $echo True to echo the menu, false to return it.
 *           Default: true.
 *     @type bool $fallback_cb If the menu doesn't exist, should a callback
 *           function be fired? Default: false (no fallback).
 *     @type string $items_wrap How the list items should be wrapped. Should be
 *           in the form of a printf()-friendly string, using numbered
 *           placeholders. Default: '<ul id="%1$s" class="%2$s">%3$s</ul>'.
 *     @type string $link_after Text after the link. Default: ''.
 *     @type string $link_before Text before the link. Default: ''.
 *     @type string $menu_class CSS class to use for the <ul> element which
 *           forms the menu. Default: 'menu'.
 *     @type string $menu_id The ID that is applied to the <ul> element which
 *           forms the menu. Default: 'menu-bp', incremented.
 *     @type string $walker Allows a custom walker class to be specified.
 *           Default: 'AJAN_Walker_Nav_Menu'.
 * }
 * @return string|null If $echo is false, returns a string containing the nav
 *         menu markup.
 */
function ajan_nav_menu( $args = array() ) {
	static $menu_id_slugs = array();

	$defaults = array(
		'after'           => '',
		'before'          => '',
		'container'       => 'div',
		'container_class' => '',
		'container_id'    => '',
		'depth'           => 0,
		'echo'            => true,
		'fallback_cb'     => false,
		'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'link_after'      => '',
		'link_before'     => '',
		'menu_class'      => 'menu',
		'menu_id'         => '',
		'walker'          => '',
	);
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'ajan_nav_menu_args', $args );
	$args = (object) $args;

	$items = $nav_menu = '';
	$show_container = false;

	// Create custom walker if one wasn't set
	if ( empty( $args->walker ) )
		$args->walker = new AJAN_Walker_Nav_Menu;

	// Sanitise values for class and ID
	$args->container_class = sanitize_html_class( $args->container_class );
	$args->container_id    = sanitize_html_class( $args->container_id );

	// Whether to wrap the ul, and what to wrap it with
	if ( $args->container ) {
		$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav', ) );

		if ( in_array( $args->container, $allowed_tags ) ) {
			$show_container = true;

			$class     = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-ajan-container"';
			$id        = $args->container_id    ? ' id="' . esc_attr( $args->container_id ) . '"'       : '';
			$nav_menu .= '<' . $args->container . $id . $class . '>';
		}
	}

	// Get the ActivityNotifications menu items
	$menu_items = apply_filters( 'ajan_nav_menu_objects', ajan_get_nav_menu_items(), $args );
	$items      = walk_nav_menu_tree( $menu_items, $args->depth, $args );
	unset( $menu_items );

	// Set the ID that is applied to the ul element which forms the menu.
	if ( ! empty( $args->menu_id ) ) {
		$wrap_id = $args->menu_id;

	} else {
		$wrap_id = 'menu-bp';

		// If a specific ID wasn't requested, and there are multiple menus on the same screen, make sure the autogenerated ID is unique
		while ( in_array( $wrap_id, $menu_id_slugs ) ) {
			if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) )
				$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
			else
				$wrap_id = $wrap_id . '-1';
		}
	}
	$menu_id_slugs[] = $wrap_id;

	// Allow plugins to hook into the menu to add their own <li>'s
	$items = apply_filters( 'ajan_nav_menu_items', $items, $args );

	// Build the output
	$wrap_class  = $args->menu_class ? $args->menu_class : '';
	$nav_menu   .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
	unset( $items );

	// If we've wrapped the ul, close it
	if ( $show_container )
		$nav_menu .= '</' . $args->container . '>';

	// Final chance to modify output
	$nav_menu = apply_filters( 'ajan_nav_menu', $nav_menu, $args );

	if ( $args->echo )
		echo $nav_menu;
	else
		return $nav_menu;
}
