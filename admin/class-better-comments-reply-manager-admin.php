<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link        https://wordpress.org/plugins/
 * @since       1.0.0
 *
 * @package     Better_Comments_Reply_Manager
 * @subpackage  Better_Comments_Reply_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Better_Comments_Reply_Manager
 * @subpackage  Better_Comments_Reply_Manager/admin
 * @author      Hernán Villanueva <chvillanuevap@gmail.com>
 */
class Better_Comments_Reply_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The comment reply status query value.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $query_value    The comment reply status query value.
	 */
	private $query_value;

	/**
	 * The comment reply status meta key.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $meta_key   The comment reply status meta key.
	 */
	private $meta_key;

	/**
	 * The descriptive name for the comments marked as needing a reply.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $label      The descriptive name for the comments marked as needing a reply.
	 */
	private $label;

	/**
	 * Only return comments marked as needing a reply and with this status.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $status     Only return comments marked as needing a reply and with this status.
	 */
	private $status;

	/**
	 * The unique ID of the screen
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $screen_id  The unique ID of the screen
	 */
	private $screen_id;

	/**
	 * The comment types that need a reply.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     array       $comment_types  The comment types that need a reply.
	 */
	private $comment_types;

	/**
	 * The capability required to use this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      $capability     The capability required to use this plugin.
	 */
	private $capability;

	/**
	 * The parent file for the screen per the admin menu system.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string      The parent file for the screen per the admin menu system.
	 */
	private $parent_file;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param   string  $plugin_name    The name of this plugin.
	 * @param   string  $version        The version of this plugin.
	 *
	 * @since   1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->query_value = 'bcrm_marked_as_needing_reply';
		$this->meta_key = '_bcrm_needsreply';
		$this->label = 'Approved & Need Reply';
		$this->status = 'approve';
		$this->screen_id = 'edit-comments';
		$this->parent_file = 'edit-comments.php';

		/**
		 * Allow users to change what comment types need a reply.
		 *
		 * The argument `$comment_type` in a query is empty for normal comments.
		 *
		 * @since 1.0.0
		 */
		$this->comment_types = apply_filters( 'better_comments_reply_manager_comment_types', array( '' ) );

		/**
		 * Allow users to change what capability is required to use this plugin.
		 *
		 * @since 1.0.0
		 */
		$this->capability = apply_filters( 'better_comments_reply_manager_pro_cap', 'moderate_comments' );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since   1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/' . $this->plugin_name . '-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Add the metadata to track which comments need a reply.
	 *
	 * For every time, and immediately after a comment is inserted into the database,
	 * check whether the comment was published by the post's author.
	 * If not, mark the comment as needing a reply.
	 *
	 * @param   int     $comment_id     The ID of the comment for which to retrieve replies.
	 *
	 * @since   1.0.0
	 */
	public function add_comment_meta( $comment_id ) {

		// Get comment object array to run author comparison.
		$comment_obj = get_comment( $comment_id );

		if ( $comment_obj === null ) {
			wp_die( sprintf( __( 'The comment with ID %d does not exist in the database.', 'better-comments-reply-manager' ), $comment_id ) );
		}

		// Assert comment reply status has not been set yet.
		$comment_reply_status = get_comment_meta( $comment_id, $this->meta_key, true );

		// @see `get_comment_meta` returns `''` if `$single == true`.
		if ( $comment_reply_status !== '' ) {
			wp_die( sprintf( __( 'The comment with ID %d already has a reply status set.', 'better-comments-reply-manager' ), $comment_id ) );
		}

		// Only regular comments need a reply.
		if ( ! in_array( $comment_obj->comment_type, $this->comment_types ) ) {

			$success = add_comment_meta( $comment_id, $this->meta_key, '0' );

			if ( $success === false ) {
				wp_die( sprintf( __( 'The comment with ID %d failed to set the reply status.', 'better-comments-reply-manager' ), $comment_id ) );
			}

			return;

		}

		// Grab post ID and user ID to check.
		$comment_post_id   = $comment_obj->comment_post_ID;
		$comment_parent_id = $comment_obj->comment_parent;
		$comment_user_id   = $comment_obj->user_id;

		// Grab post object to compare.
		$comment_post_obj    = get_post ( $comment_post_id );
		$comment_post_author = $comment_post_obj->post_author;

		// Whether or not the post author has replied.
		if ( $comment_user_id === $comment_post_author ) {

			$success = add_comment_meta( $comment_id, $this->meta_key, '0' );

			if ( $success === false ) {
				wp_die( sprintf( __( 'The comment with ID %d failed to set the reply status.', 'better-comments-reply-manager' ), $comment_id ) );
			}

			if ( $comment_parent_id ) {

				// Check whether parent comment needs a reply.
				$comment_parent_reply_status = get_comment_meta( $comment_parent_id, $this->meta_key, true );

				// Bail if status is not present (unprocessed comment).
				if ( $comment_parent_reply_status === '' || $comment_parent_reply_status === '0' ) {
					return;
				}

				$success = update_comment_meta( $comment_parent_id, $this->meta_key, '0' );

				if ( $success === false ) {
					wp_die( sprintf( __( 'The parent comment with ID %d failed to set the reply status.', 'better-comments-reply-manager' ), $comment_parent_id ) );
				}

			}

			return;

		} else {

			$success = add_comment_meta( $comment_id, $this->meta_key, '1' );

			if ( $success === false ) {
				wp_die( sprintf( __( 'The comment with ID %d failed to set the reply status.', 'better-comments-reply-manager' ), $comment_id ) );
			}

		}

	}

	/**
	 * Adds a new column to the Comments page to indicate whether or not
	 * the given comment has received a reply from the post author.
	 *
	 * @param   array   $columns    The array of columns for the 'All Comments' page.
	 * @return  array               The array of columns to display.
	 *
	 * @since   1.0.0
	 */
	public function add_comments_column( $columns = array() ) {

		$columns[ $this->query_value ] = __( 'Reply Status', 'better-comments-reply-manager' );

		return $columns;

	}

	/**
	 * Display the content of the new Reply Status column and indicate whether or not
	 * the given comment has received a reply from the post author.
	 *
	 * @return  array   The array of columns to display.
	 *
	 * @since   1.0.0
	 */
	public function comments_column_content( $column_name = '', $comment_id = 0 ) {

		// If we are not looking at the query column...
		if ( $this->query_value !== trim ( $column_name ) )
			return;

		$comment = get_comment( $comment_id );

		$comment_reply_status = get_comment_meta( $comment_id, $this->meta_key, true );

		// If comment has not been marked as needing or not a reply,
		// display message and return.
		if ( $comment_reply_status === '' ) {

			$messages[] = __( 'This comment has not been marked', 'better-comments-reply-manager' );
			$stati[]  = 'not-marked';

		// Else, if comment has been marked as needing or not a reply,
		// display additional details.
		} else {

			if ( $comment_reply_status === '0' ) {

				$messages[] = __( 'This comment does not need a reply', 'better-comments-reply-manager' );
				$stati[]  = 'marked-as-not-needing-reply';
			} else if ( $comment_reply_status === '1' ) {

				$messages[] = __( 'This comment needs a reply', 'better-comments-reply-manager' );
				$stati[]  = 'marked-as-needing-reply';

			}

			// If the comment is by the author, then we'll note that it's been replied.
			if ( $this->comment_is_by_post_author( $comment_id ) ) {

				$messages[] = __( 'This comment is by the author', 'better-comments-reply-manager' );
				$stati[]  = 'comment-by-author';

			// Otherwise, let's look at the replies to determine if the author has made a reply.
			} else {

				// First, we get all of the replies for this comment.
				$replies = $this->get_comment_replies( $comment_id );
				$author_has_replied = $this->author_has_replied( $replies );

				// Note whether or not the comment author has replied.
				if ( $author_has_replied ) {

					$messages[] = __( 'The author has replied', 'better-comments-reply-manager' );
					$stati[]  = 'author-has-replied';

				} else {

					$messages[] = __( 'The author has not replied', 'better-comments-reply-manager' );
					$stati[]  = 'author-has-not-replied';

					if ( ! empty( $replies ) ) {
						$messages[] = __( 'A user has replied', 'better-comments-reply-manager' );
						$stati[]  = 'user-has-replied';
					}

				}

			}

		}

		printf( '<ul class="bcrm-list">' );
		for ( $message = 0; $message < count( $messages ); ++$message ) {
			printf( '<li class="bcrm-item bcrm-%s">%s</li>', $stati[$message], $messages[$message] );
		}
		printf( '</ul>' );

	}

	/**
	 * Adds a new link in the comment status links to select those that need a reply.
	 *
	 * @return  array   The array of columns to display.
	 *
	 * @since   1.0.0
	 */
	public function comment_status_links( $status_links = array() ) {

		// Translators: %s: need reply comments count.
		$comment_label = _nx_noop(
				$this->label . ' <span class="count">(%s)</span>',
				$this->label . ' <span class="count">(%s)</span>',
				'comments'
		);

		// From `wp-admin/includes/class-wp-comments-list-table.php::prepare_items`.
		$comment_status_map = array(
				'hold' => 'moderated',
				'approve' => 'approved',
				'' => 'all',
		);

		// Assemble link.
		$link = admin_url( $this->screen_id . '.php' );
		$link = add_query_arg( 'comment_status', $comment_status_map[ $this->status ], $link );

		/**
		 * Use the search query argument to set the reply status.
		 *
		 * If the user is on `edit-comments.php`, and clicks on the link below,
		 * the link will add whichever query argument we choose.
		 * However, if the user is already on the "need reply" page,
		 * and enters a page number in the `current-page-selector` input,
		 * the function `prepare_items` (which gets called before `pre_get_comments`)
		 * will not add our query argument because the pagination does not go
		 * through this function (`comment_reply_status_link`).
		 * Therefore, we must use a query argument that the function
		 * `prepare_items` also uses. Among the options, the most sensible
		 * one seems to the search query `s`.
		*/
		$link = add_query_arg( 's', $this->query_value, $link );

		/**
		 * Add class if in "Need Reply" page.
		 * Because there is no `comment_status` specific to the comments
		 * that are missing a reply, two items will have the class `current`.
		 * By default, since no `comment_status` query argument is set at all,
		 * the value of the global $comment_status is set to `all` in
		 * `class-wp-comments-list-table.php` in function `prepare_items`.
		 * This is okay with us, because the `?s=bcrm_needreply` page will show ALL
		 * comments that need a reply; and comments that are in the spam or
		 * trash have already been marked as not needing reply.
		*/
		$class = '';

		if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] === $this->query_value ) {
			$class = ' class="current"';
		}

		// Get missing count.
		$num_comments_need_reply = $this->get_num_comments_need_reply();

		// Assemble status link.
		$status_links[ $this->query_value ] = "<a href='$link'$class>" . sprintf(
				translate_nooped_plural( $comment_label, $num_comments_need_reply ),
				sprintf( '<span class="%s-count">%s</span>',
						$this->query_value,
						number_format_i18n( $num_comments_need_reply )
				)
		) . '</a>';

		// Return all the status links.
		return $status_links;

	}

	/**
	 * Add class to the current comment.
	 *
	 * @param   array   $classes        Comment classes.
	 * @param   string  $class          Comment class.
	 * @param   int     $comment_id     Comment ID.
	 * @param   int     $post_id        Post ID.
	 *
	 * @since   1.0.0
	 */
	public function comment_class( $classes, $class, $comment_id, $comment, $post_id ) {

		$single = true;
		$comment_reply_status = get_comment_meta( $comment_id, $this->meta_key, $single );

		if ( $comment_reply_status === '1' ) {
			$classes[] = 'bcrm_marked_as_needing_reply';
		} else if ( $comment_reply_status === '0' ) {
			$classes[] = 'bcrm_marked_as_not_needing_reply';
		} else if ( $comment_reply_status === '' ) {
			$classes[] = 'bcrm_not_marked';
		}

		return $classes;

	}

	/**
	 * Return the comments that need a reply in a list on the comments table.
	 *
	 * @param   int    $comments  The object array of comments.
	 * @return  array             The filtered comment data.
	 *
	 * @since   1.0.0
	 */
	public function get_comments( $comments = array() ) {

		// Bail on anything not admin.
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) )
			return;

		$current_screen = get_current_screen();

		if ( $this->screen_id !== $current_screen->id ) {
			return;
		}

		if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] === $this->query_value ) {

			// Modify the query to show the comments marked as needing a reply.
			$comments->query_vars['search']     = '';
			$comments->query_vars['status']     = $this->status;
			$comments->query_vars['meta_key']   = $this->meta_key;
			$comments->query_vars['meta_value'] = '1';

		}

	}

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 *
	 * @param   int   $comment_id  The ID of the comment for the given post.
	 * @return  bool               Whether or not the comment is also by the the post author.
	 *
	 * @since   1.0.0
	 */
	private function comment_is_by_post_author( $comment_id = 0 ) {

		$comment = get_comment( $comment_id );
		$post    = get_post ( $comment->comment_post_ID );

		return $comment->comment_author_email == $this->get_post_author_email( $post->ID );

	}

	/**
	 * Retrieves all of the replies for the given comment.
	 *
	 * @param   int    $comment_id  The ID of the comment for which to retrieve replies.
	 * @return  array               The array of replies
	 *
	 * @since   1.0.0
	 */
	private function get_comment_replies( $comment_id = 0 ) {

		global $wpdb;
		$replies = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT comment_ID, comment_author_email, comment_post_ID FROM $wpdb->comments WHERE comment_parent = %d",
					$comment_id
				)
		);

		return $replies;

	}

	/**
	 * Determines whether or not the author has replied to the comment.
	 *
	 * @param   array  $replies  The array of replies for a given comment.
	 * @return  bool             Whether or not the post author has replied.
	 *
	 * @since   1.0.0
	 */
	private function author_has_replied( $replies = array() ) {

		$author_has_replied = false;

		// If there are no replies, the author clearly hasn't replied
		if ( 0 < count( $replies ) ) {

			$comment_count = 0;
			while ( $comment_count < count ( $replies ) && ! $author_has_replied ) {

				// Read the current comment
				$current_comment = $replies[ $comment_count ];

				// If the comment author email address is the same as the post author's address, then we've found a reply by the author.
				if ( $current_comment->comment_author_email == $this->get_post_author_email( $current_comment->comment_post_ID ) ) {
					$author_has_replied = true;
				} // end if

				// Now on to the next comment
				$comment_count++;

			} // end while

		} // end if/else

		return $author_has_replied;

	}

	/**
	 * Retrieves the email address for the author of the post.
	 *
	 * @param   int     $post_id  The ID of the post for which to retrieve the email address.
	 * @return  string            The email address of the post author.
	 *
	 * @since   1.0.0
	 */
	private function get_post_author_email( $post_id = 0 ) {

		// Get the author information for the specified post
		$post   = get_post( $post_id );
		$author = get_user_by( 'id', $post->post_author );

		// Let's store the author data as the author
		$author = $author->data;

		return $author->user_email;

	}

	/**
	 * Return the number of comments marked as needing a reply.
	 *
	 * @param   int     $post_id   Optional post ID for which to retrieve count.
	 * @return  int                The count.
	 *
	 * @since   1.0.0
	 */
	private function get_num_comments_need_reply( $post_id = 0 ) {

		$args = array(
						'post_id'    => $post_id,
						'count'      => true,
						'offset'     => 0,
						'number'     => 0,
						'status'     => $this->status,
						'meta_key'   => $this->meta_key,
						'meta_value' => '1',
		);

		$count = get_comments( $args );

		return $count;

	}

	/**
	 * Add option to mark comments as needing or not needing a reply in the comment row actions.
	 *
	 * @param  array   $action   Comment row actions.
	 * @param  object  $comment  Comment object.
	 *
	 * @since  1.0.0
	 */
	public function comment_row_actions( $actions, $comment ) {

		if ( ! current_user_can( $this->capability ) )
			return $actions;

		global $comment_status;

		$comment_reply_status = get_comment_meta( $comment->comment_ID, $this->meta_key, true );

		$url = admin_url( $this->parent_file );
		$url = add_query_arg( 'c', $comment->comment_ID, $url );

		// Assemble the action URLs.
		$mark_as_needs_reply_url         = add_query_arg( 'action',         'bcrm_mark_as_needs_reply', $url );
		$mark_as_does_not_need_reply_url = add_query_arg( 'action', 'bcrm_mark_as_does_not_need_reply', $url );

		// Add nonce to the action URLs.
		$mark_as_needs_reply_url         = wp_nonce_url(         $mark_as_needs_reply_url, "bcrm_mark_comment_$comment->comment_ID" );
		$mark_as_does_not_need_reply_url = wp_nonce_url( $mark_as_does_not_need_reply_url, "bcrm_mark_comment_$comment->comment_ID" );

		// If in `$this->query_value` page, only display the `Mark as Does Not Need Reply` option.
		if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] === $this->query_value ) {

			// Sanity check really; all coments on this page should have a value of 1.
			if ( $comment_reply_status === '1' ) {
				$actions ['bcrm_mark_as_does_not_need_reply'] = "<a href='" . esc_url( $mark_as_does_not_need_reply_url ) . "' class='vim-a' aria-label='" . esc_attr__( "Mark as Does Not Need Reply", 'better-comments-reply-manager-pro' ) . "'>" . __( "Mark as Does Not Need Reply", 'better-comments-reply-manager-pro' ) . '</a>';
			} else {
				wp_die( __( 'The "Approved & Need Reply" page should only display comments that need a reply', 'better-comments-reply-manager-pro' ) );
			}

			// Else if in another page, and as long as it is not `spam` nor `trash`,
			// display both the `Mark as Does Not Need Reply`
			// and `Mark as Needs Reply` options for JS.
			// One of the options is hidden with CSS styling.
		} else if ( 'spam' !== $comment_status && 'trash' !== $comment_status ) {

			$actions['bcrm_mark_as_needs_reply']         = "<a href='" . esc_url( $mark_as_needs_reply_url )         . "' class='vim-a' aria-label='" . esc_attr__( "Mark as Needs Reply", 'better-comments-reply-manager-pro' )         . "'>" . __( "Mark as Needs Reply", 'better-comments-reply-manager-pro' ) . '</a>';
			$actions['bcrm_mark_as_does_not_need_reply'] = "<a href='" . esc_url( $mark_as_does_not_need_reply_url ) . "' class='vim-a' aria-label='" . esc_attr__( "Mark as Does Not Need Reply", 'better-comments-reply-manager-pro' ) . "'>" . __( "Mark as Does Not Need Reply", 'better-comments-reply-manager-pro' ) . '</a>';

		}

		return $actions;

	}

	/**
	 * Add option to mark comments as needing or not needing a reply in the comment row actions.
	 *
	 * @since   1.0.0
	 */
	public function comment_bulk_actions_js() {

		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		if ( ! ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] === $this->query_value ) ) {
			include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-bulk-actions-needs-reply.php' );
		}

		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-bulk-actions-does-not-need-reply.php' );

	}

	/**
	 * Add option to mark comments as needing or not needing a reply in the Edit Comment page.
	 *
	 * @since   1.0.0
	 * @param   string  $html    HTML markup.
	 * @param   object  $comment WP_Comment object.
	 * @return  string           Modified HTML markup.
	 */
	public function edit_comment_misc_actions( $html, $comment ) {

		$comment_reply_status = get_comment_meta( $comment->comment_ID, $this->meta_key, true );
		$echo_checked = false;

		$html  = '';
		$html .= '<h3 id="comment-reply-status-title">Reply Status</h3>';
		$html .= '<fieldset class="misc-pub-section misc-pub-comment-reply-status" id="comment-needreply-status-radio">';
		$html .= '<legend class="screen-reader-text">' . __( 'Comment reply status', 'better-comments-reply-manager-pro' ) . '</legend>';
		$html .= '<label><input type="radio"' . checked( $comment_reply_status, '1', $echo_checked ) . ' name="bcrm_action" value="bcrm_mark_as_needs_reply" />'         . _x(         'Marked as Needs Reply', 'comment status', 'better-comments-reply-manager-pro' ) .'</label><br />';
		$html .= '<label><input type="radio"' . checked( $comment_reply_status, '0', $echo_checked ) . ' name="bcrm_action" value="bcrm_mark_as_does_not_need_reply" />' . _x( 'Marked as Does Not Need Reply', 'comment status', 'better-comments-reply-manager-pro' ) .'</label><br />';
		$html .= '</fieldset>';

		return $html;

	}

	/**
	 * Handle action posted by the comment bulk actions.
	 *
	 * @since   1.0.0
	 */
	public function admin_action_bulk_mark_comments() {

		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		if ( empty( $_REQUEST['action'] ) || (
				( 'bcrm_bulk_mark_as_needs_reply' !== $_REQUEST['action'] && 'bcrm_bulk_mark_as_needs_reply' !== $_REQUEST['action2'] ) &&
				( 'bcrm_bulk_mark_as_does_not_need_reply' !== $_REQUEST['action'] && 'bcrm_bulk_mark_as_does_not_need_reply' !== $_REQUEST['action2'] ) ) ) {
					return;
				}

				if ( empty( $_REQUEST['delete_comments'] ) || ! is_array( $_REQUEST['delete_comments'] ) ) {
					return;
				}

				check_admin_referer( 'bulk-comments' );

				$action = ( $_REQUEST['action'] != -1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];
				$comment_ids = $_REQUEST['delete_comments'];

				$this->mark_comments( $action, $comment_ids );

	}

	/**
	 * Handle action requested by the comment row actions and the Edit Comment page.
	 *
	 * @since   1.0.0
	 */
	public function admin_action_mark_comments() {

		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		if ( empty( $_REQUEST['action'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['c'] ) ) {
			return;
		}

		$action = $_REQUEST['action'];
		$comment_id = absint( $_REQUEST['c'] );

		check_admin_referer( "bcrm_mark_comment_$comment_id" );

		if ( 'bcrm_mark_as_needs_reply' !== $action && 'bcrm_mark_as_does_not_need_reply' !== $action ) {
			return;
		}

		$comment_ids = array_map( 'intval', explode( ',', trim( $comment_id, ',' ) ) );

		$this->mark_comments( $action, $comment_ids );

	}

	/**
	 * Handle action requested by the Edit Comment page.
	 *
	 * @since   1.0.0
	 */
	public function admin_action_edit_mark_comments( $location, $comment_id ) {

		check_admin_referer( 'update-comment_' . $comment_id );

		if ( isset( $_REQUEST['bcrm_action'] ) ) {

			$location = remove_query_arg( '_wpnonce', $location );
			$location = remove_query_arg( 'action', $location );

			$nonce = wp_create_nonce( "bcrm_mark_comment_$comment_id" );

			$location = add_query_arg( 'action', $_REQUEST['bcrm_action'], $location );
			$location = add_query_arg( 'c', $comment_id, $location );
			$location = add_query_arg( '_wpnonce', $nonce, $location );

			return $location;

		} else {

			return $location;

		}

	}

	/**
	 * All action handlers funnel to this function.
	 *
	 * @since   1.0.0
	 *
	 * @param   string  $action         Requested action.
	 * @param   array   $comment_ids    Array of Comment IDs.
	 */
	private function mark_comments( $action, $comment_ids ) {

		echo '<pre>' . 'mark_comments' . '</pre>';
		print_r( $_REQUEST );

		if ( ! current_user_can( $this->capability ) ) {
			comment_footer_die( __( 'Sorry, you are not allowed to edit comments on this post.', 'better-comments-reply-manager-pro' ) );
		}

		$noredir = isset( $_REQUEST['noredir'] );

		if ( '' != wp_get_referer() && ! $noredir && false === strpos( wp_get_referer(), 'comment.php' ) ) {
			$redir = wp_get_referer();
		} elseif ( '' != wp_get_original_referer() && ! $noredir ) {
			$redir = wp_get_original_referer();
		} elseif ( in_array( $action, array( 'bcrm_donotreplycomment', 'bcrm_needsreplycomment' ) ) ) {
			$redir = admin_url( 'edit-comments.php?p=' . absint( $comment->comment_post_ID ) );
		} else {
			$redir = admin_url( 'edit-comments.php' );
		}

		$redir = remove_query_arg( array(
				'bcrm_marked_as_needing_reply',
				'bcrm_marked_as_not_needing_reply',
				'spammed', 'unspammed', 'trashed', 'untrashed', 'deleted', 'ids', 'approved', 'unapproved' ), $redir );

		$marked_as_needing_reply = $marked_as_not_needing_reply = 0;

		foreach ( $comment_ids as $comment_id ) {

			if ( ! $comment = get_comment( $comment_id ) ) {
				comment_footer_die(
						__( 'Invalid comment ID.' ) .
						sprintf(' <a href="%s">' . __( 'Go back', 'better-comments-reply-manager-pro' ) . '</a>.',
								admin_url( $this->parent_file )
						)
				);
			}

			$comment_reply_status = get_comment_meta( $comment_id, $this->meta_key, true );

			switch ( $action ) {

				// Mark comment as not needing a reply.
				case 'bcrm_bulk_mark_as_does_not_need_reply' :
				case 'bcrm_mark_as_does_not_need_reply' :

					if ( $comment_reply_status === '1' || $comment_reply_status === '' ) {

						$status = update_comment_meta( $comment_id, $this->meta_key, '0' );

						// $status == $mid if `update_comment_meta` calls `add_comment_meta`,
						// and $status == true if meta key already exists.
						if ( $status === false ) {
							wp_die(
									__( 'We were unable to update the reply status.' ) .
									sprintf( ' <a href="%s">' . __( 'Go back', 'better-comments-reply-manager-pro' ) . '</a>.',
											admin_url( $this->parent_file )
									)
							);
						}
						else {
							++$marked_as_not_needing_reply;
						}

					}

					break;

					// Mark comment as needing a reply.
				case 'bcrm_bulk_mark_as_needs_reply' :
				case 'bcrm_mark_as_needs_reply' :

					if ( $comment_reply_status === '0' || $comment_reply_status === '' ) {

						$status = update_comment_meta( $comment_id, $this->meta_key, '1' );

						// $status == $mid if `update_comment_meta` calls `add_comment_meta`,
						// and $status == true if meta key already exists.
						if ( $status === false ) {
							wp_die(
									__( 'We were unable to update the reply status.' ) .
									sprintf( ' <a href="%s">' . __( 'Go back', 'better-comments-reply-manager-pro' ) . '</a>.',
											admin_url( $this->parent_file )
									)
							);
						}
						else {
							++$marked_as_needing_reply;
						}

					}

					break;

				default:
					wp_die( __( 'Unknown action.' ) );

			}   // end switch

		}   // end foreach

		switch ( $action ) {

			case 'bcrm_bulk_mark_as_does_not_need_reply' :
			case 'bcrm_mark_as_does_not_need_reply' :
				$redir = add_query_arg( array( 'bcrm_marked_as_not_needing_reply' => $marked_as_not_needing_reply ), $redir );
				break;

			case 'bcrm_bulk_mark_as_needs_reply' :
			case 'bcrm_mark_as_needs_reply' :
				$redir = add_query_arg( array( 'bcrm_marked_as_needing_reply' => $marked_as_needing_reply ), $redir );
				break;

		}

		wp_safe_redirect( $redir );
		die;

	}

	/**
	 * Display admin notices about updated reply status.
	 *
	 * @since   1.0.0
	 */
	public function admin_notices() {

		if ( isset( $_REQUEST['bcrm_marked_as_needing_reply'] ) ) {
			$marked_as_needing_reply = absint( $_REQUEST['bcrm_marked_as_needing_reply'] );

			if ( $marked_as_needing_reply ) {
				$messages[] = sprintf(
						_n( '%s comment marked as needing a reply', '%s comments marked as needing a reply', $marked_as_needing_reply ),
						number_format_i18n( $marked_as_needing_reply )
				);
			}
		}

		if ( isset( $_REQUEST['bcrm_marked_as_not_needing_reply'] ) ) {
			$marked_as_not_needing_reply = absint( $_REQUEST['bcrm_marked_as_not_needing_reply'] );

			if ( $marked_as_not_needing_reply ) {
				$messages[] = sprintf(
						_n( '%s comment marked as not needing a reply', '%s comments marked as not needing a reply', $marked_as_not_needing_reply ),
						number_format_i18n( $marked_as_not_needing_reply )
				);
			}
		}

		if ( ! empty( $messages ) ) {
			echo '<div id="moderated" class="updated notice is-dismissible"><p>'. implode ( "<br/>\n", $messages ) . '</p></div>';
		}

	}

}
