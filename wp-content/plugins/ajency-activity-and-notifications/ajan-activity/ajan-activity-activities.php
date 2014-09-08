<?php

/**
 * register default plugin activity components
 *
 * @since ajency-activity-and-notifications (0.1.0)
 * @uses ajan_has_activities() to get activities.
 * @uses ajan_register_plugin_activity_actions filter hook to return the as it is 
 *  
 */

  function ajan_register_plugin_activity_actions($args){

    $args = array(
		        array( 	'component_id'		=>	'user',
				      	'type'				=>	'user_registered',
				        'description'		=>	'New User Registered',
				        'format_callback'	=> 	'activity_format_activity_action_user_registered'
					),
		        array( 	'component_id'		=>	'activity',
				      	'type'				=>	'activity_update',
				        'description'		=>	'Posted an update',
				        'format_callback'	=> 	'activity_format_activity_action_activity_update'
					),
		); 
	return $args;

}
add_filter('ajan_register_plugin_activity_actions','ajan_register_plugin_activity_actions',9,1);



/**
 * record activity on new user registeration
 *
 * @since ajency-activity-and-notifications (0.1.0)
 * @uses user_register action hook of wordpress to register the activity
 * @uses ajan_activity_add() method to record the activity 
 *  
 */

function record_new_user_registered_activity( $registered_user_id ) {

    global $user_ID; // current logged in user

    $creator_user_info = get_userdata($user_ID);

    $registered_user_info = get_userdata($registered_user_id);

    if($user_ID ==$registered_user_id || $user_ID==0){

    	 $action = "User ".$registered_user_info->display_name." registered on ".site_url();

    	 $content = "";

		 $action_user_id = $registered_user_id; //store the regisering users id as the user_id for the activity

    }else{

    	 $action = "User ".$creator_user_info->display_name." registered user ".$registered_user_info->display_name." on ".site_url();

    	 $content = "";

		 $action_user_id = $user_ID; //store logged in user id as the user_id for the activity

    }

		$args = array(
		 
						'action'            => $action,    // The activity action - e.g. "Jon Doe posted an update"
						
						'content'           => $content,    // Optional: The content of the activity item e.g. "BuddyPress is awesome guys!"

						'component'         => 'user', // The name/ID of the component e.g. groups, profile, mycomponent
						
						'type'              => 'user_registered', // The activity type e.g. activity_update, profile_updated
						 
						'user_id'           => $action_user_id, // Optional: The user to record the activity for, can be false if this activity is not for a user.
						
						'item_id' 			=> $registered_user_id
				);

	ajan_activity_add($args); 

}

add_action( 'user_register', 'record_new_user_registered_activity', 10, 1 );