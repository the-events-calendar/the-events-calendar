<?php

/**
 * Class Tribe__Events__Linked_Posts__Chooser_Meta_Box
 *
 * Handles the Venue and Organizer sections inside the Events meta box
 */
class Tribe__Events__Linked_Posts__Chooser_Meta_Box {
	/**
	 * @var WP_Post
	 */
	protected $event;

	/**
	 * @var Tribe__Events__Main
	 */
	protected $tribe;

	/**
	 * @var Tribe__Events__Linked_Posts
	 */
	protected $linked_posts;

	/**
	 * @var string
	 */
	protected $post_type;

	/**
	 * @var string
	 */
	protected $singular_name;

	public function __construct( $event = null, $post_type = null ) {
		$this->tribe                   = Tribe__Events__Main::instance();
		$this->linked_posts            = Tribe__Events__Linked_Posts::instance();
		$this->post_type               = $post_type;
		$this->singular_name           = $this->linked_posts->linked_post_types[ $this->post_type ]['singular_name'];
		$this->singular_name_lowercase = $this->linked_posts->linked_post_types[ $this->post_type ]['singular_name_lowercase'];
		$this->get_event( $event );

		add_action( 'wp', [ $this, 'sticky_form_data' ], 50 ); // Later than events-admin.js itself is enqueued
	}

	/**
	 * Work with the specified event object or else use a placeholder if in the middle of creating a new event.
	 *
	 * @param mixed $event
	 */
	protected function get_event( $event = null ) {
		if ( is_null( $event ) ) {
			$event = $GLOBALS['post'];
		}

		if ( is_numeric( $event ) ) {
			$event = WP_Post::get_instance( $event );
		}

		if ( $event instanceof stdClass || is_array( $event ) ) {
			$event = new WP_Post( (object) $event );
		}

		if ( ! $event instanceof WP_Post ) {
			$event = new WP_Post( (object) [ 'ID' => 0 ] );
		}

		$this->event = $event;
	}

	/**
	 * Render the chooser section for the events meta box
	 */
	public function render() {
		$this->render_dropdowns();
		$this->render_add_post_button();

		/**
		 * Make this Template filterable, used for Community Facing templates.
		 *
		 * @param string $file_path
		 */
		include apply_filters( 'tribe_events_multiple_linked_post_template', $this->tribe->pluginPath . 'src/admin-views/linked-post-meta-box.php' );
	}

	/**
	 * Displays the saved linked post dropdown in the event metabox.
	 *
	 * @since 3.0
	 * @since 4.5.11 Genericized to work for all linked posts, not just organizers like it was originally.
	 */
	public function render_dropdowns() {
		$post_id                      = $this->event->ID;
		$current_linked_post_meta_key = $this->linked_posts->get_meta_key( $this->post_type );
		$current_linked_posts         = get_post_meta( $post_id, $current_linked_post_meta_key, false );
		if ( $this->post_type === Tribe__Events__Organizer::POSTTYPE ) {
		    $current_linked_posts = tribe_get_organizer_ids( $post_id );
		}

		/**
		 * Allows for filtering the array of values retrieved for a specific linked post meta field.
		 *
		 * Name of filter is assembled as tribe_events_linked_post_meta_values_{$current_linked_post_meta_key}, where
		 * $current_linked_post_meta_key is just literally the name of the current meta key. So when the _EventOrganizerID
		 * is being filtered, for example, the filter name would be tribe_events_linked_post_meta_values__EventOrganizerID
		 *
		 * @since 4.5.11
		 *
		 * @param array $current_linked_posts The array of the current meta field's values.
		 * @param int $post_id The current event's post ID.
		 */
		$current_linked_posts = apply_filters( "tribe_events_linked_post_meta_values_{$current_linked_post_meta_key}", $current_linked_posts, $post_id );

		if ( $this->use_default_post( $current_linked_posts ) ) {
			/**
			 * Filters the default selected post for the linked post
			 *
			 * @param array $default Default post array
			 * @param string $post_type Linked post post type
			 */
			$current_linked_posts = apply_filters( 'tribe_events_linked_post_default', [], $this->post_type );
		}

		/**
		 * Filters the default selected post for the linked post.
		 *
		 * @param array $current_linked_posts Array of currently linked posts
		 * @param string $post_type Linked post post type
		 */
		$current_linked_posts = (array) apply_filters( 'tribe_display_event_linked_post_dropdown_id', $current_linked_posts, $this->post_type );

		/* if the user can't create posts of the linked type, then remove any empty values from the $current_linked_posts
		   array. This prevents the automatic selection of a post every time the event is edited. */
		$linked_post_pto = get_post_type_object( $this->post_type );

		if ( ! current_user_can( $linked_post_pto->cap->create_posts ) ) {
			$current_linked_posts = array_filter( $current_linked_posts );
		}

		?><script type="text/template" id="tmpl-tribe-select-<?php echo esc_attr( $this->post_type ); ?>"><?php $this->single_post_dropdown( 0 ); ?></script><?php

		$current_linked_posts = $this->maybe_parse_candidate_linked_posts( $current_linked_posts );
		$current_linked_posts = array_values( $current_linked_posts );

		$i           = 0;
		$num_records = count( $current_linked_posts );

		do {
			echo '<tbody>';
			$this->single_post_dropdown( isset( $current_linked_posts[ $i ] ) ? $current_linked_posts[ $i ] : 0 );
			echo '</tbody>';
			$i++;
		} while ( $i < $num_records );
	}

	/**
	 * Render a single row of the linked post's table
	 *
	 * @since 3.0
	 *
	 * @param int $linked_post_id
	 */
	protected function single_post_dropdown( $linked_post_id ) {
		$linked_post_type_container = $this->linked_posts->get_post_type_container( $this->post_type );
		?>
		<tr class="saved-linked-post">
			<td class="saved-<?php echo esc_attr( $linked_post_type_container ); ?>-table-cell">
				<?php $this->move_handle(); ?>
				<label
					data-l10n-create-<?php echo esc_attr( $this->post_type ); ?>="<?php printf( esc_attr__( 'Create New %s', 'the-events-calendar' ), $this->singular_name ); ?>">
					<?php printf( esc_html__( '%s:', 'the-events-calendar' ), $this->singular_name ); ?>
				</label>
			</td>
			<td>
			<?php
				$this->linked_posts->saved_linked_post_dropdown( $this->post_type, $linked_post_id );
				$this->edit_post_link( $linked_post_id );
				if ( ! empty( $this->linked_posts->linked_post_types[ $this->post_type ]['allow_multiple'] ) ) {
					$this->delete_handle();
				}
			?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render a link to edit the linked post
	 *
	 * @since 6.0.1 Reversed check for editing posts. Added check if the $edit_link is empty.
	 * @since 3.0
	 *
	 * @param int $linked_post_id
	 */
	protected function edit_post_link( $linked_post_id ) {
		$linked_post_pto = get_post_type_object( $this->post_type );

		// Bail if the user is unable to edit the type of post.
		if (
			empty( $linked_post_pto->cap->edit_others_posts )
			|| ! current_user_can( $linked_post_pto->cap->edit_others_posts )
		) {
			return;
		}

		$edit_link = get_edit_post_link( $linked_post_id );

		// Bail if $edit_link is empty.
		if ( empty( $edit_link ) ) {
			return;
		}

		printf(
			'<div class="edit-linked-post-link"><a style="%1$s"  href="%2$s" target="_blank">%3$s</a></div>',
			esc_attr( empty( $linked_post_id ) ? 'display: none;' : 'display: inline-block;' ),
			esc_url( $edit_link ),
			sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), esc_html( $this->singular_name ) )
		);
	}

	/**
	 * Determine if the event can use the default setting
	 *
	 * @param array $current_posts
	 * @return bool
	 */
	protected function use_default_post( $current_posts ) {
		if ( ! empty( $current_posts ) ) {
			return false; // the event already has linked post(s)
		}

		if ( ! empty( $this->event->ID ) && get_post_status( $this->event->ID ) != 'auto-draft' ) {
			return false; // the event has already been saved
		}

		if ( is_admin() ) {
			return Tribe__Admin__Helpers::instance()->is_action( 'add' );
		}

		return true; // a front-end submission form (e.g., community)
	}

	/**
	 * Renders the "Add Another Organizer" button
	 */
	protected function render_add_post_button() {
		if ( empty( $this->linked_posts->linked_post_types[ $this->post_type ]['allow_multiple'] ) ) {
			return;
		}

		$classes = [ 'tribe-add-post' ];

		if ( is_admin() ) {
			$classes[] = 'button';
		} else {
			$classes[] = 'tribe-button';
			$classes[] = 'tribe-button-secondary';
		}

		?>
		<tfoot>
			<tr>
				<td></td>
				<td><a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="#"><?php echo esc_html( sprintf( __( 'Add another %s', 'the-events-calendar' ), $this->singular_name_lowercase ) ); ?></a></td>
			</tr>
		</tfoot>
		<?php
	}

	/**
	 * Renders the handle for sorting linked posts
	 *
	 * @since 3.0
	 */
	protected function move_handle() {
		echo '<span class="dashicons dashicons-screenoptions move-linked-post-group"></span>';
	}

	/**
	 * Renders the handle for deleting a linked post
	 *
	 * @since 3.0
	 */
	protected function delete_handle() {
		?>
		<a class="dashicons dashicons-trash tribe-delete-this" href="#">
			<span class="screen-reader-text"><?php esc_html_e( 'Delete this', 'the-events-calendar' ); ?></span>
		</a>
	<?php
	}

	/**
	 * Supply previously submitted linked post field values to the events-admin.js script in order to provide
	 * them with sticky qualities. This *must* run later than the action:priority used to enqueue events-admin.js.
	 */
	public function sticky_form_data() {
		$submitted_data = [];

		$linked_posts = Tribe__Events__Linked_Posts::instance();
		$container    = $linked_posts->get_post_type_container( $this->post_type );

		if ( empty( $_POST[ $container ] ) || ! is_array( $_POST[ $container ] ) ) {
			return;
		}

		foreach ( $_POST[ $container ] as $field => $set_of_values ) {
			if ( ! is_array( $set_of_values ) ) {
				continue;
			}

			foreach ( $set_of_values as $index => $value ) {
				if ( ! isset( $submitted_data[ $index ] ) ) {
					$submitted_data[ $index ] = [];
				}

				$submitted_data[ $index ][ $field ] = esc_attr( $value );
			}
		}

		wp_localize_script( 'tribe-events-admin', 'tribe_sticky_' . $this->post_type . '_fields', $submitted_data );
	}

	/**
	 * Parse candidate linked posts.
	 *
	 * @param $current_linked_posts
	 * @return mixed
	 */
	private function maybe_parse_candidate_linked_posts( array $current_linked_posts = [] ) {
		$linked_post_type_container = $this->linked_posts->get_post_type_container( $this->post_type );

		// filter out any non-truthy values
		$current_linked_posts = array_filter( $current_linked_posts );

		// We don't have any items
		$has_no_current_linked_posts                    = empty( $current_linked_posts );
		$submitted_data_contains_candidate_linked_posts = ! empty( $_POST[ $linked_post_type_container ] );

		if ( $has_no_current_linked_posts && $submitted_data_contains_candidate_linked_posts ) {
			$candidate_linked_posts    = $_POST[ $linked_post_type_container ];
			$linked_post_type_id_field = $this->linked_posts->get_post_type_id_field_index( $this->post_type );

			if ( ! empty( $candidate_linked_posts[ $linked_post_type_id_field ] ) ) {
				$candidate_linked_posts = $candidate_linked_posts[ $linked_post_type_id_field ];

				return $candidate_linked_posts;
			}

			return $current_linked_posts;
		}

		return $current_linked_posts;
	}
}
