<?php

/**
 * Class Tribe__Events__Linked_Posts__Chooser_Meta_Box
 *
 * Handles the Organizer section inside the Events meta box
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
		$this->tribe        = Tribe__Events__Main::instance();
		$this->linked_posts = Tribe__Events__Linked_Posts::instance();
		$this->post_type = $post_type;
		$this->singular_name = $this->linked_posts->linked_post_types[ $this->post_type ]['singular_name'];
		$this->singular_name_lowercase = $this->linked_posts->linked_post_types[ $this->post_type ]['singular_name_lowercase'];
		$this->get_event( $event );

		add_action( 'wp', array( $this, 'sticky_form_data' ), 50 ); // Later than events-admin.js itself is enqueued
	}

	/**
	 * Work with the specifed event object or else use a placeholder if we are in
	 * the middle of creating a new event.
	 *
	 * @param null $event
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
			$event = new WP_Post( (object) array( 'ID' => 0 ) );
		}

		$this->event = $event;
	}

	/**
	 * Render the organizer chooser section for the events meta box
	 *
	 */
	public function render() {
		$this->render_dropdowns();
		$this->render_add_post_button();

		/**
		 * Make this Template filterable, used for Community Facing templates
		 *
		 * @var string $file_path
		 */
		include apply_filters( 'tribe_events_multiple_linked_post_template', $this->tribe->pluginPath . 'src/admin-views/linked-post-meta-box.php' );
	}

	/**
	 * displays the saved organizer dropdown in the event metabox
	 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
	 *
	 */
	public function render_dropdowns() {
		$post_id = $this->event->ID;

		$current_linked_posts = get_post_meta( $post_id, $this->linked_posts->get_meta_key( $this->post_type ), false );

		if ( $this->use_default_post( $current_linked_posts ) ) {
			/**
			 * Filters the default selected post for the linked post
			 *
			 * @param array $default Default post array
			 * @param string $post_type Linked post post type
			 */
			$current_linked_posts = apply_filters( 'tribe_events_linked_post_default', array(), $this->post_type );
		}

		/**
		 * Filters the default selected post for the linked post
		 *
		 * @param array $current_linked_posts Array of currently linked posts
		 * @param string $post_type Linked post post type
		 */
		$current_linked_posts = (array) apply_filters( 'tribe_display_event_linked_post_dropdown_id', $current_linked_posts, $this->post_type );

		/* if the user can't create organizers, then remove any empty values
		   from the $current_organizers array. This prevents the automatic
		   selection of an organizer every time the event is edited. */
		$linked_post_pto = get_post_type_object( $this->post_type );
		if ( ! current_user_can( $linked_post_pto->cap->create_posts ) ) {
			$current_linked_posts = array_filter( $current_linked_posts );
		}

		?><script type="text/template" id="tmpl-tribe-select-<?php echo esc_attr( $this->post_type ); ?>"><?php $this->single_post_dropdown( 0 ); ?></script><?php

		$current_linked_posts = $this->maybe_parse_candidate_linked_posts( $current_linked_posts );

		$i = 0;
		$num_records = count( $current_linked_posts );

		do {
			echo '<tbody>';
			$this->single_post_dropdown( isset( $current_linked_posts[ $i ] ) ? $current_linked_posts[ $i ] : 0 );
			echo '</tbody>';
			$i++;
		} while ( $i < $num_records );
	}

	/**
	 * Render a single row of the organizers table
	 *
	 * @param int $organizer_id
	 *
	 */
	protected function single_post_dropdown( $linked_post_id ) {
		$linked_post_type_container = $this->linked_posts->get_post_type_container( $this->post_type );
		$linked_post_type_id_field  = $this->linked_posts->get_post_type_id_field_index( $this->post_type );
		?>
		<tr class="saved-linked-post">
			<td class="saved-organizer-table-cell">
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
	 * Render a link to edit the organizer post
	 *
	 * @param int $organizer_id
	 *
	 */
	protected function edit_post_link( $linked_post_id ) {
		$linked_post_pto = get_post_type_object( $this->post_type );
		if (
			empty( $linked_post_pto->cap->edit_others_posts )
			|| ! current_user_can( $linked_post_pto->cap->edit_others_posts )
		) {
			return;
		}
		?>
		<div class="edit-linked-post-link">
			<a
				style="<?php echo esc_attr( empty( $linked_post_id ) ? 'display: none;' : 'display: inline-block;' ); ?>"
				data-admin-url="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>"
				href="<?php echo esc_url( admin_url( sprintf( 'post.php?action=edit&post=%s', $linked_post_id ) ) ); ?>"
				target="_blank"
			>
				<?php printf( esc_html__( 'Edit %s', 'the-events-calendar' ), esc_html( $this->singular_name ) ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Determine if the event can use the default setting
	 *
	 * @param array $current_organizers
	 *
	 * @return bool
	 */
	protected function use_default_post( $current_posts ) {
		if ( ! empty( $current_posts ) ) {
			return false; // the event already has organizers
		}
		if ( ! empty( $this->event->ID ) && get_post_status( $this->event->ID ) != 'auto-draft' ) {
			return false; // the event has already been saved
		}
		if ( is_admin() ) {
			return Tribe__Admin__Helpers::instance()->is_action( 'add' );
		} else {
			return true; // a front-end submission form (e.g., community)
		}
	}

	/**
	 * Renders the "Add Another Organizer" button
	 *
	 */
	protected function render_add_post_button() {
		if ( empty( $this->linked_posts->linked_post_types[ $this->post_type ]['allow_multiple'] ) ) {
			return;
		}

		$classes = array(
			'tribe-add-post',
		);
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
	 * Renders the handle for sorting organizers
	 *
	 */
	protected function move_handle() {
		echo '<span class="dashicons dashicons-screenoptions move-linked-post-group"></span>';
	}

	/**
	 * Renders the handle for deleting an organizer
	 *
	 */
	protected function delete_handle() {
		?>
		<a class="dashicons dashicons-trash tribe-delete-this" href="#">
			<span class="screen-reader-text"><?php esc_html_e( 'Delete this', 'the-events-calendar' ); ?></span>
		</a>
	<?php
	}

	/**
	 * Supply previously submitted organizer field values to the events-admin.js
	 * script in order to provide them with sticky qualities.
	 *
	 * This *must* run later than the action:priority used to enqueue
	 * events-admin.js.
	 */
	public function sticky_form_data() {
		$submitted_data = array();

		$linked_posts = Tribe__Events__Linked_Posts::instance();
		$container = $linked_posts->get_post_type_container( $this->post_type );

		if ( empty( $_POST[ $container ] ) || ! is_array( $_POST[ $container ] ) ) {
			return;
		}

		foreach ( $_POST[ $container ] as $field => $set_of_values ) {
			if ( ! is_array( $set_of_values ) ) {
				continue;
			}

			foreach ( $set_of_values as $index => $value ) {
				if ( ! isset( $submitted_data[ $index ] ) ) {
					$submitted_data[ $index ] = array();
				}

				$submitted_data[ $index ][ $field ] = esc_attr( $value );
			}
		}

		wp_localize_script( 'tribe-events-admin', 'tribe_sticky_' . $this->post_type . '_fields', $submitted_data );
	}

	/**
	 * @param $current_linked_posts
	 *
	 * @return mixed
	 */
	private function maybe_parse_candidate_linked_posts( array $current_linked_posts = array() ) {
		$linked_post_type_container = $this->linked_posts->get_post_type_container( $this->post_type );

		// filter out any non-truthy values
		$current_linked_posts = array_filter( $current_linked_posts );

		// We don't have any items
		$has_no_current_linked_posts = empty( $current_linked_posts );
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
