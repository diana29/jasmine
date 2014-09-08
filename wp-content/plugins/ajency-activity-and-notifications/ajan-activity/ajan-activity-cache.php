<?php

/**
 * Functions related to the BuddyPress Activity component and the WP Cache.
 *
 * @since ajency-activity-and-notifications (1.6)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Slurp up activitymeta for a specified set of activity items.
 *
 * It grabs all activitymeta associated with all of the activity items passed
 * in $activity_ids and adds it to the WP cache. This improves efficiency when
 * using querying activitymeta inline.
 *
 * @param int|str|array $activity_ids Accepts a single activity ID, or a comma-
 *        separated list or array of activity ids
 */
function ajan_activity_update_meta_cache( $activity_ids = false ) {
	global $ajan;

	$cache_args = array(
		'object_ids' 	   => $activity_ids,
		'object_type' 	   => $ajan->activity->id,
		'object_column'    => 'activity_id',
		'cache_group'      => 'activity_meta',
		'meta_table' 	   => $ajan->activity->table_name_meta,
		'cache_key_prefix' => 'ajan_activity_meta'
	);

	ajan_update_meta_cache( $cache_args );
}

/**
 * Clear a cached activity item when that item is updated.
 *
 * @since 2.0
 *
 * @param AJAN_Activity_Activity $activity
 */
function ajan_activity_clear_cache_for_activity( $activity ) {
	wp_cache_delete( $activity->id, 'ajan_activity' );
}
add_action( 'ajan_activity_after_save', 'ajan_activity_clear_cache_for_activity' );

/**
 * Clear cached data for deleted activity items.
 *
 * @since 2.0
 *
 * @param array $deleted_ids IDs of deleted activity items.
 */
function ajan_activity_clear_cache_for_deleted_activity( $deleted_ids ) {
	foreach ( (array) $deleted_ids as $deleted_id ) {
		wp_cache_delete( $deleted_id, 'ajan_activity' );
	}
}
add_action( 'ajan_activity_deleted_activities', 'ajan_activity_clear_cache_for_deleted_activity' );
