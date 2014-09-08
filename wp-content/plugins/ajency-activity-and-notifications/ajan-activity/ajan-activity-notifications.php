<?php

/**
 * BuddyPress Activity Notifications.
 *
 * @package BuddyPress
 * @subpackage ActivityNotifications
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Emails *********************************************************************/

/**
 * Send email and BP notifications when a user is mentioned in an update.
 *
 * @since ajency-activity-and-notifications (1.2)
 *
 * @uses ajan_notifications_add_notification()
 * @uses ajan_get_user_meta()
 * @uses ajan_core_get_user_displayname()
 * @uses ajan_activity_get_permalink()
 * @uses ajan_core_get_user_domain()
 * @uses ajan_get_settings_slug()
 * @uses ajan_activity_filter_kses()
 * @uses ajan_core_get_core_userdata()
 * @uses wp_specialchars_decode()
 * @uses get_blog_option()
 * @uses ajan_is_active()
 * @uses ajan_is_group()
 * @uses ajan_get_current_group_name()
 * @uses apply_filters() To call the 'ajan_activity_at_message_notification_to' hook.
 * @uses apply_filters() To call the 'ajan_activity_at_message_notification_subject' hook.
 * @uses apply_filters() To call the 'ajan_activity_at_message_notification_message' hook.
 * @uses wp_mail()
 * @uses do_action() To call the 'ajan_activity_sent_mention_email' hook
 *
 * @param int $activity_id The ID of the activity update.
 * @param int $receiver_user_id The ID of the user who is receiving the update.
 */
function ajan_activity_at_message_notification( $activity_id, $receiver_user_id ) {

	// Don't leave multiple notifications for the same activity item
	$notifications = AJAN_Core_Notification::get_all_for_user( $receiver_user_id, 'all' );

	foreach( $notifications as $notification ) {
		if ( $activity_id == $notification->item_id ) {
			return;
		}
	}

	$activity = new AJAN_Activity_Activity( $activity_id );

	$subject = '';
	$message = '';
	$content = '';

	// Now email the user with the contents of the message (if they have enabled email notifications)
	if ( 'no' != ajan_get_user_meta( $receiver_user_id, 'notification_activity_new_mention', true ) ) {
		$poster_name = ajan_core_get_user_displayname( $activity->user_id );

		$message_link  = ajan_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'ajan_get_settings_slug' ) ? ajan_get_settings_slug() : 'settings';
		$settings_link = ajan_core_get_user_domain( $receiver_user_id ) . $settings_slug . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = ajan_activity_filter_kses( strip_tags( stripslashes( $activity->content ) ) );

		// Set up and send the message
		$ud       = ajan_core_get_core_userdata( $receiver_user_id );
		$to       = $ud->user_email;
		$subject  = ajan_get_email_subject( array( 'text' => sprintf( __( '%s mentioned you in an update', 'ajency-activity-and-notifications' ), $poster_name ) ) );

		if ( ajan_is_active( 'groups' ) && ajan_is_group() ) {
			$message = sprintf( __(
'%1$s mentioned you in the group "%2$s":

"%3$s"

To view and respond to the message, log in and visit: %4$s

---------------------
', 'ajency-activity-and-notifications' ), $poster_name, ajan_get_current_group_name(), $content, $message_link );
		} else {
			$message = sprintf( __(
'%1$s mentioned you in an update:

"%2$s"

To view and respond to the message, log in and visit: %3$s

---------------------
', 'ajency-activity-and-notifications' ), $poster_name, $content, $message_link );
		}

		// Only show the disable notifications line if the settings component is enabled
		if ( ajan_is_active( 'settings' ) ) {
			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'ajency-activity-and-notifications' ), $settings_link );
		}

		// Send the message
		$to 	 = apply_filters( 'ajan_activity_at_message_notification_to', $to );
		$subject = apply_filters( 'ajan_activity_at_message_notification_subject', $subject, $poster_name );
		$message = apply_filters( 'ajan_activity_at_message_notification_message', $message, $poster_name, $content, $message_link, $settings_link );

		wp_mail( $to, $subject, $message );
	}

	do_action( 'ajan_activity_sent_mention_email', $activity, $subject, $message, $content, $receiver_user_id );
}

/**
 * Send email and BP notifications when an activity item receives a comment.
 *
 * @since ajency-activity-and-notifications (1.2)
 *
 * @uses ajan_get_user_meta()
 * @uses ajan_core_get_user_displayname()
 * @uses ajan_activity_get_permalink()
 * @uses ajan_core_get_user_domain()
 * @uses ajan_get_settings_slug()
 * @uses ajan_activity_filter_kses()
 * @uses ajan_core_get_core_userdata()
 * @uses wp_specialchars_decode()
 * @uses get_blog_option()
 * @uses ajan_get_root_blog_id()
 * @uses apply_filters() To call the 'ajan_activity_new_comment_notification_to' hook
 * @uses apply_filters() To call the 'ajan_activity_new_comment_notification_subject' hook
 * @uses apply_filters() To call the 'ajan_activity_new_comment_notification_message' hook
 * @uses wp_mail()
 * @uses do_action() To call the 'ajan_activity_sent_reply_to_update_email' hook
 * @uses apply_filters() To call the 'ajan_activity_new_comment_notification_comment_author_to' hook
 * @uses apply_filters() To call the 'ajan_activity_new_comment_notification_comment_author_subject' hook
 * @uses apply_filters() To call the 'ajan_activity_new_comment_notification_comment_author_message' hook
 * @uses do_action() To call the 'ajan_activity_sent_reply_to_reply_email' hook
 *
 * @param int $comment_id The comment id.
 * @param int $commenter_id The ID of the user who posted the comment.
 * @param array $params {@link ajan_activity_new_comment()}
 */
function ajan_activity_new_comment_notification( $comment_id = 0, $commenter_id = 0, $params = array() ) {

	// Set some default parameters
	$activity_id = 0;
	$parent_id   = 0;

	extract( $params );

	$original_activity = new AJAN_Activity_Activity( $activity_id );

	if ( $original_activity->user_id != $commenter_id && 'no' != ajan_get_user_meta( $original_activity->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name   = ajan_core_get_user_displayname( $commenter_id );
		$thread_link   = ajan_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'ajan_get_settings_slug' ) ? ajan_get_settings_slug() : 'settings';
		$settings_link = ajan_core_get_user_domain( $original_activity->user_id ) . $settings_slug . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = ajan_activity_filter_kses( stripslashes($content) );

		// Set up and send the message
		$ud      = ajan_core_get_core_userdata( $original_activity->user_id );
		$to      = $ud->user_email;
		$subject = ajan_get_email_subject( array( 'text' => sprintf( __( '%s replied to one of your updates', 'ajency-activity-and-notifications' ), $poster_name ) ) );
		$message = sprintf( __(
'%1$s replied to one of your updates:

"%2$s"

To view your original update and all comments, log in and visit: %3$s

---------------------
', 'ajency-activity-and-notifications' ), $poster_name, $content, $thread_link );

		// Only show the disable notifications line if the settings component is enabled
		if ( ajan_is_active( 'settings' ) ) {
			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'ajency-activity-and-notifications' ), $settings_link );
		}

		/* Send the message */
		$to = apply_filters( 'ajan_activity_new_comment_notification_to', $to );
		$subject = apply_filters( 'ajan_activity_new_comment_notification_subject', $subject, $poster_name );
		$message = apply_filters( 'ajan_activity_new_comment_notification_message', $message, $poster_name, $content, $thread_link, $settings_link );

		wp_mail( $to, $subject, $message );

		do_action( 'ajan_activity_sent_reply_to_update_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}

	/***
	 * If this is a reply to another comment, send an email notification to the
	 * author of the immediate parent comment.
	 */
	if ( empty( $parent_id ) || ( $activity_id == $parent_id ) ) {
		return false;
	}

	$parent_comment = new AJAN_Activity_Activity( $parent_id );

	if ( $parent_comment->user_id != $commenter_id && $original_activity->user_id != $parent_comment->user_id && 'no' != ajan_get_user_meta( $parent_comment->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name   = ajan_core_get_user_displayname( $commenter_id );
		$thread_link   = ajan_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'ajan_get_settings_slug' ) ? ajan_get_settings_slug() : 'settings';
		$settings_link = ajan_core_get_user_domain( $parent_comment->user_id ) . $settings_slug . '/notifications/';

		// Set up and send the message
		$ud       = ajan_core_get_core_userdata( $parent_comment->user_id );
		$to       = $ud->user_email;
		$subject = ajan_get_email_subject( array( 'text' => sprintf( __( '%s replied to one of your comments', 'ajency-activity-and-notifications' ), $poster_name ) ) );

		$poster_name = stripslashes( $poster_name );
		$content = ajan_activity_filter_kses( stripslashes( $content ) );

$message = sprintf( __(
'%1$s replied to one of your comments:

"%2$s"

To view the original activity, your comment and all replies, log in and visit: %3$s

---------------------
', 'ajency-activity-and-notifications' ), $poster_name, $content, $thread_link );

		// Only show the disable notifications line if the settings component is enabled
		if ( ajan_is_active( 'settings' ) ) {
			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'ajency-activity-and-notifications' ), $settings_link );
		}

		/* Send the message */
		$to = apply_filters( 'ajan_activity_new_comment_notification_comment_author_to', $to );
		$subject = apply_filters( 'ajan_activity_new_comment_notification_comment_author_subject', $subject, $poster_name );
		$message = apply_filters( 'ajan_activity_new_comment_notification_comment_author_message', $message, $poster_name, $content, $settings_link, $thread_link );

		wp_mail( $to, $subject, $message );

		do_action( 'ajan_activity_sent_reply_to_reply_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}
}

/**
 * Helper method to map action arguments to function parameters
 *
 * @since ajency-activity-and-notifications (1.9.0)
 * @param int $comment_id
 * @param array $params
 */
function ajan_activity_new_comment_notification_helper( $comment_id, $params ) {
	ajan_activity_new_comment_notification( $comment_id, $params['user_id'], $params );
}
add_action( 'ajan_activity_comment_posted', 'ajan_activity_new_comment_notification_helper', 10, 2 );

/** Notifications *************************************************************/

/**
 * Format notifications related to activity.
 *
 * @since ajency-activity-and-notifications (1.5)
 *
 * @uses ajan_loggedin_user_domain()
 * @uses ajan_get_activity_slug()
 * @uses ajan_core_get_user_displayname()
 * @uses apply_filters() To call the 'ajan_activity_multiple_at_mentions_notification' hook.
 * @uses apply_filters() To call the 'ajan_activity_single_at_mentions_notification' hook.
 * @uses do_action() To call 'activity_format_notifications' hook.
 *
 * @param string $action The type of activity item. Just 'new_at_mention' for now.
 * @param int $item_id The activity ID.
 * @param int $secondary_item_id In the case of at-mentions, this is the mentioner's ID.
 * @param int $total_items The total number of notifications to format.
 * @param string $format 'string' to get a BuddyBar-compatible notification, 'array' otherwise.
 * @return string $return Formatted @mention notification.
 */
function ajan_activity_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	switch ( $action ) {
		case 'new_at_mention':
			$activity_id      = $item_id;
			$poster_user_id   = $secondary_item_id;
			$at_mention_link  = ajan_loggedin_user_domain() . ajan_get_activity_slug() . '/mentions/';
			$at_mention_title = sprintf( __( '@%s Mentions', 'ajency-activity-and-notifications' ), ajan_get_loggedin_user_username() );

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %1$d new mentions', 'ajency-activity-and-notifications' ), (int) $total_items );
				$filter = 'ajan_activity_multiple_at_mentions_notification';
			} else {
				$user_fullname = ajan_core_get_user_displayname( $poster_user_id );
				$text =  sprintf( __( '%1$s mentioned you', 'ajency-activity-and-notifications' ), $user_fullname );
				$filter = 'ajan_activity_single_at_mentions_notification';
			}
		break;
	}

	if ( 'string' == $format ) {
		$return = apply_filters( $filter, '<a href="' . esc_url( $at_mention_link ) . '" title="' . esc_attr( $at_mention_title ) . '">' . esc_html( $text ) . '</a>', $at_mention_link, (int) $total_items, $activity_id, $poster_user_id );
	} else {
		$return = apply_filters( $filter, array(
			'text' => $text,
			'link' => $at_mention_link
		), $at_mention_link, (int) $total_items, $activity_id, $poster_user_id );
	}

	do_action( 'activity_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Notify a member when their nicename is mentioned in an activity stream item.
 *
 * Hooked to the 'ajan_activity_sent_mention_email' action, we piggy back off the
 * existing email code for now, since it does the heavy lifting for us. In the
 * future when we separate emails from Notifications, this will need its own
 * 'ajan_activity_at_name_send_emails' equivalent helper function.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @param obj $activity
 * @param string $subject (not used)
 * @param string $message (not used)
 * @param string $content (not used)
 * @param int $receiver_user_id
 */
function ajan_activity_at_mention_add_notification( $activity, $subject, $message, $content, $receiver_user_id ) {
	if ( ajan_is_active( 'notifications' ) ) {
		ajan_notifications_add_notification( array(
			'user_id'           => $receiver_user_id,
			'item_id'           => $activity->id,
			'secondary_item_id' => $activity->user_id,
			'component_name'    => activitynotifications()->activity->id,
			'component_action'  => 'new_at_mention',
			'date_notified'     => ajan_core_current_time(),
			'is_new'            => 1,
		) );
	}
}
add_action( 'ajan_activity_sent_mention_email', 'ajan_activity_at_mention_add_notification', 10, 5 );

/**
 * Mark at-mention notifications as read when users visit their Mentions page.
 *
 * @since ajency-activity-and-notifications (1.5)
 *
 * @uses ajan_notifications_mark_all_notifications_by_type()
 */
function ajan_activity_remove_screen_notifications() {
	if ( ! ajan_is_active( 'notifications' ) ) {
		return;
	}

	// Only mark read if you're looking at your own mentions
	if ( ! ajan_is_my_profile() ) {
		return;
	}

	ajan_notifications_mark_notifications_by_type( ajan_loggedin_user_id(), activitynotifications()->activity->id, 'new_at_mention' );
}
add_action( 'ajan_activity_screen_mentions', 'ajan_activity_remove_screen_notifications' );

/**
 * Mark at-mention notification as read when user visits the activity with the mention.
 *
 * @since ajency-activity-and-notifications (2.0.0)
 */
function ajan_activity_remove_screen_notifications_single_activity_permalink( $activity ) {
	if ( ! ajan_is_active( 'notifications' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	// Mark as read any notifications for the current user related to this
	// activity item
	ajan_notifications_mark_notifications_by_item_id( ajan_loggedin_user_id(), $activity->id, activitynotifications()->activity->id, 'new_at_mention' );
}
add_action( 'ajan_activity_screen_single_activity_permalink', 'ajan_activity_remove_screen_notifications_single_activity_permalink' );

/**
 * Delete at-mention notifications when the corresponding activity item is deleted.
 *
 * @since ajency-activity-and-notifications (2.0.0)
 *
 * @param array $activity_ids_deleted IDs of deleted activity items.
 */
function ajan_activity_at_mention_delete_notification( $activity_ids_deleted = array() ) {
	// Let's delete all without checking if content contains any mentions
	// to avoid a query to get the activity
	if ( ajan_is_active( 'notifications' ) && ! empty( $activity_ids_deleted ) ) {
		foreach ( $activity_ids_deleted as $activity_id ) {
			ajan_notifications_delete_all_notifications_by_type( $activity_id, activitynotifications()->activity->id );
		}
	}
}
add_action( 'ajan_activity_deleted_activities', 'ajan_activity_at_mention_delete_notification', 10 );
