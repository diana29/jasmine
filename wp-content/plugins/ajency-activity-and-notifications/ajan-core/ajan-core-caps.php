<?php

/**
 * ActivityNotifications Capabilites.
 *
 * @package ActivityNotifications
 * @subpackage Capabilities
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add capabilities to WordPress user roles.
 *
 * This is called on plugin activation.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses get_role() To get the administrator, default and moderator roles.
 * @uses WP_Role::add_cap() To add various capabilities.
 * @uses do_action() Calls 'ajan_add_caps'.
 */
function ajan_add_caps() {
	global $wp_roles;

	// Load roles if not set
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	// Loop through available roles and add them
	foreach( $wp_roles->role_objects as $role ) {
		foreach ( ajan_get_caps_for_role( $role->name ) as $cap ) {
			$role->add_cap( $cap );
		}
	}

	do_action( 'ajan_add_caps' );
}

/**
 * Remove capabilities from WordPress user roles.
 *
 * This is called on plugin deactivation.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses get_role() To get the administrator and default roles.
 * @uses WP_Role::remove_cap() To remove various capabilities.
 * @uses do_action() Calls 'ajan_remove_caps'.
 */
function ajan_remove_caps() {
	global $wp_roles;

	// Load roles if not set
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	// Loop through available roles and remove them
	foreach( $wp_roles->role_objects as $role ) {
		foreach ( ajan_get_caps_for_role( $role->name ) as $cap ) {
			$role->remove_cap( $cap );
		}
	}

	do_action( 'ajan_remove_caps' );
}

/**
 * Map community caps to built in WordPress caps.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @see WP_User::has_cap() for description of the arguments passed to the
 *      'map_meta_cap' filter.
 * @uses apply_filters() Calls 'ajan_map_meta_caps' with caps, cap, user ID and
 *       args.
 *
 * @param array $caps See {@link WP_User::has_cap()}.
 * @param string $cap See {@link WP_User::has_cap()}.
 * @param int $user_id See {@link WP_User::has_cap()}.
 * @param mixed $args See {@link WP_User::has_cap()}.
 * @return array Actual capabilities for meta capability. See {@link WP_User::has_cap()}.
 */
function ajan_map_meta_caps( $caps, $cap, $user_id, $args ) {
	return apply_filters( 'ajan_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Return community capabilities.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses apply_filters() Calls 'ajan_get_community_caps' with the capabilities.
 *
 * @return array Community capabilities.
 */
function ajan_get_community_caps() {

	// Forum meta caps
	$caps = array();

	return apply_filters( 'ajan_get_community_caps', $caps );
}

/**
 * Return an array of capabilities based on the role that is being requested.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses apply_filters() Allow return value to be filtered.
 *
 * @param string $role The role for which you're loading caps.
 * @return array Capabilities for $role.
 */
function ajan_get_caps_for_role( $role = '' ) {

	// Which role are we looking for?
	switch ( $role ) {

		// Administrator
		case 'administrator' :
			$caps = array(
				// Misc
				'ajan_moderate',
			);

			break;

		case 'editor'          :
		case 'author'          :
		case 'contributor'     :
		case 'subscriber'      :
		default                :
			$caps = array();
			break;
	}

	return apply_filters( 'ajan_get_caps_for_role', $caps, $role );
}

/**
 * Set a default role for the current user.
 *
 * Give a user the default role when creating content on a site they do not
 * already have a role or capability on.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @global ActivityNotifications $ajan Global ActivityNotifications settings object.
 *
 * @uses is_multisite()
 * @uses ajan_allow_global_access()
 * @uses ajan_is_user_inactive()
 * @uses is_user_logged_in()
 * @uses current_user_can()
 * @uses WP_User::set_role()
 */
function ajan_set_current_user_default_role() {

	// Bail if not multisite or not root blog
	if ( ! is_multisite() || ! ajan_is_root_blog() )
		return;

	// Bail if user is not logged in or already a member
	if ( ! is_user_logged_in() || is_user_member_of_blog() )
		return;

	// Bail if user is not active
	if ( ajan_is_user_inactive() )
		return;

	// Set the current users default role
	activitynotifications()->current_user->set_role( ajan_get_option( 'default_role', 'subscriber' ) );
}

/**
 * Check whether the current user has a given capability.
 *
 * Can be passed blog ID, or will use the root blog by default.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @param string $capability Capability or role name.
 * @param int $blog_id Optional. Blog ID. Defaults to the BP root blog.
 * @return bool True if the user has the cap for the given blog.
 */
function ajan_current_user_can( $capability, $blog_id = 0 ) {

	// Use root blog if no ID passed
	if ( empty( $blog_id ) )
		$blog_id = ajan_get_root_blog_id();

	$retval = current_user_can_for_blog( $blog_id, $capability );

	return (bool) apply_filters( 'ajan_current_user_can', $retval, $capability, $blog_id );
}

/**
 * Temporary implementation of 'ajan_moderate' cap.
 *
 * In ActivityNotifications 1.6, the 'ajan_moderate' cap was introduced. In order to
 * enforce that ajan_current_user_can( 'ajan_moderate' ) always returns true for
 * Administrators, we must manually add the 'ajan_moderate' cap to the list of
 * user caps for Admins.
 *
 * Note that this level of enforcement is only necessary in the case of
 * non-Multisite. This is because WordPress automatically assigns every
 * capability - and thus 'ajan_moderate' - to Super Admins on a Multisite
 * installation. See {@link WP_User::has_cap()}.
 *
 * This implementation of 'ajan_moderate' is temporary, until ActivityNotifications properly
 * matches caps to roles and stores them in the database. Plugin authors: Do
 * not use this function.
 *
 * @access private
 * @since ActivityNotifications (1.6.0)
 *
 * @see WP_User::has_cap()
 *
 * @param array $allcaps The caps that WP associates with the given role.
 * @param array $caps The caps being tested for in WP_User::has_cap().
 * @param array $args Miscellaneous arguments passed to the user_has_cap filter.
 * @return array $allcaps The user's cap list, with 'ajan_moderate' appended, if relevant.
 */
function _ajan_enforce_ajan_moderate_cap_for_admins( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// Bail if not checking the 'ajan_moderate' cap
	if ( 'ajan_moderate' !== $cap )
		return $caps;

	// Bail if ActivityNotifications is not network activated
	if ( ajan_is_network_activated() )
		return $caps;

	// Never trust inactive users
	if ( ajan_is_user_inactive( $user_id ) )
		return $caps;

	// Only users that can 'manage_options' on this site can 'ajan_moderate'
	return array( 'manage_options' );
}
add_filter( 'map_meta_cap', '_ajan_enforce_ajan_moderate_cap_for_admins', 10, 4 );

/** Deprecated ****************************************************************/

/**
 * Adds ActivityNotifications-specific user roles.
 *
 * This is called on plugin activation.
 *
 * @since ActivityNotifications (1.6.0)
 * @deprecated 1.7.0
 */
function ajan_add_roles() {
	_doing_it_wrong( 'ajan_add_roles', __( 'Special community roles no longer exist. Use mapped capabilities instead', 'ajency-activity-and-notifications' ), '1.7' );
}

/**
 * Removes ActivityNotifications-specific user roles.
 *
 * This is called on plugin deactivation.
 *
 * @since ActivityNotifications (1.6.0)
 * @deprecated 1.7.0
 */
function ajan_remove_roles() {
	_doing_it_wrong( 'ajan_remove_roles', __( 'Special community roles no longer exist. Use mapped capabilities instead', 'ajency-activity-and-notifications' ), '1.7' );
}


/**
 * The participant role for registered users without roles.
 *
 * This is primarily for multisite compatibility when users without roles on
 * sites that have global communities enabled.
 *
 * @since ActivityNotifications (1.6)
 * @deprecated 1.7.0
 */
function ajan_get_participant_role() {
	_doing_it_wrong( 'ajan_get_participant_role', __( 'Special community roles no longer exist. Use mapped capabilities instead', 'ajency-activity-and-notifications' ), '1.7' );
}

/**
 * The moderator role for ActivityNotifications users.
 *
 * @since ActivityNotifications (1.6.0)
 * @deprecated 1.7.0
 */
function ajan_get_moderator_role() {
	_doing_it_wrong( 'ajan_get_moderator_role', __( 'Special community roles no longer exist. Use mapped capabilities instead', 'ajency-activity-and-notifications' ), '1.7' );
}
