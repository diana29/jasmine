<?php

/**
 * ActivityNotifications Admin Actions
 *
 * This file contains the actions that are used through-out ActivityNotifications Admin. They
 * are consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional actions can currently be found
 *
 *  - ActivityNotifications: In {@link ActivityNotifications::setup_actions()} in ActivityNotifications.php
 *  - Admin: More in {@link ajan_Admin::setup_actions()} in admin.php
 *
 * @package ActivityNotifications
 * @subpackage Admin
 * @see ajan-core-actions.php
 * @see ajan-core-filters.php
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attach ActivityNotifications to WordPress
 *
 * ActivityNotifications uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when ActivityNotifications is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *           v--WordPress Actions       v--ActivityNotifications Sub-actions
 */
add_action( 'admin_menu',              'ajan_admin_menu'                    );
add_action( 'admin_init',              'ajan_admin_init'                    );
add_action( 'admin_head',              'ajan_admin_head'                    );
add_action( 'admin_notices',           'ajan_admin_notices'                 );
add_action( 'admin_enqueue_scripts',   'ajan_admin_enqueue_scripts'         );
add_action( 'network_admin_menu',      'ajan_admin_menu'                    );
add_action( 'custom_menu_order',       'ajan_admin_custom_menu_order'       );
add_action( 'menu_order',              'ajan_admin_menu_order'              );
add_action( 'wpmu_new_blog',           'ajan_new_site',               10, 6 );

// Hook on to admin_init
add_action( 'ajan_admin_init', 'ajan_setup_updater',          1000 );
add_action( 'ajan_admin_init', 'ajan_core_activation_notice', 1010 );
add_action( 'ajan_admin_init', 'ajan_register_importers'           );
add_action( 'ajan_admin_init', 'ajan_register_admin_style'         );
add_action( 'ajan_admin_init', 'ajan_register_admin_settings'      );
add_action( 'ajan_admin_init', 'ajan_do_activation_redirect', 1    );

// Add a new separator
add_action( 'ajan_admin_menu', 'ajan_admin_separator' );

/**
 * When a new site is created in a multisite installation, run the activation
 * routine on that site
 *
 * @since ActivityNotifications (1.7)
 *
 * @param int $blog_id
 * @param int $user_id
 * @param string $domain
 * @param string $path
 * @param int $site_id
 * @param array() $meta
 */
function ajan_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	// Bail if plugin is not network activated
	if ( ! is_plugin_active_for_network( activitynotifications()->basename ) )
		return;

	// Switch to the new blog
	switch_to_blog( $blog_id );

	// Do the ActivityNotifications activation routine
	do_action( 'ajan_new_site', $blog_id, $user_id, $domain, $path, $site_id, $meta );

	// restore original blog
	restore_current_blog();
}

/** Sub-Actions ***************************************************************/

/**
 * Piggy back admin_init action
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_admin_init'
 */
function ajan_admin_init() {
	do_action( 'ajan_admin_init' );
}

/**
 * Piggy back admin_menu action
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_admin_menu'
 */
function ajan_admin_menu() {
	do_action( 'ajan_admin_menu' );
}

/**
 * Piggy back admin_head action
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_admin_head'
 */
function ajan_admin_head() {
	do_action( 'ajan_admin_head' );
}

/**
 * Piggy back admin_notices action
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_admin_notices'
 */
function ajan_admin_notices() {
	do_action( 'ajan_admin_notices' );
}

/**
 * Piggy back admin_enqueue_scripts action.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses do_action() Calls 'ajan_admin_enqueue_scripts''.
 *
 * @param string $hook_suffix The current admin page, passed to
 *        'admin_enqueue_scripts'.
 */
function ajan_admin_enqueue_scripts( $hook_suffix = '' ) {
	do_action( 'ajan_admin_enqueue_scripts', $hook_suffix );
}

/**
 * Dedicated action to register ActivityNotifications importers
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_admin_notices'
 */
function ajan_register_importers() {
	do_action( 'ajan_register_importers' );
}

/**
 * Dedicated action to register admin styles
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_admin_notices'
 */
function ajan_register_admin_style() {
	do_action( 'ajan_register_admin_style' );
}

/**
 * Dedicated action to register admin settings
 *
 * @since ActivityNotifications (1.7)
 * @uses do_action() Calls 'ajan_register_admin_settings'
 */
function ajan_register_admin_settings() {
	do_action( 'ajan_register_admin_settings' );
}
