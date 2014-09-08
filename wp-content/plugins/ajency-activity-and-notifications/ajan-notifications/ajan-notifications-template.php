<?php

/**
 * BuddyPress Notifications Template Functions
 *
 * @package BuddyPress
 * @subpackage TonificationsTemplate
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the notifications component slug.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_slug() {
	echo ajan_get_notifications_slug();
}
	/**
	 * Return the notifications component slug.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string Slug of the Notifications component.
	 */
	function ajan_get_notifications_slug() {
		return apply_filters( 'ajan_get_notifications_slug', activitynotifications()->notifications->slug );
	}

/**
 * Output the notifications permalink.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_permalink() {
	echo ajan_get_notifications_permalink();
}
	/**
	 * Return the notifications permalink.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string Notifications permalink
	 */
	function ajan_get_notifications_permalink() {
		$retval = trailingslashit( ajan_loggedin_user_domain() . ajan_get_notifications_slug() );
		return apply_filters( 'ajan_get_notifications_permalink', $retval );
	}

/**
 * Output the unread notifications permalink.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_unread_permalink() {
	echo ajan_get_notifications_unread_permalink();
}
	/**
	 * Return the unread notifications permalink.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string Unread notifications permalink
	 */
	function ajan_get_notifications_unread_permalink() {
		$retval = trailingslashit( ajan_loggedin_user_domain() . ajan_get_notifications_slug() . '/unread' );
		return apply_filters( 'ajan_get_notifications_unread_permalink', $retval );
	}

/**
 * Output the read notifications permalink.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_read_permalink() {
	echo ajan_get_notifications_read_permalink();
}
	/**
	 * Return the read notifications permalink.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string Read notifications permalink
	 */
	function ajan_get_notifications_read_permalink() {
		$retval = trailingslashit( ajan_loggedin_user_domain() . ajan_get_notifications_slug() . '/read' );
		return apply_filters( 'ajan_get_notifications_unread_permalink', $retval );
	}

/** Main Loop *****************************************************************/

/**
 * The main notifications template loop class.
 *
 * Responsible for loading a group of notifications into a loop for display.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
class AJAN_Notifications_Template {

	/**
	 * The loop iterator.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var int
	 */
	public $current_notification = -1;

	/**
	 * The number of notifications returned by the paged query.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var int
	 */
	public $current_notification_count;

	/**
	 * Total number of notifications matching the query.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var int
	 */
	public $total_notification_count;

	/**
	 * Array of notifications located by the query.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var array
	 */
	public $notifications;

	/**
	 * The notification object currently being iterated on.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var object
	 */
	public $notification;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var bool
	 */
	public $in_the_loop;

	/**
	 * The ID of the user to whom the displayed notifications belong.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var int
	 */
	public $user_id;

	/**
	 * The page number being requested.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var int
	 */
	public $pag_page;

	/**
	 * The number of items to display per page of results.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var int
	 */
	public $pag_num;

	/**
	 * An HTML string containing pagination links.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var string
	 */
	public $pag_links;

	/**
	 * A string to match against.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var string
	 */
	public $search_terms;

	/**
	 * A database column to order the results by.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var string
	 */
	public $order_by;

	/**
	 * The direction to sort the results (ASC or DESC)
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 * @access public
	 * @var string
	 */
	public $sort_order;

	/**
	 * Constructor method.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @param array $args {
	 *     @type int $user_id ID of the user to whom the displayed
	 *           notifications belong.
	 *     @type bool $is_new Whether to limit the query to unread
	 *           notifications. Default: true.
	 *     @type int $page Number of the page of results to return.
	 *           Will be overridden by URL parameter. Default: 1.
	 *     @type int $per_page Number of results to return per page.
	 *           Will be overridden by URL parameter. Default: 25.
	 *     @type int $max Optional. Max results to display.
	 *     @type string $search_terms Optional. Term to match against
	 *           component_name and component_action.
	 *     @type string $page_arg URL argument to use for pagination.
	 *           Default: 'npage'.
	 * }
	 */
	public function __construct( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'user_id'      => 0,
			'is_new'       => true,
			'page'         => 1,
			'per_page'     => 25,
			'order_by'     => 'date_notified',
			'sort_order'   => 'DESC',
			'max'          => null,
			'search_terms' => '',
			'page_arg'     => 'npage',
		) );

		// Overrides

		// Set which pagination page
		if ( isset( $_GET[ $r['page_arg'] ] ) ) {
			$pag_page = intval( $_GET[ $r['page_arg'] ] );
		} else {
			$pag_page = $r['page'];
		}

		// Set the number to show per page
		if ( isset( $_GET['num'] ) ) {
			$pag_num = intval( $_GET['num'] );
		} else {
			$pag_num = intval( $r['per_page'] );
		}

		// Sort order direction
		$orders = array( 'ASC', 'DESC' );
		if ( ! empty( $_GET['sort_order'] ) && in_array( $_GET['sort_order'], $orders ) ) {
			$sort_order = $_GET['sort_order'];
		} else {
			$sort_order = in_array( $r['sort_order'], $orders ) ? $r['sort_order'] : 'DESC';
		}

		// Setup variables
		$this->pag_page     = $pag_page;
		$this->pag_num      = $pag_num;
		$this->user_id      = $r['user_id'];
		$this->is_new       = $r['is_new'];
		$this->search_terms = $r['search_terms'];
		$this->page_arg     = $r['page_arg'];
		$this->order_by     = $r['order_by'];
		$this->sort_order   = $sort_order;

		// Get the notifications
		$notifications      = AJAN_Notifications_Notification::get_current_notifications_for_user( array(
			'user_id'      => $this->user_id,
			'is_new'       => $this->is_new,
			'page'         => $this->pag_page,
			'per_page'     => $this->pag_num,
			'search_terms' => $this->search_terms,
			'order_by'     => $this->order_by,
			'sort_order'   => $this->sort_order,
		) );

		// Setup the notifications to loop through
		$this->notifications            = $notifications['notifications'];
		$this->total_notification_count = $notifications['total'];

		if ( empty( $this->notifications ) ) {
			$this->notification_count       = 0;
			$this->total_notification_count = 0;

		} else {
			if ( ! empty( $max ) ) {
				if ( $max >= count( $this->notifications ) ) {
					$this->notification_count = count( $this->notifications );
				} else {
					$this->notification_count = (int) $max;
				}
			} else {
				$this->notification_count = count( $this->notifications );
			}
		}

		if ( (int) $this->total_notification_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( $this->page_arg, '%#%' ),
				'format'    => '',
				'total'     => ceil( (int) $this->total_notification_count / (int) $this->pag_num ),
				'current'   => $this->pag_page,
				'prev_text' => _x( '&larr;', 'Notifications pagination previous text', 'ajency-activity-and-notifications' ),
				'next_text' => _x( '&rarr;', 'Notifications pagination next text',     'ajency-activity-and-notifications' ),
				'mid_size'  => 1,
			) );

			// Remove first page from pagination
			$this->pag_links = str_replace( '?'      . $r['page_arg'] . '=1', '', $this->pag_links );
			$this->pag_links = str_replace( '&#038;' . $r['page_arg'] . '=1', '', $this->pag_links );
		}
	}

	/**
	 * Whether there are notifications available in the loop.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see ajan_has_notifications()
	 *
	 * @return bool True if there are items in the loop, otherwise false.
	 */
	public function has_notifications() {
		if ( $this->notification_count ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next notification and iterate index.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return object The next notification to iterate over.
	 */
	public function next_notification() {

		$this->current_notification++;

		$this->notification = $this->notifications[ $this->current_notification ];

		return $this->notification;
	}

	/**
	 * Rewind the blogs and reset blog index.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	public function rewind_notifications() {

		$this->current_notification = -1;

		if ( $this->notification_count > 0 ) {
			$this->notification = $this->notifications[0];
		}
	}

	/**
	 * Whether there are notifications left in the loop to iterate over.
	 *
	 * This method is used by {@link ajan_notifications()} as part of the
	 * while loop that controls iteration inside the notifications loop, eg:
	 *     while ( ajan_notifications() ) { ...
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see ajan_notifications()
	 *
	 * @return bool True if there are more notifications to show,
	 *         otherwise false.
	 */
	public function notifications() {

		if ( $this->current_notification + 1 < $this->notification_count ) {
			return true;

		} elseif ( $this->current_notification + 1 == $this->notification_count ) {
			do_action( 'notifications_loop_end');

			$this->rewind_notifications();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Set up the current notification inside the loop.
	 *
	 * Used by {@link ajan_the_notification()} to set up the current
	 * notification data while looping, so that template tags used during
	 * that iteration make reference to the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @see ajan_the_notification()
	 */
	public function the_notification() {
		$this->in_the_loop  = true;
		$this->notification = $this->next_notification();

		// loop has just started
		if ( 0 === $this->current_notification ) {
			do_action( 'notifications_loop_start' );
		}
	}
}

/** The Loop ******************************************************************/

/**
 * Initialize the notifications loop.
 *
 * Based on the $args passed, ajan_has_notifications() populates
 * activitynotifications()->notifications->query_loop global, enabling the use of BP
 * templates and template functions to display a list of notifications.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @param array $args {
 *     Arguments for limiting the contents of the notifications loop. Can be
 *     passed as an associative array, or as a URL query string.
 *     @type int $user_id ID of the user to whom notifications belong. Default:
 *           ID of the logged-in user.
 *     @type bool $is_new Whether to limit query to unread notifications.
 *           Default: when viewing the 'unread' tab, defaults to true; when
 *           viewing the 'read' tab, defaults to false.
 *     @type int $page The page of notifications being fetched. Default: 1.
 *     @type int $per_page Number of items to display on a page. Default: 25.
 *     @type int $max Optional. Max items to display. Default: false.
 *     @type string $search_terms Optional. Term to match against
 *           component_name and component_action.
 *     @type string $page_arg URL argument to use for pagination.
 *           Default: 'npage'.
 * }
 */
function ajan_has_notifications( $args = '' ) {

	// Get the default is_new argument
	if ( ajan_is_current_action( 'unread' ) ) {
		$is_new = 1;
	} elseif ( ajan_is_current_action( 'read' ) ) {
		$is_new = 0;
	}

	// Get the user ID
	if ( ajan_displayed_user_id() ) {
		$user_id = ajan_displayed_user_id();
	} else {
		$user_id = ajan_loggedin_user_id();
	}

	// Parse the args
	$r = ajan_parse_args( $args, array(
		'user_id'      => $user_id,
		'is_new'       => $is_new,
		'page'         => 1,
		'per_page'     => 25,
		'max'          => false,
		'search_terms' => isset( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '',
		'page_arg'     => 'npage',
	), 'has_notifications' );

	// Get the notifications
	$query_loop = new AJAN_Notifications_Template( $r );

	// Setup the global query loop
	activitynotifications()->notifications->query_loop = $query_loop;

	return apply_filters( 'ajan_has_notifications', $query_loop->has_notifications(), $query_loop );
}

/**
 * Get the notifications returned by the template loop.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @return array List of notifications.
 */
function ajan_the_notifications() {
	return activitynotifications()->notifications->query_loop->notifications();
}

/**
 * Get the current notification object in the loop.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @return object The current notification within the loop.
 */
function ajan_the_notification() {
	return activitynotifications()->notifications->query_loop->the_notification();
}

/** Loop Output ***************************************************************/

/**
 * Output the ID of the notification currently being iterated on.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_id() {
	echo ajan_get_the_notification_id();
}
	/**
	 * Return the ID of the notification currently being iterated on.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return int ID of the current notification.
	 */
	function ajan_get_the_notification_id() {
		return apply_filters( 'ajan_get_the_notification_id', activitynotifications()->notifications->query_loop->notification->id );
	}

/**
 * Output the associated item ID of the notification currently being iterated on.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_item_id() {
	echo ajan_get_the_notification_item_id();
}
	/**
	 * Return the associated item ID of the notification currently being iterated on.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return int ID of the item associated with the current notification.
	 */
	function ajan_get_the_notification_item_id() {
		return apply_filters( 'ajan_get_the_notification_item_id', activitynotifications()->notifications->query_loop->notification->item_id );
	}

/**
 * Output the secondary associated item ID of the notification currently being iterated on.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_secondary_item_id() {
	echo ajan_get_the_notification_secondary_item_id();
}
	/**
	 * Return the secondary associated item ID of the notification currently being iterated on.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return int ID of the secondary item associated with the current notification.
	 */
	function ajan_get_the_notification_secondary_item_id() {
		return apply_filters( 'ajan_get_the_notification_secondary_item_id', activitynotifications()->notifications->query_loop->notification->secondary_item_id );
	}

/**
 * Output the name of the component associated with the notification currently being iterated on.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_component_name() {
	echo ajan_get_the_notification_component_name();
}
	/**
	 * Return the name of the component associated with the notification currently being iterated on.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return int Name of the component associated with the current notification.
	 */
	function ajan_get_the_notification_component_name() {
		return apply_filters( 'ajan_get_the_notification_component_name', activitynotifications()->notifications->query_loop->notification->component_name );
	}

/**
 * Output the name of the action associated with the notification currently being iterated on.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_component_action() {
	echo ajan_get_the_notification_component_action();
}
	/**
	 * Return the name of the action associated with the notification currently being iterated on.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return int Name of the action associated with the current notification.
	 */
	function ajan_get_the_notification_component_action() {
		return apply_filters( 'ajan_get_the_notification_component_action', activitynotifications()->notifications->query_loop->notification->component_action );
	}

/**
 * Output the timestamp of the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_date_notified() {
	echo ajan_get_the_notification_date_notified();
}
	/**
	 * Return the timestamp of the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string Timestamp of the current notification.
	 */
	function ajan_get_the_notification_date_notified() {
		return apply_filters( 'ajan_get_the_notification_date_notified', activitynotifications()->notifications->query_loop->notification->date_notified );
	}

/**
 * Output the timestamp of the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_time_since() {
	echo ajan_get_the_notification_time_since();
}
	/**
	 * Return the timestamp of the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string Timestamp of the current notification.
	 */
	function ajan_get_the_notification_time_since() {

		// Get the notified date
		$date_notified = ajan_get_the_notification_date_notified();

		// Notified date has legitimate data
		if ( '0000-00-00 00:00:00' !== $date_notified ) {
			$retval = ajan_core_time_since( $date_notified );

		// Notified date is empty, so return a fun string
		} else {
			$retval = __( 'Date not found', 'ajency-activity-and-notifications' );
		}

		return apply_filters( 'ajan_get_the_notification_time_since', $retval );
	}

/**
 * Output full-text description for a specific notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_description() {
	echo ajan_get_the_notification_description();
}

	/**
	 * Get full-text description for a specific notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string
	 */
	function ajan_get_the_notification_description() {

		// Setup local variables
		$description  = '';
		$ajan           = activitynotifications();
		$notification = $ajan->notifications->query_loop->notification;

		// Callback function exists
		if ( isset( $ajan->{ $notification->component_name }->notification_callback ) && is_callable( $ajan->{ $notification->component_name }->notification_callback ) ) {
			$description = call_user_func( $ajan->{ $notification->component_name }->notification_callback, $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1 );

		// @deprecated format_notification_function - 1.5
		} elseif ( isset( $ajan->{ $notification->component_name }->format_notification_function ) && function_exists( $ajan->{ $notification->component_name }->format_notification_function ) ) {
			$description = call_user_func( $ajan->{ $notification->component_name }->format_notification_function, $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1 );

		// Allow non BuddyPress components to hook in
		} else {
			$description = apply_filters_ref_array( 'ajan_notifications_get_notifications_for_user', array( $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1 ) );
		}

		// Filter and return
		return apply_filters( 'ajan_get_the_notification_description', $description );
	}

/**
 * Output the mark read link for the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @uses ajan_get_the_notification_mark_read_link()
 */
function ajan_the_notification_mark_read_link() {
	echo ajan_get_the_notification_mark_read_link();
}
	/**
	 * Return the mark read link for the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	function ajan_get_the_notification_mark_read_link() {

		// Get the URL with nonce, action, and id
		$url = wp_nonce_url( add_query_arg( array( 'action' => 'read', 'notification_id' => ajan_get_the_notification_id() ), ajan_get_notifications_unread_permalink() ), 'ajan_notification_mark_read_' . ajan_get_the_notification_id() );

		// Start the output buffer
		ob_start(); ?>

		<a href="<?php echo esc_url( $url ); ?>" class="mark-read primary"><?php _e( 'Read', 'ajency-activity-and-notifications' ); ?></a>

		<?php $retval = ob_get_clean();

		return apply_filters( 'ajan_get_the_notification_mark_read_link', $retval );
	}

/**
 * Output the mark read link for the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @uses ajan_get_the_notification_mark_unread_link()
 */
function ajan_the_notification_mark_unread_link() {
	echo ajan_get_the_notification_mark_unread_link();
}
	/**
	 * Return the mark read link for the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	function ajan_get_the_notification_mark_unread_link() {

		// Get the URL with nonce, action, and id
		$url = wp_nonce_url( add_query_arg( array( 'action' => 'unread', 'notification_id' => ajan_get_the_notification_id() ), ajan_get_notifications_read_permalink() ), 'ajan_notification_mark_unread_' . ajan_get_the_notification_id() );

		// Start the output buffer
		ob_start(); ?>

		<a href="<?php echo esc_url( $url ); ?>" class="mark-unread primary"><?php _e( 'Unread', 'ajency-activity-and-notifications' ); ?></a>

		<?php $retval = ob_get_clean();

		return apply_filters( 'ajan_get_the_notification_mark_unread_link', $retval );
	}

/**
 * Output the mark link for the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @uses ajan_get_the_notification_mark_unread_link()
 */
function ajan_the_notification_mark_link() {
	echo ajan_get_the_notification_mark_link();
}
	/**
	 * Return the mark link for the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	function ajan_get_the_notification_mark_link() {

		if ( ajan_is_current_action( 'read' ) ) {
			$retval = ajan_get_the_notification_mark_unread_link();
		} else {
			$retval = ajan_get_the_notification_mark_read_link();
		}

		return apply_filters( 'ajan_get_the_notification_mark_link', $retval );
	}

/**
 * Output the delete link for the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 *
 * @uses ajan_get_the_notification_delete_link()
 */
function ajan_the_notification_delete_link() {
	echo ajan_get_the_notification_delete_link();
}
	/**
	 * Return the delete link for the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 */
	function ajan_get_the_notification_delete_link() {

		// URL to add nonce to
		if ( ajan_is_current_action( 'unread' ) ) {
			$link = ajan_get_notifications_unread_permalink();
		} elseif ( ajan_is_current_action( 'read' ) ) {
			$link = ajan_get_notifications_read_permalink();
		}

		// Get the URL with nonce, action, and id
		$url = wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'notification_id' => ajan_get_the_notification_id() ), $link ), 'ajan_notification_delete_' . ajan_get_the_notification_id() );

		// Start the output buffer
		ob_start(); ?>

		<a href="<?php echo esc_url( $url ); ?>" class="delete secondary confirm"><?php _e( 'Delete', 'ajency-activity-and-notifications' ); ?></a>

		<?php $retval = ob_get_clean();

		return apply_filters( 'ajan_get_the_notification_delete_link', $retval );
	}

/**
 * Output the action links for the current notification.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_the_notification_action_links( $args = '' ) {
	echo ajan_get_the_notification_action_links( $args );
}
	/**
	 * Return the action links for the current notification.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @param array $args {
	 *     @type string $before HTML before the links
	 *     @type string $after HTML after the links
	 *     @type string $sep HTML between the links
	 *     @type array $links Array of links to implode by 'sep'
	 * }
	 *
	 * @return string HTML links for actions to take on single notifications.
	 */
	function ajan_get_the_notification_action_links( $args = '' ) {

		// Parse
		$r = wp_parse_args( $args, array(
			'before' => '',
			'after'  => '',
			'sep'    => ' | ',
			'links'  => array(
				ajan_get_the_notification_mark_link(),
				ajan_get_the_notification_delete_link()
			)
		) );

		// Build the links
		$retval = $r['before'] . implode( $r['links'], $r['sep'] ) . $r['after'];

		return apply_filters( 'ajan_get_the_notification_action_links', $retval );
	}

/**
 * Output the pagination count for the current notification loop.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_pagination_count() {
	echo ajan_get_notifications_pagination_count();
}
	/**
	 * Return the pagination count for the current notification loop.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string HTML for the pagination count.
	 */
	function ajan_get_notifications_pagination_count() {
		$query_loop = activitynotifications()->notifications->query_loop;
		$start_num  = intval( ( $query_loop->pag_page - 1 ) * $query_loop->pag_num ) + 1;
		$from_num   = ajan_core_number_format( $start_num );
		$to_num     = ajan_core_number_format( ( $start_num + ( $query_loop->pag_num - 1 ) > $query_loop->total_notification_count ) ? $query_loop->total_notification_count : $start_num + ( $query_loop->pag_num - 1 ) );
		$total      = ajan_core_number_format( $query_loop->total_notification_count );
		$pag        = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s notification)', 'Viewing %1$s to %2$s (of %3$s notifications)', $total, 'ajency-activity-and-notifications' ), $from_num, $to_num, $total );

		return apply_filters( 'ajan_notifications_pagination_count', $pag );
	}

/**
 * Output the pagination links for the current notification loop.
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_pagination_links() {
	echo ajan_get_notifications_pagination_links();
}
	/**
	 * Return the pagination links for the current notification loop.
	 *
	 * @since ajency-activity-and-notifications (1.9.0)
	 *
	 * @return string HTML for the pagination links.
	 */
	function ajan_get_notifications_pagination_links() {
		return apply_filters( 'ajan_get_notifications_pagination_links', activitynotifications()->notifications->query_loop->pag_links );
	}

/** Form Helpers **************************************************************/

/**
 * Output the form for changing the sort order of notifications
 *
 * @since ajency-activity-and-notifications (1.9.0)
 */
function ajan_notifications_sort_order_form() {

	// Setup local variables
	$orders   = array( 'DESC', 'ASC' );
	$selected = 'DESC';

	// Check for a custom sort_order
	if ( !empty( $_REQUEST['sort_order'] ) ) {
		if ( in_array( $_REQUEST['sort_order'], $orders ) ) {
			$selected = $_REQUEST['sort_order'];
		}
	} ?>

	<form action="" method="get" id="notifications-sort-order">
		<label for="notifications-friends"><?php esc_html_e( 'Order By:', 'ajency-activity-and-notifications' ); ?></label>

		<select id="notifications-sort-order-list" name="sort_order" onchange="this.form.submit();">
			<option value="DESC" <?php selected( $selected, 'DESC' ); ?>><?php _e( 'Newest First', 'ajency-activity-and-notifications' ); ?></option>
			<option value="ASC"  <?php selected( $selected, 'ASC'  ); ?>><?php _e( 'Oldest First', 'ajency-activity-and-notifications' ); ?></option>
		</select>

		<noscript>
			<input id="submit" type="submit" name="form-submit" class="submit" value="<?php esc_attr_e( 'Go', 'ajency-activity-and-notifications' ); ?>" />
		</noscript>
	</form>

<?php
}
