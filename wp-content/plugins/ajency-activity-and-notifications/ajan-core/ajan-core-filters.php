<?php

/**
 * ActivityNotifications Filters.
 *
 * This file contains the filters that are used throughout ActivityNotifications. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional filters can currently be found.
 *
 *  - ActivityNotifications: In {@link ActivityNotifications::setup_actions()} in buddypress.php
 *  - Component: In {@link AJAN_Component::setup_actions()} in
 *                ajan-core/ajan-core-component.php
 *  - Admin: More in {@link AJAN_Admin::setup_actions()} in
 *            ajan-core/ajan-core-admin.php
 *
 * @package ActivityNotifications
 * @subpackage Core
 * @see ajan-core-actions.php
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attach ActivityNotifications to WordPress.
 *
 * ActivityNotifications uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when ActivityNotifications is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *           v--WordPress Actions       v--ActivityNotifications Sub-actions
 */
add_filter( 'request',                 'ajan_request',             10    );
add_filter( 'template_include',        'ajan_template_include',    10    );
add_filter( 'login_redirect',          'ajan_login_redirect',      10, 3 );
add_filter( 'map_meta_cap',            'ajan_map_meta_caps',       10, 4 );

// Add some filters to feedback messages
add_filter( 'ajan_core_render_message_content', 'wptexturize'       );
add_filter( 'ajan_core_render_message_content', 'convert_smilies'   );
add_filter( 'ajan_core_render_message_content', 'convert_chars'     );
add_filter( 'ajan_core_render_message_content', 'wpautop'           );
add_filter( 'ajan_core_render_message_content', 'shortcode_unautop' );
add_filter( 'ajan_core_render_message_content', 'wp_kses_data', 5   );

/**
 * Template Compatibility.
 *
 * If you want to completely bypass this and manage your own custom ActivityNotifications
 * template hierarchy, start here by removing this filter, then look at how
 * ajan_template_include() works and do something similar. :)
 */
add_filter( 'ajan_template_include',   'ajan_template_include_theme_supports', 2, 1 );
add_filter( 'ajan_template_include',   'ajan_template_include_theme_compat',   4, 2 );

// Filter ActivityNotifications template locations
add_filter( 'ajan_get_template_stack', 'ajan_add_template_stack_locations'          );

// Turn comments off for ActivityNotifications pages
add_filter( 'comments_open', 'ajan_comments_open', 10, 2 );

/**
 * Prevent specific pages (eg 'Activate') from showing on page listings.
 *
 * @uses ajan_is_active() checks if a ActivityNotifications component is active.
 *
 * @param array $pages List of excluded page IDs, as passed to the
 *        'wp_list_pages_excludes' filter.
 * @return array The exclude list, with BP's pages added.
 */
function ajan_core_exclude_pages( $pages = array() ) {

	// Bail if not the root blog
	if ( ! ajan_is_root_blog() )
		return $pages;

	$ajan = activitynotifications();

	if ( !empty( $ajan->pages->activate ) )
		$pages[] = $ajan->pages->activate->id;

	if ( !empty( $ajan->pages->register ) )
		$pages[] = $ajan->pages->register->id;

	if ( !empty( $ajan->pages->forums ) && ( !ajan_is_active( 'forums' ) || ( ajan_is_active( 'forums' ) && ajan_forums_has_directory() && !ajan_forums_is_installed_correctly() ) ) )
		$pages[] = $ajan->pages->forums->id;

	return apply_filters( 'ajan_core_exclude_pages', $pages );
}
add_filter( 'wp_list_pages_excludes', 'ajan_core_exclude_pages' );

/**
 * Prevent specific pages (eg 'Activate') from showing in the Pages meta box of the Menu Administration screen.
 *
 * @since ActivityNotifications (2.0.0)
 *
 * @uses ajan_is_root_blog() checks if current blog is root blog.
 * @uses activitynotifications() gets ActivityNotifications main instance
 *
 * @param object $object The post type object used in the meta box
 * @return object The $object, with a query argument to remove register and activate pages id.
 */
function ajan_core_exclude_pages_from_nav_menu_admin( $object = null ) {

	// Bail if not the root blog
	if ( ! ajan_is_root_blog() ) {
		return $object;
	}

	if ( 'page' != $object->name ) {
		return $object;
	}

	$ajan = activitynotifications();
	$pages = array();

	if ( ! empty( $ajan->pages->activate ) ) {
		$pages[] = $ajan->pages->activate->id;
	}

	if ( ! empty( $ajan->pages->register ) ) {
		$pages[] = $ajan->pages->register->id;
	}

	if ( ! empty( $pages ) ) {
		$object->_default_query['post__not_in'] = $pages;
	}

	return $object;
}
add_filter( 'nav_menu_meta_box_object', 'ajan_core_exclude_pages_from_nav_menu_admin', 11, 1 );

/**
 * Set "From" name in outgoing email to the site name.
 *
 * @uses ajan_get_option() fetches the value for a meta_key in the wp_X_options table.
 *
 * @return string The blog name for the root blog.
 */
function ajan_core_email_from_name_filter() {
 	return apply_filters( 'ajan_core_email_from_name_filter', ajan_get_option( 'blogname', 'WordPress' ) );
}
add_filter( 'wp_mail_from_name', 'ajan_core_email_from_name_filter' );

/**
 * Filter the blog post comments array and insert ActivityNotifications URLs for users.
 *
 * @param array $comments The array of comments supplied to the comments template.
 * @param int $post->ID The post ID.
 * @return array $comments The modified comment array.
 */
function ajan_core_filter_comments( $comments, $post_id ) {
	global $wpdb;

	foreach( (array) $comments as $comment ) {
		if ( $comment->user_id )
			$user_ids[] = $comment->user_id;
	}

	if ( empty( $user_ids ) )
		return $comments;

	$user_ids = implode( ',', wp_parse_id_list( $user_ids ) );

	if ( !$userdata = $wpdb->get_results( "SELECT ID as user_id, user_login, user_nicename FROM {$wpdb->users} WHERE ID IN ({$user_ids})" ) )
		return $comments;

	foreach( (array) $userdata as $user )
		$users[$user->user_id] = ajan_core_get_user_domain( $user->user_id, $user->user_nicename, $user->user_login );

	foreach( (array) $comments as $i => $comment ) {
		if ( !empty( $comment->user_id ) ) {
			if ( !empty( $users[$comment->user_id] ) )
				$comments[$i]->comment_author_url = $users[$comment->user_id];
		}
	}

	return $comments;
}
add_filter( 'comments_array', 'ajan_core_filter_comments', 10, 2 );

/**
 * When a user logs in, redirect him in a logical way.
 *
 * @uses apply_filters() Filter 'ajan_core_login_redirect' to modify where users
 *       are redirected to on login.
 *
 * @param string $redirect_to The URL to be redirected to, sanitized
 *        in wp-login.php.
 * @param string $redirect_to_raw The unsanitized redirect_to URL ($_REQUEST['redirect_to'])
 * @param WP_User $user The WP_User object corresponding to a successfully
 *        logged-in user. Otherwise a WP_Error object.
 * @return string The redirect URL.
 */
function ajan_core_login_redirect( $redirect_to, $redirect_to_raw, $user ) {

	// Only modify the redirect if we're on the main BP blog
	if ( !ajan_is_root_blog() ) {
		return $redirect_to;
	}

	// Only modify the redirect once the user is logged in
	if ( !is_a( $user, 'WP_User' ) ) {
		return $redirect_to;
	}

	// Allow plugins to allow or disallow redirects, as desired
	$maybe_redirect = apply_filters( 'ajan_core_login_redirect', false, $redirect_to, $redirect_to_raw, $user );
	if ( false !== $maybe_redirect ) {
		return $maybe_redirect;
	}

	// If a 'redirect_to' parameter has been passed that contains 'wp-admin', verify that the
	// logged-in user has any business to conduct in the Dashboard before allowing the
	// redirect to go through
	if ( !empty( $redirect_to ) && ( false === strpos( $redirect_to, 'wp-admin' ) || user_can( $user, 'edit_posts' ) ) ) {
		return $redirect_to;
	}

	if ( false === strpos( wp_get_referer(), 'wp-login.php' ) && false === strpos( wp_get_referer(), 'activate' ) && empty( $_REQUEST['nr'] ) ) {
		return wp_get_referer();
	}

	return apply_filters( 'ajan_core_login_redirect_to', ajan_get_root_domain() );
}
add_filter( 'ajan_login_redirect', 'ajan_core_login_redirect', 10, 3 );

/**
 * Replace the generated password in the welcome email with '[User Set]'.
 *
 * On a standard BP installation, users who register themselves also set their
 * own passwords. Therefore there is no need for the insecure practice of
 * emailing the plaintext password to the user in the welcome email.
 *
 * This filter will not fire when a user is registered by the site admin.
 *
 * @param string $welcome_email Complete email passed through WordPress.
 * @return string Filtered $welcome_email with the password replaced
 *         by '[User Set]'.
 */
function ajan_core_filter_user_welcome_email( $welcome_email ) {

	// Don't touch the email when a user is registered by the site admin
	if ( is_admin() )
		return $welcome_email;

	// Don't touch the email if we don't have a custom registration template
	if ( ! ajan_has_custom_signup_page() )
		return $welcome_email;

	// [User Set] Replaces 'PASSWORD' in welcome email; Represents value set by user
	return str_replace( 'PASSWORD', __( '[User Set]', 'ajency-activity-and-notifications' ), $welcome_email );
}
add_filter( 'update_welcome_user_email', 'ajan_core_filter_user_welcome_email' );

/**
 * Replace the generated password in the welcome email with '[User Set]'.
 *
 * On a standard BP installation, users who register themselves also set their
 * own passwords. Therefore there is no need for the insecure practice of
 * emailing the plaintext password to the user in the welcome email.
 *
 * This filter will not fire when a user is registered by the site admin.
 *
 * @param string $welcome_email Complete email passed through WordPress.
 * @param int $blog_id ID of the blog user is joining.
 * @param int $user_id ID of the user joining.
 * @param string $password Password of user.
 * @return string Filtered $welcome_email with $password replaced by '[User Set]'.
 */
function ajan_core_filter_blog_welcome_email( $welcome_email, $blog_id, $user_id, $password ) {

	// Don't touch the email when a user is registered by the site admin.
	if ( is_admin() )
		return $welcome_email;

	// Don't touch the email if we don't have a custom registration template
	if ( ! ajan_has_custom_signup_page() )
		return $welcome_email;

	// [User Set] Replaces $password in welcome email; Represents value set by user
	return str_replace( $password, __( '[User Set]', 'ajency-activity-and-notifications' ), $welcome_email );
}
add_filter( 'update_welcome_email', 'ajan_core_filter_blog_welcome_email', 10, 4 );

/**
 * Notify new users of a successful registration (with blog).
 *
 * This function filter's WP's 'wpmu_signup_blog_notification', and replaces
 * WP's default welcome email with a ActivityNotifications-specific message.
 *
 * @see wpmu_signup_blog_notification() for a description of parameters.
 *
 * @param string $domain The new blog domain.
 * @param string $path The new blog path.
 * @param string $title The site title.
 * @param string $user The user's login name.
 * @param string $user_email The user's email address.
 * @param string $key The activation key created in wpmu_signup_blog()
 * @param array $meta By default, contains the requested privacy setting and
 *        lang_id.
 * @return bool True on success, false on failure.
 */
function ajan_core_activation_signup_blog_notification( $domain, $path, $title, $user, $user_email, $key, $meta ) {

	// Set up activation link
	$activate_url = ajan_get_activation_page() ."?key=$key";
	$activate_url = esc_url( $activate_url );

	// Email contents
	$message = sprintf( __( "Thanks for registering! To complete the activation of your account and blog, please click the following link:\n\n%1\$s\n\n\n\nAfter you activate, you can visit your blog here:\n\n%2\$s", 'ajency-activity-and-notifications' ), $activate_url, esc_url( "http://{$domain}{$path}" ) );
	$subject = ajan_get_email_subject( array( 'text' => sprintf( __( 'Activate %s', 'ajency-activity-and-notifications' ), 'http://' . $domain . $path ) ) );

	// Email filters
	$to      = apply_filters( 'ajan_core_activation_signup_blog_notification_to',   $user_email, $domain, $path, $title, $user, $user_email, $key, $meta );
	$subject = apply_filters( 'ajan_core_activation_signup_blog_notification_subject', $subject, $domain, $path, $title, $user, $user_email, $key, $meta );
	$message = apply_filters( 'ajan_core_activation_signup_blog_notification_message', $message, $domain, $path, $title, $user, $user_email, $key, $meta );

	// Send the email
	wp_mail( $to, $subject, $message );

	// Set up the $admin_email to pass to the filter
	$admin_email = ajan_get_option( 'admin_email' );

	do_action( 'ajan_core_sent_blog_signup_email', $admin_email, $subject, $message, $domain, $path, $title, $user, $user_email, $key, $meta );

	// Return false to stop the original WPMU function from continuing
	return false;
}
add_filter( 'wpmu_signup_blog_notification', 'ajan_core_activation_signup_blog_notification', 1, 7 );

/**
 * Notify new users of a successful registration (without blog).
 *
 * @see wpmu_signup_user_notification() for a full description of params.
 *
 * @param string $user The user's login name.
 * @param string $user_email The user's email address.
 * @param string $key The activation key created in wpmu_signup_user()
 * @param array $meta By default, an empty array.
 * @return bool True on success, false on failure.
 */
function ajan_core_activation_signup_user_notification( $user, $user_email, $key, $meta ) {

	// Set up activation link
	$activate_url = ajan_get_activation_page() . "?key=$key";
	$activate_url = esc_url( $activate_url );

	// Email contents
	$message = sprintf( __( "Thanks for registering! To complete the activation of your account please click the following link:\n\n%1\$s\n\n", 'ajency-activity-and-notifications' ), $activate_url );
	$subject = ajan_get_email_subject( array( 'text' => __( 'Activate Your Account', 'ajency-activity-and-notifications' ) ) );

	// Email filters
	$to      = apply_filters( 'ajan_core_activation_signup_user_notification_to',   $user_email, $user, $user_email, $key, $meta );
	$subject = apply_filters( 'ajan_core_activation_signup_user_notification_subject', $subject, $user, $user_email, $key, $meta );
	$message = apply_filters( 'ajan_core_activation_signup_user_notification_message', $message, $user, $user_email, $key, $meta );

	// Send the email
	wp_mail( $to, $subject, $message );

	// Set up the $admin_email to pass to the filter
	$admin_email = ajan_get_option( 'admin_email' );

	do_action( 'ajan_core_sent_user_signup_email', $admin_email, $subject, $message, $user, $user_email, $key, $meta );

	// Return false to stop the original WPMU function from continuing
	return false;
}
add_filter( 'wpmu_signup_user_notification', 'ajan_core_activation_signup_user_notification', 1, 4 );

/**
 * Filter the page title for ActivityNotifications pages.
 *
 * @since ActivityNotifications (1.5.0)
 *
 * @see wp_title()
 * @global object $ajan ActivityNotifications global settings.
 *
 * @param string $title Original page title.
 * @param string $sep How to separate the various items within the page title.
 * @param string $seplocation Direction to display title.
 * @return string New page title.
 */
function ajan_modify_page_title( $title, $sep, $seplocation ) {
	global $ajan;

	// If this is not a BP page, just return the title produced by WP
	if ( ajan_is_blog_page() )
		return $title;

	// If this is a 404, let WordPress handle it
	if ( is_404() ) {
		return $title;
	}

	// If this is the front page of the site, return WP's title
	if ( is_front_page() || is_home() )
		return $title;

	$title = '';

	// Displayed user
	if ( ajan_get_displayed_user_fullname() && !is_404() ) {

		// Get the component's ID to try and get it's name
		$component_id = $component_name = ajan_current_component();

		// Use the actual component name
		if ( !empty( $ajan->{$component_id}->name ) ) {
			$component_name = $ajan->{$component_id}->name;

		// Fall back on the component ID (probably same as current_component)
		} elseif ( !empty( $ajan->{$component_id}->id ) ) {
			$component_name = $ajan->{$component_id}->id;
		}

		// Construct the page title. 1 = user name, 2 = seperator, 3 = component name
		$title = strip_tags( sprintf( _x( '%1$s %3$s %2$s', 'Construct the page title. 1 = user name, 2 = component name, 3 = seperator', 'ajency-activity-and-notifications' ), ajan_get_displayed_user_fullname(), ucwords( $component_name ), $sep ) );

	// A single group
	} elseif ( ajan_is_active( 'groups' ) && !empty( $ajan->groups->current_group ) && !empty( $ajan->ajan_options_nav[$ajan->groups->current_group->slug] ) ) {
		$subnav = isset( $ajan->ajan_options_nav[$ajan->groups->current_group->slug][ajan_current_action()]['name'] ) ? $ajan->ajan_options_nav[$ajan->groups->current_group->slug][ajan_current_action()]['name'] : '';
		// translators: "group name | group nav section name"
		$title = sprintf( __( '%1$s | %2$s', 'ajency-activity-and-notifications' ), $ajan->ajan_options_title, $subnav );

	// A single item from a component other than groups
	} elseif ( ajan_is_single_item() ) {
		// translators: "component item name | component nav section name | root component name"
		$title = sprintf( __( '%1$s | %2$s | %3$s', 'ajency-activity-and-notifications' ), $ajan->ajan_options_title, $ajan->ajan_options_nav[ajan_current_item()][ajan_current_action()]['name'], ajan_get_name_from_root_slug( ajan_get_root_slug() ) );

	// An index or directory
	} elseif ( ajan_is_directory() ) {

		$current_component = ajan_current_component();

		// No current component (when does this happen?)
		if ( empty( $current_component ) ) {
			$title = _x( 'Directory', 'component directory title', 'ajency-activity-and-notifications' );
		} else {
			$title = ajan_get_directory_title( $current_component );
		}

	// Sign up page
	} elseif ( ajan_is_register_page() ) {
		$title = __( 'Create an Account', 'ajency-activity-and-notifications' );

	// Activation page
	} elseif ( ajan_is_activation_page() ) {
		$title = __( 'Activate your Account', 'ajency-activity-and-notifications' );

	// Group creation page
	} elseif ( ajan_is_group_create() ) {
		$title = __( 'Create a Group', 'ajency-activity-and-notifications' );

	// Blog creation page
	} elseif ( ajan_is_create_blog() ) {
		$title = __( 'Create a Site', 'ajency-activity-and-notifications' );
	}

	// Some BP nav items contain item counts. Remove them
	$title = preg_replace( '|<span>[0-9]+</span>|', '', $title );

	return apply_filters( 'ajan_modify_page_title', $title . ' ' . $sep . ' ', $title, $sep, $seplocation );
}
add_filter( 'wp_title', 'ajan_modify_page_title', 10, 3 );
add_filter( 'ajan_modify_page_title', 'wptexturize'     );
add_filter( 'ajan_modify_page_title', 'convert_chars'   );
add_filter( 'ajan_modify_page_title', 'esc_html'        );

/**
 * Add ActivityNotifications-specific items to the wp_nav_menu.
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @param WP_Post $menu_item The menu item.
 * @return obj The modified WP_Post object.
 */
function ajan_setup_nav_menu_item( $menu_item ) {
	if ( is_admin() ) {
		return $menu_item;
	}

	// We use information stored in the CSS class to determine what kind of
	// menu item this is, and how it should be treated
	$css_target = preg_match( '/\sajan-(.*)-nav/', implode( ' ', $menu_item->classes), $matches );

	// If this isn't a BP menu item, we can stop here
	if ( empty( $matches[1] ) ) {
		return $menu_item;
	}

	switch ( $matches[1] ) {
		case 'login' :
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = wp_login_url( wp_guess_url() );
			}

			break;

		case 'logout' :
			if ( ! is_user_logged_in() ) {
				$menu_item->_invalid = true;
			} else {
				$menu_item->url = wp_logout_url( wp_guess_url() );
			}

			break;

		// Don't show the Register link to logged-in users
		case 'register' :
			if ( is_user_logged_in() ) {
				$menu_item->_invalid = true;
			}

			break;

		// All other BP nav items are specific to the logged-in user,
		// and so are not relevant to logged-out users
		default:
			if ( is_user_logged_in() ) {
				$menu_item->url = ajan_nav_menu_get_item_url( $matches[1] );
			} else {
				$menu_item->_invalid = true;
			}

			break;
	}

	// If component is deactivated, make sure menu item doesn't render
	if ( empty( $menu_item->url ) ) {
		$menu_item->_invalid = true;

	// Highlight the current page
	} else {
		$current = ajan_get_requested_url();
		if ( strpos( $current, $menu_item->url ) !== false ) {
			$menu_item->classes[] = 'current_page_item';
		}
	}

	return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'ajan_setup_nav_menu_item', 10, 1 );

/**
 * Filter SQL query strings to swap out the 'meta_id' column.
 *
 * WordPress uses the meta_id column for commentmeta and postmeta, and so
 * hardcodes the column name into its *_metadata() functions. ActivityNotifications, on
 * the other hand, uses 'id' for the primary column. To make WP's functions
 * usable for ActivityNotifications, we use this just-in-time filter on 'query' to swap
 * 'meta_id' with 'id.
 *
 * @since ActivityNotifications (2.0.0)
 *
 * @access private Do not use.
 *
 * @param string $q SQL query.
 * @return string
 */
function ajan_filter_metaid_column_name( $q ) {
	return str_replace( 'meta_id', 'id', $q );
}
