<?php
/**
 * ActivityNotifications DB schema
 *
 * @package ActivityNotifications
 * @subpackage CoreAdministration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function ajan_core_set_charset() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// ActivityNotifications component DB schema
	return !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : '';
}

function ajan_core_install( $active_components = false ) {
 
		ajan_core_install_activity_streams();
 
		ajan_core_install_notifications();
 
}

function ajan_core_install_notifications() {

	$sql             = array();
	$charset_collate = ajan_core_set_charset();
	$ajan_prefix       = ajan_core_get_table_prefix();

	$sql[] = "CREATE TABLE {$ajan_prefix}ajan_notifications (
	  		    id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    user_id bigint(20) NOT NULL,
			    item_id bigint(20) NOT NULL,
			    secondary_item_id bigint(20),
	  		    component_name varchar(75) NOT NULL,
			    component_action varchar(75) NOT NULL,
	  		    date_notified datetime NOT NULL,
			    is_new bool NOT NULL DEFAULT 0,
		        KEY item_id (item_id),
			    KEY secondary_item_id (secondary_item_id),
			    KEY user_id (user_id),
			    KEY is_new (is_new),
			    KEY component_name (component_name),
	 	   	    KEY component_action (component_action),
			    KEY useritem (user_id,is_new)
		       ) {$charset_collate};";

	dbDelta( $sql );
}

function ajan_core_install_activity_streams() {

	$sql             = array();
	$charset_collate = ajan_core_set_charset();
	$ajan_prefix       = ajan_core_get_table_prefix();

	$sql[] = "CREATE TABLE {$ajan_prefix}ajan_activity (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id bigint(20) NOT NULL,
				component varchar(75) NOT NULL,
				type varchar(75) NOT NULL,
				action text NOT NULL,
				content longtext NOT NULL, 
				item_id bigint(20) NOT NULL,
				secondary_item_id bigint(20) DEFAULT NULL,
				date_recorded datetime NOT NULL,
				hide_sitewide bool DEFAULT 0,
				mptt_left int(11) NOT NULL DEFAULT 0,
				mptt_right int(11) NOT NULL DEFAULT 0, 
				KEY date_recorded (date_recorded),
				KEY user_id (user_id),
				KEY item_id (item_id),
				KEY secondary_item_id (secondary_item_id),
				KEY component (component),
				KEY type (type),
				KEY mptt_left (mptt_left),
				KEY mptt_right (mptt_right),
				KEY hide_sitewide (hide_sitewide)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE {$ajan_prefix}ajan_activity_meta (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				activity_id bigint(20) NOT NULL,
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext DEFAULT NULL,
				KEY activity_id (activity_id),
				KEY meta_key (meta_key)
		   	   ) {$charset_collate};";
 
	dbDelta( $sql );
}
 
 
 