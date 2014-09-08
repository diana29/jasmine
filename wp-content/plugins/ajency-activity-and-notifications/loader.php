<?php
/**
 * The Ajency Activity And Notifications Plugin
 *
 * A user activity and user notifications module for wordpress.
 *
 * $Id$
 *
 * @package ajency-activity-and-notifications 
 */

/**
 * Plugin Name: Ajency Activity And Notifications
 * Plugin URI:  http://ajency.in
 * Description: A user activity and user notifications module for wordpress.
 * Author:      Team Ajency
 * Author URI:  http://ajency.in
 * Version:     http://ajency.in
 * Text Domain: ajency-activity-and-notifications
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Constants *****************************************************************/

if ( !class_exists( 'ActivityNotifications' ) ) :
/**
 * Main ActivityNotifications Class
 *
 * Tap tap tap... Is this thing on?
 *
 * @since ajency-activity-and-notifications (0.1.0)
 */
class ActivityNotifications {

	/** Magic *************************************************************/

	/**
	 * ajency-activity-and-notifications uses many variables, most of which can be filtered to
	 * customize the way that it works. To prevent unauthorized access,
	 * these variables are stored in a private array that is magically
	 * updated using PHP 5.2+ methods. This is to prevent third party
	 * plugins from tampering with essential information indirectly, which
	 * would cause issues later.
	 *
	 * @see ActivityNotifications::setup_globals()
	 * @var array
	 */
	private $data;

	/** Not Magic *********************************************************/

	/**
	 * @var array Primary ActivityNotifications navigation.
	 */
	public $ajan_nav = array();

	/**
	 * @var array Secondary ActivityNotifications navigation to $ajan_nav.
	 */
	public $ajan_options_nav = array();

	/**
	 * @var array The unfiltered URI broken down into chunks.
	 * @see ajan_core_set_uri_globals()
	 */
	public $unfiltered_uri = array();

	/**
	 * @var array The canonical URI stack.
	 * @see ajan_redirect_canonical()
	 * @see ajan_core_new_nav_item()
	 */
	public $canonical_stack = array();

	/**
	 * @var array Additional navigation elements (supplemental).
	 */
	public $action_variables = array();

	/**
	 * @var array Required components (core, members).
	 */
	public $required_components = array();

	/**
	 * @var array Additional active components.
	 */
	public $loaded_components = array();

	/**
	 * @var array Active components.
	 */
	public $active_components = array();

	/** Option Overload ***************************************************/

	/**
	 * @var array Optional Overloads default options retrieved from get_option().
	 */
	public $options = array();

	/** Singleton *********************************************************/

	/**
	 * Main ActivityNotifications Instance.
	 *
	 * ActivityNotifications is great
	 * Please load it only one time
	 * For this, we thank you
	 *
	 * Insures that only one instance of ActivityNotifications exists in memory at any
	 * one time. Also prevents needing to define globals all over the place.
	 *
	 * @since ActivityNotifications (1.7.0)
	 *
	 * @static object $instance
	 * @uses ActivityNotifications::constants() Setup the constants (mostly deprecated).
	 * @uses ActivityNotifications::setup_globals() Setup the globals needed.
	 * @uses ActivityNotifications::legacy_constants() Setup the legacy constants (deprecated).
	 * @uses ActivityNotifications::includes() Include the required files.
	 * @uses ActivityNotifications::setup_actions() Setup the hooks and actions.
	 * @see activitynotifications()
	 *
	 * @return ActivityNotifications The one true ActivityNotifications.
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if ( null === $instance ) {
			$instance = new ActivityNotifications;
			$instance->constants();
			$instance->setup_globals();
			$instance->legacy_constants();
			$instance->includes();
			$instance->setup_actions();
		}

		// Always return the instance 
		return $instance;
	}

	/** Magic Methods *****************************************************/

	/**
	 * A dummy constructor to prevent ActivityNotifications from being loaded more than once.
	 *
	 * @since ActivityNotifications (1.7.0)
	 * @see ActivityNotifications::instance()
	 * @see activitynotifications()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent ActivityNotifications from being cloned.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ajency-activity-and-notifications' ), '1.7' ); }

	/**
	 * A dummy magic method to prevent ActivityNotifications from being unserialized.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ajency-activity-and-notifications' ), '1.7' ); }

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting ActivityNotifications varibles.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting ActivityNotifications varibles.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method for unsetting ActivityNotifications variables.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods ***************************************************/

	/**
	 * Bootstrap constants.
	 *
	 * @since ActivityNotifications (1.6.0)
	 *
	 * @uses is_multisite()
	 * @uses get_current_site()
	 * @uses get_current_blog_id()
	 * @uses plugin_dir_path()
	 * @uses plugin_dir_url()
	 */
	private function constants() {

		// Place your custom code (actions/filters) in a file called
		// '/plugins/an-custom.php' and it will be loaded before anything else.
		if ( file_exists( WP_PLUGIN_DIR . '/an-custom.php' ) )
			require( WP_PLUGIN_DIR . '/an-custom.php' );

		// Path and URL
		if ( ! defined( 'AJAN_PLUGIN_DIR' ) ) {
			define( 'AJAN_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		}

		if ( ! defined( 'AJAN_PLUGIN_URL' ) ) {
			$plugin_url = plugin_dir_url( __FILE__ );

			// If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
			if ( is_ssl() )
				$plugin_url = str_replace( 'http://', 'https://', $plugin_url );

			define( 'AJAN_PLUGIN_URL', $plugin_url );
		}

		// Define on which blog ID ActivityNotifications should run
		if ( ! defined( 'AJAN_ROOT_BLOG' ) ) {

			// Default to use current blog ID
			// Fulfills non-network installs and AJAN_ENABLE_MULTIBLOG installs
			$root_blog_id = get_current_blog_id();

			// Multisite check
			if ( is_multisite() ) {

				// Multiblog isn't enabled
				if ( ! defined( 'AJAN_ENABLE_MULTIBLOG' ) || ( defined( 'AJAN_ENABLE_MULTIBLOG' ) && (int) constant( 'AJAN_ENABLE_MULTIBLOG' ) === 0 ) ) {
					// Check to see if BP is network-activated
					// We're not using is_plugin_active_for_network() b/c you need to include the
					// /wp-admin/includes/plugin.php file in order to use that function.

					// get network-activated plugins
					$plugins = get_site_option( 'active_sitewide_plugins');

					// basename
					$basename = plugin_basename( constant( 'AJAN_PLUGIN_DIR' ) . 'ajan-loader.php' );

					// plugin is network-activated; use main site ID instead
					if ( isset( $plugins[ $basename ] ) ) {
						$current_site = get_current_site();
						$root_blog_id = $current_site->blog_id;
					}
				}

			}

			define( 'AJAN_ROOT_BLOG', $root_blog_id );
		}

		// Whether to refrain from loading deprecated functions
		if ( ! defined( 'AJAN_IGNORE_DEPRECATED' ) ) {
			define( 'AJAN_IGNORE_DEPRECATED', false );
		}

		// The search slug has to be defined nice and early because of the way
		// search requests are loaded
		//
		// @todo Make this better
		if ( !defined( 'AJAN_SEARCH_SLUG' ) )
			define( 'AJAN_SEARCH_SLUG', 'search' );
	}

	/**
	 * Component global variables.
	 *
	 * @since ActivityNotifications (1.6.0)
	 * @access private
	 *
	 * @uses plugin_dir_path() To generate ActivityNotifications plugin path.
	 * @uses plugin_dir_url() To generate ActivityNotifications plugin url.
	 * @uses apply_filters() Calls various filters.
	 */
	private function setup_globals() {

		/** Versions **************************************************/

		$this->version    = '2.0.2';
		$this->db_version = 8311;

		/** Loading ***************************************************/

		$this->load_deprecated = ! apply_filters( 'ajan_ignore_deprecated', AJAN_IGNORE_DEPRECATED );

		/** Toolbar ***************************************************/

		/**
		 * @var string The primary toolbar ID
		 */
		$this->my_account_menu_id = '';

		/** URIs ******************************************************/

		/**
		 * @var int The current offset of the URI.
		 * @see ajan_core_set_uri_globals()
		 */
		$this->unfiltered_uri_offset = 0;

		/**
		 * @var bool Are status headers already sent?
		 */
		$this->no_status_set = false;

		/** Components ************************************************/

		/**
		 * @var string Name of the current ActivityNotifications component (primary)
		 */
		$this->current_component = '';

		/**
		 * @var string Name of the current ActivityNotifications item (secondary)
		 */
		$this->current_item = '';

		/**
		 * @var string Name of the current ActivityNotifications action (tertiary)
		 */
		$this->current_action = '';

		/**
		 * @var bool Displaying custom 2nd level navigation menu (I.E a group)
		 */
		$this->is_single_item = false;

		/** Root ******************************************************/

		// ActivityNotifications Root blog ID
		$this->root_blog_id = (int) apply_filters( 'ajan_get_root_blog_id', AJAN_ROOT_BLOG );

		/** Paths******************************************************/

		// ActivityNotifications root directory
		$this->file           = __FILE__;
		$this->basename       = plugin_basename( $this->file );
		$this->plugin_dir     = AJAN_PLUGIN_DIR;
		$this->plugin_url     = AJAN_PLUGIN_URL;

		// Languages
		$this->lang_dir       = $this->plugin_dir . 'ajan-languages'; 

		/** Theme Compat **********************************************/

		$this->theme_compat   = new stdClass(); // Base theme compatibility class
		$this->filters        = new stdClass(); // Used when adding/removing filters

		/** Users *****************************************************/

		$this->current_user   = new stdClass();
		$this->displayed_user = new stdClass();
	}

	/**
	 * Legacy ActivityNotifications constants.
	 *
	 * Try to avoid using these. Their values have been moved into variables
	 * in the instance, and have matching functions to get/set their values.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	private function legacy_constants() {

		// Define the ActivityNotifications version
		if ( !defined( 'AJAN_VERSION'    ) ) define( 'AJAN_VERSION',    $this->version   );

		// Define the database version
		if ( !defined( 'AJAN_DB_VERSION' ) ) define( 'AJAN_DB_VERSION', $this->db_version );
	}

	/**
	 * Include required files.
	 *
	 * @since ActivityNotifications (1.6.0)
	 * @access private
	 *
	 * @uses is_admin() If in WordPress admin, load additional file.
	 */
	private function includes() {

		// Load the WP abstraction file so ActivityNotifications can run on all WordPress setups.
		require( $this->plugin_dir . '/ajan-core/ajan-core-wpabstraction.php' );

		// Setup the versions (after we include multisite abstraction above)
		$this->versions();

		/** Update/Install ********************************************/

		// Theme compatability
		require( $this->plugin_dir . 'ajan-core/ajan-core-template-loader.php'     );
		require( $this->plugin_dir . 'ajan-core/ajan-core-theme-compatibility.php' );

		// Require all of the ActivityNotifications core libraries
		require( $this->plugin_dir . 'ajan-core/ajan-core-dependency.php' );
		require( $this->plugin_dir . 'ajan-core/ajan-core-actions.php'    );
		require( $this->plugin_dir . 'ajan-core/ajan-core-caps.php'       );
		require( $this->plugin_dir . 'ajan-core/ajan-core-cache.php'      );
		require( $this->plugin_dir . 'ajan-core/ajan-core-cssjs.php'      );
		require( $this->plugin_dir . 'ajan-core/ajan-core-update.php'     );
		require( $this->plugin_dir . 'ajan-core/ajan-core-options.php'    );
		require( $this->plugin_dir . 'ajan-core/ajan-core-classes.php'    );
		require( $this->plugin_dir . 'ajan-core/ajan-core-filters.php'    );
		require( $this->plugin_dir . 'ajan-core/ajan-core-avatars.php'    ); 
		require( $this->plugin_dir . 'ajan-core/ajan-core-template.php'   );
		require( $this->plugin_dir . 'ajan-core/ajan-core-adminbar.php'   );
		require( $this->plugin_dir . 'ajan-core/ajan-core-ajanbar.php'   );
		require( $this->plugin_dir . 'ajan-core/ajan-core-catchuri.php'   );
		require( $this->plugin_dir . 'ajan-core/ajan-core-component.php'  );
		require( $this->plugin_dir . 'ajan-core/ajan-core-functions.php'  );
		require( $this->plugin_dir . 'ajan-core/ajan-core-moderation.php' );
		require( $this->plugin_dir . 'ajan-core/ajan-core-loader.php'     );
		require( $this->plugin_dir . 'ajan-core/ajan-core-rest-api.php'     );

 
	}

	/**
	 * Set up the default hooks and actions.
	 *
	 * @since ActivityNotifications (1.6.0)
	 * @access private
	 *
	 * @uses register_activation_hook() To register the activation hook.
	 * @uses register_deactivation_hook() To register the deactivation hook.
	 * @uses add_action() To add various actions.
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'ajan_activation'   );
		add_action( 'deactivate_' . $this->basename, 'ajan_deactivation' );

		// If ActivityNotifications is being deactivated, do not add any actions
		if ( ajan_is_deactivation( $this->basename ) )
			return;

		// Array of ActivityNotifications core actions
		$actions = array(
			'setup_theme',              // Setup the default theme compat
			'setup_current_user',       // Setup currently logged in user
			'register_post_types',      // Register post types
			'register_post_statuses',   // Register post statuses
			'register_taxonomies',      // Register taxonomies
			'register_views',           // Register the views
			'core_register_activity_component',           // Register custom activity components
			'register_theme_directory', // Register the theme directory
			'register_theme_packages',  // Register bundled theme packages (ajan-themes)
			'load_textdomain',          // Load textdomain
			'add_rewrite_tags',         // Add rewrite tags
			'generate_rewrite_rules'    // Generate rewrite rules
		);

		// Add the actions
		foreach( $actions as $class_action )
			add_action( 'ajan_' . $class_action, array( $this, $class_action ), 5 );

		// All ActivityNotifications actions are setup (includes bajan-core-hooks.php)
		do_action_ref_array( 'ajan_after_setup_actions', array( &$this ) );
	}

	/**
	 * Private method to align the active and database versions.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	private function versions() {

		// Get the possible DB versions (boy is this gross)
		$versions               = array();
		$versions['1.6-single'] = get_blog_option( $this->root_blog_id, '_ajan_db_version' );

		// 1.6-single exists, so trust it
		if ( !empty( $versions['1.6-single'] ) ) {
			$this->db_version_raw = (int) $versions['1.6-single'];

		// If no 1.6-single exists, use the max of the others
		} else {
			$versions['1.2']        = get_site_option(                      'ajan-core-db-version' );
			$versions['1.5-multi']  = get_site_option(                           'ajan-db-version' );
			$versions['1.6-multi']  = get_site_option(                          '_ajan_db_version' );
			$versions['1.5-single'] = get_blog_option( $this->root_blog_id,      'ajan-db-version' );

			// Remove empty array items
			$versions             = array_filter( $versions );
			$this->db_version_raw = (int) ( !empty( $versions ) ) ? (int) max( $versions ) : 0;
		}
	}

	/** Public Methods ****************************************************/

	/**
	 * Set up ActivityNotifications's legacy theme directory.
	 *
	 * Starting with version 1.2, and ending with version 1.8, ActivityNotifications
	 * registered a custom theme directory - ajan-themes - which contained
	 * the ajan-default theme. Since ActivityNotifications 1.9, ajan-themes is no longer
	 * registered (and ajan-default no longer offered) on new installations.
	 * Sites using ajan-default (or a child theme of ajan-default) will
	 * continue to have ajan-themes registered as before.
	 *
	 * @since ActivityNotifications (1.5.0)
	 *
	 * @todo Move ajan-default to wordpress.org/extend/themes and remove this.
	 */
	public function register_theme_directory() {
		if ( ! ajan_do_register_theme_directory() ) {
			return;
		}

		register_theme_directory( $this->old_themes_dir );
	}

	/**
	 * Register bundled theme packages.
	 *
	 * Note that since we currently have complete control over ajan-themes and
	 * the ajan-legacy folders, it's fine to hardcode these here. If at a
	 * later date we need to automate this, an API will need to be built.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function register_theme_packages() {

		// Register the default theme compatibility package
		ajan_register_theme_package( array(
			'id'      => 'legacy',
			'name'    => __( 'ActivityNotifications Default', 'ajency-activity-and-notifications' ),
			'version' => ajan_get_version(),
			'dir'     => trailingslashit( $this->themes_dir . '/ajan-legacy' ),
			'url'     => trailingslashit( $this->themes_url . '/ajan-legacy' )
		) );

		// Register the basic theme stack. This is really dope.
		ajan_register_template_stack( 'get_stylesheet_directory', 10 );
		ajan_register_template_stack( 'get_template_directory',   12 );
		ajan_register_template_stack( 'ajan_get_theme_compat_dir',  14 );
	}

	/**
	 * Set up the default ActivityNotifications theme compatability location.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function setup_theme() {

		// Bail if something already has this under control
		if ( ! empty( $this->theme_compat->theme ) )
			return;

		// Setup the theme package to use for compatibility
		ajan_setup_theme_compat( ajan_get_theme_package_id() );
	}
}

/**
 * The main function responsible for returning the one true ActivityNotifications Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $ajan = activitynotifications(); ?>
 *
 * @return ActivityNotifications The one true ActivityNotifications Instance.
 */
function activitynotifications() {
	return ActivityNotifications::instance();
}

/**
 * Hook ActivityNotifications early onto the 'plugins_loaded' action..
 *
 * This gives all other plugins the chance to load before ActivityNotifications, to get
 * their actions, filters, and overrides setup without ActivityNotifications being in the
 * way.
 */
if ( defined( 'AJENCYACTIVITYNOTIFICATIONS_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'ajency-activity-and-notifications', (int) AJENCYACTIVITYNOTIFICATIONS_LATE_LOAD );

// "And now here's something we hope you'll really like!"
} else {
	$GLOBALS['ajan'] = activitynotifications();
}

endif;
