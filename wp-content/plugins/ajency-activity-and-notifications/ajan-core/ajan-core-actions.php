<?php

/**
 * ActivityNotifications Filters & Actions.
 *
 * @package ActivityNotifications
 * @subpackage Hooks
 *
 * This file contains the actions and filters that are used through-out ActivityNotifications.
 * They are consolidated here to make searching for them easier, and to help
 * developers understand at a glance the order in which things occur.
 *
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
add_action( 'plugins_loaded',          'ajan_loaded',                 10    );
add_action( 'init',                    'ajan_init',                   10    );
add_action( 'parse_query',             'ajan_parse_query',            2     ); // Early for overrides
add_action( 'wp',                      'ajan_ready',                  10    );
add_action( 'set_current_user',        'ajan_setup_current_user',     10    );
add_action( 'setup_theme',             'ajan_setup_theme',            10    );
add_action( 'after_setup_theme',       'ajan_after_setup_theme',      100   ); // After WP themes
add_action( 'wp_enqueue_scripts',      'ajan_enqueue_scripts',        10    );
add_action( 'admin_bar_menu',          'ajan_setup_admin_bar',        20    ); // After WP core
add_action( 'template_redirect',       'ajan_template_redirect',      10    );
add_action( 'widgets_init',            'ajan_widgets_init',           10    );
add_action( 'generate_rewrite_rules',  'ajan_generate_rewrite_rules', 10    );

/**
 * ajan_loaded - Attached to 'plugins_loaded' above
 *
 * Attach various loader actions to the ajan_loaded action.
 * The load order helps to execute code at the correct time.
 *                                                      v---Load order
 */
add_action( 'ajan_loaded', 'ajan_setup_components',         2  );
add_action( 'ajan_loaded', 'ajan_include',                  4  );
add_action( 'ajan_loaded', 'ajan_setup_widgets',            6  );
add_action( 'ajan_loaded', 'ajan_register_theme_packages',  12 );
add_action( 'ajan_loaded', 'ajan_register_theme_directory', 14 );

/**
 * ajan_init - Attached to 'init' above
 *
 * Attach various initialization actions to the ajan_init action.
 * The load order helps to execute code at the correct time.
 *                                                   v---Load order
 */
add_action( 'ajan_init', 'ajan_core_set_uri_globals',    2  );
add_action( 'ajan_init', 'ajan_setup_globals',           4  );
add_action( 'ajan_init', 'ajan_setup_nav',               6  );
add_action( 'ajan_init', 'ajan_setup_title',             8  );
add_action( 'ajan_init', 'ajan_core_load_admin_bar_css', 12 );
add_action( 'ajan_init', 'ajan_add_rewrite_tags',        20 );
add_action( 'ajan_init', 'ajan_add_rewrite_rules',       30 );
add_action( 'ajan_init', 'ajan_add_permastructs',        40 );

/**
 * ajan_template_redirect - Attached to 'template_redirect' above
 *
 * Attach various template actions to the ajan_template_redirect action.
 * The load order helps to execute code at the correct time.
 *
 * Note that we currently use template_redirect versus template include because
 * ActivityNotifications is a bully and overrides the existing themes output in many
 * places. This won't always be this way, we promise.
 *                                                           v---Load order
 */
add_action( 'ajan_template_redirect', 'ajan_redirect_canonical', 2  );
add_action( 'ajan_template_redirect', 'ajan_actions',            4  );
add_action( 'ajan_template_redirect', 'ajan_screens',            6  );
add_action( 'ajan_template_redirect', 'ajan_post_request',       10 );
add_action( 'ajan_template_redirect', 'ajan_get_request',        10 );

/**
 * Add the ActivityNotifications functions file
 */
add_action( 'ajan_after_setup_theme', 'ajan_load_theme_functions', 1 );

// Load the admin
if ( is_admin() ) {
	add_action( 'ajan_loaded', 'ajan_admin' );
}

// Activation redirect
add_action( 'ajan_activation', 'ajan_add_activation_redirect' );
