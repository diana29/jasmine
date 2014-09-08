<?php

/**
 * BuddyPress Notifications Screen Functions.
 *
 * Screen functions are the controllers of BuddyPress. They will execute when
 * their specific URL is caught. They will first save or manipulate data using
 * business functions, then pass on the user to a template file.
 *
 * @package BuddyPress
 * @subpackage NotificationsScreens
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Catch and route the 'unread' notifications screen.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_screen_unread() {
	do_action( 'ajan_notifications_screen_unread' );

	ajan_core_load_template( apply_filters( 'ajan_notifications_template_unread', 'members/single/home' ) );
}

/**
 * Catch and route the 'read' notifications screen.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_screen_read() {
	do_action( 'ajan_notifications_screen_read' );

	ajan_core_load_template( apply_filters( 'ajan_notifications_template_read', 'members/single/home' ) );
}

/**
 * Catch and route the 'settings' notifications screen.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_screen_settings() {

}
