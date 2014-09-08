<?php

/**
 * Functions related to notifications caching.
 *
 * @since ajency-activity-and-notifications (2.0.0)
 */

/**
 * Invalidate 'all_for_user_' cache when saving.
 *
 * @since ajency-activity-and-notifications (2.0.0)
 *
 * @param AJAN_Notification_Notification $n Notification object.
 */
function ajan_notifications_clear_all_for_user_cache_after_save( AJAN_Notifications_Notification $n ) {
	wp_cache_delete( 'all_for_user_' . $n->user_id, 'ajan_notifications' );
}
add_action( 'ajan_notification_after_save', 'ajan_notifications_clear_all_for_user_cache_after_save' );

/**
 * Invalidate the 'all_for_user_' cache when deleting.
 *
 * @since ajency-activity-and-notifications (2.0.0)
 *
 * @param int $args Notification deletion arguments.
 */
function ajan_notifications_clear_all_for_user_cache_before_delete( $args ) {
	// Pull up a list of items matching the args (those about te be deleted)
	$ns = AJAN_Notifications_Notification::get( $args );

	$user_ids = array();
	foreach ( $ns as $n ) {
		$user_ids[] = $n->user_id;
	}

	foreach ( array_unique( $user_ids ) as $user_id ) {
		wp_cache_delete( 'all_for_user_' . $user_id, 'ajan_notifications' );
	}
}
add_action( 'ajan_notification_before_delete', 'ajan_notifications_clear_all_for_user_cache_before_delete' );
