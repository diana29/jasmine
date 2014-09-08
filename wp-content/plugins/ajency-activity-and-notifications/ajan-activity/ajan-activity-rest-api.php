<?php


function activate_activity_rest_api(){




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
		function ajan_activity_api_init() {
				global $user_ID; 
			 
				global $ajan_api_activity;

				$ajan_api_activity = new AJAN_API_Activity($user_ID);

				add_filter( 'json_endpoints', array( $ajan_api_activity, 'register_routes' ) );
		}

		add_action( 'wp_json_server_before_serve', 'ajan_activity_api_init' );

		/**
		 * Extended class defining api cals for activites
		 *
		 * @since ajency-activity-and-notifications (0.1)
		 * 
		 */
		class AJAN_API_Activity {

			public function __construct( $user_id = false ) {
				   $this->user_id = $user_id ;
				}

			public function register_routes( $routes ) {
			$routes['/activity/create'] = array(
				array( array( $this, 'add_activity'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
			);
			$routes['/activity/delete/(?P<id>\d+)'] = array(
				 array( array( $this, 'delete_activity'), WP_JSON_Server::DELETABLE)
				
			);
 			$routes['/activity/update/(?P<id>\d+)'] = array(
				 array( array( $this, 'update_activity'),      WP_JSON_Server::EDITABLE | WP_JSON_Server::ACCEPT_JSON ),
				
			);
			$routes['/comment/create'] = array(
				array( array( $this, 'add_comment'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
			);
			$routes['/activities/me'] = array(
				//returns the collection of logged in user activities
				array( array( $this, 'get_logged_in_user_activities'), WP_JSON_Server::READABLE ),	 
			);
			$routes['/activities/user/(?P<id>\d+)'] = array(
				//returns the activities of a user ; user_id should the id of the user whose activites are required
				array( array( $this, 'get_user_activities'), WP_JSON_Server::READABLE ),	 
			);
			 

			// Add more custom routes here

			return $routes;
		}

		function get_user_activities($id,$component=''){

				$args['user_id'] = $id;
				/*if(is_array($filter)){
					foreach($filter as $filter_key => $filter_item){
						$args[$filter_key] = $filter_item;
					}
				}*/
				if(isset($_REQUEST['component'])){
  					$component = $_REQUEST['component'];
  				}
				$args['component'] = $component;
				return ajan_get_user_personal_activities($args);
		}

		function get_logged_in_user_activities(){
			$component = "";
  				if(isset($_REQUEST['component'])){
  					$component = $_REQUEST['component'];
  				}
				return $this->get_user_activities($this->user_id,$component);

		}

		function add_activity(){
	 			
	 			global $user_ID;

	 			$activity = array();

	 			$error = array();

	 			$status = false;  

	 			if(isset($_POST["user_id"])){
	 				
	 				$activity['user_id'] = $_POST["user_id"];
	 			}

	 			if(isset($_POST["action"])){
	 				
	 				$activity['action'] = $_POST["action"];
	 			}

	 			if(isset($_POST["content"])){
	 				
	 				$activity['content'] = $_POST["content"];
	 			}

	 			if(isset($_POST["component"])){
	 				
	 				$activity['component'] = $_POST["component"];
	 			}

	 			if(isset($_POST["type"])){
	 				
	 				$activity['type'] = $_POST["type"];
	 			}
	 
	 			if(!isset($_POST["component"]) || !isset($_POST["type"]) || empty($_POST["component"]) || empty($_POST["type"])){
	 				
	 				 $error[] = "Activity component or type not set.";
	 			}   
	 			if(count($error)==0){
	  
					$response = ajan_activity_add($activity); 
					if($response !=false){

						$response = ajan_get_activity_by_id($response);
					}
					$status = true;

	 			}else{

	 				$response = $error;

	 				$status = false;


	 			}
				
				$response = array('status'=>$status,'response' => $response);

				$response = json_encode( $response );

			    header( "Content-Type: application/json" );

			    echo $response;

			    exit;

		}

		function update_activity($id){
	 			
	 			global $user_ID;

	 			$activity = array();

	 			$error = array();

	 			$status = false;  
				$id = (int) $id;

				if ( empty( $id ) ) {
					$error[] = "Invalid Activity ID";
				} 
	 				
	 			$activity['id'] = $id;
	 			 

	 			if(isset($_POST["action"])){
	 				
	 				$activity['action'] = $_POST["action"];
	 			}

	 			if(isset($_POST["content"])){
	 				
	 				$activity['content'] = $_POST["content"];
	 			}

	 			if(isset($_POST["component"])){
	 				
	 				$activity['component'] = $_POST["component"];
	 			}

	 			if(isset($_POST["type"])){
	 				
	 				$activity['type'] = $_POST["type"];
	 			}
	  
	  			 var_dump($activity);

	 			if(count($error)==0){
 
					$response = ajan_activity_add($activity); 

					if($response !=false){

						$response = ajan_get_activity_by_id($response);
					}
					$status = true;

	 			}else{

	 				$response = $error;

	 				$status = false;


	 			}
				
				$response = array('status'=>$status,'response' => $response);

				$response = json_encode( $response );

			    header( "Content-Type: application/json" );

			    echo $response;

			    exit;

		}
 

		function delete_activity($id){
 
			$status = ajan_activity_delete_by_id($id);
			$response = json_encode(array('status'=>$status ));

			header( "Content-Type: application/json" );

			echo $response;

			exit;

		}
		function add_comment(){
	 			
	 			global $user_ID;

	 			$comment = array();

	 			$error = array();

	 			$status = false;

	 			if(isset($_POST["user_id"])){
	 				
	 				$comment['user_id'] = $_POST["user_id"];
	 			} 
	 			if(isset($_POST["content"])){
	 				
	 				$comment['content'] = $_POST["content"];
	 			}
	 			if(isset($_POST["parent_id"])){
	 				
	 				$comment['parent_id'] = $_POST["parent_id"];
	 			}
	 			if(isset($_POST["activity_id"])){
	 				
	 				$comment['activity_id'] = $_POST["activity_id"];
	 			}
 
	 			if(count($error)==0){
	 
					$response = ajan_activity_new_comment($comment); 
					 
					if($response !=false){
						$response = ajan_get_activity_by_id($response);
					}
					
					$status = true;

	 			}else{

	 				$response = $error;

	 				$status = false;


	 			}
				
				$response = array('status'=>$status,'response' => $response);

				$response = json_encode( $response );

			    header( "Content-Type: application/json" );

			    echo $response;

			    exit;

		}
		// ...
	}


	}

}
add_action( 'ajan_init', 'activate_activity_rest_api', 13 );





