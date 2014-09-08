<?php

/**
 * ActivityNotifications Template Functions.
 *
 * This file contains functions necessary to mirror the WordPress core template
 * loading process. Many of those functions are not filterable, and even then
 * would not be robust enough to predict where ActivityNotifications templates might exist.
 *
 * @package ActivityNotifications
 * @subpackage TemplateFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get a ActivityNotifications template part for display in a theme.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses ajan_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 *
 * @param string $slug Template part slug. Used to generate filenames, eg
 *        'friends' for 'friends.php'.
 * @param string $name Optional. Template part name. Used to generate
 *        secondary filenames, eg 'personal' for 'activity-personal.php'.
 * @return string Path to located template. See {@link ajan_locate_template()}.
 */
function ajan_get_template_part( $slug, $name = null ) {

	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'ajan_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return ajan_locate_template( $templates, true, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load Optional. If true, the template file will be loaded when
 *        found. If false, the path will be returned. Default: false.
 * @param bool $require_once Optional. Whether to require_once or require. Has
 *        no effect if $load is false. Default: true.
 * @return string The template filename if one is located.
 */
function ajan_locate_template( $template_names, $load = false, $require_once = true ) {

	// No file found yet
	$located            = false;
	$template_locations = ajan_get_template_stack();

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name  = ltrim( $template_name, '/' );

		// Loop through template stack
		foreach ( (array) $template_locations as $template_location ) {

			// Continue if $template_location is empty
			if ( empty( $template_location ) )
				continue;

			// Check child theme first
			if ( file_exists( trailingslashit( $template_location ) . $template_name ) ) {
				$located = trailingslashit( $template_location ) . $template_name;
				break 2;
			}
		}
	}

	/**
	 * This action exists only to follow the standard ActivityNotifications coding convention,
	 * and should not be used to short-circuit any part of the template locator.
	 *
	 * If you want to override a specific template part, please either filter
	 * 'ajan_get_template_part' or add a new location to the template stack.
	 */
	do_action( 'ajan_locate_template', $located, $template_name, $template_names, $template_locations, $load, $require_once );

	// Maybe load the template if one was located
	$use_themes = defined( 'WP_USE_THEMES' ) && WP_USE_THEMES;
	$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
	if ( ( $use_themes || $doing_ajax ) && ( true == $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Register a new template stack location.
 *
 * This allows for templates to live in places beyond just the parent/child
 * relationship, to allow for custom template locations. Used in conjunction
 * with ajan_locate_template(), this allows for easy template overrides.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @todo Make 'callable' instead of 'function'.
 *
 * @param string $location Callback function that returns the stack location.
 * @param int $priority Optional. The priority parameter as passed to
 *        add_filter(). Default: 10.
 * @return bool See {@link add_filter()}.
 */
function ajan_register_template_stack( $location_callback = '', $priority = 10 ) {

	// Bail if no location, or function does not exist
	if ( empty( $location_callback ) || ! function_exists( $location_callback ) )
		return false;

	// Add location callback to template stack
	return add_filter( 'ajan_template_stack', $location_callback, (int) $priority );
}

/**
 * Deregister a previously registered template stack location.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @see ajan_register_template_stack()
 *
 * @param string $location Callback function that returns the stack location.
 * @param int $priority Optional. The priority parameter passed to
 *        {@link ajan_register_template_stack()}. Default: 10.
 * @return bool See {@link remove_filter()}.
 */
function ajan_deregister_template_stack( $location_callback = '', $priority = 10 ) {

	// Bail if no location, or function does not exist
	if ( empty( $location_callback ) || ! function_exists( $location_callback ) )
		return false;

	// Add location callback to template stack
	return remove_filter( 'ajan_template_stack', $location_callback, (int) $priority );
}

/**
 * Get the "template stack", a list of registered directories where templates can be found.
 *
 * Calls the functions added to the 'ajan_template_stack' filter hook, and return
 * an array of the template locations.
 *
 * @see ajan_register_template_stack()
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @global array $wp_filter Stores all of the filters.
 * @global array $merged_filters Merges the filter hooks using this function..
 * @global array $wp_current_filter stores the list of current filters with
 *         the current one last.
 * @return array The filtered value after all hooked functions are applied to it.
 */
function ajan_get_template_stack() {
	global $wp_filter, $merged_filters, $wp_current_filter;

	// Setup some default variables
	$tag  = 'ajan_template_stack';
	$args = $stack = array();

	// Add 'ajan_template_stack' to the current filter array
	$wp_current_filter[] = $tag;

	// Sort
	if ( ! isset( $merged_filters[ $tag ] ) ) {
		ksort( $wp_filter[$tag] );
		$merged_filters[ $tag ] = true;
	}

	// Ensure we're always at the beginning of the filter array
	reset( $wp_filter[ $tag ] );

	// Loop through 'ajan_template_stack' filters, and call callback functions
	do {
		foreach( (array) current( $wp_filter[$tag] ) as $the_ ) {
			if ( ! is_null( $the_['function'] ) ) {
				$args[1] = $stack;
				$stack[] = call_user_func_array( $the_['function'], array_slice( $args, 1, (int) $the_['accepted_args'] ) );
			}
		}
	} while ( next( $wp_filter[$tag] ) !== false );

	// Remove 'ajan_template_stack' from the current filter array
	array_pop( $wp_current_filter );

	// Remove empties and duplicates
	$stack = array_unique( array_filter( $stack ) );

	return (array) apply_filters( 'ajan_get_template_stack', $stack ) ;
}

/**
 * Put a template part into an output buffer, and return it.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @see ajan_get_template_part() for a description of $slug and $name params.
 *
 * @param string $slug See {@link ajan_get_template_part()}.
 * @param string $name See {@link ajan_get_template_part()}.
 * @param bool $echo If true, template content will be echoed. If false,
 *        returned. Default: true.
 * @return string|null If $echo, returns the template content.
 */
function ajan_buffer_template_part( $slug, $name = null, $echo = true ) {
	ob_start();

	// Remove 'ajan_replace_the_content' filter to prevent infinite loops
	remove_filter( 'the_content', 'ajan_replace_the_content' );

	ajan_get_template_part( $slug, $name );

	// Remove 'ajan_replace_the_content' filter to prevent infinite loops
	add_filter( 'the_content', 'ajan_replace_the_content' );

	// Get the output buffer contents
	$output = ob_get_clean();

	// Echo or return the output buffer contents
	if ( true === $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Retrieve the path to a template.
 *
 * Used to quickly retrieve the path of a template without including the file
 * extension. It will also check the parent theme and theme-compat theme with
 * the use of {@link ajan_locate_template()}. Allows for more generic template
 * locations without the use of the other get_*_template() functions.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses ajan_set_theme_compat_templates()
 * @uses ajan_locate_template()
 * @uses ajan_set_theme_compat_template()
 *
 * @param string $type Filename without extension.
 * @param array $templates An optional list of template candidates.
 * @return string Full path to file.
 */
function ajan_get_query_template( $type, $templates = array() ) {
	$type = preg_replace( '|[^a-z0-9-]+|', '', $type );

	if ( empty( $templates ) )
		$templates = array( "{$type}.php" );

	// Filter possible templates, try to match one, and set any ActivityNotifications theme
	// compat properties so they can be cross-checked later.
	$templates = apply_filters( "ajan_get_{$type}_template", $templates );
	$templates = ajan_set_theme_compat_templates( $templates );
	$template  = ajan_locate_template( $templates );
	$template  = ajan_set_theme_compat_template( $template );

	return apply_filters( "ajan_{$type}_template", $template );
}

/**
 * Get the possible subdirectories to check for templates in
 *
 * @since ActivityNotifications (1.7)
 * @param array $templates Templates we are looking for
 * @return array Possible subfolders to look in
 */
function ajan_get_template_locations( $templates = array() ) {
	$locations = array(
		'ajency-activity-and-notifications',
		'community',
		''
	);
	return apply_filters( 'ajan_get_template_locations', $locations, $templates );
}

/**
 * Add template locations to template files being searched for.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param array $stacks Array of template locations.
 * @return array() Array of all template locations registered so far.
 */
function ajan_add_template_stack_locations( $stacks = array() ) {
	$retval = array();

	// Get alternate locations
	$locations = ajan_get_template_locations();

	// Loop through locations and stacks and combine
	foreach ( (array) $stacks as $stack )
		foreach ( (array) $locations as $custom_location )
			$retval[] = untrailingslashit( trailingslashit( $stack ) . $custom_location );

	return apply_filters( 'ajan_add_template_stack_locations', array_unique( $retval ), $stacks );
}

/**
 * Add checks for ActivityNotifications conditions to 'parse_query' action.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param WP_Query $posts_query
 */
function ajan_parse_query( $posts_query ) {

	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() )
		return;

	// Bail if filters are suppressed on this query
	if ( true == $posts_query->get( 'suppress_filters' ) )
		return;

	// Bail if in admin
	if ( is_admin() )
		return;

	// Allow ActivityNotifications components to parse the main query
	do_action_ref_array( 'ajan_parse_query', array( &$posts_query ) );
}

/**
 * Possibly intercept the template being loaded.
 *
 * Listens to the 'template_include' filter and waits for any ActivityNotifications specific
 * template condition to be met. If one is met and the template file exists,
 * it will be used; otherwise
 *
 * Note that the _edit() checks are ahead of their counterparts, to prevent them
 * from being stomped on accident.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $template
 * @return string The path to the template file that is being used.
 */
function ajan_template_include_theme_supports( $template = '' ) {

	// Look for root ActivityNotifications template files in parent/child themes
	$new_template = apply_filters( 'ajan_get_root_template', false, $template );

	// A ActivityNotifications template file was located, so override the WordPress
	// template and use it to switch off ActivityNotifications's theme compatibility.
	if ( !empty( $new_template ) ) {
		$template = ajan_set_template_included( $new_template );
	}

	return apply_filters( 'ajan_template_include_theme_supports', $template );
}

/**
 * Set the included template.
 *
 * @since ActivityNotifications (1.8.0)
 *
 * @param mixed $template Default: false.
 * @return mixed False if empty. Template name if template included.
 */
function ajan_set_template_included( $template = false ) {
	activitynotifications()->theme_compat->found_template = $template;

	return activitynotifications()->theme_compat->found_template;
}

/**
 * Is a ActivityNotifications template being included?
 *
 * @since ActivityNotifications (1.8.0)
 * @return bool True if yes, false if no.
 */
function ajan_is_template_included() {
	return ! empty( activitynotifications()->theme_compat->found_template );
}

/**
 * Attempt to load a custom BP functions file, similar to each themes functions.php file.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @global string $pagenow
 * @uses ajan_locate_template()
 */
function ajan_load_theme_functions() {
	global $pagenow, $wp_query;

	// do not load our custom BP functions file if theme compat is disabled
	if ( ! ajan_use_theme_compat_with_current_theme() ) {
		return;
	}

	// Do not include on ActivityNotifications deactivation
	if ( ajan_is_deactivation() )
		return;

	// If the $wp_query global is empty (the main query has not been run,
	// or has been reset), load_template() will fail at setting certain
	// global values. This does not happen on a normal page load, but can
	// cause problems when running automated tests
	if ( ! is_a( $wp_query, 'WP_Query' ) ) {
		return;
	}

	// Only include if not installing or if activating via wp-activate.php
	if ( ! defined( 'WP_INSTALLING' ) || 'wp-activate.php' === $pagenow ) {
		ajan_locate_template( 'buddypress-functions.php', true );
	}
}

/**
 * Get the templates to use as the endpoint for ActivityNotifications template parts.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @return array Array of possible root level wrapper template files.
 */
function ajan_get_theme_compat_templates() {
	$templates = array(
		'plugin-buddypress.php',
		'buddypress.php',
		'community.php',
		'generic.php',
		'page.php',
		'single.php',
		'index.php'
	);
	return ajan_get_query_template( 'ajency-activity-and-notifications', $templates );
}
