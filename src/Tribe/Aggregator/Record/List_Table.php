<?php
// Don't load directly
defined( 'WPINC' ) or die;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
}

class Tribe__Events__Aggregator__Record__List_Table extends WP_List_Table {

	public $tab;
	public $page;
	public $user;

	public function __construct( $args = array() ) {
		$screen = WP_Screen::get( Tribe__Events__Aggregator__Records::$post_type );

		$default = array(
			'screen' => $screen,
			'tab' => Tribe__Events__Aggregator__Tabs::instance()->get_active(),
		);
		$args = wp_parse_args( $args, $default );

		parent::__construct( $args );

		// Set Current Tab
		$this->tab = $args['tab'];

		// Set page Instance
		$this->page = Tribe__Events__Aggregator__Page::instance();

		// Set current user
		$this->user = wp_get_current_user();
	}

	/**
	 *
	 * @global array    $avail_post_stati
	 * @global WP_Query $wp_query
	 * @global int      $per_page
	 * @global string   $mode
	 */
	public function prepare_items() {
		if ( ! isset( $_GET['orderby'] ) ) {
			$_GET['orderby'] = 'imported';
		}

		// Set order
		if ( isset( $_GET['order'] ) && 'asc' === $_GET['order'] ) {
			$order = 'asc';
		} else {
			$order = 'desc';
		}

		$args = array(
			'post_type' => $this->screen->post_type,
			'orderby'   => 'modified',
			'order'     => $order,
			'paged'     => absint( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 ),
		);

		$status = Tribe__Events__Aggregator__Records::$status;

		switch ( $this->tab->get_slug() ) {
			case 'scheduled':
				$args['ping_status'] = 'schedule';
				$args['post_status'] = $status->schedule;
				break;

			case 'history':
				$args['post_status'] = array(
					$status->success,
					$status->failed,
					$status->pending,
				);
				break;
		}

		if ( isset( $_GET['origin'] ) ) {
			$args['post_mime_type'] = 'ea/' . $_GET['origin'];
		}

		// retrieve the "per_page" option
		$screen_option = $this->screen->get_option( 'per_page', 'option' );

		// retrieve the value of the option stored for the current user
		$per_page = get_user_meta( $this->user->ID, $screen_option, true );

		if ( empty ( $per_page ) || $per_page < 1 ) {
			// get the default value if none is set
			$per_page = $this->screen->get_option( 'per_page', 'default' );
		}

		$args['posts_per_page'] = $per_page;

		$query = new WP_Query( $args );

		$this->items = $query->posts;

		$this->set_pagination_args( array(
			'total_items' => $query->found_posts,
			'per_page' => $query->query_vars['posts_per_page'],
		) );
	}

	public function nonce() {
		wp_nonce_field( 'aggregator_' . $this->tab->get_slug() . '_request', 'aggregator[nonce]' );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'imported' => 'imported',
		);
	}

	/**
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		// Skip it early because we are not doing filters on MVP
		return false;

		if ( 'bottom' === $which ) {
			return false;
		}

		echo '<div class="alignleft actions">';

		$field = (object) array();
		$field->label = esc_html__( 'Filter By Origin', 'the-events-calendar' );
		$field->placeholder = esc_attr__( 'Filter By Origin', 'the-events-calendar' );
		$field->options = Tribe__Events__Aggregator::instance()->api( 'origins' )->get();

		?>
			<label class="screen-reader-text" for="tribe-ea-field-origin"><?php echo $field->label; ?></label>
			<input
				name="aggregator[origin]"
				type="hidden"
				id="tribe-ea-field-origin"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-medium"
				placeholder="<?php echo $field->placeholder; ?>"
				data-hide-search
				multiple
				data-options="<?php echo esc_attr( json_encode( $field->options ) ); ?>">
		<?php

		if ( 'schedule' === $this->tab->get_slug() ) {
			$field = (object) array();
			$field->label = esc_html__( 'Filter By Frequency', 'the-events-calendar' );
			$field->placeholder = esc_attr__( 'Filter By Frequency', 'the-events-calendar' );
			$field->options = Tribe__Events__Aggregator__Cron::instance()->get_frequency();

			?>
				<label class="screen-reader-text" for="tribe-ea-field-frequency"><?php echo $field->label; ?></label>
				<input
					name="aggregator[origin]"
					type="hidden"
					id="tribe-ea-field-origin"
					class="tribe-ea-field tribe-ea-dropdown"
					placeholder="<?php echo $field->placeholder; ?>"
					data-hide-search
					multiple
					data-options="<?php echo esc_attr( json_encode( $field->options ) ); ?>">
			<?php
		}

		submit_button( esc_attr__( 'Filter', 'the-events-calendar' ), 'apply_filters', '', false );

		echo '</div>';
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			array(
				'id' => 'delete',
				'text' => 'Delete',
			),
			// array(
			// 	'id' => 'import',
			// 	'text' => 'Import Now',
			// ),
		);
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backwards-compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		// On History Tab there is no Bulk Actions
		if ( 'history' === $this->tab->get_slug() ) {
			return false;
		}

		if ( 'bottom' === $which ) {
			return false;
		}

		// disable bulk actions if the Aggregator service is inactive
		if ( ! Tribe__Events__Aggregator::instance()->is_service_active() ) {
			return '';
		}

		$field = (object) array();
		$field->label = esc_html__( 'Bulk Actions', 'the-events-calendar' );
		$field->placeholder = esc_attr__( 'Bulk Actions', 'the-events-calendar' );
		$field->options = $this->get_bulk_actions();

		?>
			<label class="screen-reader-text" for="tribe-ea-field-action"><?php echo $field->label; ?></label>
			<input
				name="aggregator[action]"
				type="hidden"
				id="tribe-ea-field-action"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-medium"
				placeholder="<?php echo $field->placeholder; ?>"
				data-hide-search
				data-options="<?php echo esc_attr( json_encode( $field->options ) ); ?>">
		<?php

		submit_button( esc_attr__( 'Apply', 'the-events-calendar' ), 'do_action', '', false );
		echo "\n";
	}

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$views = array();
		$given_origin = isset( $_GET['origin'] ) ? $_GET['origin'] : false;

		$type = array( 'schedule' );
		if ( 'history' === $this->tab->get_slug() ) {
			$type[] = 'manual';
		}

		$status = array();
		if ( 'history' === $this->tab->get_slug() ) {
			$status[] = 'success';
			$status[] = 'failed';
			$status[] = 'pending';
		} else {
			$status[] = 'schedule';
		}

		$origins = Tribe__Events__Aggregator__Records::instance()->count_by_origin( $type, $status );

		$total = array_sum( $origins );
		$link = $this->page->get_url( array( 'tab' => $this->tab->get_slug() ) );
		$text = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total,
				'records'
			),
			number_format_i18n( $total )
		);
		$views['all'] = ( $given_origin ? sprintf( '<a href="%s">%s</a>', $link, $text ) : $text );

		foreach ( $origins as $origin => $count ) {
			$origin_instance = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $origin );
			$link = $this->page->get_url( array( 'tab' => $this->tab->get_slug(), 'origin' => $origin ) );
			$text = $origin_instance->get_label() . sprintf( ' <span class="count">(%s)</span>', number_format_i18n( $count ) );
			$views[ $origin ] = ( $given_origin !== $origin ? sprintf( '<a href="%s">%s</a>', $link, $text ) : $text );
		}

		return $views;
	}

	/**
	 *
	 * @return array
	 */
	public function get_columns() {
		$post_type = $this->screen->post_type;

		$columns = array();

		switch ( $this->tab->get_slug() ) {
			case 'scheduled':
				// We only need the checkbox when the EA service is active because there aren't any bulk
				// actions when EA is disabled
				if ( Tribe__Events__Aggregator::instance()->is_service_active() ) {
					$columns['cb'] = '<input type="checkbox" />';
				}
				$columns['source'] = esc_html_x( 'Source', 'column name', 'the-events-calendar' );
				$columns['frequency'] = esc_html_x( 'Frequency', 'column name', 'the-events-calendar' );
				$columns['imported'] = esc_html_x( 'Last Import', 'column name', 'the-events-calendar' );
				break;

			case 'history':
				$columns['source'] = esc_html_x( 'Source', 'column name', 'the-events-calendar' );
				$columns['frequency'] = esc_html_x( 'Type', 'column name', 'the-events-calendar' );
				$columns['imported'] = esc_html_x( 'When', 'column name', 'the-events-calendar' );
				break;
		}
		$columns['total'] = esc_html_x( '# Imported', 'column name', 'the-events-calendar' );

		/**
		 * Filter the columns displayed in the Posts list table for a specific post type.
		 *
		 * @since 4.3
		 *
		 * @param array $post_columns An array of column names.
		 */
		return apply_filters( 'tribe_aggregator_manage_record_columns', $columns );
	}

	protected function handle_row_actions( $post, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		if ( 'scheduled' !== $this->tab->get_slug() ) {
			return '';
		}

		// disable row actions if the Aggregator service is inactive
		if ( ! Tribe__Events__Aggregator::instance()->is_service_active() ) {
			return '';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		$actions = array();

		if ( current_user_can( $post_type_object->cap->edit_post, $post->ID ) ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				__( 'Edit', 'the-events-calendar' )
			);

			$args = array(
				'tab'    => $this->tab->get_slug(),
				'action' => 'run-import',
				'ids'   => absint( $post->ID ),
				'nonce'  => wp_create_nonce( 'aggregator_' . $this->tab->get_slug() . '_request' ),
			);
			$actions['run-now'] = sprintf(
				'<a href="%1$s" title="%2$s">%3$s</a>',
				Tribe__Events__Aggregator__Page::instance()->get_url( $args ),
				esc_attr__( 'Start an import from this source now, regardless of schedule.', 'the-events-calendar' ),
				esc_html__( 'Run Import', 'the-events-calendar' )
			);
		}

		if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete">%s</a>',
				get_delete_post_link( $post->ID, '', true ),
				__( 'Delete', 'the-events-calendar' )
			);
		}

		return $this->row_actions( $actions );
	}

	private function get_status_icon( $record ) {
		$post = $record->post;

		$classes[] = 'dashicons';
		if ( false !== strpos( $post->post_status, 'tribe-ea-' ) ) {
			$classes[] = str_replace( 'tribe-ea-', 'tribe-ea-status-', $post->post_status );
		} else {
			$classes[] = 'tribe-ea-status-' . $post->post_status;
		}

		$helper_text = '';

		switch ( $post->post_status ) {
			case 'tribe-ea-success':
				$classes[] = 'dashicons-yes';
				$helper_text = __( 'Import completed', 'the-events-calendar' );
				break;
			case 'tribe-ea-failed':
				$classes[] = 'dashicons-warning';
				$helper_text = __( 'Import failed', 'the-events-calendar' );
				if ( $errors = $record->get_errors() ) {
					$error_messages = array();
					foreach ( $errors as $error ) {
						$error_messages[] = $error->comment_content;
					}

					$helper_text .= ': ' . implode( '; ', $error_messages );
				}
				break;
			case 'tribe-ea-schedule':
				$classes[] = 'dashicons-backup';
				$helper_text = __( 'Import schedule', 'the-events-calendar' );
				break;
			case 'tribe-ea-pending':
				$classes[] = 'dashicons-clock';
				$helper_text = __( 'Import pending', 'the-events-calendar' );
				break;
			case 'tribe-ea-draft':
				$classes[] = 'dashicons-welcome-write-blog';
				$helper_text = __( 'Import preview', 'the-events-calendar' );
				break;
		}

		$html[] = '<div class="tribe-ea-report-status">';
		$html[] = '<span class="' . implode( ' ', $classes ) . '" title="' . esc_attr( $helper_text ) . '"></span>';
		$html[] = '</div>';

		return $this->render( $html );
	}

	private function render( $html = array(), $glue = "\r\n", $echo = false ) {
		$html = implode( $glue, (array) $html );

		if ( $echo ) {
			echo $html;
		}
		return $html;
	}

	public function column_source( $post ) {
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

		if ( 'scheduled' !== $this->tab->get_slug() ) {
			$html[] = $this->get_status_icon( $record );
		}

		$source_info = $record->get_source_info();
		$source_info['title'] = $source_info['title'];

		if ( $record->is_schedule && Tribe__Events__Aggregator::instance()->is_service_active() ) {
			$html[] = '<p><b><a href="' . get_edit_post_link( $post->ID ) . '">' . esc_html( $source_info['title'] ) . '</a></b></p>';
		} else {
			$html[] = '<p><b>' . esc_html( $source_info['title'] ) . '</b></p>';
		}

		$html[] = '<p>' . esc_html_x( 'via ', 'record via origin', 'the-events-calendar' ) . '<strong>' . $source_info['via']  . '</strong></p>';

		if (
			! empty( $record->meta['keywords'] )
			|| ! empty( $record->meta['start'] )
			|| ! empty( $record->meta['location'] )
			|| ! empty( $record->meta['radius'] )
		) {
			$html[] = '<div class="tribe-view-filters-container">';
			$html[] = '<a href="" class="tribe-view-filters">' . esc_html__( 'View Filters', 'the-events-calendar' ) . '</a>';
			$html[] = '<dl class="tribe-filters">';

			if ( ! empty( $record->meta['keywords'] ) ) {
				$html[] = '<dt>' . __( 'Keywords:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['keywords'] ) . '</dd>';
			}

			if ( ! empty( $record->meta['start'] ) ) {
				$html[] = '<dt>' . __( 'Start:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['start'] ) . '</dd>';
			}

			if ( ! empty( $record->meta['location'] ) ) {
				$html[] = '<dt>' . __( 'Location:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['location'] ) . '</dd>';
			}

			if ( ! empty( $record->meta['radius'] ) ) {
				$html[] = '<dt>' . __( 'Radius:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['radius'] ) . '</dd>';
			}

			$html[] = '</dl></div>';
		}

		return $this->render( $html );
	}

	public function column_imported( $post ) {
		$html = array();
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

		if ( 'scheduled' === $this->tab->get_slug() ) {
			$last_import_error = $record->get_last_import_status( 'error' );

			if ( $last_import_error ) {
				$html[] = '<span class="dashicons dashicons-warning tribe-ea-status-failed" title="' . esc_attr( $last_import_error ) . '"></span>';
			}

			$has_child_record = $record->get_child_record_by_status( 'success', 1 );

			if ( ! $has_child_record ) {
				$html[] = '<i>' . esc_html__( 'Unknown', 'the-events-calendar' ) . '</i>';

				return $this->render( $html );
			}
		}

		$last_import = null;
		$original = $post->post_modified_gmt;
		$time = strtotime( $original );
		$now = current_time( 'timestamp', true );

		$html[] = '<span title="' . esc_attr( $original ) . '">';
		if ( ( $now - $time ) <= DAY_IN_SECONDS ) {
			$diff = human_time_diff( $time, $now );
			if ( ( $now - $time ) > 0 ) {
				$html[] = sprintf( esc_html_x( 'about %s ago', 'human readable time ago', 'the-events-calendar' ), $diff );
			} else {
				$html[] = sprintf( esc_html_x( 'in about %s', 'in human readable time', 'the-events-calendar' ), $diff );
			}
		} else {
			$html[] = date( tribe_get_date_format( true ), $time ) . '<br>' . date( Tribe__Date_Utils::TIMEFORMAT, $time );
		}

		$html[] = '</span>';
		return $this->render( $html );
	}

	public function column_frequency( $post ) {
		if ( 'schedule' === $post->ping_status ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $post->post_content ) );
			if ( ! empty( $frequency->text ) ) {
				$html[] = $frequency->text;
			} else {
				$html[] = esc_html__( 'Invalid Frequency', 'the-events-calendar' );
			}
		} else {
			$html[] = esc_html__( 'One Time', 'the-events-calendar' );
		}

		return $this->render( $html );
	}

	public function column_total( $post ) {
		$html = array();

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );
		$last_imported = $record->get_child_record_by_status( 'success', 1 );

		// is this the scheduled import page?
		if ( $last_imported && $last_imported->have_posts() ) {
			// Fetches the Record Object
			$last_imported = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $last_imported->post->ID );

			$html[] = '<div class="tribe-ea-total">' . number_format_i18n( $record->get_event_count( 'created' ) ) . ' ' . esc_html__( 'all time', 'the-events-calendar' ) . '</div>';

			$html[] = '<label>' . esc_html__( 'Latest Import:', 'the-events-calendar' ) . '</label>';
			$html[] = '<ul class="tribe-ea-raw-list">';
			$created = $last_imported->get_event_count( 'created' );
			$html[] = '<li>' . number_format_i18n( $created ? $created : 0 ) . ' ' . esc_html__( 'new', 'the-events-calendar' ) . '</li>';
			if ( $last_imported_updated = $last_imported->get_event_count( 'updated' ) ) {
				$html[] = '<li>' . number_format_i18n( $last_imported_updated ) . ' ' . esc_html__( 'updated', 'the-events-calendar' ) . '</li>';
			}
			$html[] = '</ul>';
		} elseif ( 'schedule' === $record->type && ! empty( $record->post->post_parent ) ) { // is this a child of a schedule record on History page

			$created = $record->get_event_count( 'created' );
			$html[] = number_format_i18n( $created ? $created : 0 ) . ' ' . esc_html__( 'new', 'the-events-calendar' ) . '<br>';

			if ( ! empty( $record->post->post_parent ) && $updated = $record->get_event_count( 'updated' ) ) {
				$html[] = number_format_i18n( $updated ) . ' ' . esc_html__( 'updated', 'the-events-calendar' ) . '<br>';
			}
		} else { // manual on History page
			$created = $record->get_event_count( 'created' );

			$html[] = number_format_i18n( $created ? $created : 0 ) . ' ' . esc_html__( 'new', 'the-events-calendar' ) . '<br>';

			if ( $updated = $record->get_event_count( 'updated' ) ) {
				$html[] = number_format_i18n( $updated ) . ' ' . esc_html__( 'updated', 'the-events-calendar' ) . '<br>';
			}
		}

		return $this->render( $html, "\n" );
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @param WP_Post $post The current WP_Post object.
	 */
	public function column_cb( $post ) {
		?>
			<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $post->ID ); ?>"><?php
				printf( __( 'Select %s', 'the-events-calendar' ), _draft_or_post_title() );
			?></label>
			<input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="aggregator[records][]" value="<?php echo esc_attr( $post->ID ); ?>" />
			<div class="locked-indicator"></div>
		<?php
	}
}
