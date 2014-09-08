<?php

/**
 * BuddyPress Activity Streams Loader.
 *
 * An activity stream component, for users, groups, and site tracking.
 *
 * @package BuddyPress
 * @subpackage ActivityCore
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Main Activity Class.
 *
 * @since ajency-activity-and-notifications (1.5)
 */
class AJAN_Activity_Component extends AJAN_Component {

	/**
	 * Start the activity component setup process.
	 *
	 * @since ajency-activity-and-notifications (1.5)
	 */
	public function __construct() {
		parent::start(
			'activity',
			__( 'Activity Streams', 'ajency-activity-and-notifications' ),
			activitynotifications()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 10
			)
		);
	}

	/**
	 * Include component files.
	 *
	 * @since ajency-activity-and-notifications (1.5)
	 *
	 * @see AJAN_Component::includes() for a description of arguments.
	 *
	 * @param array $includes See AJAN_Component::includes() for a description.
	 */
	public function includes( $includes = array() ) {
		// Files to include
		$includes = array(
			'actions',
			'screens',
			'filters',
			'classes',
			'template',
			'functions',
			'notifications',
			'cache',
			'custom',
			'activities',
			'rest-api'
		);

		// Load Akismet support if Akismet is configured
		$akismet_key = ajan_get_option( 'wordpress_api_key' );
		if ( defined( 'AKISMET_VERSION' ) && ( !empty( $akismet_key ) || defined( 'WPCOM_API_KEY' ) ) && apply_filters( 'ajan_activity_use_akismet', ajan_is_akismet_active() ) ) {
			$includes[] = 'akismet';
		}

		if ( is_admin() ) {
			$includes[] = 'admin';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up component global variables.
	 *
	 * The AJAN_ACTIVITY_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since ajency-activity-and-notifications (1.5)
	 *
	 * @see AJAN_Component::setup_globals() for a description of arguments.
	 *
	 * @param array $args See AJAN_Component::setup_globals() for a description.
	 */
	public function setup_globals( $args = array() ) {
		$ajan = activitynotifications();

		// Define a slug, if necessary
		if ( !defined( 'AJAN_ACTIVITY_SLUG' ) )
			define( 'AJAN_ACTIVITY_SLUG', $this->id );

		// Global tables for activity component
		$global_tables = array(
			'table_name'      => $ajan->table_prefix . 'ajan_activity',
			'table_name_meta' => $ajan->table_prefix . 'ajan_activity_meta',
		);

		// Metadata tables for groups component
		$meta_tables = array(
			'activity' => $ajan->table_prefix . 'ajan_activity_meta',
		);

		// All globals for activity component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'                  => AJAN_ACTIVITY_SLUG,
			'root_slug'             => isset( $ajan->pages->activity->slug ) ? $ajan->pages->activity->slug : AJAN_ACTIVITY_SLUG,
			'has_directory'         => true,
			'directory_title'       => _x( 'Sitewide Activity', 'component directory title', 'ajency-activity-and-notifications' ),
			'notification_callback' => 'ajan_activity_format_notifications',
			'search_string'         => __( 'Search Activity...', 'ajency-activity-and-notifications' ),
			'global_tables'         => $global_tables,
			'meta_tables'           => $meta_tables,
		);

		parent::setup_globals( $args );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since ajency-activity-and-notifications (1.5)
	 *
	 * @see AJAN_Component::setup_nav() for a description of arguments.
	 * @uses ajan_is_active()
	 * @uses is_user_logged_in()
	 * @uses ajan_get_friends_slug()
	 * @uses ajan_get_groups_slug()
	 *
	 * @param array $main_nav Optional. See AJAN_Component::setup_nav() for
	 *                        description.
	 * @param array $sub_nav Optional. See AJAN_Component::setup_nav() for
	 *                       description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Add 'Activity' to the main navigation
		$main_nav = array(
			'name'                => __( 'Activity', 'ajency-activity-and-notifications' ),
			'slug'                => $this->slug,
			'position'            => 10,
			'screen_function'     => 'ajan_activity_screen_my_activity',
			'default_subnav_slug' => 'just-me',
			'item_css_id'         => $this->id
		);

		// Stop if there is no user displayed or logged in
		if ( !is_user_logged_in() && !ajan_displayed_user_id() )
			return;

		// Determine user to use
		if ( ajan_displayed_user_domain() ) {
			$user_domain = ajan_displayed_user_domain();
		} elseif ( ajan_loggedin_user_domain() ) {
			$user_domain = ajan_loggedin_user_domain();
		} else {
			return;
		}

		// User link
		$activity_link = trailingslashit( $user_domain . $this->slug );

		// Add the subnav items to the activity nav item if we are using a theme that supports this
		$sub_nav[] = array(
			'name'            => __( 'Personal', 'ajency-activity-and-notifications' ),
			'slug'            => 'just-me',
			'parent_url'      => $activity_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'ajan_activity_screen_my_activity',
			'position'        => 10
		);

		// @ mentions
		if ( ajan_activity_do_mentions() ) {
			$sub_nav[] = array(
				'name'            => __( 'Mentions', 'ajency-activity-and-notifications' ),
				'slug'            => 'mentions',
				'parent_url'      => $activity_link,
				'parent_slug'     => $this->slug,
				'screen_function' => 'ajan_activity_screen_mentions',
				'position'        => 20,
				'item_css_id'     => 'activity-mentions'
			);
		}

		// Favorite activity items
		$sub_nav[] = array(
			'name'            => __( 'Favorites', 'ajency-activity-and-notifications' ),
			'slug'            => 'favorites',
			'parent_url'      => $activity_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'ajan_activity_screen_favorites',
			'position'        => 30,
			'item_css_id'     => 'activity-favs'
		);

		// Additional menu if friends is active
		if ( ajan_is_active( 'friends' ) ) {
			$sub_nav[] = array(
				'name'            => __( 'Friends', 'ajency-activity-and-notifications' ),
				'slug'            => ajan_get_friends_slug(),
				'parent_url'      => $activity_link,
				'parent_slug'     => $this->slug,
				'screen_function' => 'ajan_activity_screen_friends',
				'position'        => 40,
				'item_css_id'     => 'activity-friends'
			) ;
		}

		// Additional menu if groups is active
		if ( ajan_is_active( 'groups' ) ) {
			$sub_nav[] = array(
				'name'            => __( 'Groups', 'ajency-activity-and-notifications' ),
				'slug'            => ajan_get_groups_slug(),
				'parent_url'      => $activity_link,
				'parent_slug'     => $this->slug,
				'screen_function' => 'ajan_activity_screen_groups',
				'position'        => 50,
				'item_css_id'     => 'activity-groups'
			);
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since ajency-activity-and-notifications (1.5)
	 *
	 * @see AJAN_Component::setup_nav() for a description of the $wp_admin_nav
	 *      parameter array.
	 * @uses is_user_logged_in()
	 * @uses trailingslashit()
	 * @uses ajan_get_total_mention_count_for_user()
	 * @uses ajan_loggedin_user_id()
	 * @uses ajan_is_active()
	 * @uses ajan_get_friends_slug()
	 * @uses ajan_get_groups_slug()
	 *
	 * @param array $wp_admin_nav See AJAN_Component::setup_admin_bar() for a
	 *                            description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		$ajan = activitynotifications();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain   = ajan_loggedin_user_domain();
			$activity_link = trailingslashit( $user_domain . $this->slug );

			// Unread message count
			if ( ajan_activity_do_mentions() ) {
				$count = ajan_get_total_mention_count_for_user( ajan_loggedin_user_id() );
				if ( !empty( $count ) ) {
					$title = sprintf( __( 'Mentions <span class="count">%s</span>', 'ajency-activity-and-notifications' ), number_format_i18n( $count ) );
				} else {
					$title = __( 'Mentions', 'ajency-activity-and-notifications' );
				}
			}

			// Add the "Activity" sub menu
			$wp_admin_nav[] = array(
				'parent' => $ajan->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Activity', 'ajency-activity-and-notifications' ),
				'href'   => trailingslashit( $activity_link )
			);

			// Personal
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-personal',
				'title'  => __( 'Personal', 'ajency-activity-and-notifications' ),
				'href'   => trailingslashit( $activity_link )
			);

			// Mentions
			if ( ajan_activity_do_mentions() ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-mentions',
					'title'  => $title,
					'href'   => trailingslashit( $activity_link . 'mentions' )
				);
			}

			// Favorites
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-favorites',
				'title'  => __( 'Favorites', 'ajency-activity-and-notifications' ),
				'href'   => trailingslashit( $activity_link . 'favorites' )
			);

			// Friends?
			if ( ajan_is_active( 'friends' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-friends',
					'title'  => __( 'Friends', 'ajency-activity-and-notifications' ),
					'href'   => trailingslashit( $activity_link . ajan_get_friends_slug() )
				);
			}

			// Groups?
			if ( ajan_is_active( 'groups' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-groups',
					'title'  => __( 'Groups', 'ajency-activity-and-notifications' ),
					'href'   => trailingslashit( $activity_link . ajan_get_groups_slug() )
				);
			}
		}

		//parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since ajency-activity-and-notifications (1.5)
	 *
	 * @uses ajan_is_activity_component()
	 * @uses ajan_is_my_profile()
	 * @uses ajan_core_fetch_avatar()
	 */
	public function setup_title() {
		$ajan = activitynotifications();

		// Adjust title based on view
		if ( ajan_is_activity_component() ) {
			if ( ajan_is_my_profile() ) {
				$ajan->ajan_options_title = __( 'My Activity', 'ajency-activity-and-notifications' );
			} else {
				$ajan->ajan_options_avatar = ajan_core_fetch_avatar( array(
					'item_id' => ajan_displayed_user_id(),
					'type'    => 'thumb',
					'alt'	  => sprintf( __( 'Profile picture of %s', 'ajency-activity-and-notifications' ), ajan_get_displayed_user_fullname() )
				) );
				$ajan->ajan_options_title  = ajan_get_displayed_user_fullname();
			}
		}

		parent::setup_title();
	}

	/**
	 * Set up actions necessary for the component.
	 *
	 * @since ajency-activity-and-notifications (1.6)
	 */
	public function setup_actions() {
		// Spam prevention
		add_action( 'ajan_include', 'ajan_activity_setup_akismet' );

		parent::setup_actions();
	}
}

/**
 * Bootstrap the Activity component.
 */
function ajan_setup_activity() {
	activitynotifications()->activity = new AJAN_Activity_Component();
}
add_action( 'ajan_setup_components', 'ajan_setup_activity', 6 );
