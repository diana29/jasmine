<?php


function activate_notification_rest_api(){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
//json rest api

if(is_plugin_active('json-rest-api/plugin.php')){

/**
	 * plugin api calls class
	 *
	 * @since ajency-activity-and-notifications (0.1)
	 *
	 * @uses json rest api plugin action hook wp_json_server_before_serve
	 */
	function ajan_noticiation_api_init() {

			global $user_ID; 
			
			global $ajan_api_notification;

			$ajan_api_notification = new AJAN_API_Notification($user_ID);

			add_filter( 'json_endpoints', array( $ajan_api_notification, 'register_routes' ) );
	}

	add_action( 'wp_json_server_before_serve', 'ajan_noticiation_api_init' );

	/**
	 * Extended class defining api cals for activites
	 *
	 * @since ajency-activity-and-notifications (0.1)
	 * 
	 */
	class AJAN_API_Notification {

		public function __construct( $user_id = false ) {

				   $this->user_id = $user_id ;
				}

		public function register_routes( $routes ) {
		$routes['/notification/create'] = array(
			array( array( $this, 'add_notification'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
		);
		$routes['/notifications/me'] = array(
			//returns the collection of logged in user activities
			array( array( $this, 'get_logged_in_user_notifications'), WP_JSON_Server::READABLE ),	 
		); 
		 

		// Add more custom routes here

		return $routes;
	}

		function get_user_notifications($id){

				return array(ajan_get_notifications_for_user($id));
		}

		function get_logged_in_user_notifications(){
 
  
				return $this->get_user_notifications($this->user_id);

		}

		function add_notification(){

			global $user_ID;

	 			$activity = array();

	 			$error = array();

	 			$status = "";

	 			if(isset($_POST["user_id"])){
	 				
	 				$activity['user_id'] = $_POST["user_id"];
	 			}

	 			if(isset($_POST["item_id"])){
	 				
	 				$activity['item_id'] = $_POST["item_id"];
	 			}

	 			if(isset($_POST["secondary_item_id"])){
	 				
	 				$activity['secondary_item_id'] = $_POST["secondary_item_id"];
	 			}

	 			if(isset($_POST["content"])){
	 				
	 				$activity['content'] = $_POST["content"];
	 			}

	 			if(isset($_POST["component_name"])){
	 				
	 				$activity['component_name'] = $_POST["component_name"];
	 			}

	 			if(isset($_POST["component_action"])){
	 				
	 				$activity['component_action'] = $_POST["component_action"];
	 			}
	 
	 			if(!isset($_POST["'component_name' "]) || !isset($_POST["'component_action' "]) || empty($_POST["component_name"]) || empty($_POST["component_action"])){
	 				
	 				 $error[] = "Notification component name or component action not set.";
	 			}   
	 			if(count($error)==0){
	  
					$response = ajan_notifications_add_notification ($activity); 

					$status = "1";

	 			}else{

	 				$response = $error;

	 				$status = "0";


	 			}
				
				$response = array('status'=>$status,'response' => $response);

				$response = json_encode( $response );

			    header( "Content-Type: application/json" );

			    echo $response;

			    exit;

		}
	}

}
}
add_action( 'ajan_init', 'activate_notification_rest_api', 13 );