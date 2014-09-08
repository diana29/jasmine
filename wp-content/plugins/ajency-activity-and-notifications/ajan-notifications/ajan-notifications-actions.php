<?php

/**
 * BuddyPress Notifications Actions
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 *
 * @package BuddyPress
 * @subpackage NotificationsActions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


 

/**
 * Handle marking single notifications as read.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @return boolean
 */
function ajan_notifications_action_mark_read() {

	// Bail if not the unread screen
	if ( ! ajan_is_notifications_component() || ! ajan_is_current_action( 'unread' ) ) {
		return false;
	}

	// Get the action
	$action = !empty( $_GET['action']          ) ? $_GET['action']          : '';
	$nonce  = !empty( $_GET['_wpnonce']        ) ? $_GET['_wpnonce']        : '';
	$id     = !empty( $_GET['notification_id'] ) ? $_GET['notification_id'] : '';

	// Bail if no action or no ID
	if ( ( 'read' !== $action ) || empty( $id ) || empty( $nonce ) ) {
		return false;
	}

	// Check the nonce and mark the notification
	if ( ajan_verify_nonce_request( 'ajan_notification_mark_read_' . $id ) && ajan_notifications_mark_notification( $id, false ) ) {
		ajan_core_add_message( __( 'Notification successfully marked read.',         'ajency-activity-and-notifications' )          );
	} else {
		ajan_core_add_message( __( 'There was a problem marking that notification.', 'ajency-activity-and-notifications' ), 'error' );
	}

	// Redirect
	ajan_core_redirect( ajan_displayed_user_domain() . ajan_get_notifications_slug() . '/unread/' );
}
add_action( 'ajan_actions', 'ajan_notifications_action_mark_read' );

/**
 * Handle marking single notifications as unread.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @return boolean
 */
function ajan_notifications_action_mark_unread() {

	// Bail if not the read screen
	if ( ! ajan_is_notifications_component() || ! ajan_is_current_action( 'read' ) ) {
		return false;
	}

	// Get the action
	$action = !empty( $_GET['action']          ) ? $_GET['action']          : '';
	$nonce  = !empty( $_GET['_wpnonce']        ) ? $_GET['_wpnonce']        : '';
	$id     = !empty( $_GET['notification_id'] ) ? $_GET['notification_id'] : '';

	// Bail if no action or no ID
	if ( ( 'unread' !== $action ) || empty( $id ) || empty( $nonce ) ) {
		return false;
	}

	// Check the nonce and mark the notification
	if ( ajan_verify_nonce_request( 'ajan_notification_mark_unread_' . $id ) && ajan_notifications_mark_notification( $id, true ) ) {
		ajan_core_add_message( __( 'Notification successfully marked unread.',       'ajency-activity-and-notifications' )          );
	} else {
		ajan_core_add_message( __( 'There was a problem marking that notification.', 'ajency-activity-and-notifications' ), 'error' );
	}

	// Redirect
	ajan_core_redirect( ajan_displayed_user_domain() . ajan_get_notifications_slug() . '/read/' );
}
add_action( 'ajan_actions', 'ajan_notifications_action_mark_unread' );

/**
 * Handle deleting single notifications.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @return boolean
 */
function ajan_notifications_action_delete() {

	// Bail if not the read or unread screen
	if ( ! ajan_is_notifications_component() || ! ( ajan_is_current_action( 'read' ) || ajan_is_current_action( 'unread' ) ) ) {
		return false;
	}

	// Get the action
	$action = !empty( $_GET['action']          ) ? $_GET['action']          : '';
	$nonce  = !empty( $_GET['_wpnonce']        ) ? $_GET['_wpnonce']        : '';
	$id     = !empty( $_GET['notification_id'] ) ? $_GET['notification_id'] : '';

	// Bail if no action or no ID
	if ( ( 'delete' !== $action ) || empty( $id ) || empty( $nonce ) ) {
		return false;
	}

	// Check the nonce and delete the notification
	if ( ajan_verify_nonce_request( 'ajan_notification_delete_' . $id ) && ajan_notifications_delete_notification( $id ) ) {
		ajan_core_add_message( __( 'Notification successfully deleted.',              'ajency-activity-and-notifications' )          );
	} else {
		ajan_core_add_message( __( 'There was a problem deleting that notification.', 'ajency-activity-and-notifications' ), 'error' );
	}

	// Redirect
	ajan_core_redirect( ajan_displayed_user_domain() . ajan_get_notifications_slug() . '/' . ajan_current_action() . '/' );
}
add_action( 'ajan_actions', 'ajan_notifications_action_delete' );
