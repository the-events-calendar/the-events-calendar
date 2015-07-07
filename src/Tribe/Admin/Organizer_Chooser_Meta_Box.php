<?php

/**
 * Class Tribe__Events__Admin__Organizer_Chooser_Meta_Box
 *
 * Handles the Organizer section inside the Events meta box
 */
class Tribe__Events__Admin__Organizer_Chooser_Meta_Box {
	/**
	 * @var WP_Post
	 */
	protected $event;

	/**
	 * @var Tribe__Events__Main
	 */
	protected $tribe;

	public function __construct( $event = null ) {
		$this->tribe = Tribe__Events__Main::instance();
		$this->get_event( $event );
	}

	/**
	 * Work with the specifed event object or else use a placeholder if we are in
	 * the middle of creating a new event.
	 *
	 * @param null $event
	 */
	protected function get_event( $event = null ) {
		global $post;

		if ( $event === null ) {
			$this->event = $post;
		} elseif ( $event instanceof WP_Post ) {
			$this->event = $event;
		} else {
			$this->event = new WP_Post( (object) array( 'ID' => 0 ) );
		}
	}

	/**
	 * Render the organizer chooser section for the events meta box
	 *
	 * @return void
	 */
	public function render() {
		$this->render_dropdowns();
		$this->render_add_organizer_button();
		include( $this->tribe->pluginPath . 'src/admin-views/new-organizer-meta-section.php' );
	}

	/**
	 * displays the saved organizer dropdown in the event metabox
	 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
	 *
	 * @return void
	 */
	public function render_dropdowns() {
		$postId = $this->event->ID;
		$current_organizers = get_post_meta( $postId, '_EventOrganizerID', false );
		if ( $this->use_default_organizer( $current_organizers ) ) {
			$current_organizers = array( $this->tribe->defaults()->organizer_id() );
		}
		$current_organizers = (array)apply_filters( 'tribe_display_event_organizer_dropdown_id', $current_organizers );

		?><script type="text/template" id="tmpl-tribe-select-organizer"><?php $this->single_organizer_dropdown(0); ?></script><?php

		foreach ( $current_organizers as $organizer_id ) {
			echo '<tbody>';
			$this->single_organizer_dropdown( $organizer_id );
			echo '</tbody>';
		}
	}

	/**
	 * Render a single row of the organizers table
	 *
	 * @param int $organizer_id
	 *
	 * @return void
	 */
	protected function single_organizer_dropdown( $organizer_id ) {
		?>
		<tr class="saved_organizer">
			<td style="width:170px"><?php
				$this->move_handle();
				?><label data-l10n-create-organizer="<?php esc_attr_e( sprintf( __( 'Create New %s', 'tribe-events-calendar' ), $this->tribe->singular_organizer_label ) ); ?>"><?php printf( __( 'Use Saved %s:', 'tribe-events-calendar' ), $this->tribe->singular_organizer_label ); ?></label>
			</td>
			<td><?php
				$this->tribe->saved_organizers_dropdown( $organizer_id, 'organizer[OrganizerID][]' );
				$this->edit_organizer_link( $organizer_id );
				$this->delete_handle();
			?></td>
		</tr>
	<?php

	}

	/**
	 * Render a link to edit the organizer post
	 *
	 * @param int $organizer_id
	 *
	 * @return void
	 */
	protected function edit_organizer_link( $organizer_id ) {
		?>
		<div class="edit-organizer-link"><a
				<?php if ( empty( $organizer_id ) ) { ?> style="display:none;"<?php } ?>
				data-admin-url="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>"
				href="<?php echo esc_url( admin_url( sprintf( 'post.php?action=edit&post=%s', $organizer_id ) ) ); ?>"
				target="_blank"><?php printf( __( 'Edit %s', 'tribe-events-calendar' ), $this->tribe->singular_organizer_label ); ?></a>
		</div>
		<?php
	}

	/**
	 * Determine if the event can use the default organizer setting
	 *
	 * @param array $current_organizers
	 *
	 * @return bool
	 */
	protected function use_default_organizer( $current_organizers ) {
		if ( !empty( $current_organizers ) ) {
			return FALSE; // the event already has organizers
		}
		if ( !empty( $this->event->ID ) && get_post_status( $this->event->ID ) != 'auto-draft' ) {
			return FALSE; // the event has already been saved
		}
		if ( is_admin() ) {
			if ( get_current_screen()->action == 'add' ) {
				return TRUE; // we're on the add new event screen
			} else {
				return FALSE;
			}
		} else {
			return TRUE; // a front-end submission form (e.g., community)
		}
	}

	/**
	 * Renders the "Add Another Organizer" button
	 *
	 * @return void
	 */
	protected function render_add_organizer_button() {
		printf( '<tfoot><tr><td colspan="2"><a class="tribe-add-organizer" href="#">%s</a></td></tr></tfoot>', __( 'Add another organizer', 'tribe-events-calendar' ) );
	}

	/**
	 * Renders the handle for sorting organizers
	 *
	 * @return void
	 */
	protected function move_handle() {
		echo '<span class="dashicons dashicons-screenoptions move-organizer-group"></span>';
	}

	/**
	 * Renders the handle for deleting an organizer
	 *
	 * @return void
	 */
	protected function delete_handle() {
		echo '<a class="dashicons dashicons-trash delete-organizer-group" href="#"></a>';
	}

}
