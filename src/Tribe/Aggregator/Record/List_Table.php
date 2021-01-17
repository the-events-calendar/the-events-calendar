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

	public function __construct( $args = [] ) {
		$screen = WP_Screen::get( Tribe__Events__Aggregator__Records::$post_type );

		$default = [
			'screen' => $screen,
			'tab'    => Tribe__Events__Aggregator__Tabs::instance()->get_active(),
		];
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

		$args = [
			'post_type' => $this->screen->post_type,
			'orderby'   => 'modified',
			'order'     => $order,
			'paged'     => absint( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 ),
		];

		$status = Tribe__Events__Aggregator__Records::$status;

		switch ( $this->tab->get_slug() ) {
			case 'scheduled':
				$args['ping_status'] = 'schedule';
				$args['post_status'] = $status->schedule;
				break;

			case 'history':
				$args['post_status'] = [
					$status->success,
					$status->failed,
					$status->pending,
				];
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
		$search_term            = tribe_get_request_var( 's' );
		if ( 'scheduled' === $this->tab->get_slug() && ! empty( $search_term ) ) {
			// nonce check if search form submitted.
			$nonce = isset( $_POST['s'] ) && isset( $_POST['aggregator']['nonce'] ) ? sanitize_text_field( $_POST['aggregator']['nonce'] ) :  '';
			if ( isset( $_GET['s'] ) || wp_verify_nonce( $nonce, 'aggregator_' . $this->tab->get_slug() . '_request' ) ) {
				$search_term        = filter_var( $search_term, FILTER_VALIDATE_URL )
					? esc_url_raw( $search_term )
					: sanitize_text_field( $search_term );
				$args['meta_query'] = [
					'relation' => 'OR',
					[
						'key'     => '_tribe_aggregator_source_name',
						'value'   => $search_term,
						'compare' => 'LIKE',
					],
					[
						'key'     => '_tribe_aggregator_import_name',
						'value'   => $search_term,
						'compare' => 'LIKE',
					],
					[
						'key'     => '_tribe_aggregator_source',
						'value'   => $search_term,
						'compare' => 'LIKE',
					],
				];
			}
		}

		$query = new WP_Query( $args );

		$this->items = $query->posts;

		$this->set_pagination_args(
			[
				'total_items' => $query->found_posts,
				'per_page'    => $query->query_vars['posts_per_page'],
			]
		);
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
		return [
			'imported' => 'imported',
		];
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

		$field        = (object) [];
		$field->label = esc_html__( 'Filter By Origin', 'the-events-calendar' );
		$field->placeholder = esc_attr__( 'Filter By Origin', 'the-events-calendar' );
		$field->options = tribe( 'events-aggregator.main' )->api( 'origins' )->get();

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
			$field        = (object) [];
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
		return [
			[
				'id'   => 'delete',
				'text' => 'Delete',
			],
		];
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
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return '';
		}

		$field        = (object) [];
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
		$views        = [];
		$given_origin = isset( $_GET['origin'] ) ? $_GET['origin'] : false;

		$type = [ 'schedule' ];
		if ( 'history' === $this->tab->get_slug() ) {
			$type[] = 'manual';
		}

		$status = [];
		if ( 'history' === $this->tab->get_slug() ) {
			$status[] = 'success';
			$status[] = 'failed';
			$status[] = 'pending';
		} else {
			$status[] = 'schedule';
		}

		$origins = Tribe__Events__Aggregator__Records::instance()->count_by_origin( $type, $status );

		$total = array_sum( $origins );
		$link = $this->page->get_url( [ 'tab' => $this->tab->get_slug() ] );
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

			if ( null === $origin_instance ) {
				$debug_message = sprintf(
					'The aggregator origin "%s" contains records, but is not supported and was skipped in the counts.',
					$origin
				);
				tribe( 'logger' )->log_debug( $debug_message, 'aggregator' );

				continue;
			}

			$link = $this->page->get_url( [ 'tab' => $this->tab->get_slug(), 'origin' => $origin ] );
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
		$columns = [];

		switch ( $this->tab->get_slug() ) {
			case 'scheduled':
				// We only need the checkbox when the EA service is active because there aren't any bulk
				// actions when EA is disabled
				if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
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
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return '';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		$actions = [];

		if ( current_user_can( $post_type_object->cap->edit_post, $post->ID ) ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				__( 'Edit', 'the-events-calendar' )
			);

			$args = [
				'tab'    => $this->tab->get_slug(),
				'action' => 'run-import',
				'ids'    => absint( $post->ID ),
				'nonce'  => wp_create_nonce( 'aggregator_' . $this->tab->get_slug() . '_request' ),
			];

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

	/**
	 * Returns the status icon HTML
	 *
	 * @param Tribe__Events__Aggregator__Record__Abstract $record
	 *
	 * @return array|string
	 */
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
					$error_messages = [];
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

	private function render( $html = [], $glue = "\r\n", $echo = false ) {
		$html = implode( $glue, (array) $html );

		if ( $echo ) {
			echo $html;
		}
		return $html;
	}

	public function column_source( $post ) {
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

		if ( tribe_is_error( $record ) ) {
			return '';
		}

		if ( 'scheduled' !== $this->tab->get_slug() ) {
			$html[] = $this->get_status_icon( $record );
		}

		$source_info = $record->get_source_info();

		if ( is_array( $source_info['title'] ) ) {
			$source_info['title'] = implode( ', ', $source_info['title'] );
		}

		$title = $source_info['title'];
		if ( ! empty( $record->meta['import_name'] ) ) {
			$title = $record->meta['import_name'];
		}

		if ( $record->is_schedule && tribe( 'events-aggregator.main' )->is_service_active() ) {
			$html[] = '<p><b><a href="' . get_edit_post_link( $post->ID ) . '">' . esc_html( $title ) . '</a></b></p>';
		} else {
			$html[] = '<p><b>' . esc_html( $title ) . '</b></p>';
		}

		$html[] = '<p>' . esc_html_x( 'via ', 'record via origin', 'the-events-calendar' ) . '<strong>' . $source_info['via']  . '</strong></p>';

		$html[] = '<div class="tribe-view-links-container" style="display:flex; flex-direction:row">';
		if ( 'scheduled' === $this->tab->get_slug() ) {
			$filter_link = admin_url( "edit.php?post_type=tribe_events&aggregator_record={$record->id}" );
			$html[]      = '<div class="tribe-view-events-container">';
			$html[]      = '<a href="' . esc_url( $filter_link ) . '" class="tribe-view-events">' . esc_html__( 'View Events', 'the-events-calendar' ) . '</a>';
			$html[]      = '</div>';
		}

		if (
			! empty( $record->meta['keywords'] )
			|| ! empty( $record->meta['start'] )
			|| ! empty( $record->meta['location'] )
			|| ! empty( $record->meta['radius'] )
		) {
			$html[] = '<div class="tribe-view-filters-container">';
			if ( 'scheduled' === $this->tab->get_slug() ) {
				$html[] = '&nbsp;|&nbsp;';
			}
			$html[] = '<a href="" class="tribe-view-filters">' . esc_html__( 'View Filters', 'the-events-calendar' ) . '</a>';
			$html[] = '<dl class="tribe-filters">';

			if ( ! empty( $record->meta['keywords'] ) ) {
				$html[] = '<dt>' . __( 'Keywords:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['keywords'] ) . '</dd>';
			}

			if ( ! empty( $record->meta['start'] ) ) {
				$start = $record->meta['start'];
				if ( is_numeric( $start ) ) {
					$start = date( Tribe__Date_Utils::DATEONLYFORMAT, $start );
				}

				$html[] = '<dt>' . __( 'Start:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $start ) . '</dd>';
			}

			if ( ! empty( $record->meta['location'] ) ) {
				$html[] = '<dt>' . __( 'Location:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['location'] ) . '</dd>';
			}

			if ( ! empty( $record->meta['radius'] ) ) {
				$html[] = '<dt>' . __( 'Radius:', 'the-events-calendar' ) . '</dt><dd>' . esc_html( $record->meta['radius'] ) . '</dd>';
			}

			$html[] = '</dl></div>';
		}

		$html[] = '</div>';

		/**
		 * Customize the Events > Import > History > Source column HTML.
		 *
		 * @since 5.1.1
		 *
		 * @param array                                       $html   List of HTML details.
		 * @param Tribe__Events__Aggregator__Record__Abstract $record The record object.
		 */
		$html = apply_filters( 'tribe_aggregator_manage_record_column_source_html', $html, $record );

		return $this->render( $html );
	}

	public function column_imported( $post ) {
		$html   = [];
		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

		if ( tribe_is_error( $record ) ) {
			return '';
		}

		if ( 'scheduled' === $this->tab->get_slug() ) {
			$last_import_error = $record->get_last_import_status( 'error' );
			$status = 'success';

			if ( $last_import_error ) {
				$html[] = '<span class="dashicons dashicons-warning tribe-ea-status-failed" title="' . esc_attr( $last_import_error ) . '"></span>';
				$status = 'failed';
			}

			$has_child_record = $record->get_child_record_by_status( $status, 1 );

			if ( ! $has_child_record ) {
				$html[] = '<i>' . esc_html__( 'Unknown', 'the-events-calendar' ) . '</i>';

				return $this->render( $html );
			}
		}

		$last_import = null;
		$original = $post->post_modified_gmt;
		$time = strtotime( $original );
		$now = current_time( 'timestamp', true );

		$retry_time = false;

		if ( ! empty( $last_import_error ) ) {
			$retry_time = $record->get_retry_time();
		}

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

		if ( $retry_time ) {
			$html[] = '<div title="' . esc_attr( $original ) . '-retry">';
			if ( ( $retry_time - $now ) <= DAY_IN_SECONDS ) {
				$diff   = human_time_diff( $retry_time, $now );
				$html[] = sprintf( esc_html_x( 'retrying in about %s', 'in human readable time', 'the-events-calendar' ), $diff );
			} else {
				$html[] = sprintf(
					esc_html_x( 'retrying at %s', 'when the retry will happen, a date', 'the-events-calendar' ),
					date( tribe_get_date_format( true ), $retry_time )
				);
			}
			$html[] = '</div>';
		}

		return $this->render( $html );
	}

	public function column_frequency( $post ) {
		if ( 'schedule' === $post->ping_status ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( [ 'id' => $post->post_content ] );
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
		$html = [];

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

		if ( tribe_is_error( $record ) ) {
			return '';
		}

		$last_imported = $record->get_child_record_by_status( 'success', 1 );

		// is this the scheduled import page?
		if ( $last_imported && $last_imported->have_posts() ) {
			// Fetches the Record Object
			$last_imported = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $last_imported->post->ID );

			if ( tribe_is_error( $last_imported ) ) {
				return '';
			}

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

	/**
	 * Displays the pagination.
	 *
	 * @since 5.3.0
	 * @access protected
	 *
	 * @param string $which Equal to NULL, 'top' or 'bottom'.
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$output = '<span class="displaying-num">' . sprintf(
			/* translators: %s: Number of items. */
			_n( '%s item', '%s items', $total_items, 'the-events-calendar' ),
			number_format_i18n( $total_items )
		) . '</span>';

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : admin_url( $wp->request );
		$current_url = set_url_scheme( $request_uri, 'relative' );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$search_term = tribe_get_request_var( 's' );
		if ( ! empty( $search_term ) ) {
			$search_term = filter_var( $search_term, FILTER_VALIDATE_URL )
				? esc_url_raw( $search_term )
				: sanitize_text_field( $search_term );
			$current_url = add_query_arg( 's', $search_term, $current_url );
		}

		$page_links = [];

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = 1 === $current || 2 === $current;
		$disable_last  = $total_pages === $current || $total_pages - 1 === $current;
		$disable_prev  = 1 === $current;
		$disable_next  = $total_pages === $current;

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page', 'the-events-calendar' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				__( 'Previous page', 'the-events-calendar' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page', 'the-events-calendar' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page', 'the-events-calendar' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = $total_pages_before . sprintf(
			/* translators: 1: Current page, 2: Total pages. */
			_x( '%1$s of %2$s', 'paging', 'the-events-calendar' ),
			$html_current_page,
			$html_total_pages
		) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				__( 'Next page', 'the-events-calendar' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page', 'the-events-calendar' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		?>
		<div class='tablenav-pages<?php echo esc_attr( $page_class ); ?>'>
			<?php echo $output; ?>
		</div>
		<?php
	}
}
