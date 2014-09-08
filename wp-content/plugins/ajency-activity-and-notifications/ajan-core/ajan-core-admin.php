<?php

/**
 * Main ActivityNotifications Admin Class.
 *
 * @package ActivityNotifications
 * @subpackage CoreAdministration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AJAN_Admin' ) ) :
/**
 * Load ActivityNotifications plugin admin area.
 *
 * @package ActivityNotifications
 * @subpackage CoreAdministration
 *
 * @since ActivityNotifications (1.6.0)
 */
class AJAN_Admin {

	/** Directory *************************************************************/

	/**
	 * Path to the ActivityNotifications admin directory.
	 *
	 * @var string $admin_dir
	 */
	public $admin_dir = '';

	/** URLs ******************************************************************/

	/**
	 * URL to the ActivityNotifications admin directory.
	 *
	 * @var string $admin_url
	 */
	public $admin_url = '';

	/**
	 * URL to the ActivityNotifications images directory.
	 *
	 * @var string $images_url
	 */
	public $images_url = '';

	/**
	 * URL to the ActivityNotifications admin CSS directory.
	 *
	 * @var string $css_url
	 */
	public $css_url = '';

	/**
	 * URL to the ActivityNotifications admin JS directory.
	 *
	 * @var string
	 */
	public $js_url = '';

	/** Other *****************************************************************/

	/**
	 * Notices used for user feedback, like saving settings.
	 *
	 * @var array()
	 */
	public $notices = array();

	/** Methods ***************************************************************/

	/**
	 * The main ActivityNotifications admin loader.
	 *
	 * @since ActivityNotifications (1.6.0)
	 *
	 * @uses AJAN_Admin::setup_globals() Setup the globals needed.
	 * @uses AJAN_Admin::includes() Include the required files.
	 * @uses AJAN_Admin::setup_actions() Setup the hooks and actions.
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Set admin-related globals.
	 *
	 * @access private
	 * @since ActivityNotifications (1.6.0)
	 */
	private function setup_globals() {
		$ajan = activitynotifications();

		// Paths and URLs
		$this->admin_dir  = trailingslashit( $ajan->plugin_dir  . 'ajan-core/admin' ); // Admin path
		$this->admin_url  = trailingslashit( $ajan->plugin_url  . 'ajan-core/admin' ); // Admin url
		$this->images_url = trailingslashit( $this->admin_url . 'images'        ); // Admin images URL
		$this->css_url    = trailingslashit( $this->admin_url . 'css'           ); // Admin css URL
		$this->js_url     = trailingslashit( $this->admin_url . 'js'            ); // Admin css URL

		// Main settings page
		$this->settings_page = ajan_core_do_network_admin() ? 'settings.php' : 'options-general.php';

		// Main capability
		$this->capability = ajan_core_do_network_admin() ? 'manage_network_options' : 'manage_options';
	}

	/**
	 * Include required files.
	 *
	 * @since ActivityNotifications (1.6.0)
	 * @access private
	 */
	private function includes() {
		require( $this->admin_dir . 'ajan-core-actions.php'    ); 
		require( $this->admin_dir . 'ajan-core-functions.php'  ); 
	}

	/**
	 * Set up the admin hooks, actions, and filters.
	 *
	 * @access private
	 * @since ActivityNotifications (1.6.0)
	 *
	 * @uses add_action() To add various actions.
	 * @uses add_filter() To add various filters.
	 */
	private function setup_actions() {

		/** General Actions ***************************************************/

		// Add some page specific output to the <head>
		add_action( 'ajan_admin_head',            array( $this, 'admin_head'  ), 999 );

		// Add menu item to settings menu
		add_action( ajan_core_admin_hook(),       array( $this, 'admin_menus' ), 5 );

		// Enqueue all admin JS and CSS
		add_action( 'ajan_admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/** ActivityNotifications Actions ************************************************/

		// Load the ActivityNotifications metabox in the WP Nav Menu Admin UI
		add_action( 'load-nav-menus.php', 'ajan_admin_wp_nav_menu_meta_box' );

		// Add settings
		add_action( 'ajan_register_admin_settings', array( $this, 'register_admin_settings' ) );

		// Add a link to ActivityNotifications About page to the admin bar
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_about_link' ), 15 );

		// Add a description of new ActivityNotifications tools in the available tools page
		add_action( 'tool_box', 'ajan_core_admin_available_tools_intro' );
		add_action( 'ajan_network_tool_box', 'ajan_core_admin_available_tools_intro' );

		// On non-multisite, catch
		add_action( 'load-users.php', 'ajan_core_admin_user_manage_spammers' );

		/** Filters ***********************************************************/

		// Add link to settings page
		add_filter( 'plugin_action_links',               array( $this, 'modify_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

		// Add "Mark as Spam" row actions on users.php
		add_filter( 'ms_user_row_actions', 'ajan_core_admin_user_row_actions', 10, 2 );
		add_filter( 'user_row_actions',    'ajan_core_admin_user_row_actions', 10, 2 );
	}

	/**
	 * Add the navigational menu elements.
	 *
	 * @since ActivityNotifications (1.6)
	 *
	 * @uses add_management_page() To add the Recount page in Tools section.
	 * @uses add_options_page() To add the Forums settings page in Settings
	 *       section.
	 */
	public function admin_menus() {

		// Bail if user cannot moderate
		if ( ! ajan_current_user_can( 'manage_options' ) )
			return;

		// About
		add_dashboard_page(
			__( 'Welcome to ActivityNotifications',  'ajency-activity-and-notifications' ),
			__( 'Welcome to ActivityNotifications',  'ajency-activity-and-notifications' ),
			'manage_options',
			'ajan-about',
			array( $this, 'about_screen' )
		);

		// Credits
		add_dashboard_page(
			__( 'Welcome to ActivityNotifications',  'ajency-activity-and-notifications' ),
			__( 'Welcome to ActivityNotifications',  'ajency-activity-and-notifications' ),
			'manage_options',
			'ajan-credits',
			array( $this, 'credits_screen' )
		);

		$hooks = array();

		// Changed in BP 1.6 . See ajan_core_admin_backpat_menu()
		$hooks[] = add_menu_page(
			__( 'ActivityNotifications', 'ajency-activity-and-notifications' ),
			__( 'ActivityNotifications', 'ajency-activity-and-notifications' ),
			$this->capability,
			'ajan-general-settings',
			'ajan_core_admin_backpat_menu',
			'div'
		);

		$hooks[] = add_submenu_page(
			'ajan-general-settings',
			__( 'ActivityNotifications Help', 'ajency-activity-and-notifications' ),
			__( 'Help', 'ajency-activity-and-notifications' ),
			$this->capability,
			'ajan-general-settings',
			'ajan_core_admin_backpat_page'
		);

		// Add the option pages
		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'ActivityNotifications Components', 'ajency-activity-and-notifications' ),
			__( 'ActivityNotifications', 'ajency-activity-and-notifications' ),
			$this->capability,
			'ajan-components',
			'ajan_core_admin_components_settings'
		);

		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'ActivityNotifications Pages', 'ajency-activity-and-notifications' ),
			__( 'ActivityNotifications Pages', 'ajency-activity-and-notifications' ),
			$this->capability,
			'ajan-page-settings',
			'ajan_core_admin_slugs_settings'
		);

		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'ActivityNotifications Settings', 'ajency-activity-and-notifications' ),
			__( 'ActivityNotifications Settings', 'ajency-activity-and-notifications' ),
			$this->capability,
			'ajan-settings',
			'ajan_core_admin_settings'
		);

		// For consistency with non-Multisite, we add a Tools menu in
		// the Network Admin as a home for our Tools panel
		if ( is_multisite() && ajan_core_do_network_admin() ) {
			$tools_parent = 'network-tools';

			$hooks[] = add_menu_page(
				__( 'Tools', 'ajency-activity-and-notifications' ),
				__( 'Tools', 'ajency-activity-and-notifications' ),
				$this->capability,
				$tools_parent,
				'ajan_core_tools_top_level_item',
				'',
				24 // just above Settings
			);

			$hooks[] = add_submenu_page(
				$tools_parent,
				__( 'Available Tools', 'ajency-activity-and-notifications' ),
				__( 'Available Tools', 'ajency-activity-and-notifications' ),
				$this->capability,
				'available-tools',
				'ajan_core_admin_available_tools_page'
			);
		} else {
			$tools_parent = 'tools.php';
		}

		$hooks[] = add_submenu_page(
			$tools_parent,
			__( 'ActivityNotifications Tools', 'ajency-activity-and-notifications' ),
			__( 'ActivityNotifications', 'ajency-activity-and-notifications' ),
			$this->capability,
			'ajan-tools',
			'ajan_core_admin_tools'
		);

		// Fudge the highlighted subnav item when on a ActivityNotifications admin page
		foreach( $hooks as $hook ) {
			add_action( "admin_head-$hook", 'ajan_core_modify_admin_menu_highlight' );
		}
	}

	/**
	 * Register the settings.
	 *
	 * @since ActivityNotifications (1.6.0)
	 *
	 * @uses add_settings_section() To add our own settings section.
	 * @uses add_settings_field() To add various settings fields.
	 * @uses register_setting() To register various settings.
	 */
	public function register_admin_settings() {

		/** Main Section ******************************************************/

		// Add the main section
		add_settings_section( 'ajan_main',            __( 'Main Settings',    'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_main_section',     'ajency-activity-and-notifications'            );

		// Hide toolbar for logged out users setting
		add_settings_field( 'hide-loggedout-adminbar',        __( 'Toolbar',        'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_admin_bar',        'ajency-activity-and-notifications', 'ajan_main' );
	 	register_setting  ( 'ajency-activity-and-notifications',           'hide-loggedout-adminbar',        'intval'                                                                              );

		// Only show 'switch to Toolbar' option if the user chose to retain the BuddyBar during the 1.6 upgrade
		if ( (bool) ajan_get_option( '_ajan_force_ajanbar', false ) ) {
			add_settings_field( '_ajan_force_ajanbar', __( 'Toolbar', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_force_ajanbar', 'ajency-activity-and-notifications', 'ajan_main' );
		 	register_setting( 'ajency-activity-and-notifications', '_ajan_force_ajanbar', 'ajan_admin_sanitize_callback_force_ajanbar' );
		}

		// Allow account deletion
		add_settings_field( 'ajan-disable-account-deletion', __( 'Account Deletion', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_account_deletion', 'ajency-activity-and-notifications', 'ajan_main' );
	 	register_setting  ( 'ajency-activity-and-notifications',           'ajan-disable-account-deletion', 'intval'                                                                              );

		/** XProfile Section **************************************************/

		if ( ajan_is_active( 'xprofile' ) ) {

			// Add the main section
			add_settings_section( 'ajan_xprofile',      __( 'Profile Settings', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_xprofile_section', 'ajency-activity-and-notifications'                );

			$avatar_setting = 'ajan_xprofile';

			// Profile sync setting
			add_settings_field( 'ajan-disable-profile-sync',   __( 'Profile Syncing',  'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_profile_sync',     'ajency-activity-and-notifications', 'ajan_xprofile' );
			register_setting  ( 'ajency-activity-and-notifications',         'ajan-disable-profile-sync',     'intval'                                                                                  );
		}

		/** Groups Section ****************************************************/

		if ( ajan_is_active( 'groups' ) ) {

			// Add the main section
			add_settings_section( 'ajan_groups',        __( 'Groups Settings',  'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_groups_section',   'ajency-activity-and-notifications'              );

			if ( empty( $avatar_setting ) )
				$avatar_setting = 'ajan_groups';

			// Allow subscriptions setting
			add_settings_field( 'ajan_restrict_group_creation', __( 'Group Creation',   'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_group_creation',   'ajency-activity-and-notifications', 'ajan_groups' );
			register_setting  ( 'ajency-activity-and-notifications',         'ajan_restrict_group_creation',   'intval'                                                                                );
		}

		/** Forums ************************************************************/

		if ( ajan_is_active( 'forums' ) ) {

			// Add the main section
			add_settings_section( 'ajan_forums',        __( 'Legacy Group Forums',       'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_bbpress_section',       'ajency-activity-and-notifications'              );

			// Allow subscriptions setting
			add_settings_field( 'bb-config-location', __( 'bbPress Configuration', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_bbpress_configuration', 'ajency-activity-and-notifications', 'ajan_forums' );
			register_setting  ( 'ajency-activity-and-notifications',         'bb-config-location',        ''                                                                                           );
		}

		/** Activity Section **************************************************/

		if ( ajan_is_active( 'activity' ) ) {

			// Add the main section
			add_settings_section( 'ajan_activity',      __( 'Activity Settings', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_activity_section', 'ajency-activity-and-notifications'                );

			// Activity commenting on blog and forum posts
			add_settings_field( 'ajan-disable-blogforum-comments', __( 'Blog &amp; Forum Comments', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_blogforum_comments', 'ajency-activity-and-notifications', 'ajan_activity' );
			register_setting( 'ajency-activity-and-notifications', 'ajan-disable-blogforum-comments', 'ajan_admin_sanitize_callback_blogforum_comments' );

			// Activity Heartbeat refresh
			add_settings_field( '_ajan_enable_heartbeat_refresh', __( 'Activity auto-refresh', 'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_heartbeat', 'ajency-activity-and-notifications', 'ajan_activity' );
			register_setting( 'ajency-activity-and-notifications', '_ajan_enable_heartbeat_refresh', 'intval' );

			// Allow activity akismet
			if ( is_plugin_active( 'akismet/akismet.php' ) && defined( 'AKISMET_VERSION' ) ) {
				add_settings_field( '_ajan_enable_akismet', __( 'Akismet',          'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_activity_akismet', 'ajency-activity-and-notifications', 'ajan_activity' );
				register_setting  ( 'ajency-activity-and-notifications',         '_ajan_enable_akismet',   'intval'                                                                                  );
			}
		}

		/** Avatar upload for users or groups ************************************/

		if ( ! empty( $avatar_setting ) ) {
		    // Allow avatar uploads
		    add_settings_field( 'ajan-disable-avatar-uploads', __( 'Avatar Uploads',   'ajency-activity-and-notifications' ), 'ajan_admin_setting_callback_avatar_uploads',   'ajency-activity-and-notifications', $avatar_setting );
		    register_setting  ( 'ajency-activity-and-notifications',         'ajan-disable-avatar-uploads',   'intval'                                                                                    );
		}
	}

	/**
	 * Add a link to ActivityNotifications About page to the admin bar.
	 *
	 * @since ActivityNotifications (1.9.0)
	 *
	 * @param WP_Admin_Bar $wp_admin_bar As passed to 'admin_bar_menu'.
	 */
	public function admin_bar_about_link( $wp_admin_bar ) {
		if ( is_user_logged_in() ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'wp-logo',
				'id'     => 'ajan-about',
				'title'  => esc_html__( 'About ActivityNotifications', 'ajency-activity-and-notifications' ),
				'href'   => add_query_arg( array( 'page' => 'ajan-about' ), ajan_get_admin_url( 'index.php' ) ),
			) );
		}
	}

	/**
	 * Add Settings link to plugins area.
	 *
	 * @since ActivityNotifications (1.6.0)
	 *
	 * @param array $links Links array in which we would prepend our link.
	 * @param string $file Current plugin basename.
	 * @return array Processed links.
	 */
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not ActivityNotifications
		if ( plugin_basename( activitynotifications()->file ) != $file )
			return $links;

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . add_query_arg( array( 'page' => 'ajan-components' ), ajan_get_admin_url( $this->settings_page ) ) . '">' . esc_html__( 'Settings', 'ajency-activity-and-notifications' ) . '</a>',
			'about'    => '<a href="' . add_query_arg( array( 'page' => 'ajan-about'      ), ajan_get_admin_url( 'index.php'          ) ) . '">' . esc_html__( 'About',    'ajency-activity-and-notifications' ) . '</a>'
		) );
	}

	/**
	 * Add some general styling to the admin area.
	 *
	 * @since ActivityNotifications (1.6.0)
	 */
	public function admin_head() {

		// Settings pages
		remove_submenu_page( $this->settings_page, 'ajan-page-settings' );
		remove_submenu_page( $this->settings_page, 'ajan-settings'      );

		// Network Admin Tools
		remove_submenu_page( 'network-tools', 'network-tools' );

		// About and Credits pages
		remove_submenu_page( 'index.php', 'ajan-about'   );
		remove_submenu_page( 'index.php', 'ajan-credits' );
	}

	/**
	 * Add some general styling to the admin area.
	 *
	 * @since ActivityNotifications (1.6.0)
	 */
	public function enqueue_scripts() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$file = $this->css_url . "common{$min}.css";
		$file = apply_filters( 'ajan_core_admin_common_css', $file );
		wp_enqueue_style( 'ajan-admin-common-css', $file, array(), ajan_get_version() );
	}

	/** About *****************************************************************/

	/**
	 * Output the about screen.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function about_screen() {
		global $wp_rewrite;

		$is_new_install = ! empty( $_GET['is_new_install'] );

		$pretty_permalinks_enabled = ! empty( $wp_rewrite->permalink_structure );

		$image_base = activitynotifications()->plugin_url . 'ajan-core/images/bp20/';

		list( $display_version ) = explode( '-', ajan_get_version() ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to ActivityNotifications %s', 'ajency-activity-and-notifications' ), $display_version ); ?></h1>
			<div class="about-text">
				<?php if ( $is_new_install ) : ?>
					<?php printf( __( 'It&#8217;s a great time to use ActivityNotifications! With a focus on speed, admin tools, and developer enhancements, %s is our leanest and most powerful version yet.', 'ajency-activity-and-notifications' ), $display_version ); ?>
				<?php else : ?>
					<?php printf( __( 'Thanks for updating! With a focus on speed, admin tools, and developer enhancements, ActivityNotifications %s is our leanest and most powerful version yet.', 'ajency-activity-and-notifications' ), $display_version ); ?>
				<?php endif; ?>
			</div>

			<div class="ajan-badge"></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'What&#8217;s New', 'ajency-activity-and-notifications' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'ajency-activity-and-notifications' ); ?>
				</a>
			</h2>

			<?php if ( $is_new_install ) : ?>
			<h3><?php _e( 'Getting Started', 'ajency-activity-and-notifications' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Your Default Setup', 'ajency-activity-and-notifications' ); ?></h4>

					<?php if ( ajan_is_active( 'members' ) && ajan_is_active( 'activity' ) && current_user_can( $this->capability ) ) : ?>
						<p><?php printf(
						__( 'ActivityNotifications&#8217;s powerful features help your users connect and collaborate. To help get your community started, we&#8217;ve activated two of the most commonly used tools in BP: <strong>Extended Profiles</strong> and <strong>Activity Streams</strong>. See these components in action at the %1$s and %2$s directories, and be sure to spend a few minutes <a href="%3$s">configuring user profiles</a>. Want to explore more of BP&#8217;s features? Visit the <a href="%4$s">Components panel</a>.', 'ajency-activity-and-notifications' ),
						$pretty_permalinks_enabled ? '<a href="' . trailingslashit( ajan_get_root_domain() . '/' . ajan_get_members_root_slug() ) . '">' . __( 'Members', 'ajency-activity-and-notifications' ) . '</a>' : __( 'Members', 'ajency-activity-and-notifications' ),
						$pretty_permalinks_enabled ? '<a href="' . trailingslashit( ajan_get_root_domain() . '/' . ajan_get_activity_root_slug() ) . '">' . __( 'Activity', 'ajency-activity-and-notifications' ) . '</a>' : __( 'Activity', 'ajency-activity-and-notifications' ),
						ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-profile-setup' ), 'users.php' ) ),
						ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-components' ), $this->settings_page ) )
					); ?></p>

					<?php else : ?>
						<p><?php printf(
						__( 'ActivityNotifications&#8217;s powerful features help your users connect and collaborate. Want to explore BP&#8217;s features? Visit the <a href="%s">Components panel</a>.', 'ajency-activity-and-notifications' ),
						ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-components' ), $this->settings_page ) )
					); ?></p>

					<?php endif; ?>

					<h4><?php _e( 'Community and Support', 'ajency-activity-and-notifications' ); ?></h4>
					<p><?php _e( 'Looking for help? The <a href="http://codex.buddypress.org/">ActivityNotifications Codex</a> has you covered, with dozens of user-contributed guides on how to configure and use your BP site. Can&#8217;t find what you need? Stop by <a href="http://buddypress.org/support/">our support forums</a>, where a vibrant community of ActivityNotifications users and developers is waiting to share tips, show off their sites, talk about the future of ActivityNotifications, and much more.', 'ajency-activity-and-notifications' ) ?></p>
				</div>
				<hr />

			<?php endif; ?>

			<div class="changelog">
				<h2 class="about-headline-callout"><?php _e( 'Performance Improvements', 'ajency-activity-and-notifications' ); ?></h2>
				<img class="about-overview-img" src="<?php echo $image_base ?>performance.png" alt="Performance improvements in BP 2.0" />
				<p><?php esc_html_e( 'Whether your community has tens of members or tens of thousands, we think the performance improvements in ActivityNotifications 2.0 will knock your socks off. We&#8217;ve slashed our memory footprint and query overhead across the board, with a special focus on the Activity and Members components.', 'ajency-activity-and-notifications' ) ?></p>
			</div>

			<hr />

			<div class="changelog">
				<h2 class="about-headline-callout"><?php _e( 'New Administrative Tools', 'ajency-activity-and-notifications' ); ?></h2>

				<div class="feature-section col two-col">
					<div>
						<h4><?php esc_html_e( 'Extended Profiles in Admin', 'ajency-activity-and-notifications' ); ?></h4>
						<p><?php esc_html_e( 'Site administrators can edit members&#8217; xProfile data at Dashboard > Users > Extended Profiles.', 'ajency-activity-and-notifications' ); ?></p>
						<img src="<?php echo $image_base ?>admin-xprofile.jpg" style="width:90%" />
					</div>

					<div class="last-feature">
						<h4><?php esc_html_e( 'Registration Management', 'ajency-activity-and-notifications' ); ?></h4>
						<p><?php esc_html_e( 'Perform common tasks with pending signups - including resending activation emails and manually activating accounts - on the new Pending tab of Dashboard > Users.', 'ajency-activity-and-notifications' ); ?></p>
						<img src="<?php echo $image_base ?>users-pending.jpg" style="width:90%" />
					</div>
				</div>

				<div class="feature-section col two-col">
					<div>
						<h4><?php esc_html_e( 'ActivityNotifications Repair Tools', 'ajency-activity-and-notifications' ); ?></h4>
						<p><?php esc_html_e( 'Dashboard > Tools > ActivityNotifications contains a number of tools for correcting data that occasionally gets out of sync on BP installs.', 'ajency-activity-and-notifications' ); ?></p>
						<img src="<?php echo $image_base ?>tools-buddypress.jpg" style="width:90%" />
					</div>

					<div class="feature-section col two-col">
						<h4><?php esc_html_e( 'Mark Spammers in Admin', 'ajency-activity-and-notifications' ); ?></h4>
						<p><?php esc_html_e( 'Admins on non-Multisite installations can now perform spam actions from Dashboard > Users > All Users.', 'ajency-activity-and-notifications' ); ?></p>
						<img src="<?php echo $image_base ?>user-mark-spam.jpg" style="width:90%" />
					</div>
				</div>

			</div>

			<hr />

			<div class="changelog">
				<h2 class="about-headline-callout"><?php esc_html_e( 'A More Dynamic Activity Stream', 'ajency-activity-and-notifications' ); ?></h2>
				<div class="feature-section col two-col">
					<div>
						<p><?php esc_html_e( 'Spend a lot of time viewing the activity stream? ActivityNotifications 2.0 automatically lets you know when new items are waiting to be loaded.', 'ajency-activity-and-notifications' ); ?></p>

						<p><?php esc_html_e( 'The activity stream is better integrated with blog posts, too. Comment on a blog post, and an activity item is posted. Comment on a blog-related activity item, and a blog comment is posted. No more worrying about fractured conversations.', 'ajency-activity-and-notifications' ) ?></p>

						<p><?php esc_html_e( 'We&#8217;ve also reworked the way that phrases like "Boone posted an update" are handled, so that they&#8217;re always up-to-date and always translatable.', 'ajency-activity-and-notifications' ) ?></p>
					</div>

					<div class="feature-section col two-col">
						<img src="<?php echo $image_base ?>load-newest.jpg" style="width:90%" />
					</div>
				</div>
			</div>

			<hr />

			<div class="changelog">
				<h2 class="about-headline-callout"><?php esc_html_e( 'Developer Tools', 'ajency-activity-and-notifications' ); ?></h2>

				<p><?php esc_html_e( 'ActivityNotifications 2.0 is full of new and improved tools for the theme and plugin developer. A few highlights:', 'ajency-activity-and-notifications' ) ?></p>
					<ul>
						<li><?php _e( 'The <code>AJAN_XProfile_Field_Type</code> class makes it a breeze to create new xProfile field types with custom display callbacks, validation, and more.', 'ajency-activity-and-notifications' ); ?></li>
						 <li><?php _e( 'Major improvements have taken place with respect to object caching throughout ActivityNotifications. If you use Memcached, APC, or some other persistent object caching backend on your ActivityNotifications site, you should notice huge performance boosts.', 'ajency-activity-and-notifications' ); ?></li>
						 <li><?php _e( 'Our internal metadata libraries have been rewritten to use WP&#8217;s <code>add_metadata()</code>, <code>update_metadata()</code>, and so on. This means greater consistency and parity between the components when storing and retrieving ActivityNotifications metadata.', 'ajency-activity-and-notifications' ); ?></li>
						 <li><?php printf( __( '<a href="%s">&hellip;and lots more!</a>', 'ajency-activity-and-notifications' ), 'http://codex.buddypress.org/releases/version-2-0' ); ?></li>
					</ul>
				</div>

				<hr />

				<?php if ( current_user_can( $this->capability ) ) :?>
					<div class="return-to-dashboard">
						<a href="<?php echo esc_url( ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-components' ), $this->settings_page ) ) ); ?>"><?php _e( 'Go to the ActivityNotifications Settings page', 'ajency-activity-and-notifications' ); ?></a>
					</div>
				<?php endif ;?>

			</div>

		<?php
	}

	/**
	 * Output the credits screen.
	 *
	 * Hardcoding this in here is pretty janky. It's fine for now, but we'll
	 * want to leverage api.wordpress.org eventually.
	 *
	 * @since ActivityNotifications (1.7.0)
	 */
	public function credits_screen() {

		$is_new_install = ! empty( $_GET['is_new_install'] );

		list( $display_version ) = explode( '-', ajan_get_version() ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to ActivityNotifications %s', 'ajency-activity-and-notifications' ), $display_version ); ?></h1>
			<div class="about-text">
				<?php if ( $is_new_install ) : ?>
					<?php printf( __( 'It&#8217;s a great time to use ActivityNotifications! With a focus on speed, admin tools, and developer enhancements, %s is our leanest and most powerful version yet.', 'ajency-activity-and-notifications' ), $display_version ); ?>
				<?php else : ?>
					<?php printf( __( 'Thanks for updating! With a focus on speed, admin tools, and developer enhancements, ActivityNotifications %s is our leanest and most powerful version yet.', 'ajency-activity-and-notifications' ), $display_version ); ?>
				<?php endif; ?>
			</div>

			<div class="ajan-badge"></div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-about' ), 'index.php' ) ) ); ?>" class="nav-tab">
					<?php _e( 'What&#8217;s New', 'ajency-activity-and-notifications' ); ?>
				</a><a href="<?php echo esc_url( ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-credits' ), 'index.php' ) ) ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Credits', 'ajency-activity-and-notifications' ); ?>
				</a>
			</h2>

			<p class="about-description"><?php _e( 'ActivityNotifications is created by a worldwide network of friendly folks.', 'ajency-activity-and-notifications' ); ?></p>

			<h4 class="wp-people-group"><?php _e( 'Project Leaders', 'ajency-activity-and-notifications' ); ?></h4>
			<ul class="wp-people-group " id="wp-people-group-project-leaders">
				<li class="wp-person" id="wp-person-johnjamesjacoby">
					<a href="http://profiles.wordpress.org/johnjamesjacoby"><img src="http://0.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f?s=60" class="gravatar" alt="John James Jacoby" /></a>
					<a class="web" href="http://profiles.wordpress.org/johnjamesjacoby">John James Jacoby</a>
					<span class="title"><?php _e( 'Project Lead', 'ajency-activity-and-notifications' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-boonebgorges">
					<a href="http://profiles.wordpress.org/boonebgorges"><img src="http://0.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c?s=60" class="gravatar" alt="Boone B. Gorges" /></a>
					<a class="web" href="http://profiles.wordpress.org/boonebgorges">Boone B. Gorges</a>
					<span class="title"><?php _e( 'Lead Developer', 'ajency-activity-and-notifications' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-djpaul">
					<a href="http://profiles.wordpress.org/djpaul"><img src="http://0.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df?s=60" class="gravatar" alt="Paul Gibbs" /></a>
					<a class="web" href="http://profiles.wordpress.org/djpaul">Paul Gibbs</a>
					<span class="title"><?php _e( 'Lead Developer', 'ajency-activity-and-notifications' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Core Team', 'ajency-activity-and-notifications' ); ?></h4>
			<ul class="wp-people-group " id="wp-people-group-core-team">
				<li class="wp-person" id="wp-person-r-a-y">
					<a href="http://profiles.wordpress.org/r-a-y"><img src="http://0.gravatar.com/avatar/3bfa556a62b5bfac1012b6ba5f42ebfa?s=60" class="gravatar" alt="Ray" /></a>
					<a class="web" href="http://profiles.wordpress.org/r-a-y">Ray</a>
					<span class="title"><?php _e( 'Core Developer', 'ajency-activity-and-notifications' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-imath">
					<a href="http://profiles.wordpress.org/imath"><img src="http://0.gravatar.com/avatar/8b208ca408dad63888253ee1800d6a03?s=60" class="gravatar" alt="Mathieu Viet" /></a>
					<a class="web" href="http://profiles.wordpress.org/imath">Mathieu Viet</a>
					<span class="title"><?php _e( 'Core Developer', 'ajency-activity-and-notifications' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-mercime">
					<a href="http://profiles.wordpress.org/mercime"><img src="http://0.gravatar.com/avatar/fae451be6708241627983570a1a1817a?s=60" class="gravatar" alt="Mercime" /></a>
					<a class="web" href="http://profiles.wordpress.org/mercime">Mercime</a>
					<span class="title"><?php _e( 'Navigator', 'ajency-activity-and-notifications' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Recent Rockstars', 'ajency-activity-and-notifications' ); ?></h4>
			<ul class="wp-people-group " id="wp-people-group-rockstars">
				<li class="wp-person" id="wp-person-dcavins">
					<a href="http://profiles.wordpress.org/dcavins"><img src="http://0.gravatar.com/avatar/a5fa7e83d59cb45ebb616235a176595a?s=60" class="gravatar" alt="David Cavins" /></a>
					<a class="web" href="http://profiles.wordpress.org/dcavins">David Cavins</a>
				</li>
				<li class="wp-person" id="wp-person-henry-wright">
					<a href="http://profiles.wordpress.org/henry.wright"><img src="http://0.gravatar.com/avatar/0da2f1a9340d6af196b870f6c107a248?s=60" class="gravatar" alt="Henry Wright" /></a>
					<a class="web" href="http://profiles.wordpress.org/henry.wright">Henry Wright</a>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php _e( 'Contributors to ActivityNotifications 2.0', 'ajency-activity-and-notifications' ); ?></h4>
			<p class="wp-credits-list">
				<a href="https://profiles.wordpress.org/boonebgorges/">boonebgorges</a>,
				<a href="https://profiles.wordpress.org/Bowromir/">Bowromir</a>,
				<a href="https://profiles.wordpress.org/burakali/">burakali</a>,
				<a href="https://profiles.wordpress.org/chouf1/">chouf1</a>,
				<a href="https://profiles.wordpress.org/cmmarslender/">cmmarslender</a>,
				<a href="https://profiles.wordpress.org/danbp/">danbp</a>,
				<a href="https://profiles.wordpress.org/dcavins/">dcavins</a>,
				<a href="https://profiles.wordpress.org/Denis-de-Bernardy/">Denis-de-Bernardy</a>,
				<a href="https://profiles.wordpress.org/DJPaul/">DJPaul</a>,
				<a href="https://profiles.wordpress.org/ericlewis/">ericlewis</a>,
				<a href="https://profiles.wordpress.org/glyndavidson/">glyndavidson</a>,
				<a href="https://profiles.wordpress.org/graham-washbrook/">graham-washbrook</a>,
				<a href="https://profiles.wordpress.org/henrywright/">henrywright</a>,
				<a href="https://profiles.wordpress.org/henry.wright/">henry.wright</a>,
				<a href="https://profiles.wordpress.org/hnla/">hnla</a>,
				<a href="https://profiles.wordpress.org/imath/">imath</a>,
				<a href="https://profiles.wordpress.org/johnjamesjacoby/">johnjamesjacoby</a>,
				<a href="https://profiles.wordpress.org/karmatosed/">karmatosed</a>,
				<a href="https://profiles.wordpress.org/lenasterg/">lenasterg</a>,
				<a href="https://profiles.wordpress.org/MacPresss/">MacPresss</a>,
				<a href="https://profiles.wordpress.org/markoheijnen/">markoheijnen</a>,
				<a href="https://profiles.wordpress.org/megainfo/">megainfo</a>,
				<a href="https://profiles.wordpress.org/modemlooper/">modemlooper</a>,
				<a href="https://profiles.wordpress.org/mpa4hu/">mpa4hu</a>,
				<a href="https://profiles.wordpress.org/needle/">needle</a>,
				<a href="https://profiles.wordpress.org/netweb/">netweb</a>,
				<a href="https://profiles.wordpress.org/ninnypants/">ninnypants</a>,
				Pietro Oliva,
				<a href="https://profiles.wordpress.org/pross/">pross</a>,
				<a href="https://profiles.wordpress.org/r-a-y/">r-a-y</a>,
				<a href="https://profiles.wordpress.org/reactuate/">reactuate</a>,
				<a href="https://profiles.wordpress.org/rodrigorznd/">rodrigorznd</a>,
				<a href="https://profiles.wordpress.org/rogercoathup/">rogercoathup</a>,
				<a href="https://profiles.wordpress.org/rzen/">rzen</a>,
				<a href="https://profiles.wordpress.org/SergeyBiryukov/">SergeyBiryukov</a>,
				<a href="https://profiles.wordpress.org/shanebp/">shanebp</a>,
				<a href="https://profiles.wordpress.org/SlothLoveChunk/">SlothLoveChunk</a>,
				<a href="https://profiles.wordpress.org/StijnDeWitt/">StijnDeWitt</a>,
				<a href="https://profiles.wordpress.org/terraling/">terraling</a>,
				<a href="https://profiles.wordpress.org/trishasalas/">trishasalas</a>,
				<a href="https://profiles.wordpress.org/tw2113/">tw2113</a>,
				<a href="https://profiles.wordpress.org/vanillalounge/">vanillalounge</a>.
			</p>

			<?php if ( current_user_can( $this->capability ) ) :?>
				<div class="return-to-dashboard">
					<a href="<?php echo esc_url( ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-components' ), $this->settings_page ) ) ); ?>"><?php _e( 'Go to the ActivityNotifications Settings page', 'ajency-activity-and-notifications' ); ?></a>
				</div>
			<?php endif;?>

		</div>

		<?php
	}
}
endif; // class_exists check

/**
 * Setup ActivityNotifications Admin.
 *
 * @since ActivityNotifications (1.6.0)
 *
 * @uses AJAN_Admin
 */
function ajan_admin() {
       activitynotifications()->admin = new AJAN_Admin();
}

 

