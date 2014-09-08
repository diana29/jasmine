<?php

/**
 * BuddyPress Member Notifications Loader.
 *
 * Initializes the Notifications component.
 *
 * @package BuddyPress
 * @subpackage NotificationsLoader
 * @since ajency-activity-and-notifications (1.9.0)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class AJAN_Notifications_Component extends AJAN_Component {

	/**
	 * Start the notifications component creation process.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	public function __construct() {
		parent::start(
			'notifications',
			__( 'Notifications', 'ajency-activity-and-notifications' ),
			activitynotifications()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 30
			)
		);
	}

	/**
	 * Include notifications component files.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see AJAN_Component::includes() for a description of arguments.
	 *
	 * @param array $includes See AJAN_Component::includes() for a description.
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'actions',
			'classes',
			'screens',
			'adminbar',
			'buddybar',
			'template',
			'functions',
			'cache',
			'rest-api',
			'custom'
		);

		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * The AJAN_FRIENDS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see AJAN_Component::setup_globals() for a description of arguments.
	 *
	 * @param array $args See AJAN_Component::setup_globals() for a description.
	 */
	public function setup_globals( $args = array() ) {
		// Define a slug, if necessary
		if ( !defined( 'AJAN_NOTIFICATIONS_SLUG' ) ) {
			define( 'AJAN_NOTIFICATIONS_SLUG', $this->id );
		}

		// Global tables for the notifications component
		$global_tables = array(
			'table_name' => ajan_core_get_table_prefix() . 'ajan_notifications'
		);

		// All globals for the notifications component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'          => AJAN_NOTIFICATIONS_SLUG,
			'has_directory' => false,
			'search_string' => __( 'Search Notifications...', 'ajency-activity-and-notifications' ),
			'global_tables' => $global_tables,
		);

		parent::setup_globals( $args );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see AJAN_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See AJAN_Component::setup_nav() for
	 *        description.
	 * @param array $sub_nav Optional. See AJAN_Component::setup_nav() for
	 *        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Only grab count if we're on a user page and current user has access
		if ( ajan_is_user() && ajan_user_has_access() ) {
			$count    = ajan_notifications_get_unread_notification_count( ajan_displayed_user_id() );
			$class    = ( 0 === $count ) ? 'no-count' : 'count';
			$nav_name = sprintf( __( 'Notifications <span class="%s">%s</span>', 'ajency-activity-and-notifications' ), esc_attr( $class ), number_format_i18n( $count ) );
		} else {
			$nav_name = __( 'Notifications', 'ajency-activity-and-notifications' );
		}

		// Add 'Notifications' to the main navigation
		$main_nav = array(
			'name'                    => $nav_name,
			'slug'                    => $this->slug,
			'position'                => 30,
			'show_for_displayed_user' => ajan_core_can_edit_settings(),
			'screen_function'         => 'ajan_notifications_screen_unread',
			'default_subnav_slug'     => 'unread',
			'item_css_id'             => $this->id,
		);

		// Determine user to use
		if ( ajan_displayed_user_domain() ) {
			$user_domain = ajan_displayed_user_domain();
		} elseif ( ajan_loggedin_user_domain() ) {
			$user_domain = ajan_loggedin_user_domain();
		} else {
			return;
		}

		$notifications_link = trailingslashit( $user_domain . ajan_get_notifications_slug() );

		// Add the subnav items to the notifications nav item
		$sub_nav[] = array(
			'name'            => __( 'Unread', 'ajency-activity-and-notifications' ),
			'slug'            => 'unread',
			'parent_url'      => $notifications_link,
			'parent_slug'     => ajan_get_notifications_slug(),
			'screen_function' => 'ajan_notifications_screen_unread',
			'position'        => 10,
			'item_css_id'     => 'notifications-my-notifications',
			'user_has_access' => ajan_core_can_edit_settings(),
		);

		$sub_nav[] = array(
			'name'            => __( 'Read',   'ajency-activity-and-notifications' ),
			'slug'            => 'read',
			'parent_url'      => $notifications_link,
			'parent_slug'     => ajan_get_notifications_slug(),
			'screen_function' => 'ajan_notifications_screen_read',
			'position'        => 20,
			'user_has_access' => ajan_core_can_edit_settings(),
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see AJAN_Component::setup_nav() for a description of the $wp_admin_nav
	 *      parameter array.
	 *
	 * @param array $wp_admin_nav See AJAN_Component::setup_admin_bar() for a
	 *        description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$notifications_link = trailingslashit( ajan_loggedin_user_domain() . $this->slug );

			// Pending notification requests
			$count = ajan_notifications_get_unread_notification_count( ajan_loggedin_user_id() );
			if ( ! empty( $count ) ) {
				$title  = sprintf( __( 'Notifications <span class="count">%s</span>', 'ajency-activity-and-notifications' ), number_format_i18n( $count ) );
				$unread = sprintf( __( 'Unread <span class="count">%s</span>',        'ajency-activity-and-notifications' ), number_format_i18n( $count ) );
			} else {
				$title  = __( 'Notifications', 'ajency-activity-and-notifications' );
				$unread = __( 'Unread',        'ajency-activity-and-notifications' );
			}

			// Add the "My Account" sub menus
			$wp_admin_nav[] = array(
				'parent' => activitynotifications()->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => trailingslashit( $notifications_link ),
			);

			// Unread
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-unread',
				'title'  => $unread,
				'href'   => trailingslashit( $notifications_link ),
			);

			// Read
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-read',
				'title'  => __( 'Read', 'ajency-activity-and-notifications' ),
				'href'   => trailingslashit( $notifications_link . 'read' ),
			);
		}

		//parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	public function setup_title() {
		$ajan = activitynotifications();

		// Adjust title
		if ( ajan_is_notifications_component() ) {
			if ( ajan_is_my_profile() ) {
				$ajan->ajan_options_title = __( 'Notifications', 'ajency-activity-and-notifications' );
			} else {
				$ajan->ajan_options_avatar = ajan_core_fetch_avatar( array(
					'item_id' => ajan_displayed_user_id(),
					'type'    => 'thumb',
					'alt'     => sprintf( __( 'Profile picture of %s', 'ajency-activity-and-notifications' ), ajan_get_displayed_user_fullname() )
				) );
				$ajan->ajan_options_title = ajan_get_displayed_user_fullname();
			}
		}

		parent::setup_title();
	}
}

/**
 * Bootstrap the Notifications component.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_setup_notifications() {
	activitynotifications()->notifications = new AJAN_Notifications_Component();
}
add_action( 'ajan_setup_components', 'ajan_setup_notifications', 6 );
