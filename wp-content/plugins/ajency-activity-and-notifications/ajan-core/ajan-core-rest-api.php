<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
//json rest api

if(is_plugin_active('json-rest-api/plugin.php')){

	/**
	 * Change the json rest api plugin prefix from wp-json to api
	 *
	 * @since ajency-activity-and-notifications (0.1)
	 *
	 * @uses json rest api plugin filter hook json_url_prefix
	 */
	function change_json_rest_api_prefix($prefix){

		return "api";

	}
	add_filter( 'json_url_prefix', 'change_json_rest_api_prefix',10,1);


}