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

		$query = new WP_Query( $args );

		$this->items = $query->posts;

		$this->set_pagination_args( array(
			'total_items' => $query->found_posts,
			'per_page' => $query->query_vars['posts_per_page'],
		) );
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

		$field = (object) array();
		$field->label = esc_html__( 'Bulk Actions', 'the-events-calendar' );
		$field->placeholder = esc_attr__( 'Bulk Actions', 'the-events-calendar' );
		$field->options = $this->get_bulk_actions();

		?>
			<label class="screen-reader-text" for="tribe-ea-field-origin"><?php echo $field->label; ?></label>
			<input
				name="aggregator[origin]"
				type="hidden"
				id="tribe-ea-field-origin"
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
				$columns['cb'] = '<input type="checkbox" />';
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

	private function get_status_icon( $post ) {
		$classes[] = 'dashicons';
		if ( false !== strpos( $post->post_status, 'tribe-ea-' ) ) {
			$classes[] = str_replace( 'tribe-ea-', 'tribe-ea-status-', $post->post_status );
		} else {
			$classes[] = 'tribe-ea-status-' . $post->post_status;
		}

		switch ( $post->post_status ) {
			case 'tribe-ea-success':
				$classes[] = 'dashicons-yes';
				break;
			case 'tribe-ea-failed':
				$classes[] = 'dashicons-warning';
				break;
			case 'tribe-ea-schedule':
				$classes[] = 'dashicons-backup';
				break;
			case 'tribe-ea-pending':
				$classes[] = 'dashicons-clock';
				break;
			case 'tribe-ea-draft':
				$classes[] = 'dashicons-welcome-write-blog';
				break;
		}

		$html[] = '<div class="tribe-ea-report-status">';
		$html[] = '<span class="' . implode( ' ', $classes ) . '"></span>';
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
			$html[] = $this->get_status_icon( $post );
		}

		$html[] = '<p>' . esc_html_x( 'via ', 'record via origin', 'the-events-calendar' ) . '<strong>' . $record->get_label() . '</strong></p>';
		if ( 'ea/ics' === $post->post_mime_type || 'ea/csv' === $post->post_mime_type ) {
			$file_path = get_attached_file( absint( $record->meta['file'] ) );
			$filename = basename( $file_path );
			$html[] = '<p>' . esc_html__( 'Source:', 'the-events-calendar' ) . ' <code>' . esc_html( $filename ) . '</code></p>';
		} else {
			$html[] = '<p>' . esc_html__( 'Source:', 'the-events-calendar' ) . ' <code>' . esc_html( $record->meta['source'] ) . '</code></p>';
		}

		return $this->render( $html );
	}

	public function column_imported( $post ) {
		$last_import = null;
		$original = $post->post_modified;
		$time = strtotime( $original );
		$now = time();

		$html[] = '<span title="' . esc_attr( $original ) . '">';
		if ( ( $now - $time ) <= DAY_IN_SECONDS ) {
			$diff = human_time_diff( $time, $now );
			if ( ( $now - $time ) > 0 ) {
				$html[] = sprintf( esc_html_x( 'about %s ago', 'human readable time ago', 'the-events-calendar' ), $diff );
			} else {
				$html[] = sprintf( esc_html_x( 'in about %s', 'in human readable time', 'the-events-calendar' ), $diff );
			}
		} else {
			$html[] = date( Tribe__Date_Utils::DATEONLYFORMAT, $time ) . '<br>' . date( Tribe__Date_Utils::TIMEFORMAT, $time );
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
		$html[] = esc_html__( 'All Time: ', 'the-events-calendar' ) . $post->comment_count;

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );
		$last_imported = $record->get_child_record_by_status( 'success', 1 );
		if ( $last_imported && $last_imported->have_posts() ) {
			$html[] = esc_html__( 'Last Import: ', 'the-events-calendar' ) . $last_imported->post->comment_count;
		}

		return $this->render( $html, '<br>' );
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
			<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php
				printf( __( 'Select %s' ), _draft_or_post_title() );
			?></label>
			<input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="post[]" value="<?php the_ID(); ?>" />
			<div class="locked-indicator"></div>
		<?php
	}

}
