<?php

/**
 * ActivityNotifications Common Admin Functions
 *
 * @package ActivityNotifications
 * @subpackage CoreAdministration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Menu **********************************************************************/

/**
 * Initializes the wp-admin area "ActivityNotifications" menus and sub menus.
 *
 * @package ActivityNotifications Core
 * @uses ajan_current_user_can() returns true if the current user is a site admin, false if not
 */
function ajan_core_admin_menu_init() {
	add_action( ajan_core_admin_hook(), 'ajan_core_add_admin_menu', 9 );
}

/**
 * In BP 1.6, the top-level admin menu was removed. For backpat, this function
 * keeps the top-level menu if a plugin has registered a menu into the old
 * 'ajan-general-settings' menu.
 *
 * The old "ajan-general-settings" page was renamed "ajan-components".
 *
 * @global array $_parent_pages
 * @global array $_registered_pages
 * @global array $submenu
 * @since ActivityNotifications (1.6)
 */
function ajan_core_admin_backpat_menu() {
	global $_parent_pages, $_registered_pages, $submenu;

	// If there's no ajan-general-settings menu (perhaps because the current
	// user is not an Administrator), there's nothing to do here
	if ( ! isset( $submenu['ajan-general-settings'] ) ) {
		return;
	}

	/**
	 * By default, only the core "Help" submenu is added under the top-level ActivityNotifications menu.
	 * This means that if no third-party plugins have registered their admin pages into the
	 * 'ajan-general-settings' menu, it will only contain one item. Kill it.
	 */
	if ( 1 != count( $submenu['ajan-general-settings'] ) ) {
		return;
	}

	// This removes the top-level menu
	remove_submenu_page( 'ajan-general-settings', 'ajan-general-settings' );
	remove_menu_page( 'ajan-general-settings' );

	// These stop people accessing the URL directly
	unset( $_parent_pages['ajan-general-settings'] );
	unset( $_registered_pages['toplevel_page_ajan-general-settings'] );
}
add_action( ajan_core_admin_hook(), 'ajan_core_admin_backpat_menu', 999 );

/**
 * This tells WP to highlight the Settings > ActivityNotifications menu item,
 * regardless of which actual ActivityNotifications admin screen we are on.
 *
 * The conditional prevents the behaviour when the user is viewing the
 * backpat "Help" page, the Activity page, or any third-party plugins.
 *
 * @global string $plugin_page
 * @global array $submenu
 * @since ActivityNotifications (1.6)
 */
function ajan_core_modify_admin_menu_highlight() {
	global $pagenow, $plugin_page, $submenu_file;

	// This tweaks the Settings subnav menu to show only one ActivityNotifications menu item
	if ( ! in_array( $plugin_page, array( 'ajan-activity', 'ajan-general-settings', ) ) )
		$submenu_file = 'ajan-components';

	// Network Admin > Tools
	if ( in_array( $plugin_page, array( 'ajan-tools', 'available-tools' ) ) ) {
		$submenu_file = $plugin_page;
	}
}

/**
 * Generates markup for a fallback top-level ActivityNotifications menu page, if the site is running
 * a legacy plugin which hasn't been updated. If the site is up to date, this page
 * will never appear.
 *
 * @see ajan_core_admin_backpat_menu()
 * @since ActivityNotifications (1.6)
 * @todo Add convenience links into the markup once new positions are finalised.
 */
function ajan_core_admin_backpat_page() {
	$url          = ajan_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' );
	$settings_url = add_query_arg( 'page', 'ajan-components', $url ); ?>

	<div class="wrap">
		<?php screen_icon( 'ajency-activity-and-notifications' ); ?>
		<h2><?php _e( 'Why have all my ActivityNotifications menus disappeared?', 'ajency-activity-and-notifications' ); ?></h2>

		<p><?php _e( "Don't worry! We've moved the ActivityNotifications options into more convenient and easier to find locations. You're seeing this page because you are running a legacy ActivityNotifications plugin which has not been updated.", 'ajency-activity-and-notifications' ); ?></p>
		<p><?php printf( __( 'Components, Pages, Settings, and Forums, have been moved to <a href="%s">Settings &gt; ActivityNotifications</a>. Profile Fields has been moved into the <a href="%s">Users</a> menu.', 'ajency-activity-and-notifications' ), esc_url( $settings_url ), ajan_get_admin_url( 'users.php?page=ajan-profile-setup' ) ); ?></p>
	</div>

	<?php
}

/** Notices *******************************************************************/

/**
 * Print admin messages to admin_notices or network_admin_notices
 *
 * ActivityNotifications combines all its messages into a single notice, to avoid a preponderance of yellow
 * boxes.
 *
 * @package ActivityNotifications Core
 * @since ActivityNotifications (1.5)
 *
 * @uses ajan_current_user_can() to check current user permissions before showing the notices
 * @uses ajan_is_root_blog()
 */
function ajan_core_print_admin_notices() {

	// Only the super admin should see messages
	if ( ! ajan_current_user_can( 'ajan_moderate' ) ) {
		return;
	}

	// On multisite installs, don't show on a non-root blog, unless
	// 'do_network_admin' is overridden.
	if ( is_multisite() && ajan_core_do_network_admin() && ! ajan_is_root_blog() ) {
		return;
	}

	// Get the admin notices
	$admin_notices = activitynotifications()->admin->notices;

	// Show the messages
	if ( !empty( $admin_notices ) ) : ?>

		<div id="message" class="updated fade">

			<?php foreach ( $admin_notices as $notice ) : ?>

				<p><?php echo $notice; ?></p>

			<?php endforeach; ?>

		</div>

	<?php endif;
}
add_action( 'admin_notices',         'ajan_core_print_admin_notices' );
add_action( 'network_admin_notices', 'ajan_core_print_admin_notices' );

/**
 * Add an admin notice to the BP queue
 *
 * Messages added with this function are displayed in ActivityNotifications's general purpose admin notices
 * box. It is recommended that you hook this function to admin_init, so that your messages are
 * loaded in time.
 *
 * @package ActivityNotifications Core
 * @since ActivityNotifications (1.5)
 *
 * @param string $notice The notice you are adding to the queue
 */
function ajan_core_add_admin_notice( $notice = '' ) {

	// Do not add if the notice is empty
	if ( empty( $notice ) ) {
		return;
	}

	// Double check the object before referencing it
	if ( ! isset( activitynotifications()->admin->notices ) ) {
		activitynotifications()->admin->notices = array();
	}

	// Add the notice
	activitynotifications()->admin->notices[] = $notice;
}

/**
 * Verify that some BP prerequisites are set up properly, and notify the admin if not
 *
 * On every Dashboard page, this function checks the following:
 *   - that pretty permalinks are enabled
 *   - that every BP component that needs a WP page for a directory has one
 *   - that no WP page has multiple BP components associated with it
 * The administrator will be shown a notice for each check that fails.
 *
 * @global WPDB $wpdb WordPress DB object
 * @global WP_Rewrite $wp_rewrite
 * @since ActivityNotifications (1.2)
 */
function ajan_core_activation_notice() {
	global $wpdb, $wp_rewrite;

	$ajan = activitynotifications();

	// Only the super admin gets warnings
	if ( ! ajan_current_user_can( 'ajan_moderate' ) ) {
		return;
	}

	// On multisite installs, don't load on a non-root blog, unless do_network_admin is overridden
	if ( is_multisite() && ajan_core_do_network_admin() && !ajan_is_root_blog() ) {
		return;
	}

	// Bail if in network admin, and ActivityNotifications is not network activated
	if ( is_network_admin() && ! ajan_is_network_activated() ) {
		return;
	}

	// Bail in network admin
	if ( is_user_admin() ) {
		return;
	}

	/**
	 * Check to make sure that the blog setup routine has run. This can't happen during the
	 * wizard because of the order which the components are loaded. We check for multisite here
	 * on the off chance that someone has activated the blogs component and then disabled MS
	 */
	if ( ajan_is_active( 'blogs' ) ) {
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$ajan->blogs->table_name}" );

		if ( empty( $count ) ) {
			ajan_blogs_record_existing_blogs();
		}
	}

	/**
	 * Are pretty permalinks enabled?
	 */
	if ( isset( $_POST['permalink_structure'] ) ) {
		return;
	}

	if ( empty( $wp_rewrite->permalink_structure ) ) {
		ajan_core_add_admin_notice( sprintf( __( '<strong>ActivityNotifications is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'ajency-activity-and-notifications' ), admin_url( 'options-permalink.php' ) ) );
	}

	/**
	 * Check for orphaned BP components (BP component is enabled, no WP page exists)
	 */
	$orphaned_components = array();
	$wp_page_components  = array();

	// Only components with 'has_directory' require a WP page to function
	foreach( array_keys( $ajan->loaded_components ) as $component_id ) {
		if ( !empty( $ajan->{$component_id}->has_directory ) ) {
			$wp_page_components[] = array(
				'id'   => $component_id,
				'name' => isset( $ajan->{$component_id}->name ) ? $ajan->{$component_id}->name : ucwords( $ajan->{$component_id}->id )
			);
		}
	}
 

	// On the first admin screen after a new installation, this isn't set, so grab it to supress a misleading error message.
	if ( empty( $ajan->pages->members ) ) {
		$ajan->pages = ajan_core_get_directory_pages();
	}

	foreach( $wp_page_components as $component ) {
		if ( !isset( $ajan->pages->{$component['id']} ) ) {
			$orphaned_components[] = $component['name'];
		}
	}

	// Special case: If the Forums component is orphaned, but the bbPress 1.x installation is
	// not correctly set up, don't show a nag. (In these cases, it's probably the case that the
	// user is using bbPress 2.x; see https://buddypress.trac.wordpress.org/ticket/4292
	if ( isset( $ajan->forums->name ) && in_array( $ajan->forums->name, $orphaned_components ) && !ajan_forums_is_installed_correctly() ) {
		$forum_key = array_search( $ajan->forums->name, $orphaned_components );
		unset( $orphaned_components[$forum_key] );
		$orphaned_components = array_values( $orphaned_components );
	}

	if ( !empty( $orphaned_components ) ) {
		$admin_url = ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-page-settings' ), 'admin.php' ) );
		$notice    = sprintf( __( 'The following active ActivityNotifications Components do not have associated WordPress Pages: %2$s. <a href="%1$s">Repair</a>', 'ajency-activity-and-notifications' ), $admin_url, '<strong>' . implode( '</strong>, <strong>', $orphaned_components ) . '</strong>' );

		ajan_core_add_admin_notice( $notice );
	}

	// BP components cannot share a single WP page. Check for duplicate assignments, and post a message if found.
	$dupe_names = array();
	$page_ids   = (array)ajan_core_get_directory_page_ids();
	$dupes      = array_diff_assoc( $page_ids, array_unique( $page_ids ) );

	if ( !empty( $dupes ) ) {
		foreach( array_keys( $dupes ) as $dupe_component ) {
			$dupe_names[] = $ajan->pages->{$dupe_component}->title;
		}

		// Make sure that there are no duplicate duplicates :)
		$dupe_names = array_unique( $dupe_names );
	}

	// If there are duplicates, post a message about them
	if ( !empty( $dupe_names ) ) {
		$admin_url = ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-page-settings' ), 'admin.php' ) );
		$notice    = sprintf( __( 'Each ActivityNotifications Component needs its own WordPress page. The following WordPress Pages have more than one component associated with them: %2$s. <a href="%1$s">Repair</a>', 'ajency-activity-and-notifications' ), $admin_url, '<strong>' . implode( '</strong>, <strong>', $dupe_names ) . '</strong>' );

		ajan_core_add_admin_notice( $notice );
	}
}

/**
 * Redirect user to ActivityNotifications's What's New page on activation
 *
 * @since ActivityNotifications (1.7)
 *
 * @internal Used internally to redirect ActivityNotifications to the about page on activation
 *
 * @uses get_transient() To see if transient to redirect exists
 * @uses delete_transient() To delete the transient if it exists
 * @uses is_network_admin() To bail if being network activated
 * @uses wp_safe_redirect() To redirect
 * @uses add_query_arg() To help build the URL to redirect to
 * @uses admin_url() To get the admin URL to index.php
 */
function ajan_do_activation_redirect() {

	// Bail if no activation redirect
	if ( ! get_transient( '_ajan_activation_redirect' ) )
		return;

	// Delete the redirect transient
	delete_transient( '_ajan_activation_redirect' );

	// Bail if activating from network, or bulk
	if ( isset( $_GET['activate-multi'] ) )
		return;

	/*$query_args = array( 'page' => 'ajan-about' );
	if ( get_transient( '_ajan_is_new_install' ) ) {
		$query_args['is_new_install'] = '1';
		delete_transient( '_ajan_is_new_install' );
	}*/

	// Redirect to ActivityNotifications about page
	wp_safe_redirect( add_query_arg( $query_args, ajan_get_admin_url( 'index.php' ) ) );
}

/** UI/Styling ****************************************************************/

/**
 * Output the tabs in the admin area
 *
 * @since ActivityNotifications (1.5)
 * @param string $active_tab Name of the tab that is active
 */
function ajan_core_admin_tabs( $active_tab = '' ) {

	// Declare local variables
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';

	// Setup core admin tabs
	$tabs = array(
		'0' => array(
			'href' => ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-components' ), 'admin.php' ) ),
			'name' => __( 'Components', 'ajency-activity-and-notifications' )
		),
		'1' => array(
			'href' => ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-page-settings' ), 'admin.php' ) ),
			'name' => __( 'Pages', 'ajency-activity-and-notifications' )
		),
		'2' => array(
			'href' => ajan_get_admin_url( add_query_arg( array( 'page' => 'ajan-settings' ), 'admin.php' ) ),
			'name' => __( 'Settings', 'ajency-activity-and-notifications' )
		),
	);

	// If forums component is active, add additional tab
	if ( ajan_is_active( 'forums' ) && class_exists( 'AJAN_Forums_Component' ) ) {

		// enqueue thickbox
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		$tabs['3'] = array(
			'href' => ajan_get_admin_url( add_query_arg( array( 'page' => 'bb-forums-setup'  ), 'admin.php' ) ),
			'name' => __( 'Forums', 'ajency-activity-and-notifications' )
		);
	}

	// Allow the tabs to be filtered
	$tabs = apply_filters( 'ajan_core_admin_tabs', $tabs );

	// Loop through tabs and build navigation
	foreach ( array_values( $tabs ) as $tab_data ) {
		$is_current = (bool) ( $tab_data['name'] == $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
	}

	// Output the tabs
	echo $tabs_html;

	// Do other fun things
	do_action( 'ajan_admin_tabs' );
}

/** Help **********************************************************************/

/**
 * adds contextual help to ActivityNotifications admin pages
 *
 * @since ActivityNotifications (1.7)
 * @todo Make this part of the AJAN_Component class and split into each component
 */
function ajan_core_add_contextual_help( $screen = '' ) {

	$screen = get_current_screen();

	switch ( $screen->id ) {

		// Compontent page
		case 'settings_page_ajan-components' :

			// help tabs
			$screen->add_help_tab( array(
				'id'      => 'ajan-comp-overview',
				'title'   => __( 'Overview', 'ajency-activity-and-notifications' ),
				'content' => ajan_core_add_contextual_help_content( 'ajan-comp-overview' ),
			) );

			// help panel - sidebar links
			$screen->set_help_sidebar(
				'<p><strong>' . __( 'For more information:', 'ajency-activity-and-notifications' ) . '</strong></p>' .
				'<p>' . __( '<a href="http://codex.buddypress.org/getting-started/configure-buddypress-components/#settings-buddypress-components">Managing Components</a>', 'ajency-activity-and-notifications' ) . '</p>' .
				'<p>' . __( '<a href="http://buddypress.org/support/">Support Forums</a>', 'ajency-activity-and-notifications' ) . '</p>'
			);
			break;

		// Pages page
		case 'settings_page_ajan-page-settings' :

			// Help tabs
			$screen->add_help_tab( array(
				'id' => 'ajan-page-overview',
				'title' => __( 'Overview', 'ajency-activity-and-notifications' ),
				'content' => ajan_core_add_contextual_help_content( 'ajan-page-overview' ),
			) );

			// Help panel - sidebar links
			$screen->set_help_sidebar(
				'<p><strong>' . __( 'For more information:', 'ajency-activity-and-notifications' ) . '</strong></p>' .
				'<p>' . __( '<a href="http://codex.buddypress.org/getting-started/configure-buddypress-components/#settings-buddypress-pages">Managing Pages</a>', 'ajency-activity-and-notifications' ) . '</p>' .
				'<p>' . __( '<a href="http://buddypress.org/support/">Support Forums</a>', 'ajency-activity-and-notifications' ) . '</p>'
			);

			break;

		// Settings page
		case 'settings_page_ajan-settings' :

			// Help tabs
			$screen->add_help_tab( array(
				'id'      => 'ajan-settings-overview',
				'title'   => __( 'Overview', 'ajency-activity-and-notifications' ),
				'content' => ajan_core_add_contextual_help_content( 'ajan-settings-overview' ),
			) );

			// Help panel - sidebar links
			$screen->set_help_sidebar(
				'<p><strong>' . __( 'For more information:', 'ajency-activity-and-notifications' ) . '</strong></p>' .
				'<p>' . __( '<a href="http://codex.buddypress.org/getting-started/configure-buddypress-components/#settings-buddypress-settings">Managing Settings</a>', 'ajency-activity-and-notifications' ) . '</p>' .
				'<p>' . __( '<a href="http://buddypress.org/support/">Support Forums</a>', 'ajency-activity-and-notifications' ) . '</p>'
			);

			break;

		// Profile fields page
		case 'users_page_ajan-profile-setup' :

			// Help tabs
			$screen->add_help_tab( array(
				'id'      => 'ajan-profile-overview',
				'title'   => __( 'Overview', 'ajency-activity-and-notifications' ),
				'content' => ajan_core_add_contextual_help_content( 'ajan-profile-overview' ),
			) );

			// Help panel - sidebar links
			$screen->set_help_sidebar(
				'<p><strong>' . __( 'For more information:', 'ajency-activity-and-notifications' ) . '</strong></p>' .
				'<p>' . __( '<a href="http://codex.buddypress.org/getting-started/configure-buddypress-components/#users-profile-fields">Managing Profile Fields</a>', 'ajency-activity-and-notifications' ) . '</p>' .
				'<p>' . __( '<a href="http://buddypress.org/support/">Support Forums</a>', 'ajency-activity-and-notifications' ) . '</p>'
			);

			break;
	}
}
add_action( 'contextual_help', 'ajan_core_add_contextual_help' );

/**
 * renders contextual help content to contextual help tabs
 *
 * @since ActivityNotifications (1.7)
 */
function ajan_core_add_contextual_help_content( $tab = '' ) {

	switch ( $tab ) {
		case 'ajan-comp-overview' :
			$retval = __( 'By default, all ActivityNotifications components are enabled. You can selectively disable any of the components by using the form. Your ActivityNotifications installation will continue to function. However, the features of the disabled components will no longer be accessible to anyone using the site.', 'ajency-activity-and-notifications' );
			break;

		case 'ajan-page-overview' :
			$retval = __( 'ActivityNotifications Components use WordPress Pages for their root directory/archive pages. Here you can change the page associations for each active component.', 'ajency-activity-and-notifications' );
			break;

		case 'ajan-settings-overview' :
			$retval = __( 'Extra configuration settings.', 'ajency-activity-and-notifications' );
			break;

		case 'ajan-profile-overview' :
			$retval = __( 'Your users will distinguish themselves through their profile page. Create relevant profile fields that will show on each users profile.</br></br>Note: Any fields in the first group will appear on the signup page.', 'ajency-activity-and-notifications' );
			break;

		default:
			$retval = false;
			break;
	}

	// Wrap text in a paragraph tag
	if ( !empty( $retval ) ) {
		$retval = '<p>' . $retval . '</p>';
	}

	return $retval;
}

/** Separator *****************************************************************/

/**
 * Add a separator to the WordPress admin menus
 *
 * @since ActivityNotifications (1.7)
 *
 * @uses ajan_current_user_can() To check users capability on root blog
 */
function ajan_admin_separator() {

	// Bail if ActivityNotifications is not network activated and viewing network admin
	if ( is_network_admin() && ! ajan_is_network_activated() )
		return;

	// Bail if ActivityNotifications is network activated and viewing site admin
	if ( ! is_network_admin() && ajan_is_network_activated() )
		return;

	// Prevent duplicate separators when no core menu items exist
	if ( ! ajan_current_user_can( 'ajan_moderate' ) )
		return;

	// Bail if there are no components with admin UI's. Hardcoded for now, until
	// there's a real API for determining this later.
	if ( ! ajan_is_active( 'activity' ) && ! ajan_is_active( 'groups' ) )
		return;

	global $menu;

	$menu[] = array( '', 'read', 'separator-buddypress', '', 'wp-menu-separator buddypress' );
}

/**
 * Tell WordPress we have a custom menu order
 *
 * @since ActivityNotifications (1.7)
 *
 * @param bool $menu_order Menu order
 * @uses ajan_current_user_can() To check users capability on root blog
 * @return bool Always true
 */
function ajan_admin_custom_menu_order( $menu_order = false ) {

	// Bail if user cannot see admin pages
	if ( ! ajan_current_user_can( 'ajan_moderate' ) )
		return $menu_order;

	return true;
}

/**
 * Move our custom separator above our custom post types
 *
 * @since ActivityNotifications (1.7)
 *
 * @param array $menu_order Menu Order
 * @uses ajan_current_user_can() To check users capability on root blog
 * @return array Modified menu order
 */
function ajan_admin_menu_order( $menu_order = array() ) {

	// Bail if user cannot see admin pages
	if ( empty( $menu_order ) || ! ajan_current_user_can( 'ajan_moderate' ) )
		return $menu_order;

	// Initialize our custom order array
	$ajan_menu_order = array();

	// Menu values
	$last_sep     = is_network_admin() ? 'separator1' : 'separator2';

	// Filter the custom admin menus
	$custom_menus = (array) apply_filters( 'ajan_admin_menu_order', array() );

	// Bail if no components have top level admin pages
	if ( empty( $custom_menus ) )
		return $menu_order;

	// Add our separator to beginning of array
	array_unshift( $custom_menus, 'separator-buddypress' );

	// Loop through menu order and do some rearranging
	foreach ( (array) $menu_order as $item ) {

		// Position ActivityNotifications menus above appearance
		if ( $last_sep == $item ) {

			// Add our custom menus
			foreach( (array) $custom_menus as $custom_menu ) {
				if ( array_search( $custom_menu, $menu_order ) ) {
					$ajan_menu_order[] = $custom_menu;
				}
			}

			// Add the appearance separator
			$ajan_menu_order[] = $last_sep;

		// Skip our menu items
		} elseif ( ! in_array( $item, $custom_menus ) ) {
			$ajan_menu_order[] = $item;
		}
	}

	// Return our custom order
	return $ajan_menu_order;
}

/** Utility  *****************************************************************/

/**
 * When using a WP_List_Table, get the currently selected bulk action
 *
 * WP_List_Tables have bulk actions at the top and at the bottom of the tables,
 * and the inputs have different keys in the $_REQUEST array. This function
 * reconciles the two values and returns a single action being performed.
 *
 * @since ActivityNotifications (1.7)
 * @return string
 */
function ajan_admin_list_table_current_bulk_action() {

	$action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	// If the bottom is set, let it override the action
	if ( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] != "-1" ) {
		$action = $_REQUEST['action2'];
	}

	return $action;
}

/** Menus *********************************************************************/

/**
 * Register meta box and associated JS for ActivityNotifications WP Nav Menu .
 *
 * @since ActivityNotifications (1.9.0)
 */
function ajan_admin_wp_nav_menu_meta_box() {
	if ( ! ajan_is_root_blog() ) {
		return;
	}

	add_meta_box( 'add-buddypress-nav-menu', __( 'ActivityNotifications', 'ajency-activity-and-notifications' ), 'ajan_admin_do_wp_nav_menu_meta_box', 'nav-menus', 'side', 'default' );

	add_action( 'admin_print_footer_scripts', 'ajan_admin_wp_nav_menu_restrict_items' );
}

/**
 * Build and populate the ActivityNotifications accordion on Appearance > Menus.
 *
 * @since ActivityNotifications (1.9.0)
 *
 * @global $nav_menu_selected_id
 */
function ajan_admin_do_wp_nav_menu_meta_box() {
	global $nav_menu_selected_id;

	$walker = new AJAN_Walker_Nav_Menu_Checklist( false );
	$args   = array( 'walker' => $walker );

	$post_type_name = 'ajency-activity-and-notifications';

	$tabs = array();

	$tabs['loggedin']['label']  = __( 'Logged-In', 'ajency-activity-and-notifications' );
	$tabs['loggedin']['pages']  = ajan_nav_menu_get_loggedin_pages();

	$tabs['loggedout']['label'] = __( 'Logged-Out', 'ajency-activity-and-notifications' );
	$tabs['loggedout']['pages'] = ajan_nav_menu_get_loggedout_pages();

	?>

	<div id="buddypress-menu" class="posttypediv">
		<h4><?php _e( 'Logged-In', 'ajency-activity-and-notifications' ) ?></h4>
		<p><?php _e( '<em>Logged-In</em> links are relative to the current user, and are not visible to visitors who are not logged in.', 'ajency-activity-and-notifications' ) ?></p>

		<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedin" class="tabs-panel tabs-panel-active">
			<ul id="buddypress-menu-checklist-loggedin" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedin']['pages'] ), 0, (object) $args );?>
			</ul>
		</div>

		<h4><?php _e( 'Logged-Out', 'ajency-activity-and-notifications' ) ?></h4>
		<p><?php _e( '<em>Logged-Out</em> links are not visible to users who are logged in.', 'ajency-activity-and-notifications' ) ?></p>

		<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedout" class="tabs-panel tabs-panel-active">
			<ul id="buddypress-menu-checklist-loggedout" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedout']['pages'] ), 0, (object) $args );?>
			</ul>
		</div>

		<p class="button-controls">
			<span class="add-to-menu">
				<input type="submit"<?php if ( function_exists( 'wp_nav_menu_disabled_check' ) ) : wp_nav_menu_disabled_check( $nav_menu_selected_id ); endif; ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'ajency-activity-and-notifications' ); ?>" name="add-custom-menu-item" id="submit-buddypress-menu" />
				<span class="spinner"></span>
			</span>
		</p>
	</div><!-- /#buddypress-menu -->

	<?php
}

/**
 * Restrict various items from view if editing a ActivityNotifications menu.
 *
 * If a person is editing a BP menu item, that person should not be able to
 * see or edit the following fields:
 *
 * - CSS Classes - We use the 'ajan-menu' CSS class to determine if the
 *   menu item belongs to BP, so we cannot allow manipulation of this field to
 *   occur.
 * - URL - This field is automatically generated by BP on output, so this
 *   field is useless and can cause confusion.
 *
 * Note: These restrictions are only enforced if javascript is enabled.
 *
 * @since ActivityNotifications (1.9.0)
 */
function ajan_admin_wp_nav_menu_restrict_items() {
?>
	<script type="text/javascript">
	jQuery( '#menu-to-edit').on( 'click', 'a.item-edit', function() {
		var settings  = jQuery(this).closest( '.menu-item-bar' ).next( '.menu-item-settings' );
		var css_class = settings.find( '.edit-menu-item-classes' );

		if( css_class.val().indexOf( 'ajan-menu' ) === 0 ) {
			css_class.attr( 'readonly', 'readonly' );
			settings.find( '.field-url' ).css( 'display', 'none' );
		}
	});
	</script>
<?php
}

/**
 * Add "Mark as Spam/Ham" button to user row actions.
 *
 * @since ActivityNotifications (2.0.0)
 *
 * @param array $actions User row action links.
 * @param object $user_object Current user information.
 * @return array $actions User row action links.
 */
function ajan_core_admin_user_row_actions( $actions, $user_object ) {

	if ( current_user_can( 'edit_user', $user_object->ID ) && ajan_loggedin_user_id() != $user_object->ID ) {

		$url = ajan_get_admin_url( 'users.php' );

		if ( ajan_is_user_spammer( $user_object->ID ) ) {
			$actions['ham'] = "<a href='" . wp_nonce_url( $url . "?action=ham&amp;user=$user_object->ID", 'ajan-spam-user' ) . "'>" . __( 'Not Spam', 'ajency-activity-and-notifications' ) . "</a>";
		} else {
			$actions['spam'] = "<a class='submitdelete' href='" . wp_nonce_url( $url . "?action=spam&amp;user=$user_object->ID", 'ajan-spam-user' ) . "'>" . __( 'Mark as Spam', 'ajency-activity-and-notifications' ) . "</a>";
		}
	}

	return $actions;
}

/**
 * Catch requests to mark individual users as spam/ham from users.php.
 *
 * @since ActivityNotifications (2.0.0)
 */
function ajan_core_admin_user_manage_spammers() {

	// Print our inline scripts on non-Multisite
	add_action( 'admin_footer', 'ajan_core_admin_user_spammed_js' );

	$action  = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;
	$updated = isset( $_REQUEST['updated'] ) ? $_REQUEST['updated'] : false;
	$mode    = isset( $_POST['mode'] ) ? $_POST['mode'] : false;

	// if this is a multisite, bulk request, stop now!
	if ( 'list' == $mode ) {
		return;
	}

	// Process a spam/ham request
	if ( ! empty( $action ) && in_array( $action, array( 'spam', 'ham' ) ) ) {

		check_admin_referer( 'ajan-spam-user' );

		$user_id = ! empty( $_REQUEST['user'] ) ? intval( $_REQUEST['user'] ) : false;

		if ( empty( $user_id ) ) {
			return;
		}

		$redirect = wp_get_referer();

		$status = ( $action == 'spam' ) ? 'spam' : 'ham';

		// Process the user
		ajan_core_process_spammer_status( $user_id, $status );

		$redirect = add_query_arg( array( 'updated' => 'marked-' . $status ), $redirect);

		wp_redirect( $redirect );
	}

	// Display feedback
	if ( ! empty( $updated ) && in_array( $updated, array( 'marked-spam', 'marked-ham' ) ) ) {

		if ( 'marked-spam' === $updated ) {
			$notice = __( 'User marked as spammer. Spam users are visible only to site admins.', 'ajency-activity-and-notifications' );
		} else {
			$notice = __( 'User removed from spam.', 'ajency-activity-and-notifications' );
		}

		ajan_core_add_admin_notice( $notice );
	}
}

/**
 * Inline script that adds the 'site-spammed' class to spammed users.
 *
 * @since ActivityNotifications (2.0.0)
 */
function ajan_core_admin_user_spammed_js() {
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function($) {
			$( '.row-actions .ham' ).each( function() {
				$( this ).closest( 'tr' ).addClass( 'site-spammed' );
			});
		});
	</script>
	<?php
}
