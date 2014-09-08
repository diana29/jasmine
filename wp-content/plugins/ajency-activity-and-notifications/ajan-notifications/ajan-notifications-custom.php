<?php

/**
 * Allow core components and dependent plugins to register activity actions.
 *
 * @since ajency-activity-and-notifications (1.2)
 *
 * @uses do_action() To call 'ajan_register_notification_actions' hook.
 */
function ajan_register_notification_actions() { 

	do_action( 'ajan_theme_set_notification_action' );
 
}
add_action( 'ajan_init', 'ajan_register_notification_actions', 11 );

/**
 * Register the notification actions for updates
 *
 * @since ajency-activity-and-notifications (1.6)
 *
 * @global object $ajan BuddyPress global settings.
 */
function ajan_theme_set_notification_action() {
	global $ajan;
	$components = array();
	$components[] = array(	'component_id'		=>	'activity', 
						'format_callback'	=>	'ajan_activity_format_notifications'
					 ); 

	$theme_notification_actions = apply_filters('ajan_register_theme_notification_actions',$components);
 
	foreach($theme_notification_actions as $theme_notification_action){
			ajan_notification_set_action($theme_notification_action['component_id'], 
			$theme_notification_action['format_callback'] 
		); 
	}

}
add_action( 'ajan_theme_set_notification_action', 'ajan_theme_set_notification_action' );



function ajan_notification_set_action($component,$callback){
 
	$ajan = activitynotifications(); 

	$ajan->{'notifications'}->notification_components[$component] =  $callback ;
 
	return true;
}


/**
 * get users notifications
 *
 * @since ajency-activity-and-notifications (0.1.0)
 * @uses ajan_notifications_get_notifications_for_user() to user notification. 
 * @param $user_id the users whose notifications need to be returned, 
 * if not passed the logged in users notifications are returned  
 */
function  ajan_get_notifications_for_user($user_id=0){

//if no user_id is passed then get the current logged in user id and return his activities
	if($user_id==0){

		global $user_ID;

		$user_id = $user_ID;

	}
 

	return ajan_notifications_get_notifications_for_user($user_id,'object');
}

 