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

	public function __construct( $args = array() ) {
		$screen = WP_Screen::get( Tribe__Events__Aggregator__Records::$post_type );

		$default = array(
			'screen' => $screen,
			'tab' => Tribe__Events__Aggregator__Tabs::instance()->get_active(),
		);
		$args = wp_parse_args( $args, $default );

		parent::__construct( $args );

		// Set Curret Tab
		$this->tab = $args['tab'];
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
				$args['ping_status'] = 'scheduled';
				break;

			case 'past':
				$args['post_status'] = 'any';
				$args['post_parent'] = 0;
				break;
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
	 * @since 3.1.0
	 * @access protected
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

		if ( 'scheduled' === $this->tab->get_slug() ) {
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
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			array(
				'id' => 'delete',
				'text' => 'Delete',
			),
			array(
				'id' => 'import',
				'text' => 'Import Now',
			),
		);
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backwards-compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
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
	 *
	 * @return array
	 */
	public function get_columns() {
		$post_type = $this->screen->post_type;

		$columns = array();

		// $columns['cb'] = '<input type="checkbox" />';

		$columns['source'] = esc_html_x( 'Source', 'column name', 'the-events-calendar' );

		switch ( $this->tab->get_slug() ) {
			case 'scheduled':
				$columns['frequency'] = esc_html_x( 'Frequency', 'column name', 'the-events-calendar' );
				$columns['imported'] = esc_html_x( 'Last Import', 'column name', 'the-events-calendar' );
				break;

			case 'past':
				$columns['frequency'] = esc_html_x( 'Type', 'column name', 'the-events-calendar' );
				$columns['imported'] = esc_html_x( 'Imported', 'column name', 'the-events-calendar' );
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
		return apply_filters( 'tribe_ea_manage_record_columns', $columns );
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
				$classes[] = 'dashicons-marker';
				break;
			case 'tribe-ea-failed':
				$classes[] = 'dashicons-warning';
				break;
			case 'tribe-ea-scheduled':
				$classes[] = 'dashicons-backup';
				break;
			case 'tribe-ea-pending':
				$classes[] = 'dashicons-image-rotate';
				break;
			case 'draft':
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
		$html[] = $this->get_status_icon( $post );
		$html[] = '<p><strong>' . $post->post_mime_type . '</strong></p>';
		$html[] = '<p>' . esc_html__( 'Hash:', 'the-events-calendar' ) . ' <code>' . esc_html( $post->post_title ) . '</code></p>';

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
		if ( 'scheduled' === $post->ping_status ) {
			$frequency = get_post_meta( $post->ID, '_tribe_ea_frequency', true );
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $frequency ) );
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
		return $post->comment_count;
	}

}