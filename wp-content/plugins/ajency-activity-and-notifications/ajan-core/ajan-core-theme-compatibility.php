<?php

/**
 * ActivityNotifications Core Theme Compatibility.
 *
 * @package ActivityNotifications
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Theme Compat **************************************************************/

/**
 * What follows is an attempt at intercepting the natural page load process
 * to replace the_content() with the appropriate ActivityNotifications content.
 *
 * To do this, ActivityNotifications does several direct manipulations of global variables
 * and forces them to do what they are not supposed to be doing.
 *
 * Don't try anything you're about to witness here, at home. Ever.
 */

/** Base Class ****************************************************************/

/**
 * Theme Compatibility base class.
 *
 * This is only intended to be extended, and is included here as a basic guide
 * for future Theme Packs to use. {@link AJAN_Legacy} is a good example of
 * extending this class.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @todo We should probably do something similar to AJAN_Component::start().
 * @todo If this is only intended to be extended, it should be abstract.
 *
 * @param array $properties {
 *     An array of properties describing the theme compat package.
 *     @type string $id ID of the package. Must be unique.
 *     @type string $name Name of the theme. This should match the name given
 *           in style.css.
 *     @type string $version Theme version. Used for busting script and style
 *           browser caches.
 *     @type string $dir Filesystem path of the theme.
 *     @type string $url Base URL of the theme.
 * }
 */
class AJAN_Theme_Compat {

	/**
	 * Template package properties, as passed to the constructor.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Pass the $properties to the object on creation.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
    	public function __construct( Array $properties = array() ) {
		$this->_data = $properties;
	}

	/**
	 * Set up the ActivityNotifications-specific theme compat methods.
	 *
	 * Themes shoud use this method in their constructor.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	protected function start() {
		// Sanity check
		if ( ! ajan_use_theme_compat_with_current_theme() ) {
			return;
		}

		// Setup methods
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set up global data for your template package.
	 *
	 * Meant to be overridden in your class. See
	 * {@link AJAN_Legacy::setup_globals()} for an example.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	protected function setup_globals() {}

	/**
	 * Set up theme hooks for your template package.
	 *
	 * Meant to be overridden in your class. See
	 * {@link AJAN_Legacy::setup_actions()} for an example.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	protected function setup_actions() {}

	/**
	 * Set a theme's property.
	 *
	 * @since ActivityNotifications (1.7.0)
	 *
	 * @param string $property Property name.
	 * @param mixed $value Property value.
	 * @return bool True on success, false on failure.
	 */
	public function __set( $property, $value ) {
		return $this->_data[$property] = $value;
	}

	/**
	 * Get a theme's property.
	 *
	 * @since ActivityNotifications (1.7.0)
	 *
	 * @param string $property Property name.
	 * @return mixed The value of the property if it exists, otherwise an
	 *         empty string.
	 */
	public function __get( $property ) {
		return array_key_exists( $property, $this->_data ) ? $this->_data[$property] : '';
	}
}

/** Functions *****************************************************************/

/**
 * Set up the default theme compat theme.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $theme Optional. The unique ID identifier of a theme package.
 */
function ajan_setup_theme_compat( $theme = '' ) {
	$ajan = activitynotifications();

	// Make sure theme package is available, set to default if not
	if ( ! isset( $ajan->theme_compat->packages[$theme] ) || ! is_a( $ajan->theme_compat->packages[$theme], 'AJAN_Theme_Compat' ) ) {
		$theme = 'legacy';
	}

	// Set the active theme compat theme
	$ajan->theme_compat->theme = $ajan->theme_compat->packages[$theme];
}

/**
 * Get the ID of the theme package being used.
 *
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own ActivityNotifications compatability layers for their themes.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses apply_filters()
 *
 * @return string ID of the theme package in use.
 */
function ajan_get_theme_compat_id() {
	return apply_filters( 'ajan_get_theme_compat_id', activitynotifications()->theme_compat->theme->id );
}

/**
 * Get the name of the theme package being used.
 *
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own ActivityNotifications compatability layers for their themes.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses apply_filters()
 *
 * @return string Name of the theme package currently in use.
 */
function ajan_get_theme_compat_name() {
	return apply_filters( 'ajan_get_theme_compat_name', activitynotifications()->theme_compat->theme->name );
}

/**
 * Get the version of the theme package being used.
 *
 * This can be filtered or set manually. Tricky theme authors can override the
 * default and include their own ActivityNotifications compatability layers for their themes.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses apply_filters()
 *
 * @return string The version string of the theme package currently in use.
 */
function ajan_get_theme_compat_version() {
	return apply_filters( 'ajan_get_theme_compat_version', activitynotifications()->theme_compat->theme->version );
}

/**
 * Get the absolute path of the theme package being used.
 *
 * or set manually. Tricky theme authors can override the default and include
 * their own ActivityNotifications compatability layers for their themes.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses apply_filters()
 *
 * @return string The absolute path of the theme package currently in use.
 */
function ajan_get_theme_compat_dir() {
	return apply_filters( 'ajan_get_theme_compat_dir', activitynotifications()->theme_compat->theme->dir );
}

/**
 * Get the URL of the theme package being used.
 *
 * This can be filtered, or set manually. Tricky theme authors can override
 * the default and include their own ActivityNotifications compatability layers for their
 * themes.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses apply_filters()
 *
 * @return string URL of the theme package currently in use.
 */
function ajan_get_theme_compat_url() {
	return apply_filters( 'ajan_get_theme_compat_url', activitynotifications()->theme_compat->theme->url );
}

/**
 * Should we use theme compat for this theme?
 *
 * If the current theme's need for theme compat hasn't yet been detected, we
 * do so using ajan_detect_theme_compat_with_current_theme().
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @uses ajan_detect_theme_compat_with_current_theme()
 *
 * @return bool True if the current theme needs theme compatibility.
 */
function ajan_use_theme_compat_with_current_theme() {
	if ( ! isset( activitynotifications()->theme_compat->use_with_current_theme ) ) {
		ajan_detect_theme_compat_with_current_theme();
	}

	return apply_filters( 'ajan_use_theme_compat_with_current_theme', activitynotifications()->theme_compat->use_with_current_theme );
}

/**
 * Set our flag to determine whether theme compat should be enabled.
 *
 * Theme compat is disabled when a theme meets one of the following criteria:
 * 1) It declares BP support with add_theme_support( 'ajency-activity-and-notifications' )
 * 2) It is ajan-default, or a child theme of ajan-default
 * 3) A legacy template is found at members/members-loop.php. This is a
 *    fallback check for themes that were derived from ajan-default, and have
 *    not been updated for BP 1.7+; we make the assumption that any theme in
 *    this category will have the members-loop.php template, and so use its
 *    presence as an indicator that theme compatibility is not required
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @return bool True if the current theme needs theme compatibility.
 */
function ajan_detect_theme_compat_with_current_theme() {
	if ( isset( activitynotifications()->theme_compat->use_with_current_theme ) ) {
		return activitynotifications()->theme_compat->use_with_current_theme;
	}

	// theme compat enabled by default
	$theme_compat = true;

	// If the theme supports 'ajency-activity-and-notifications', bail.
	if ( current_theme_supports( 'ajency-activity-and-notifications' ) ) {
		$theme_compat = false;

	// If the theme doesn't support BP, do some additional checks
	} else {
		// Bail if theme is a derivative of ajan-default
		if ( in_array( 'ajan-default', array( get_template(), get_stylesheet() ) ) ) {
			$theme_compat = false;

		// Bruteforce check for a BP template
		// Examples are clones of ajan-default
		} else if ( locate_template( 'members/members-loop.php', false, false ) ) {
			$theme_compat = false;
		}
	}

	// set a flag in the activitynotifications() singleton so we don't have to run this again
	activitynotifications()->theme_compat->use_with_current_theme = $theme_compat;

	return $theme_compat;
}

/**
 * Is the current page using theme compatibility?
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @return bool True if the current page uses theme compatibility.
 */
function ajan_is_theme_compat_active() {
	$ajan = activitynotifications();

	if ( empty( $ajan->theme_compat->active ) )
		return false;

	return $ajan->theme_compat->active;
}

/**
 * Set the flag that tells whether the current page is using theme compatibility.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param bool $set True to set the flag to true, false to set it to false.
 * @return bool Returns the value of $set.
 */
function ajan_set_theme_compat_active( $set = true ) {
	activitynotifications()->theme_compat->active = $set;

	return (bool) activitynotifications()->theme_compat->active;
}

/**
 * Set the theme compat templates global.
 *
 * Stash possible template files for the current query. Useful if plugins want
 * to override them, or see what files are being scanned for inclusion.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param array $templates The template stack.
 * @return array The template stack (value of $templates).
 */
function ajan_set_theme_compat_templates( $templates = array() ) {
	activitynotifications()->theme_compat->templates = $templates;

	return activitynotifications()->theme_compat->templates;
}

/**
 * Set the theme compat template global.
 *
 * Stash the template file for the current query. Useful if plugins want
 * to override it, or see what file is being included.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $template The template currently in use.
 * @return string The template currently in use (value of $template).
 */
function ajan_set_theme_compat_template( $template = '' ) {
	activitynotifications()->theme_compat->template = $template;

	return activitynotifications()->theme_compat->template;
}

/**
 * Set the theme compat original_template global.
 *
 * Stash the original template file for the current query. Useful for checking
 * if ActivityNotifications was able to find a more appropriate template.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $template The template originally selected by WP.
 * @return string The template originally selected by WP (value of $template).
 */
function ajan_set_theme_compat_original_template( $template = '' ) {
	activitynotifications()->theme_compat->original_template = $template;

	return activitynotifications()->theme_compat->original_template;
}

/**
 * Check whether a given template is the one that WP originally selected to display current page.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $template The template name to check.
 * @return bool True if the value of $template is the same as the
 *         "original_template" originally selected by WP. Otherwise false.
 */
function ajan_is_theme_compat_original_template( $template = '' ) {
	$ajan = activitynotifications();

	if ( empty( $ajan->theme_compat->original_template ) )
		return false;

	return (bool) ( $ajan->theme_compat->original_template == $template );
}

/**
 * Register a new ActivityNotifications theme package in the active theme packages array.
 *
 * For an example of how this function is used, see:
 * {@link ActivityNotifications::register_theme_packages()}.
 *
 * @since ActivityNotifications (1.7)
 *
 * @see AJAN_Theme_Compat for a description of the $theme parameter arguments.
 *
 * @param array $theme See {@link AJAN_Theme_Compat}.
 * @param bool $override If true, overrides whatever package is currently set.
 *        Default: true.
 */
function ajan_register_theme_package( $theme = array(), $override = true ) {

	// Create new AJAN_Theme_Compat object from the $theme array
	if ( is_array( $theme ) ) {
		$theme = new AJAN_Theme_Compat( $theme );
	}

	// Bail if $theme isn't a proper object
	if ( ! is_a( $theme, 'AJAN_Theme_Compat' ) ) {
		return;
	}

	// Load up ActivityNotifications
	$ajan = activitynotifications();

	// Only set if the theme package was not previously registered or if the
	// override flag is set
	if ( empty( $ajan->theme_compat->packages[$theme->id] ) || ( true === $override ) ) {
		$ajan->theme_compat->packages[$theme->id] = $theme;
	}
}

/**
 * Populate various WordPress globals with dummy data to prevent errors.
 *
 * This dummy data is necessary because theme compatibility essentially fakes
 * WordPress into thinking that there is content where, in fact, there is none
 * (at least, no WordPress post content). By providing dummy data, we ensure
 * that template functions - things like is_page() - don't throw errors.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @global WP_Query $wp_query WordPress database access object.
 * @global object $post Current post object.
 *
 * @param array $args Array of optional arguments. Arguments parallel the
 *        properties of {@link WP_Post}; see that class for more details.
 */
function ajan_theme_compat_reset_post( $args = array() ) {
	global $wp_query, $post;

	// Switch defaults if post is set
	if ( isset( $wp_query->post ) ) {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => $wp_query->post->ID,
			'post_status'           => $wp_query->post->post_status,
			'post_author'           => $wp_query->post->post_author,
			'post_parent'           => $wp_query->post->post_parent,
			'post_type'             => $wp_query->post->post_type,
			'post_date'             => $wp_query->post->post_date,
			'post_date_gmt'         => $wp_query->post->post_date_gmt,
			'post_modified'         => $wp_query->post->post_modified,
			'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
			'post_content'          => $wp_query->post->post_content,
			'post_title'            => $wp_query->post->post_title,
			'post_excerpt'          => $wp_query->post->post_excerpt,
			'post_content_filtered' => $wp_query->post->post_content_filtered,
			'post_mime_type'        => $wp_query->post->post_mime_type,
			'post_password'         => $wp_query->post->post_password,
			'post_name'             => $wp_query->post->post_name,
			'guid'                  => $wp_query->post->guid,
			'menu_order'            => $wp_query->post->menu_order,
			'pinged'                => $wp_query->post->pinged,
			'to_ping'               => $wp_query->post->to_ping,
			'ping_status'           => $wp_query->post->ping_status,
			'comment_status'        => $wp_query->post->comment_status,
			'comment_count'         => $wp_query->post->comment_count,
			'filter'                => $wp_query->post->filter,

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	} else {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => -9999,
			'post_status'           => 'public',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	}

	// Bail if dummy post is empty
	if ( empty( $dummy ) ) {
		return;
	}

	// Set the $post global
	$post = new WP_Post( (object) $dummy );

	// Copy the new post global into the main $wp_query
	$wp_query->post       = $post;
	$wp_query->posts      = array( $post );

	// Prevent comments form from appearing
	$wp_query->post_count = 1;
	$wp_query->is_404     = $dummy['is_404'];
	$wp_query->is_page    = $dummy['is_page'];
	$wp_query->is_single  = $dummy['is_single'];
	$wp_query->is_archive = $dummy['is_archive'];
	$wp_query->is_tax     = $dummy['is_tax'];

	// Clean up the dummy post
	unset( $dummy );

	/**
	 * Force the header back to 200 status if not a deliberate 404
	 *
	 * @see http://bbpress.trac.wordpress.org/ticket/1973
	 */
	if ( ! $wp_query->is_404() ) {
		status_header( 200 );
	}

	// If we are resetting a post, we are in theme compat
	ajan_set_theme_compat_active( true );
}

/**
 * Reset main query vars and filter 'the_content' to output a ActivityNotifications template part as needed.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @uses ajan_is_single_user() To check if page is single user.
 * @uses ajan_get_single_user_template() To get user template.
 * @uses ajan_is_single_user_edit() To check if page is single user edit.
 * @uses ajan_get_single_user_edit_template() To get user edit template.
 * @uses ajan_is_single_view() To check if page is single view.
 * @uses ajan_get_single_view_template() To get view template.
 * @uses ajan_is_forum_edit() To check if page is forum edit.
 * @uses ajan_get_forum_edit_template() To get forum edit template.
 * @uses ajan_is_topic_merge() To check if page is topic merge.
 * @uses ajan_get_topic_merge_template() To get topic merge template.
 * @uses ajan_is_topic_split() To check if page is topic split.
 * @uses ajan_get_topic_split_template() To get topic split template.
 * @uses ajan_is_topic_edit() To check if page is topic edit.
 * @uses ajan_get_topic_edit_template() To get topic edit template.
 * @uses ajan_is_reply_edit() To check if page is reply edit.
 * @uses ajan_get_reply_edit_template() To get reply edit template.
 * @uses ajan_set_theme_compat_template() To set the global theme compat template.
 *
 * @param string $template Template name.
 * @return string $template Template name.
 */
function ajan_template_include_theme_compat( $template = '' ) {

	// If the current theme doesn't need theme compat, bail at this point.
	if ( ! ajan_use_theme_compat_with_current_theme() ) {
		return $template;
	}

	/**
	 * Use this action to execute code that will communicate to ActivityNotifications's
	 * theme compatibility layer whether or not we're replacing the_content()
	 * with some other template part.
	 */
	do_action( 'ajan_template_include_reset_dummy_post_data' );

	// Bail if the template already matches a ActivityNotifications template
	if ( !empty( activitynotifications()->theme_compat->found_template ) )
		return $template;

	/**
	 * If we are relying on ActivityNotifications's built in theme compatibility to load
	 * the proper content, we need to intercept the_content, replace the
	 * output, and display ours instead.
	 *
	 * To do this, we first remove all filters from 'the_content' and hook
	 * our own function into it, which runs a series of checks to determine
	 * the context, and then uses the built in shortcodes to output the
	 * correct results from inside an output buffer.
	 *
	 * Uses ajan_get_theme_compat_templates() to provide fall-backs that
	 * should be coded without superfluous mark-up and logic (prev/next
	 * navigation, comments, date/time, etc...)
	 *
	 * Hook into 'ajan_get_buddypress_template' to override the array of
	 * possible templates, or 'ajan_buddypress_template' to override the result.
	 */
	if ( ajan_is_theme_compat_active() ) {
		$template = ajan_get_theme_compat_templates();

		add_filter( 'the_content', 'ajan_replace_the_content' );

		// Add ActivityNotifications's head action to wp_head
		if ( ! has_action( 'wp_head', 'ajan_head' ) ) {
			add_action( 'wp_head', 'ajan_head' );
		}
	}

	return apply_filters( 'ajan_template_include_theme_compat', $template );
}

/**
 * Conditionally replace 'the_content'.
 *
 * Replaces the_content() if the post_type being displayed is one that would
 * normally be handled by ActivityNotifications, but proper single page templates do not
 * exist in the currently active theme.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param string $content Original post content.
 * @return string $content Post content, potentially modified.
 */
function ajan_replace_the_content( $content = '' ) {

	// Bail if not the main loop where theme compat is happening
	if ( ! ajan_do_theme_compat() )
		return $content;

	// Set theme compat to false early, to avoid recursion from nested calls to
	// the_content() that execute before theme compat has unhooked itself.
	ajan_set_theme_compat_active( false );

	// Do we have new content to replace the old content?
	$new_content = apply_filters( 'ajan_replace_the_content', $content );

	// Juggle the content around and try to prevent unsightly comments
	if ( !empty( $new_content ) && ( $new_content !== $content ) ) {

		// Set the content to be the new content
		$content = $new_content;

		// Clean up after ourselves
		unset( $new_content );

		// Reset the $post global
		wp_reset_postdata();
	}

	// Return possibly hi-jacked content
	return $content;
}

/**
 * Are we currently replacing the_content?
 *
 * @since ActivityNotifications (1.8.0)
 *
 * @return bool True if the_content is currently in the process of being
 *         filtered and replaced.
 */
function ajan_do_theme_compat() {
	return (bool) ( ! ajan_is_template_included() && in_the_loop() && ajan_is_theme_compat_active() );
}

/** Filters *******************************************************************/

/**
 * Remove all filters from a WordPress filter hook.
 *
 * Removed filters are stashed in the $ajan global, in case they need to be
 * restored later.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @global WP_filter $wp_filter
 * @global array $merged_filters
 *
 * @param string $tag The filter tag to remove filters from.
 * @param int $priority Optional. If present, only those callbacks attached
 *        at a given priority will be removed. Otherwise, all callbacks
 *        attached to the tag will be removed, regardless of priority.
 * @return bool True on success.
 */
function ajan_remove_all_filters( $tag, $priority = false ) {
	global $wp_filter, $merged_filters;

	$ajan = activitynotifications();

	// Filters exist
	if ( isset( $wp_filter[$tag] ) ) {

		// Filters exist in this priority
		if ( !empty( $priority ) && isset( $wp_filter[$tag][$priority] ) ) {

			// Store filters in a backup
			$ajan->filters->wp_filter[$tag][$priority] = $wp_filter[$tag][$priority];

			// Unset the filters
			unset( $wp_filter[$tag][$priority] );

		// Priority is empty
		} else {

			// Store filters in a backup
			$ajan->filters->wp_filter[$tag] = $wp_filter[$tag];

			// Unset the filters
			unset( $wp_filter[$tag] );
		}
	}

	// Check merged filters
	if ( isset( $merged_filters[$tag] ) ) {

		// Store filters in a backup
		$ajan->filters->merged_filters[$tag] = $merged_filters[$tag];

		// Unset the filters
		unset( $merged_filters[$tag] );
	}

	return true;
}

/**
 * Restore filters that were removed using ajan_remove_all_filters().
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @global WP_filter $wp_filter
 * @global array $merged_filters
 *
 * @param string $tag The tag to which filters should be restored.
 * @param int $priority Optional. If present, only those filters that were
 *        originally attached to the tag with $priority will be restored.
 *        Otherwise, all available filters will be restored, regardless of
 *        priority.
 * @return bool True on success.
 */
function ajan_restore_all_filters( $tag, $priority = false ) {
	global $wp_filter, $merged_filters;

	$ajan = activitynotifications();

	// Filters exist
	if ( isset( $ajan->filters->wp_filter[$tag] ) ) {

		// Filters exist in this priority
		if ( !empty( $priority ) && isset( $ajan->filters->wp_filter[$tag][$priority] ) ) {

			// Store filters in a backup
			$wp_filter[$tag][$priority] = $ajan->filters->wp_filter[$tag][$priority];

			// Unset the filters
			unset( $ajan->filters->wp_filter[$tag][$priority] );

		// Priority is empty
		} else {

			// Store filters in a backup
			$wp_filter[$tag] = $ajan->filters->wp_filter[$tag];

			// Unset the filters
			unset( $ajan->filters->wp_filter[$tag] );
		}
	}

	// Check merged filters
	if ( isset( $ajan->filters->merged_filters[$tag] ) ) {

		// Store filters in a backup
		$merged_filters[$tag] = $ajan->filters->merged_filters[$tag];

		// Unset the filters
		unset( $ajan->filters->merged_filters[$tag] );
	}

	return true;
}

/**
 * Force comments_status to 'closed' for ActivityNotifications post types.
 *
 * @since ActivityNotifications (1.7.0)
 *
 * @param bool $open True if open, false if closed.
 * @param int $post_id ID of the post to check.
 * @return bool True if open, false if closed.
 */
function ajan_comments_open( $open, $post_id = 0 ) {

	$retval = is_activitynotifications() ? false : $open;

	// Allow override of the override
	return apply_filters( 'ajan_force_comment_status', $retval, $open, $post_id );
}

/**
 * Do not allow {@link comments_template()} to render during theme compatibility.
 *
 * When theme compatibility sets the 'is_page' flag to true via
 * {@link ajan_theme_compat_reset_post()}, themes that use comments_template()
 * in their page template will run.
 *
 * To prevent comments_template() from rendering, we set the 'is_page' and
 * 'is_single' flags to false since that function looks at these conditionals
 * before querying the database for comments and loading the comments template.
 *
 * This is done during the output buffer as late as possible to prevent any
 * wonkiness.
 *
 * @since ActivityNotifications (1.9.2)
 *
 * @param string $retval The current post content.
 */
function ajan_theme_compat_toggle_is_page( $retval = '' ) {
	global $wp_query;

	$wp_query->is_single = false;
	$wp_query->is_page   = false;

	// Set a switch so we know that we've toggled these WP_Query properties
	activitynotifications()->theme_compat->is_page_toggled = true;

	return $retval;
}
add_filter( 'ajan_replace_the_content', 'ajan_theme_compat_toggle_is_page', 9999 );

/**
 * Restores the 'is_single' and 'is_page' flags if toggled by ActivityNotifications.
 *
 * @since ActivityNotifications (1.9.2)
 *
 * @see ajan_theme_compat_toggle_is_page()
 * @param object $query The WP_Query object.
 */
function ajan_theme_compat_loop_end( $query ) {

	// Get ActivityNotifications
	$ajan = activitynotifications();

	// Bail if page is not toggled
	if ( ! isset( $ajan->theme_compat->is_page_toggled ) ) {
		return;
	}

	// Revert our toggled WP_Query properties
	$query->is_single = true;
	$query->is_page   = true;

	// Unset our switch
	unset( $ajan->theme_compat->is_page_toggled );
}
add_action( 'loop_end', 'ajan_theme_compat_loop_end' );
