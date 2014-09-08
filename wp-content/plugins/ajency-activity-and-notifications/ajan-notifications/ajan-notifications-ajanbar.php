<?php

/**
 * BuddyPress Notifications Navigational Functions.
 *
 * Sets up navigation elements, including BuddyBar functionality, for the
 * Notifications component.
 *
 * @package BuddyPress
 * @subpackage NotificationsBuddyBar
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Create the Notifications menu for the BuddyBar.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_buddybar_menu() {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	echo '<li id="bp-adminbar-notifications-menu"><a href="' . esc_url( ajan_loggedin_user_domain() ) . '">';
	_e( 'Notifications', 'ajency-activity-and-notifications' );

	if ( $notification_count = ajan_notifications_get_unread_notification_count( ajan_loggedin_user_id() ) ) : ?>
		<span><?php echo ajan_core_number_format( $notification_count ); ?></span>
	<?php
	endif;

	echo '</a>';
	echo '<ul>';

	if ( $notifications = ajan_notifications_get_notifications_for_user( ajan_loggedin_user_id() ) ) {
		$counter = 0;
		for ( $i = 0, $count = count( $notifications ); $i < $count; ++$i ) {
			$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : ''; ?>

			<li<?php echo $alt ?>><?php echo $notifications[$i] ?></li>

			<?php $counter++;
		}
	} else { ?>

		<li><a href="<?php echo esc_url( ajan_loggedin_user_domain() ); ?>"><?php _e( 'No new notifications.', 'ajency-activity-and-notifications' ); ?></a></li>

	<?php
	}

	echo '</ul>';
	echo '</li>';
}
add_action( 'ajan_adminbar_menus', 'ajan_adminbar_notifications_menu', 8 );
