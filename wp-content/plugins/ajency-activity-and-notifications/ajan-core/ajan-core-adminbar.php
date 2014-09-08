<?php

/**
 * ActivityNotifications Core Toolbar.
 *
 * Handles the core functions related to the WordPress Toolbar.
 *
 * @package ActivityNotifications
 * @subpackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the secondary ActivityNotifications area to the my-account menu.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @global WP_Admin_Bar $wp_admin_bar
 */
function ajan_admin_bar_my_account_root() {
	global $wp_admin_bar;

	// Bail if this is an ajax request
	if ( !ajan_use_wp_admin_bar() || defined( 'DOING_AJAX' ) )
		return;

	// Only add menu for logged in user
	if ( is_user_logged_in() ) {

		// Add secondary parent item for all ActivityNotifications components
		$wp_admin_bar->add_menu( array(
			'parent'    => 'my-account',
			'id'        => 'my-account-buddypress',
			'title'     => __( 'My Account', 'ajency-activity-and-notifications' ),
			'group'     => true,
			'meta'      => array(
				'class' => 'ab-sub-secondary'
			)
		) );
	}
}
add_action( 'admin_bar_menu', 'ajan_admin_bar_my_account_root', 100 );

/**
 * Handle the Toolbar/BuddyBar business.
 *
 * @since ActivityNotifications (1.2.0)
 *
 * @global string $wp_version
 * @uses ajan_get_option()
 * @uses is_user_logged_in()
 * @uses ajan_use_wp_admin_bar()
 * @uses show_admin_bar()
 * @uses add_action() To hook 'ajan_adminbar_logo' to 'ajan_adminbar_logo'.
 * @uses add_action() To hook 'ajan_adminbar_login_menu' to 'ajan_adminbar_menus'.
 * @uses add_action() To hook 'ajan_adminbar_account_menu' to 'ajan_adminbar_menus'.
 * @uses add_action() To hook 'ajan_adminbar_thisblog_menu' to 'ajan_adminbar_menus'.
 * @uses add_action() To hook 'ajan_adminbar_random_menu' to 'ajan_adminbar_menus'.
 * @uses add_action() To hook 'ajan_core_admin_bar' to 'wp_footer'.
 * @uses add_action() To hook 'ajan_core_admin_bar' to 'admin_footer'.
 */
function ajan_core_load_admin_bar() {

	// Show the Toolbar for logged out users
	if ( ! is_user_logged_in() && (int) ajan_get_option( 'hide-loggedout-adminbar' ) != 1 ) {
		show_admin_bar( true );
	}

	// Hide the WordPress Toolbar and show the BuddyBar
	if ( ! ajan_use_wp_admin_bar() ) {

		// Keep the WP Toolbar from loading
		show_admin_bar( false );

		// Actions used to build the BP Toolbar
		add_action( 'ajan_adminbar_logo',  'ajan_adminbar_logo'               );
		add_action( 'ajan_adminbar_menus', 'ajan_adminbar_login_menu',    2   );
		add_action( 'ajan_adminbar_menus', 'ajan_adminbar_account_menu',  4   );
		add_action( 'ajan_adminbar_menus', 'ajan_adminbar_thisblog_menu', 6   );
		add_action( 'ajan_adminbar_menus', 'ajan_adminbar_random_menu',   100 );

		// Actions used to append BP Toolbar to footer
		add_action( 'wp_footer',    'ajan_core_admin_bar', 8 );
		add_action( 'admin_footer', 'ajan_core_admin_bar'    );
	}
}
add_action( 'init', 'ajan_core_load_admin_bar', 9 );

/**
 * Handle the Toolbar CSS.
 *
 * @since ActivityNotifications (1.5.0)
 */
function ajan_core_load_admin_bar_css() {
	global $wp_styles;

	if ( ! ajan_use_wp_admin_bar() || ! is_admin_bar_showing() )
		return;

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Toolbar styles
	$stylesheet = activitynotifications()->plugin_url . "ajan-core/css/admin-bar{$min}.css";

	wp_enqueue_style( 'ajan-admin-bar', apply_filters( 'ajan_core_admin_bar_css', $stylesheet ), array( 'admin-bar' ), ajan_get_version() );
	$wp_styles->add_data( 'ajan-admin-bar', 'rtl', true );
	if ( $min )
		$wp_styles->add_data( 'ajan-admin-bar', 'suffix', $min );
}
