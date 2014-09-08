<?php

/**
 * ActivityNotifications Updater.
 *
 * @package ActivityNotifications
 * @subpackage Updater
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Is this a fresh installation of ActivityNotifications?
 *
 * If there is no raw DB version, we infer that this is the first installation.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses get_option()
 * @uses ajan_get_db_version() To get ActivityNotifications's database version.
 *
 * @return bool True if this is a fresh BP install, otherwise false.
 */
function ajan_is_install() {
	return ! ajan_get_db_version_raw();
}

/**
 * Is this a ActivityNotifications update?
 *
 * Determined by comparing the registered ActivityNotifications version to the version
 * number stored in the database. If the registered version is greater, it's
 * an update.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses get_option()
 * @uses ajan_get_db_version() To get ActivityNotifications's database version.
 *
 * @return bool True if update, otherwise false.
 */
function ajan_is_update() {

	// Current DB version of this site (per site in a multisite network)
	$current_db   = ajan_get_option( '_ajan_db_version' );
	$current_live = ajan_get_db_version();

	// Compare versions (cast as int and bool to be safe)
	$is_update = (bool) ( (int) $current_db < (int) $current_live );

	// Return the product of version comparison
	return $is_update;
}

/**
 * Determine whether ActivityNotifications is in the process of being activated.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses activitynotifications()
 *
 * @return bool True if activating ActivityNotifications, false if not.
 */
function ajan_is_activation( $basename = '' ) {
	$ajan     = activitynotifications();
	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && ( '-1' != $_REQUEST['action'] ) ) {
		$action = $_REQUEST['action'];
	} elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' != $_REQUEST['action2'] ) ) {
		$action = $_REQUEST['action2'];
	}

	// Bail if not activating
	if ( empty( $action ) || !in_array( $action, array( 'activate', 'activate-selected' ) ) ) {
		return false;
	}

	// The plugin(s) being activated
	if ( $action == 'activate' ) {
		$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
	} else {
		$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
	}

	// Set basename if empty
	if ( empty( $basename ) && !empty( $ajan->basename ) ) {
		$basename = $ajan->basename;
	}

	// Bail if no basename
	if ( empty( $basename ) ) {
		return false;
	}

	// Is ActivityNotifications being activated?
	return in_array( $basename, $plugins );
}

/**
 * Determine whether ActivityNotifications is in the process of being deactivated.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses activitynotifications()
 *
 * @return bool True if deactivating ActivityNotifications, false if not.
 */
function ajan_is_deactivation( $basename = '' ) {
	$ajan     = activitynotifications();
	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && ( '-1' != $_REQUEST['action'] ) ) {
		$action = $_REQUEST['action'];
	} elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' != $_REQUEST['action2'] ) ) {
		$action = $_REQUEST['action2'];
	}

	// Bail if not deactivating
	if ( empty( $action ) || !in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) ) {
		return false;
	}

	// The plugin(s) being deactivated
	if ( 'deactivate' == $action ) {
		$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
	} else {
		$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
	}

	// Set basename if empty
	if ( empty( $basename ) && !empty( $ajan->basename ) ) {
		$basename = $ajan->basename;
	}

	// Bail if no basename
	if ( empty( $basename ) ) {
		return false;
	}

	// Is bbPress being deactivated?
	return in_array( $basename, $plugins );
}

/**
 * Update the BP version stored in the database to the current version.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses ajan_get_db_version() To get ActivityNotifications's database version.
 * @uses ajan_update_option() To update ActivityNotifications's database version.
 */
function ajan_version_bump() {
	ajan_update_option( '_ajan_db_version', ajan_get_db_version() );
}

/**
 * Set up the ActivityNotifications updater.
 *
 * @since ActivityNotifications (1.6.0)
 */
function ajan_setup_updater() {

	// Are we running an outdated version of ActivityNotifications?
	if ( ! ajan_is_update() )
		return;

	ajan_version_updater();
}

/**
 * Initialize an update or installation of ActivityNotifications.
 *
 * ActivityNotifications's version updater looks at what the current database version is,
 * and runs whatever other code is needed - either the "update" or "install"
 * code.
 *
 * This is most often used when the data schema changes, but should also be used
 * to correct issues with ActivityNotifications metadata silently on software update.
 *
 * @since ActivityNotifications (1.7.0)
 */
function ajan_version_updater() {

	// Get the raw database version
	$raw_db_version = (int) ajan_get_db_version_raw();

	$default_components = apply_filters( 'ajan_new_install_default_components', array(
		'activity'      => 1,
		'notifications' => 1,
	) );

	require_once( activitynotifications()->plugin_dir . '/ajan-core/admin/ajan-core-schema.php' );

	// Install BP schema and activate only Activity and XProfile
	if ( ajan_is_install() ) {

		// Apply schema and set Activity and XProfile components as active
		ajan_core_install( $default_components );
		ajan_update_option( 'ajan-active-components', $default_components );
		ajan_core_add_page_mappings( $default_components, 'delete' );

	// Upgrades
	} else {

		// Run the schema install to update tables
		ajan_core_install();

		// 1.5
		if ( $raw_db_version < 1801 ) {
			ajan_update_to_1_5();
			ajan_core_add_page_mappings( $default_components, 'delete' );
		}

		// 1.6
		if ( $raw_db_version < 6067 ) {
			ajan_update_to_1_6();
		}

		// 1.9
		if ( $raw_db_version < 7553 ) {
			ajan_update_to_1_9();
		}

		// 1.9.2
		if ( $raw_db_version < 7731 ) {
			ajan_update_to_1_9_2();
		}

		// 2.0
		if ( $raw_db_version < 7892 ) {
			ajan_update_to_2_0();
		}

		// 2.0.1
		if ( $raw_db_version < 8311 ) {
			ajan_update_to_2_0_1();
		}
	}

	/** All done! *************************************************************/

	// Bump the version
	ajan_version_bump();
}

/** Upgrade Routines **********************************************************/

/**
 * Remove unused metadata from database when upgrading from < 1.5.
 *
 * Database update methods based on version numbers.
 *
 * @since ActivityNotifications (1.7.0)
 */
function ajan_update_to_1_5() {

	// Delete old database version options
	delete_site_option( 'ajan-activity-db-version' );
	delete_site_option( 'ajan-blogs-db-version'    );
	delete_site_option( 'ajan-friends-db-version'  );
	delete_site_option( 'ajan-groups-db-version'   );
	delete_site_option( 'ajan-messages-db-version' );
	delete_site_option( 'ajan-xprofile-db-version' );
}

/**
 * Remove unused metadata from database when upgrading from < 1.6.
 *
 * Database update methods based on version numbers.
 *
 * @since ActivityNotifications (1.7.0)
 */
function ajan_update_to_1_6() {

	// Delete possible site options
	delete_site_option( 'ajan-db-version'       );
	delete_site_option( '_ajan_db_version'      );
	delete_site_option( 'ajan-core-db-version'  );
	delete_site_option( '_ajan-core-db-version' );

	// Delete possible blog options
	delete_blog_option( ajan_get_root_blog_id(), 'ajan-db-version'       );
	delete_blog_option( ajan_get_root_blog_id(), 'ajan-core-db-version'  );
	delete_site_option( ajan_get_root_blog_id(), '_ajan-core-db-version' );
	delete_site_option( ajan_get_root_blog_id(), '_ajan_db_version'      );
}

/**
 * Add the notifications component to active components.
 *
 * Notifications was added in 1.9.0, and previous installations will already
 * have the core notifications API active. We need to add the new Notifications
 * component to the active components option to retain existing functionality.
 *
 * @since ActivityNotifications (1.9.0)
 */
function ajan_update_to_1_9() {

	// Setup hardcoded keys
	$active_components_key      = 'ajan-active-components';
	$notifications_component_id = 'notifications';

	// Get the active components
	$active_components          = ajan_get_option( $active_components_key );

	// Add notifications
	if ( ! in_array( $notifications_component_id, $active_components ) ) {
		$active_components[ $notifications_component_id ] = 1;
	}

	// Update the active components option
	ajan_update_option( $active_components_key, $active_components );
}

/**
 * Perform database updates for BP 1.9.2
 *
 * In 1.9, ActivityNotifications stopped registering its theme directory when it detected
 * that ajan-default (or a child theme) was not currently being used, in effect
 * deprecating ajan-default. However, this ended up causing problems when site
 * admins using ajan-default would switch away from the theme temporarily:
 * ajan-default would no longer be available, with no obvious way (outside of
 * a manual filter) to restore it. In 1.9.2, we add an option that flags
 * whether ajan-default or a child theme is active at the time of upgrade; if so,
 * the theme directory will continue to be registered even if the theme is
 * deactivated temporarily. Thus, new installations will not see ajan-default,
 * but legacy installations using the theme will continue to see it.
 *
 * @since ActivityNotifications (1.9.2)
 */
function ajan_update_to_1_9_2() {
	if ( 'ajan-default' === get_stylesheet() || 'ajan-default' === get_template() ) {
		update_site_option( '_ajan_retain_ajan_default', 1 );
	}
}

/**
 * 2.0 update routine.
 *
 * - Ensure that the activity tables are installed, for last_activity storage.
 * - Migrate last_activity data from usermeta to activity table
 * - Add values for all ActivityNotifications options to the options table
 *
 * @since ActivityNotifications (2.0.0)
 */
function ajan_update_to_2_0() {

	/** Install activity tables for 'last_activity' ***************************/

	ajan_core_install_activity_streams();

	/** Migrate 'last_activity' data ******************************************/

	ajan_last_activity_migrate();

	/** Migrate signups data **************************************************/

	if ( ! is_multisite() ) {

		// Maybe install the signups table
		ajan_core_maybe_install_signups();

		// Run the migration script
		ajan_members_migrate_signups();
	}

	/** Add BP options to the options table ***********************************/

	ajan_add_options();
}

/**
 * 2.0.1 database upgrade routine
 *
 * @since ActivityNotifications (2.0.1)
 *
 * @return void
 */
function ajan_update_to_2_0_1() {

	// We purposely call this during both the 2.0 upgrade and the 2.0.1 upgrade.
	// Don't worry; it won't break anything, and safely handles all cases.
	ajan_core_maybe_install_signups();
}

/**
 * Redirect user to BP's What's New page on first page load after activation.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @internal Used internally to redirect ActivityNotifications to the about page on activation.
 *
 * @uses set_transient() To drop the activation transient for 30 seconds.
 */
function ajan_add_activation_redirect() {

	// Bail if activating from network, or bulk
	if ( isset( $_GET['activate-multi'] ) )
		return;

	// Record that this is a new installation, so we show the right
	// welcome message
	if ( ajan_is_install() ) {
		set_transient( '_ajan_is_new_install', true, 30 );
	}

	// Add the transient to redirect
	set_transient( '_ajan_activation_redirect', true, 30 );
}

/** Signups *******************************************************************/

/**
 * Check if the signups table needs to be created.
 *
 * @since ActivityNotifications (2.0.0)
 *
 * @global WPDB $wpdb
 *
 * @return bool If signups table exists
 */
function ajan_core_maybe_install_signups() {

	// Bail if we are explicitly not upgrading global tables
	if ( defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
		return false;
	}

	global $wpdb;

	// The table to run queries against
	$signups_table = $wpdb->base_prefix . 'signups';

	// Suppress errors because users shouldn't see what happens next
	$old_suppress  = $wpdb->suppress_errors();

	// Never use ajan_core_get_table_prefix() for any global users tables
	$table_exists  = (bool) $wpdb->get_results( "DESCRIBE {$signups_table};" );

	// Table already exists, so maybe upgrade instead?
	if ( true === $table_exists ) {

		// Look for the 'signup_id' column
		$column_exists = $wpdb->query( "SHOW COLUMNS FROM {$signups_table} LIKE 'signup_id'" );

		// 'signup_id' column doesn't exist, so run the upgrade
		if ( empty( $column_exists ) ) {
			ajan_core_upgrade_signups();
		}

	// Table does not exist, and we are a single site, so install the multisite
	// signups table using WordPress core's database schema.
	} elseif ( ! is_multisite() ) {
		ajan_core_install_signups();
	}

	// Restore previous error suppression setting
	$wpdb->suppress_errors( $old_suppress );
}

/** Activation Actions ********************************************************/

/**
 * Fire activation hooks and events.
 *
 * Runs on ActivityNotifications activation.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action() Calls 'ajan_activation' hook.
 */
function ajan_activation() {

	// Force refresh theme roots.
	delete_site_transient( 'theme_roots' );

	// Add options
	ajan_add_options();

	// Use as of (1.6)
	do_action( 'ajan_activation' );

	// @deprecated as of (1.6)
	do_action( 'ajan_loader_activate' );
}

/**
 * Fire deactivation hooks and events.
 *
 * Runs on ActivityNotifications deactivation.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action() Calls 'ajan_deactivation' hook.
 */
function ajan_deactivation() {

	// Force refresh theme roots.
	delete_site_transient( 'theme_roots' );

	// Switch to WordPress's default theme if current parent or child theme
	// depend on ajan-default. This is to prevent white screens of doom.
	if ( in_array( 'ajan-default', array( get_template(), get_stylesheet() ) ) ) {
		switch_theme( WP_DEFAULT_THEME, WP_DEFAULT_THEME );
		update_option( 'template_root',   get_raw_theme_root( WP_DEFAULT_THEME, true ) );
		update_option( 'stylesheet_root', get_raw_theme_root( WP_DEFAULT_THEME, true ) );
	}

	// Use as of (1.6)
	do_action( 'ajan_deactivation' );

	// @deprecated as of (1.6)
	do_action( 'ajan_loader_deactivate' );
}

/**
 * Fire uninstall hook.
 *
 * Runs when uninstalling ActivityNotifications.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses do_action() Calls 'ajan_uninstall' hook.
 */
function ajan_uninstall() {
	do_action( 'ajan_uninstall' );
}
