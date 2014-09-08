<?php

/**
 * ActivityNotifications URI catcher.
 *
 * Functions for parsing the URI and determining which ActivityNotifications template file
 * to use on-screen.
 *
 * @package ActivityNotifications
 * @subpackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Analyze the URI and break it down into ActivityNotifications-usable chunks.
 *
 * ActivityNotifications can use complete custom friendly URIs without the user having to
 * add new rewrite rules. Custom components are able to use their own custom
 * URI structures with very little work.
 *
 * The URIs are broken down as follows:
 *   - http:// domain.com / members / andy / [current_component] / [current_action] / [action_variables] / [action_variables] / ...
 *   - OUTSIDE ROOT: http:// domain.com / sites / buddypress / members / andy / [current_component] / [current_action] / [action_variables] / [action_variables] / ...
 *
 *	Example:
 *    - http://domain.com/members/andy/profile/edit/group/5/
 *    - $ajan->current_component: string 'xprofile'
 *    - $ajan->current_action: string 'edit'
 *    - $ajan->action_variables: array ['group', 5]
 *
 * @since ActivityNotifications (1.0.0)
 */
function ajan_core_set_uri_globals() {
	global $ajan, $current_blog, $wp_rewrite;

	// Don't catch URIs on non-root blogs unless multiblog mode is on
	if ( !ajan_is_root_blog() && !ajan_is_multiblog_mode() )
		return false;

	// Define local variables
	$root_profile = $match   = false;
	$key_slugs    = $matches = $uri_chunks = array();

	// Fetch all the WP page names for each component
	if ( empty( $ajan->pages ) )
		$ajan->pages = ajan_core_get_directory_pages();

	// Ajax or not?
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX || strpos( $_SERVER['REQUEST_URI'], 'wp-load.php' ) )
		$path = ajan_core_referrer();
	else
		$path = esc_url( $_SERVER['REQUEST_URI'] );

	// Filter the path
	$path = apply_filters( 'ajan_uri', $path );

	// Take GET variables off the URL to avoid problems
	$path = strtok( $path, '?' );

	// Fetch current URI and explode each part separated by '/' into an array
	$ajan_uri = explode( '/', $path );

	// Loop and remove empties
	foreach ( (array) $ajan_uri as $key => $uri_chunk ) {
		if ( empty( $ajan_uri[$key] ) ) {
			unset( $ajan_uri[$key] );
		}
	}

	// If running off blog other than root, any subdirectory names must be
	// removed from $ajan_uri. This includes two cases:
	//
	//    1. when WP is installed in a subdirectory,
	//    2. when BP is running on secondary blog of a subdirectory
	//       multisite installation. Phew!
	if ( is_multisite() && !is_subdomain_install() && ( ajan_is_multiblog_mode() || 1 != ajan_get_root_blog_id() ) ) {

		// Blow chunks
		$chunks = explode( '/', $current_blog->path );

		// If chunks exist...
		if ( !empty( $chunks ) ) {

			// ...loop through them...
			foreach( $chunks as $key => $chunk ) {
				$bkey = array_search( $chunk, $ajan_uri );

				// ...and unset offending keys
				if ( false !== $bkey ) {
					unset( $ajan_uri[$bkey] );
				}

				$ajan_uri = array_values( $ajan_uri );
			}
		}
	}

	// Get site path items
	$paths = explode( '/', ajan_core_get_site_path() );

	// Take empties off the end of path
	if ( empty( $paths[count( $paths ) - 1] ) )
		array_pop( $paths );

	// Take empties off the start of path
	if ( empty( $paths[0] ) )
		array_shift( $paths );

	// Reset indexes
	$ajan_uri = array_values( $ajan_uri );
	$paths  = array_values( $paths );

	// Unset URI indices if they intersect with the paths
	foreach ( (array) $ajan_uri as $key => $uri_chunk ) {
		if ( isset( $paths[$key] ) && $uri_chunk == $paths[$key] ) {
			unset( $ajan_uri[$key] );
		}
	}

	// Reset the keys by merging with an empty array
	$ajan_uri = array_merge( array(), $ajan_uri );

	// If a component is set to the front page, force its name into $ajan_uri
	// so that $current_component is populated (unless a specific WP post is being requested
	// via a URL parameter, usually signifying Preview mode)
	if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) && empty( $ajan_uri ) && empty( $_GET['p'] ) && empty( $_GET['page_id'] ) ) {
		$post = get_post( get_option( 'page_on_front' ) );
		if ( !empty( $post ) ) {
			$ajan_uri[0] = $post->post_name;
		}
	}

	// Keep the unfiltered URI safe
	$ajan->unfiltered_uri = $ajan_uri;

	// Don't use $ajan_unfiltered_uri, this is only for backpat with old plugins. Use $ajan->unfiltered_uri.
	$GLOBALS['ajan_unfiltered_uri'] = &$ajan->unfiltered_uri;

	// Get slugs of pages into array
	foreach ( (array) $ajan->pages as $page_key => $ajan_page )
		$key_slugs[$page_key] = trailingslashit( '/' . $ajan_page->slug );

	// Bail if keyslugs are empty, as BP is not setup correct
	if ( empty( $key_slugs ) )
		return;

	// Loop through page slugs and look for exact match to path
	foreach ( $key_slugs as $key => $slug ) {
		if ( $slug == $path ) {
			$match      = $ajan->pages->{$key};
			$match->key = $key;
			$matches[]  = 1;
			break;
		}
	}

	// No exact match, so look for partials
	if ( empty( $match ) ) {

		// Loop through each page in the $ajan->pages global
		foreach ( (array) $ajan->pages as $page_key => $ajan_page ) {

			// Look for a match (check members first)
			if ( in_array( $ajan_page->name, (array) $ajan_uri ) ) {

				// Match found, now match the slug to make sure.
				$uri_chunks = explode( '/', $ajan_page->slug );

				// Loop through uri_chunks
				foreach ( (array) $uri_chunks as $key => $uri_chunk ) {

					// Make sure chunk is in the correct position
					if ( !empty( $ajan_uri[$key] ) && ( $ajan_uri[$key] == $uri_chunk ) ) {
						$matches[] = 1;

					// No match
					} else {
						$matches[] = 0;
					}
				}

				// Have a match
				if ( !in_array( 0, (array) $matches ) ) {
					$match      = $ajan_page;
					$match->key = $page_key;
					break;
				};

				// Unset matches
				unset( $matches );
			}

			// Unset uri chunks
			unset( $uri_chunks );
		}
	}

	// URLs with AJAN_ENABLE_ROOT_PROFILES enabled won't be caught above
	if ( empty( $matches ) && ajan_core_enable_root_profiles() ) {

		// Switch field based on compat
		$field = ajan_is_username_compatibility_mode() ? 'login' : 'slug';

		// Make sure there's a user corresponding to $ajan_uri[0]
		if ( !empty( $ajan->pages->members ) && !empty( $ajan_uri[0] ) && $root_profile = get_user_by( $field, $ajan_uri[0] ) ) {

			// Force BP to recognize that this is a members page
			$matches[]  = 1;
			$match      = $ajan->pages->members;
			$match->key = 'members';
		}
	}

	// Search doesn't have an associated page, so we check for it separately
	if ( !empty( $ajan_uri[0] ) && ( ajan_get_search_slug() == $ajan_uri[0] ) ) {
		$matches[]   = 1;
		$match       = new stdClass;
		$match->key  = 'search';
		$match->slug = ajan_get_search_slug();
	}

	// This is not a ActivityNotifications page, so just return.
	if ( empty( $matches ) )
		return false;

	$wp_rewrite->use_verbose_page_rules = false;

	// Find the offset. With $root_profile set, we fudge the offset down so later parsing works
	$slug       = !empty ( $match ) ? explode( '/', $match->slug ) : '';
	$uri_offset = empty( $root_profile ) ? 0 : -1;

	// Rejig the offset
	if ( !empty( $slug ) && ( 1 < count( $slug ) ) ) {
		array_pop( $slug );
		$uri_offset = count( $slug );
	}

	// Global the unfiltered offset to use in ajan_core_load_template().
	// To avoid PHP warnings in ajan_core_load_template(), it must always be >= 0
	$ajan->unfiltered_uri_offset = $uri_offset >= 0 ? $uri_offset : 0;

	// We have an exact match
	if ( isset( $match->key ) ) {

		// Set current component to matched key
		$ajan->current_component = $match->key;

		// If members component, do more work to find the actual component
		if ( 'members' == $match->key ) {

			// Viewing a specific user
			if ( !empty( $ajan_uri[$uri_offset + 1] ) ) {

				// Switch the displayed_user based on compatbility mode
				if ( ajan_is_username_compatibility_mode() ) {
					$ajan->displayed_user->id = (int) ajan_core_get_userid( urldecode( $ajan_uri[$uri_offset + 1] ) );
				} else {
					$ajan->displayed_user->id = (int) ajan_core_get_userid_from_nicename( urldecode( $ajan_uri[$uri_offset + 1] ) );
				}

				if ( !ajan_displayed_user_id() ) {

					// Prevent components from loading their templates
					$ajan->current_component = '';

					ajan_do_404();
					return;
				}

				// If the displayed user is marked as a spammer, 404 (unless logged-
				// in user is a super admin)
				if ( ajan_displayed_user_id() && ajan_is_user_spammer( ajan_displayed_user_id() ) ) {
					if ( ajan_current_user_can( 'ajan_moderate' ) ) {
						ajan_core_add_message( __( 'This user has been marked as a spammer. Only site admins can view this profile.', 'ajency-activity-and-notifications' ), 'warning' );
					} else {
						ajan_do_404();
						return;
					}
				}

				// Bump the offset
				if ( isset( $ajan_uri[$uri_offset + 2] ) ) {
					$ajan_uri                = array_merge( array(), array_slice( $ajan_uri, $uri_offset + 2 ) );
					$ajan->current_component = $ajan_uri[0];

				// No component, so default will be picked later
				} else {
					$ajan_uri                = array_merge( array(), array_slice( $ajan_uri, $uri_offset + 2 ) );
					$ajan->current_component = '';
				}

				// Reset the offset
				$uri_offset = 0;
			}
		}
	}

	// Set the current action
	$ajan->current_action = isset( $ajan_uri[$uri_offset + 1] ) ? $ajan_uri[$uri_offset + 1] : '';

	// Slice the rest of the $ajan_uri array and reset offset
	$ajan_uri      = array_slice( $ajan_uri, $uri_offset + 2 );
	$uri_offset  = 0;

	// Set the entire URI as the action variables, we will unset the current_component and action in a second
	$ajan->action_variables = $ajan_uri;

	// Reset the keys by merging with an empty array
	$ajan->action_variables = array_merge( array(), $ajan->action_variables );
}

/**
 * Are root profiles enabled and allowed?
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @return bool True if yes, false if no.
 */
function ajan_core_enable_root_profiles() {

	$retval = false;

	if ( defined( 'AJAN_ENABLE_ROOT_PROFILES' ) && ( true == AJAN_ENABLE_ROOT_PROFILES ) )
		$retval = true;

	return apply_filters( 'ajan_core_enable_root_profiles', $retval );
}

/**
 * Load a specific template file with fallback support.
 *
 * Example:
 *   ajan_core_load_template( 'members/index' );
 * Loads:
 *   wp-content/themes/[activated_theme]/members/index.php
 *
 * @param array $templates Array of templates to attempt to load.
 * @return bool|null Returns false on failure.
 */
function ajan_core_load_template( $templates ) {
	global $post, $ajan, $wp_query, $wpdb;

	// Determine if the root object WP page exists for this request
	// note: get_page_by_path() breaks non-root pages
	if ( !empty( $ajan->unfiltered_uri_offset ) ) {
		if ( !$page_exists = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s", $ajan->unfiltered_uri[$ajan->unfiltered_uri_offset] ) ) ) {
			return false;
		}
	}

	// Set the root object as the current wp_query-ied item
	$object_id = 0;
	foreach ( (array) $ajan->pages as $page ) {
		if ( $page->name == $ajan->unfiltered_uri[$ajan->unfiltered_uri_offset] ) {
			$object_id = $page->id;
		}
	}

	// Make the queried/post object an actual valid page
	if ( !empty( $object_id ) ) {
		$wp_query->queried_object    = get_post( $object_id );
		$wp_query->queried_object_id = $object_id;
		$post                        = $wp_query->queried_object;
	}

	// Fetch each template and add the php suffix
	$filtered_templates = array();
	foreach ( (array) $templates as $template ) {
		$filtered_templates[] = $template . '.php';
	}

	// Filter the template locations so that plugins can alter where they are located
	$located_template = apply_filters( 'ajan_located_template', locate_template( (array) $filtered_templates, false ), $filtered_templates );
	if ( !empty( $located_template ) ) {

		// Template was located, lets set this as a valid page and not a 404.
		status_header( 200 );
		$wp_query->is_page     = true;
		$wp_query->is_singular = true;
		$wp_query->is_404      = false;

		do_action( 'ajan_core_pre_load_template', $located_template );

		load_template( apply_filters( 'ajan_load_template', $located_template ) );

		do_action( 'ajan_core_post_load_template', $located_template );

		// Kill any other output after this.
		exit();

	// No template found, so setup theme compatability
	// @todo Some other 404 handling if theme compat doesn't kick in
	} else {

		// We know where we are, so reset important $wp_query bits here early.
		// The rest will be done by ajan_theme_compat_reset_post() later.
		if ( is_activitynotifications() ) {
			status_header( 200 );
			$wp_query->is_page     = true;
			$wp_query->is_singular = true;
			$wp_query->is_404      = false;
		}

		do_action( 'ajan_setup_theme_compat' );
	}
}

/**
 * Redirect away from /profile URIs if XProfile is not enabled.
 */
function ajan_core_catch_profile_uri() {
	if ( !ajan_is_active( 'xprofile' ) ) {
		ajan_core_load_template( apply_filters( 'ajan_core_template_display_profile', 'members/single/home' ) );
	}
}

/**
 * Catch unauthorized access to certain ActivityNotifications pages and redirect accordingly.
 *
 * @since ActivityNotifications (1.5.0)
 */
function ajan_core_catch_no_access() {
	global $ajan, $wp_query;

	// If coming from ajan_core_redirect() and $ajan_no_status_set is true,
	// we are redirecting to an accessible page so skip this check.
	if ( !empty( $ajan->no_status_set ) )
		return false;

	if ( !isset( $wp_query->queried_object ) && !ajan_is_blog_page() ) {
		ajan_do_404();
	}
}
add_action( 'ajan_template_redirect', 'ajan_core_catch_no_access', 1 );

/**
 * Redirect a user to log in for BP pages that require access control.
 *
 * Add an error message (if one is provided).
 *
 * If authenticated, redirects user back to requested content by default.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @param array $args {
 *     @type int $mode Specifies the destintation of the redirect. 1 will
 *           direct to the root domain (home page), which assumes you have a
 *           log-in form there; 2 directs to wp-login.php. Default: 2.
 *     @type string $redirect The URL the user will be redirected to after
 *           successfully logging in. Default: the URL originally requested.
 *     @type string $root The root URL of the site, used in case of error or
 *           mode 1 redirects. Default: the value of {@link ajan_get_root_domain()}.
 *     @type string $message An error message to display to the user on the
 *           log-in page. Default: "You must log in to access the page you
 *           requested."
 * }
 */
function ajan_core_no_access( $args = '' ) {

 	// Build the redirect URL
 	$redirect_url  = is_ssl() ? 'https://' : 'http://';
 	$redirect_url .= $_SERVER['HTTP_HOST'];
 	$redirect_url .= $_SERVER['REQUEST_URI'];

	$defaults = array(
		'mode'     => 2,                    // 1 = $root, 2 = wp-login.php
		'redirect' => $redirect_url,        // the URL you get redirected to when a user successfully logs in
		'root'     => ajan_get_root_domain(),	// the landing page you get redirected to when a user doesn't have access
		'message'  => __( 'You must log in to access the page you requested.', 'ajency-activity-and-notifications' )
	);

	$r = wp_parse_args( $args, $defaults );
	$r = apply_filters( 'ajan_core_no_access', $r );
	extract( $r, EXTR_SKIP );

	/**
	 * @ignore Ignore these filters and use 'ajan_core_no_access' above
	 */
	$mode     = apply_filters( 'ajan_no_access_mode',     $mode,     $root,     $redirect, $message );
	$redirect = apply_filters( 'ajan_no_access_redirect', $redirect, $root,     $message,  $mode    );
	$root     = apply_filters( 'ajan_no_access_root',     $root,     $redirect, $message,  $mode    );
	$message  = apply_filters( 'ajan_no_access_message',  $message,  $root,     $redirect, $mode    );
	$root     = trailingslashit( $root );

	switch ( $mode ) {

		// Option to redirect to wp-login.php
		// Error message is displayed with ajan_core_no_access_wp_login_error()
		case 2 :
			if ( !empty( $redirect ) ) {
				ajan_core_redirect( add_query_arg( array( 'action' => 'bpnoaccess' ), wp_login_url( $redirect ) ) );
			} else {
				ajan_core_redirect( $root );
			}

			break;

		// Redirect to root with "redirect_to" parameter
		// Error message is displayed with ajan_core_add_message()
		case 1 :
		default :

			$url = $root;
			if ( !empty( $redirect ) )
				$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $root );

			if ( !empty( $message ) ) {
				ajan_core_add_message( $message, 'error' );
			}

			ajan_core_redirect( $url );

			break;
	}
}

/**
 * Add an error message to wp-login.php.
 *
 * Hooks into the "bpnoaccess" action defined in ajan_core_no_access().
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @global $error Error message to pass to wp-login.php
 */
function ajan_core_no_access_wp_login_error() {
	global $error;

	$error = apply_filters( 'ajan_wp_login_error', __( 'You must log in to access the page you requested.', 'ajency-activity-and-notifications' ), $_REQUEST['redirect_to'] );

	// shake shake shake!
	add_action( 'login_head', 'wp_shake_js', 12 );
}
add_action( 'login_form_bpnoaccess', 'ajan_core_no_access_wp_login_error' );

/**
 * Canonicalize ActivityNotifications URLs.
 *
 * This function ensures that requests for ActivityNotifications content are always
 * redirected to their canonical versions. Canonical versions are always
 * trailingslashed, and are typically the most general possible versions of the
 * URL - eg, example.com/groups/mygroup/ instead of
 * example.com/groups/mygroup/home/.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @see AJAN_Members_Component::setup_globals() where
 *      $ajan->canonical_stack['base_url'] and ['component'] may be set.
 * @see ajan_core_new_nav_item() where $ajan->canonical_stack['action'] may be set.
 * @uses ajan_get_canonical_url()
 * @uses ajan_get_requested_url()
 */
function ajan_redirect_canonical() {
	global $ajan;

	if ( !ajan_is_blog_page() && apply_filters( 'ajan_do_redirect_canonical', true ) ) {
		// If this is a POST request, don't do a canonical redirect.
		// This is for backward compatibility with plugins that submit form requests to
		// non-canonical URLs. Plugin authors should do their best to use canonical URLs in
		// their form actions.
		if ( !empty( $_POST ) ) {
			return;
		}

		// build the URL in the address bar
		$requested_url  = ajan_get_requested_url();

		// Stash query args
		$url_stack      = explode( '?', $requested_url );
		$req_url_clean  = $url_stack[0];
		$query_args     = isset( $url_stack[1] ) ? $url_stack[1] : '';

		$canonical_url  = ajan_get_canonical_url();

		// Only redirect if we've assembled a URL different from the request
		if ( $canonical_url !== $req_url_clean ) {

			// Template messages have been deleted from the cookie by this point, so
			// they must be readded before redirecting
			if ( isset( $ajan->template_message ) ) {
				$message      = stripslashes( $ajan->template_message );
				$message_type = isset( $ajan->template_message_type ) ? $ajan->template_message_type : 'success';

				ajan_core_add_message( $message, $message_type );
			}

			if ( !empty( $query_args ) ) {
				$canonical_url .= '?' . $query_args;
			}

			ajan_core_redirect( $canonical_url, 301 );
		}
	}
}

/**
 * Output rel=canonical header tag for ActivityNotifications content.
 *
 * @since ActivityNotifications (1.6.0)
 */
function ajan_rel_canonical() {
	$canonical_url = ajan_get_canonical_url();

	// Output rel=canonical tag
	echo "<link rel='canonical' href='" . esc_attr( $canonical_url ) . "' />\n";
}

/**
 * Get the canonical URL of the current page.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses apply_filters() Filter ajan_get_canonical_url to modify return value.
 *
 * @param array $args {
 *     Optional array of arguments.
 *     @type bool $include_query_args Whether to include current URL arguments
 *           in the canonical URL returned from the function.
 * }
 * @return string Canonical URL for the current page.
 */
function ajan_get_canonical_url( $args = array() ) {
	global $ajan;

	// For non-BP content, return the requested url, and let WP do the work
	if ( ajan_is_blog_page() ) {
		return ajan_get_requested_url();
	}

	$defaults = array(
		'include_query_args' => false // Include URL arguments, eg ?foo=bar&foo2=bar2
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r );

	// Special case: when a ActivityNotifications directory (eg example.com/members)
	// is set to be the front page, ensure that the current canonical URL
	// is the home page URL.
	if ( 'page' == get_option( 'show_on_front' ) && $page_on_front = (int) get_option( 'page_on_front' ) ) {
		$front_page_component = array_search( $page_on_front, ajan_core_get_directory_page_ids() );

		// If requesting the front page component directory, canonical
		// URL is the front page. We detect whether we're detecting a
		// component *directory* by checking that ajan_current_action()
		// is empty - ie, this not a single item or a feed
		if ( false !== $front_page_component && ajan_is_current_component( $front_page_component ) && ! ajan_current_action() ) {
			$ajan->canonical_stack['canonical_url'] = trailingslashit( ajan_get_root_domain() );

		// Except when the front page is set to the registration page
		// and the current user is logged in. In this case we send to
		// the members directory to avoid redirect loops
		} else if ( ajan_is_register_page() && 'register' == $front_page_component && is_user_logged_in() ) {
			$ajan->canonical_stack['canonical_url'] = apply_filters( 'ajan_loggedin_register_page_redirect_to', trailingslashit( ajan_get_root_domain() . '/' . ajan_get_members_root_slug() ) );
		}
	}

	if ( empty( $ajan->canonical_stack['canonical_url'] ) ) {
		// Build the URL in the address bar
		$requested_url  = ajan_get_requested_url();

		// Stash query args
		$url_stack      = explode( '?', $requested_url );

		// Build the canonical URL out of the redirect stack
		if ( isset( $ajan->canonical_stack['base_url'] ) )
			$url_stack[0] = $ajan->canonical_stack['base_url'];

		if ( isset( $ajan->canonical_stack['component'] ) )
			$url_stack[0] = trailingslashit( $url_stack[0] . $ajan->canonical_stack['component'] );

		if ( isset( $ajan->canonical_stack['action'] ) )
			$url_stack[0] = trailingslashit( $url_stack[0] . $ajan->canonical_stack['action'] );

		if ( !empty( $ajan->canonical_stack['action_variables'] ) ) {
			foreach( (array) $ajan->canonical_stack['action_variables'] as $av ) {
				$url_stack[0] = trailingslashit( $url_stack[0] . $av );
			}
		}

		// Add trailing slash
		$url_stack[0] = trailingslashit( $url_stack[0] );

		// Stash in the $ajan global
		$ajan->canonical_stack['canonical_url'] = implode( '?', $url_stack );
	}

	$canonical_url = $ajan->canonical_stack['canonical_url'];

	if ( !$include_query_args ) {
		$canonical_url = array_reverse( explode( '?', $canonical_url ) );
		$canonical_url = array_pop( $canonical_url );
	}

	return apply_filters( 'ajan_get_canonical_url', $canonical_url, $args );
}

/**
 * Return the URL as requested on the current page load by the user agent.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @return string Requested URL string.
 */
function ajan_get_requested_url() {
	global $ajan;

	if ( empty( $ajan->canonical_stack['requested_url'] ) ) {
		$ajan->canonical_stack['requested_url']  = is_ssl() ? 'https://' : 'http://';
		$ajan->canonical_stack['requested_url'] .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	return apply_filters( 'ajan_get_requested_url', $ajan->canonical_stack['requested_url'] );
}

/**
 * Remove WP's canonical redirect when we are trying to load BP-specific content.
 *
 * Avoids issues with WordPress thinking that a ActivityNotifications URL might actually
 * be a blog post or page.
 *
 * This function should be considered temporary, and may be removed without
 * notice in future versions of ActivityNotifications.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses ajan_is_blog_page()
 */
function _ajan_maybe_remove_redirect_canonical() {
	if ( ! ajan_is_blog_page() )
		remove_action( 'template_redirect', 'redirect_canonical' );
}
add_action( 'ajan_init', '_ajan_maybe_remove_redirect_canonical' );

/**
 * Rehook maybe_redirect_404() to run later than the default.
 *
 * WordPress's maybe_redirect_404() allows admins on a multisite installation
 * to define 'NOBLOGREDIRECT', a URL to which 404 requests will be redirected.
 * maybe_redirect_404() is hooked to template_redirect at priority 10, which
 * creates a race condition with ajan_template_redirect(), our piggyback hook.
 * Due to a legacy bug in ActivityNotifications, internal BP content (such as members and
 * groups) is marked 404 in $wp_query until ajan_core_load_template(), when BP
 * manually overrides the automatic 404. However, the race condition with
 * maybe_redirect_404() means that this manual un-404-ing doesn't happen in
 * time, with the results that maybe_redirect_404() thinks that the page is
 * a legitimate 404, and redirects incorrectly to NOBLOGREDIRECT.
 *
 * By switching maybe_redirect_404() to catch at a higher priority, we avoid
 * the race condition. If ajan_core_load_template() runs, it dies before reaching
 * maybe_redirect_404(). If ajan_core_load_template() does not run, it means that
 * the 404 is legitimate, and maybe_redirect_404() can proceed as expected.
 *
 * This function will be removed in a later version of ActivityNotifications. Plugins
 * (and plugin authors!) should ignore it.
 *
 * @since ActivityNotifications (1.6.1)
 *
 * @link http://buddypress.trac.wordpress.org/ticket/4329
 * @link http://buddypress.trac.wordpress.org/ticket/4415
 */
function _ajan_rehook_maybe_redirect_404() {
	if ( defined( 'NOBLOGREDIRECT' ) ) {
		remove_action( 'template_redirect', 'maybe_redirect_404' );
		add_action( 'template_redirect', 'maybe_redirect_404', 100 );
	}
}
add_action( 'template_redirect', '_ajan_rehook_maybe_redirect_404', 1 );

/**
 * Remove WP's rel=canonical HTML tag if we are trying to load BP-specific content.
 *
 * This function should be considered temporary, and may be removed without
 * notice in future versions of ActivityNotifications.
 *
 * @since ActivityNotifications (1.6.0)
 */
function _ajan_maybe_remove_rel_canonical() {
	if ( ! ajan_is_blog_page() && ! is_404() ) {
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'ajan_head', 'ajan_rel_canonical' );
	}
}
add_action( 'wp_head', '_ajan_maybe_remove_rel_canonical', 8 );
