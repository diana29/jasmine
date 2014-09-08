<?php

/**
 * Plugin Dependency Action Hooks.
 *
 * The purpose of the following hooks is to mimic the behavior of something
 * called 'plugin dependency' which enables a plugin to have plugins of their
 * own in a safe and reliable way.
 *
 * We do this in ActivityNotifications by mirroring existing WordPress hooks in many places
 * allowing dependant plugins to hook into the ActivityNotifications specific ones, thus
 * guaranteeing proper code execution only when ActivityNotifications is active.
 *
 * The following functions are wrappers for hooks, allowing them to be
 * manually called and/or piggy-backed on top of other hooks if needed.
 *
 * @todo use anonymous functions when PHP minimun requirement allows (5.3)
 */

/**
 * Fire the 'ajan_include' action, where plugins should include files.
 */
function ajan_include() {
	do_action( 'ajan_include' );
}

/**
 * Fire the 'ajan_setup_components' action, where plugins should initialize components.
 */
function ajan_setup_components() {
	do_action( 'ajan_setup_components' );
}

/**
 * Fire the 'ajan_setup_globals' action, where plugins should initialize global settings.
 */
function ajan_setup_globals() {
	do_action( 'ajan_setup_globals' );
}

/**
 * Fire the 'ajan_setup_nav' action, where plugins should register their navigation items.
 */
function ajan_setup_nav() {
	do_action( 'ajan_setup_nav' );
}

/**
 * Fire the 'ajan_setup_admin_bar' action, where plugins should add items to the WP admin bar.
 */
function ajan_setup_admin_bar() {
	if ( ajan_use_wp_admin_bar() )
		do_action( 'ajan_setup_admin_bar' );
}

/**
 * Fire the 'ajan_setup_title' action, where plugins should modify the page title.
 */
function ajan_setup_title() {
	do_action( 'ajan_setup_title' );
}

/**
 * Fire the 'ajan_register_widgets' action, where plugins should register widgets.
 */
function ajan_setup_widgets() {
	do_action( 'ajan_register_widgets' );
}

/**
 * Set up the currently logged-in user.
 *
 * @uses did_action() To make sure the user isn't loaded out of order.
 * @uses do_action() Calls 'ajan_setup_current_user'.
 */
function ajan_setup_current_user() {

	// If the current user is being setup before the "init" action has fired,
	// strange (and difficult to debug) role/capability issues will occur.
	if ( ! did_action( 'after_setup_theme' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'The current user is being initialized without using $wp->init().', 'ajency-activity-and-notifications' ), '1.7' );
	}

	do_action( 'ajan_setup_current_user' );
}

/**
 * Fire the 'ajan_init' action, ActivityNotifications's main initialization hook.
 */
function ajan_init() {
	do_action( 'ajan_init' );
}

/**
 * Fire the 'ajan_loaded' action, which fires after BP's core plugin files have been loaded.
 *
 * Attached to 'plugins_loaded'.
 */
function ajan_loaded() {
	do_action( 'ajan_loaded' );
}

/**
 * Fire the 'ajan_ready' action, which runs after BP is set up and the page is about to render.
 *
 * Attached to 'wp'.
 */
function ajan_ready() {
	do_action( 'ajan_ready' );
}

/**
 * Fire the 'ajan_actions' action, which runs just before rendering.
 *
 * Attach potential template actions, such as catching form requests or routing
 * custom URLs.
 */
function ajan_actions() {
	do_action( 'ajan_actions' );
}

/**
 * Fire the 'ajan_screens' action, which runs just before rendering.
 *
 * Runs just after 'ajan_actions'. Use this hook to attach your template
 * loaders.
 */
function ajan_screens() {
	do_action( 'ajan_screens' );
}

/**
 * Fire 'ajan_widgets_init', which runs after widgets have been set up.
 *
 * Hooked to 'widgets_init'.
 */
function ajan_widgets_init() {
	do_action ( 'ajan_widgets_init' );
}

/**
 * Fire 'ajan_head', which is used to hook scripts and styles in the <head>.
 *
 * Hooked to 'wp_head'.
 */
function ajan_head() {
	do_action ( 'ajan_head' );
}

/** Theme Permissions *********************************************************/

/**
 * Fire the 'ajan_template_redirect' action.
 *
 * Run at 'template_redirect', just before WordPress selects and loads a theme
 * template. The main purpose of this hook in ActivityNotifications is to redirect users
 * who do not have the proper permission to access certain content.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action()
 */
function ajan_template_redirect() {
	do_action( 'ajan_template_redirect' );
}

/** Theme Helpers *************************************************************/

/**
 * Fire the 'ajan_register_theme_directory' action.
 *
 * The main action used registering theme directories.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @uses do_action()
 */
function ajan_register_theme_directory() {
	do_action( 'ajan_register_theme_directory' );
}

/**
 * Fire the 'ajan_register_theme_packages' action.
 *
 * The main action used registering theme packages.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses do_action()
 */
function ajan_register_theme_packages() {
	do_action( 'ajan_register_theme_packages' );
}

/**
 * Fire the 'ajan_enqueue_scripts' action, where BP enqueues its CSS and JS.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action() Calls 'ajan_enqueue_scripts'.
 */
function ajan_enqueue_scripts() {
	do_action ( 'ajan_enqueue_scripts' );
}

/**
 * Fire the 'ajan_add_rewrite_tag' action, where BP adds its custom rewrite tags.
 *
 * @since ActivityNotifications (1.8.0)
 *
 * @uses do_action() Calls 'ajan_add_rewrite_tags'.
 */
function ajan_add_rewrite_tags() {
	do_action( 'ajan_add_rewrite_tags' );
}

/**
 * Fire the 'ajan_add_rewrite_rules' action, where BP adds its custom rewrite rules.
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @uses do_action() Calls 'ajan_add_rewrite_rules'.
 */
function ajan_add_rewrite_rules() {
	do_action( 'ajan_add_rewrite_rules' );
}

/**
 * Fire the 'ajan_add_permastructs' action, where BP adds its BP-specific permalink structure.
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @uses do_action() Calls 'ajan_add_permastructs'.
 */
function ajan_add_permastructs() {
	do_action( 'ajan_add_permastructs' );
}

/**
 * Fire the 'ajan_setup_theme' action.
 *
 * The main purpose of 'ajan_setup_theme' is give themes a place to load their
 * ActivityNotifications-specific functionality.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action() Calls 'ajan_setup_theme'.
 */
function ajan_setup_theme() {
	do_action ( 'ajan_setup_theme' );
}

/**
 * Fire the 'ajan_after_setup_theme' action.
 *
 * Piggy-back action for ActivityNotifications-specific theme actions once the theme has
 * been set up and the theme's functions.php has loaded.
 *
 * Hooked to 'after_setup_theme' with a priority of 100. This allows plenty of
 * time for other themes to load their features, such as ActivityNotifications support,
 * before our theme compatibility layer kicks in.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action() Calls 'ajan_after_setup_theme'.
 */
function ajan_after_setup_theme() {
	do_action ( 'ajan_after_setup_theme' );
}

/** Theme Compatibility Filter ************************************************/

/**
 * Fire the 'ajan_request' filter, a piggy-back of WP's 'request'.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @see WP::parse_request() for a description of parameters.
 *
 * @param array $query_vars See {@link WP::parse_request()}.
 * @return array $query_vars See {@link WP::parse_request()}.
 */
function ajan_request( $query_vars = array() ) {
	return apply_filters( 'ajan_request', $query_vars );
}

/**
 * Fire the 'ajan_login_redirect' filter, a piggy-back of WP's 'login_redirect'.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $redirect_to See 'login_redirect'.
 * @param string $redirect_to_raw See 'login_redirect'.
 * @param string $user See 'login_redirect'.
 */
function ajan_login_redirect( $redirect_to = '', $redirect_to_raw = '', $user = false ) {
	return apply_filters( 'ajan_login_redirect', $redirect_to, $redirect_to_raw, $user );
}

/**
 * Fire 'ajan_template_include', main filter used for theme compatibility and displaying custom BP theme files.
 *
 * Hooked to 'template_include'.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses apply_filters()
 *
 * @param string $template See 'template_include'.
 * @return string Template file to use.
 */
function ajan_template_include( $template = '' ) {
	return apply_filters( 'ajan_template_include', $template );
}

/**
 * Fire the 'ajan_generate_rewrite_rules' filter, where BP generates its rewrite rules.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses do_action() Calls 'ajan_generate_rewrite_rules' with {@link WP_Rewrite}.
 *
 * @param WP_Rewrite $wp_rewrite See 'generate_rewrite_rules'.
 */
function ajan_generate_rewrite_rules( $wp_rewrite ) {
	do_action_ref_array( 'ajan_generate_rewrite_rules', array( &$wp_rewrite ) );
}

/**
 * Fire the 'ajan_allowed_themes' filter.
 *
 * Filter the allowed themes list for ActivityNotifications-specific themes.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses apply_filters() Calls 'ajan_allowed_themes' with the allowed themes list.
 */
function ajan_allowed_themes( $themes ) {
	return apply_filters( 'ajan_allowed_themes', $themes );
}

/** Requests ******************************************************************/

/**
 * The main action used for handling theme-side POST requests
 *
 * @since ActivityNotifications (1.9.0)
 * @uses do_action()
 */
function ajan_post_request() {

	// Bail if not a POST action
	if ( ! ajan_is_post_request() )
		return;

	// Bail if no action
	if ( empty( $_POST['action'] ) )
		return;

	// This dynamic action is probably the one you want to use. It narrows down
	// the scope of the 'action' without needing to check it in your function.
	do_action( 'ajan_post_request_' . $_POST['action'] );

	// Use this static action if you don't mind checking the 'action' yourself.
	do_action( 'ajan_post_request',   $_POST['action'] );
}

/**
 * The main action used for handling theme-side GET requests
 *
 * @since ActivityNotifications (1.9.0)
 * @uses do_action()
 */
function ajan_get_request() {

	// Bail if not a POST action
	if ( ! ajan_is_get_request() )
		return;

	// Bail if no action
	if ( empty( $_GET['action'] ) )
		return;

	// This dynamic action is probably the one you want to use. It narrows down
	// the scope of the 'action' without needing to check it in your function.
	do_action( 'ajan_get_request_' . $_GET['action'] );

	// Use this static action if you don't mind checking the 'action' yourself.
	do_action( 'ajan_get_request',   $_GET['action'] );
}

/* functions added below are from members section

ajan_is_user_deleted( $user_id = 0 )
ajan_is_user_inactive( $user_id = 0 )
ajan_is_user_active( $user_id = 0 )
ajan_get_user_last_activity( $user_id = 0 )
ajan_update_user_last_activity( $user_id = 0, $time = '' )
_ajan_get_user_meta_last_activity_warning( $retval, $object_id, $meta_key )
ajan_displayed_user_domain()

*/

/**
 * Checks if the user has been marked as deleted.
 * 
 * @param int $user_id int The id for the user.
 * @return bool True if deleted, False if not.
 */
function ajan_is_user_deleted( $user_id = 0 ) {

	// No user to check
	if ( empty( $user_id ) )
		return false;

	$ajan = activitynotifications();

	// Assume user is not deleted
	$is_deleted = false;

	// Setup our user
	$user = false;

	// Get locally-cached data if available
	switch ( $user_id ) {
		case ajan_loggedin_user_id() :
			$user = ! empty( $ajan->loggedin_user->userdata ) ? $ajan->loggedin_user->userdata : false;
			break;

		case ajan_displayed_user_id() :
			$user = ! empty( $ajan->displayed_user->userdata ) ? $ajan->displayed_user->userdata : false;
			break;
	}

	// Manually get userdata if still empty
	if ( empty( $user ) ) {
		$user = get_userdata( $user_id );
	}

	// No user found
	if ( empty( $user ) ) {
		$is_deleted = true;

	// User found
	} else {

		// Check if deleted
		if ( !empty( $user->deleted ) )
			$is_deleted = true;

		if ( 2 == $user->user_status )
			$is_deleted = true;

	}

	return apply_filters( 'ajan_is_user_deleted', (bool) $is_deleted );
}


/**
 * Checks if user is not active.
 * 
 *
 * @uses is_user_logged_in() To check if user is logged in
 * @uses ajan_get_displayed_user_id() To get current user ID
 * @uses ajan_is_user_active() To check if user is active
 *
 * @param int $user_id The user ID to check
 * @return bool True if inactive, false if active
 */
function ajan_is_user_inactive( $user_id = 0 ) {

	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = ajan_loggedin_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Return the inverse of active
	return !ajan_is_user_active( $user_id );
}
/**
 * Checks if user is active
 *
 * @since BuddyPress (1.6)
 *
 * @uses is_user_logged_in() To check if user is logged in
 * @uses ajan_loggedin_user_id() To get current user ID
 * @uses ajan_is_user_spammer() To check if user is spammer
 * @uses ajan_is_user_deleted() To check if user is deleted
 *
 * @param int $user_id The user ID to check
 * @return bool True if public, false if not
 */
function ajan_is_user_active( $user_id = 0 ) {

	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = ajan_loggedin_user_id();

	// No user to check
	if ( empty( $user_id ) )
		return false;
 

	// Check deleted
	if ( ajan_is_user_deleted( $user_id ) )
		return false;

	// Assume true if not spam or deleted
	return true;
}


/**
 * Get the last activity for a given user.
 *
 * @param int $user_id The ID of the user.
 * @return string Time of last activity, in 'Y-m-d H:i:s' format, or an empty
 *         string if none is found.
 */
function ajan_get_user_last_activity( $user_id = 0 ) {
	$activity = '';

	$last_activity = AJAN_Core_User::get_last_activity( $user_id );
	if ( ! empty( $last_activity[ $user_id ] ) ) {
		$activity = $last_activity[ $user_id ]['date_recorded'];
	}

	return apply_filters( 'ajan_get_user_last_activity', $activity, $user_id );
}

/**
 * Update a user's last activity.
 * 
 *
 * @param int $user_id ID of the user being updated.
 * @param string $time Time of last activity, in 'Y-m-d H:i:s' format.
 * @return bool True on success, false on failure.
 */
function ajan_update_user_last_activity( $user_id = 0, $time = '' ) {
	// Fall back on current user
	if ( empty( $user_id ) ) {
		$user_id = ajan_loggedin_user_id();
	}

	// Bail if the user id is 0, as there's nothing to update
	if ( empty( $user_id ) ) {
		return false;
	}

	// Fall back on current time
	if ( empty( $time ) ) {
		$time = ajan_core_current_time();
	}

	// As of BuddyPress 2.0, last_activity is no longer stored in usermeta.
	// However, we mirror it there for backward compatibility. Do not use!
	// Remove our warning and re-add.
	remove_filter( 'update_user_metadata', '_ajan_update_user_meta_last_activity_warning', 10, 4 );
	remove_filter( 'get_user_metadata', '_ajan_get_user_meta_last_activity_warning', 10, 3 );
	update_user_meta( $user_id, 'last_activity', $time );
	add_filter( 'update_user_metadata', '_ajan_update_user_meta_last_activity_warning', 10, 4 );
	add_filter( 'get_user_metadata', '_ajan_get_user_meta_last_activity_warning', 10, 3 );

	return AJAN_Core_User::update_last_activity( $user_id, $time );
}


/**
 * Backward compatibility for 'last_activity' usermeta fetching.
 *
 * In BuddyPress 2.0, user last_activity data was moved out of usermeta. For
 * backward compatibility, we continue to mirror the data there. This function
 * serves two purposes: it warns plugin authors of the change, and it returns
 * the data from the proper location.
 * 
 *
 * @access private For internal use only.
 *
 * @param null $retval
 * @param int $object_id ID of the user.
 * @param string $meta_key Meta key being fetched.
 */
function _ajan_get_user_meta_last_activity_warning( $retval, $object_id, $meta_key ) {
	static $warned;

	if ( 'last_activity' === $meta_key ) {
		// Don't send the warning more than once per pageload
		if ( empty( $warned ) ) {
			_doing_it_wrong( 'get_user_meta( $user_id, \'last_activity\' )', __( 'User last_activity data is no longer stored in usermeta. Use ajan_get_user_last_activity() instead.', 'buddypress' ), '2.0.0' );
			$warned = 1;
		}

		return ajan_get_user_last_activity( $object_id );
	}

	return $retval;
}
add_filter( 'get_user_metadata', '_ajan_get_user_meta_last_activity_warning', 10, 3 );

function ajan_displayed_user_domain() {
	global $ajan;
	return apply_filters( 'ajan_displayed_user_domain', isset( $ajan->displayed_user->domain ) ? $ajan->displayed_user->domain : '' );
}


function ajan_loggedin_user_domain() {
	global $ajan;
	return apply_filters( 'ajan_loggedin_user_domain', isset( $ajan->loggedin_user->domain ) ? $ajan->loggedin_user->domain : '' );
}
function ajan_core_can_edit_settings() {
	if ( ajan_is_my_profile() )
		return true;

	if ( is_super_admin( ajan_displayed_user_id() ) && ! is_super_admin() ) {
		return false;
	}

	if ( ajan_current_user_can( 'ajan_moderate' ) || current_user_can( 'edit_users' ) )
		return true;

	return false;
}

/**
 * Build the "Notifications" dropdown
 * 
 */
function ajan_members_admin_bar_notifications_menu() {

	// Bail if notifications is not active
	if ( ! ajan_is_active( 'notifications' ) ) {
		return false;
	}

	ajan_notifications_toolbar_menu();
}
add_action( 'admin_bar_menu', 'ajan_members_admin_bar_notifications_menu', 90 );


/**
 * Returns a HTML formatted link for a user with the user's full name as the link text.
 * eg: <a href="http://andy.domain.com/">Andy Peatling</a>
 * Optional parameters will return just the name or just the URL.
 *
 * @param int $user_id User ID to check.
 * @param bool $no_anchor Disable URL and HTML and just return full name. Default false.
 * @param bool $just_link Disable full name and HTML and just return the URL text. Default false.
 * @return string|bool The link text based on passed parameters, or false on no match.
 * @todo This function needs to be cleaned up or split into separate functions
 */
function ajan_core_get_userlink( $user_id, $no_anchor = false, $just_link = false ) {
	$display_name = ajan_core_get_user_displayname( $user_id );

	if ( empty( $display_name ) )
		return false;

	if ( $no_anchor )
		return $display_name;

	if ( !$url = ajan_core_get_user_domain( $user_id ) )
		return false;

	if ( $just_link )
		return $url;

	return apply_filters( 'ajan_core_get_userlink', '<a href="' . $url . '" title="' . $display_name . '">' . $display_name . '</a>', $user_id );
}


/**
 * Fetch the display name for a user.
 *
 * @param int|string $user_id_or_username User ID or username.
 * @return string|bool The display name for the user in question, or false if
 *         user not found.
 */
function ajan_core_get_user_displayname( $user_id_or_username ) {
	global $ajan;

	$fullname = '';

	if ( empty( $user_id_or_username ) ) {
		return false;
	}

	if ( ! is_numeric( $user_id_or_username ) ) {
		$user_id = ajan_core_get_userid( $user_id_or_username );
	} else {
		$user_id = $user_id_or_username;
	}

	if ( empty( $user_id ) ) {
		return false;
	}
	//function edited to use wordpress function get_userdata instead of using xprofile
	$user_info = get_userdata($user_id);

 
	$fullname = $user_info->display_name;
	 

	return apply_filters( 'ajan_core_get_user_displayname', $fullname, $user_id );
}


function ajan_core_get_user_domain( $user_id, $user_nicename = false, $user_login = false ) {

$domain = '';
return apply_filters( 'ajan_core_get_user_domain', $domain, $user_id, $user_nicename, $user_login );	
}
function ajan_loggedin_user_username() {
	echo ajan_get_loggedin_user_username();
}
	function ajan_get_loggedin_user_username() {
		global $ajan;

		if ( ajan_loggedin_user_id() ) {
			$username = ajan_core_get_username( ajan_loggedin_user_id(), $ajan->loggedin_user->userdata->user_nicename, $ajan->loggedin_user->userdata->user_login );
		} else {
			$username = '';
		}

		return apply_filters( 'ajan_get_loggedin_user_username', $username );
	}



	/**
 * Returns the username for a user based on their user id.
 *
 * @package BuddyPress Core
 * @param int $uid User ID to check.
 * @uses ajan_core_get_core_userdata() Fetch the userdata for a user ID
 * @return string|bool The username of the matched user, or false.
 */
function ajan_core_get_username( $user_id = 0, $user_nicename = false, $user_login = false ) {
	$ajan = activitynotifications();

	// Check cache for user nicename
	$username = wp_cache_get( 'ajan_user_username_' . $user_id, 'bp' );
	if ( false === $username ) {

		// Cache not found so prepare to update it
		$update_cache = true;

		// Nicename and login were not passed
		if ( empty( $user_nicename ) && empty( $user_login ) ) {

			// User ID matches logged in user
			if ( ajan_loggedin_user_id() == $user_id ) {
				$userdata = &$ajan->loggedin_user->userdata;

			// User ID matches displayed in user
			} elseif ( ajan_displayed_user_id() == $user_id ) {
				$userdata = &$ajan->displayed_user->userdata;

			// No user ID match
			} else {
				$userdata = false;
			}

			// No match so go dig
			if ( empty( $userdata ) ) {

				// User not found so return false
				if ( !$userdata = ajan_core_get_core_userdata( $user_id ) ) {
					return false;
				}
			}

			// Update the $user_id for later
			$user_id       = $userdata->ID;

			// Two possible options
			$user_nicename = $userdata->user_nicename;
			$user_login    = $userdata->user_login;
		}

		// Pull an audible and maybe use the login over the nicename
		$username = ajan_is_username_compatibility_mode() ? $user_login : $user_nicename;

	// Username found in cache so don't update it again
	} else {
		$update_cache = false;
	}

	// Add this to cache
	if ( ( true === $update_cache ) && !empty( $username ) ) {
		wp_cache_set( 'ajan_user_username_' . $user_id, $username, 'bp' );

	// @todo bust this cache if no $username found?
	//} else {
	//	wp_cache_delete( 'ajan_user_username_' . $user_id );
	}

	return apply_filters( 'ajan_core_get_username', $username );
}
/**
 * Fetch everything in the wp_users table for a user, without any usermeta.
 * 
 * @param int $user_id The ID of the user.
 * @uses AJAN_Core_User::get_core_userdata() Performs the query.
 */
function ajan_core_get_core_userdata( $user_id ) {
	if ( empty( $user_id ) )
		return false;

	if ( !$userdata = wp_cache_get( 'ajan_core_userdata_' . $user_id, 'ajan' ) ) {
		$userdata = AJAN_Core_User::get_core_userdata( $user_id );
		wp_cache_set( 'ajan_core_userdata_' . $user_id, $userdata, 'ajan' );
	}
	return apply_filters( 'ajan_core_get_core_userdata', $userdata );
}


function ajan_get_displayed_user_fullname(){
	return "fullname";
}


/**
 * Returns the user_id for a user based on their user_nicename.
 * @param string $username Username to check.
 * @global $wpdb WordPress DB access object.
 * @return int|bool The ID of the matched user, or false.
 */
function ajan_core_get_userid_from_nicename( $user_nicename ) {
	global $wpdb;

	if ( empty( $user_nicename ) )
		return false;

	return apply_filters( 'ajan_core_get_userid_from_nicename', $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE user_nicename = %s", $user_nicename ) ), $user_nicename );
}
