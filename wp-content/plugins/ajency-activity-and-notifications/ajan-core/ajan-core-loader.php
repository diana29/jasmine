<?php

/**
 * ActivityNotifications Core Loader.
 *
 * Core contains the commonly used functions, classes, and APIs.
 *
 * @package ajency-activity-and-notifications
 * @subpackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class AJAN_Core extends AJAN_Component {

	/**
	 * Start the members component creation process.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @uses AJAN_Core::bootstrap()
	 */
	public function __construct() {
		parent::start(
			'core',
			__( 'Ajency Activity And Notifications Core', 'ajency-activity-and-notifications' ),
			activitynotifications()->plugin_dir
		);

		$this->bootstrap();
	}

	/**
	 * Populate the global data needed before ActivityNotifications can continue.
	 *
	 * This involves figuring out the currently required, active, deactive,
	 * and optional components.
	 *
	 * @since ajency-activity-and-notifications (0.1.0)
	 */
	private function bootstrap() {
		$ajan = activitynotifications();

		/**
		 * At this point in the stack, ActivityNotifications core has been loaded but
		 * individual components (friends/activity/groups/etc...) have not.
		 *
		 * The 'ajan_core_loaded' action lets you execute code ahead of the
		 * other components.
		 */
		do_action( 'ajan_core_loaded' );

		/** Components ********************************************************/

		// Set the included and optional components.
		$ajan->optional_components = apply_filters( 'ajan_optional_components', array( ) );

		// Set the required components
		$ajan->required_components = apply_filters( 'ajan_required_components', array('notifications' ,'activity'   ) );
 
		// Loop through optional components
		foreach( $ajan->optional_components as $component ) {
			if ( ajan_is_active( $component ) && file_exists( $ajan->plugin_dir . '/ajan-' . $component . '/ajan-' . $component . '-loader.php' ) ) {
				include( $ajan->plugin_dir . '/ajan-' . $component . '/ajan-' . $component . '-loader.php' );
			}
		}

		// Loop through required components
		foreach( $ajan->required_components as $component ) {
			if ( file_exists( $ajan->plugin_dir . '/ajan-' . $component . '/ajan-' . $component . '-loader.php' ) ) {
				include( $ajan->plugin_dir . '/ajan-' . $component . '/ajan-' . $component . '-loader.php' );
			}
		}

		// Add Core to required components
		$ajan->required_components[] = 'core';

		do_action( 'ajan_core_components_included' );
	}

	/**
	 * Include ajan-core files.
	 *
	 * @see AJAN_Component::includes() for description of parameters.
	 *
	 * @param array $includes See {@link AJAN_Component::includes()}.
	 */
	public function includes( $includes = array() ) {

		if ( !is_admin() )
			return;

		$includes = array( 
			'admin'
		);

		parent::includes( $includes );
	}

	/**
	 * Set up ajan-core global settings.
	 *
	 * Sets up a majority of the ActivityNotifications globals that require a minimal
	 * amount of processing, meaning they cannot be set in the ActivityNotifications class.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @see AJAN_Component::setup_globals() for description of parameters.
	 *
	 * @param array $args See {@link AJAN_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		$ajan = activitynotifications();

		/** Database **********************************************************/

		// Get the base database prefix
		if ( empty( $ajan->table_prefix ) )
			$ajan->table_prefix = ajan_core_get_table_prefix();

		// The domain for the root of the site where the main blog resides
		if ( empty( $ajan->root_domain ) )
			$ajan->root_domain = ajan_core_get_root_domain();

		// Fetches all of the core ActivityNotifications settings in one fell swoop
		if ( empty( $ajan->site_options ) )
			$ajan->site_options = ajan_core_get_root_options();

		// The names of the core WordPress pages used to display ActivityNotifications content
		if ( empty( $ajan->pages ) )
			$ajan->pages = ajan_core_get_directory_pages();

		/** Basic current user data *******************************************/

		// Logged in user is the 'current_user'
		$current_user            = wp_get_current_user();

		// The user ID of the user who is currently logged in.
		$ajan->loggedin_user       = new stdClass;
		$ajan->loggedin_user->id   = isset( $current_user->ID ) ? $current_user->ID : 0;

		/** Avatars ***********************************************************/

		// Fetches the default Gravatar image to use if the user/group/blog has no avatar or gravatar
		$ajan->grav_default        = new stdClass;
		$ajan->grav_default->user  = apply_filters( 'ajan_user_gravatar_default',  $ajan->site_options['avatar_default'] );
		$ajan->grav_default->group = apply_filters( 'ajan_group_gravatar_default', $ajan->grav_default->user );
		$ajan->grav_default->blog  = apply_filters( 'ajan_blog_gravatar_default',  $ajan->grav_default->user );

		// Notifications table. Included here for legacy purposes. Use
		// ajan-notifications instead.
		$ajan->core->table_name_notifications = $ajan->table_prefix . 'ajan_notifications';

		/**
		 * Used to determine if user has admin rights on current content. If the
		 * logged in user is viewing their own profile and wants to delete
		 * something, is_item_admin is used. This is a generic variable so it
		 * can be used by other components. It can also be modified, so when
		 * viewing a group 'is_item_admin' would be 'true' if they are a group
		 * admin, and 'false' if they are not.
		 */
		ajan_update_is_item_admin( ajan_user_has_access(), 'core' );

		// Is the logged in user is a mod for the current item?
		ajan_update_is_item_mod( false,                  'core' );

		do_action( 'ajan_core_setup_globals' );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @see AJAN_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See AJAN_Component::setup_nav() for
	 *        description.
	 * @param array $sub_nav Optional. See AJAN_Component::setup_nav() for
	 *        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$ajan = activitynotifications();

	 
	}
}

/**
 * Set up the ActivityNotifications Core component.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @global ActivityNotifications $ajan ActivityNotifications global settings object.
 */
function ajan_setup_core() {
	activitynotifications()->core = new AJAN_Core();
}
add_action( 'ajan_setup_components', 'ajan_setup_core', 2 );


