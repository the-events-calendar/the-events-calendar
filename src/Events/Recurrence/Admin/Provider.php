<?php
/**
 * Owns occurrence identity in the admin, including when Pro is deactivated.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Admin
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Admin;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Events__Main as TEC;
use WP_Post;

/** Common presentation, independent of the plugin supplying recurrence authoring. @since TBD */
class Provider extends Service_Provider {
	/** @var bool Whether the list context has been rendered. @since TBD */
	private bool $rendered = false;

	/** Registers the UI independently of Free's dates-only editor providers. @since TBD @return void */
	public function register(): void {
		foreach ( [ Presentation::class, Pro_Status::class, List_Query::class, Editor::class ] as $service ) {
			if ( ! $this->container->isBound( $service ) ) {
				$this->container->singleton( $service );
			}
		}
		$this->container->make( List_Query::class )->register();
		$this->container->make( Editor::class )->register();
		add_filter( 'manage_tribe_events_posts_columns', [ $this, 'columns' ], 100 );
		add_filter( 'manage_edit-tribe_events_sortable_columns', [ $this, 'sortable' ], 100 );
		add_action( 'manage_tribe_events_posts_custom_column', [ $this, 'column' ], 100, 2 );
		add_filter( 'post_row_actions', [ $this, 'row_actions' ], 100, 2 );
		add_filter( 'views_edit-tribe_events', [ $this, 'views' ], 100 );
		add_action( 'restrict_manage_posts', [ $this, 'filters' ], 20, 2 );
		add_filter( 'wp_list_table_show_post_checkbox', [ $this, 'checkbox' ], 10, 2 );
		add_filter( 'bulk_actions-edit-tribe_events', [ $this, 'bulk_actions' ], 100 );
		add_filter( 'disable_months_dropdown', [ $this, 'hide_months' ], 10, 2 );
		add_filter( 'get_edit_post_link', [ $this, 'event_edit_link' ], 100, 3 );
		add_filter( 'ngettext', [ $this, 'item_label' ], 10, 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
		add_action( 'clean_post_cache', [ $this->container->make( Presentation::class ), 'reset' ] );
		add_action( 'quick_edit_custom_box', [ $this, 'inline_context' ], 10, 2 );
		add_filter( 'post_column_taxonomy_links', [ $this, 'taxonomy_links' ] );
		add_filter( 'screen_settings', [ $this, 'screen_settings' ], 10, 2 );
		add_action( 'check_admin_referer', [ $this, 'save_screen_settings' ], 10, 2 );
	}

	/** Removes all owned callbacks and per-request state. @since TBD @return void */
	public function unregister(): void {
		$this->container->make( List_Query::class )->unregister();
		$this->container->make( Editor::class )->unregister();
		remove_filter( 'manage_tribe_events_posts_columns', [ $this, 'columns' ], 100 );
		remove_filter( 'manage_edit-tribe_events_sortable_columns', [ $this, 'sortable' ], 100 );
		remove_action( 'manage_tribe_events_posts_custom_column', [ $this, 'column' ], 100 );
		remove_filter( 'post_row_actions', [ $this, 'row_actions' ], 100 );
		remove_filter( 'views_edit-tribe_events', [ $this, 'views' ], 100 );
		remove_action( 'restrict_manage_posts', [ $this, 'filters' ], 20 );
		remove_filter( 'wp_list_table_show_post_checkbox', [ $this, 'checkbox' ] );
		remove_filter( 'bulk_actions-edit-tribe_events', [ $this, 'bulk_actions' ], 100 );
		remove_filter( 'disable_months_dropdown', [ $this, 'hide_months' ] );
		remove_filter( 'get_edit_post_link', [ $this, 'event_edit_link' ], 100 );
		remove_filter( 'ngettext', [ $this, 'item_label' ] );
		remove_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
		remove_action( 'clean_post_cache', [ $this->container->make( Presentation::class ), 'reset' ] );
		remove_action( 'quick_edit_custom_box', [ $this, 'inline_context' ] );
		remove_filter( 'post_column_taxonomy_links', [ $this, 'taxonomy_links' ] );
		remove_filter( 'screen_settings', [ $this, 'screen_settings' ] );
		remove_action( 'check_admin_referer', [ $this, 'save_screen_settings' ] );
		remove_filter( 'wp_redirect', [ $this, 'screen_settings_redirect' ] );
		$this->container->make( Presentation::class )->reset();
		$this->rendered = false;
	}

	/** Whether the current screen is the event list. @since TBD @return bool */
	public static function is_list(): bool {
		if ( wp_doing_ajax() && 'inline-save' === tribe_get_request_var( 'action' ) && TEC::POSTTYPE === tribe_get_request_var( 'post_type' ) ) {
			return true;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && 'edit' === $screen->base && TEC::POSTTYPE === $screen->post_type;
	}

	/** The explicit link context or saved Screen Options preference. @since TBD @return string */
	public static function view(): string {
		$view = tribe_get_request_var( 'tec_events_view', '' );
		if ( in_array( $view, [ 'events', 'occurrences' ], true ) ) {
			return $view;
		}
		return '0' === get_user_option( 'tec_events_display_occurrences' ) ? 'events' : 'occurrences';
	}

	/**
	 * Adds the display preference to WordPress's existing Screen Options form.
	 *
	 * @since TBD
	 * @param string     $settings Existing screen settings.
	 * @param \WP_Screen $screen   Current screen.
	 * @return string Settings with the event display checkbox.
	 */
	public function screen_settings( string $settings, \WP_Screen $screen ): string {
		if ( 'edit' !== $screen->base || TEC::POSTTYPE !== $screen->post_type ) {
			return $settings;
		}
		return $settings . '<fieldset class="tec-occurrence-admin__screen-options"><legend>' . esc_html__( 'Events', 'the-events-calendar' ) . '</legend>'
			. '<input type="hidden" name="tec_occurrence_screen_options" value="1" />'
			. '<label for="tec-display-occurrences"><input type="checkbox" id="tec-display-occurrences" name="tec_display_occurrences" value="1" ' . checked( 'occurrences', self::view(), false ) . ' /> '
			. esc_html__( 'Display occurrences', 'the-events-calendar' ) . '</label>'
			. '<p class="description">' . esc_html__( 'Show each scheduled date as a row. Turn off to show one row per event, including unscheduled drafts, and use event bulk actions.', 'the-events-calendar' ) . '</p></fieldset>';
	}

	/**
	 * Saves alongside core's Screen Options submission after its nonce check.
	 * Core processes this form before admin_init/current_screen, so the nonce action
	 * is the seam that retains its native per-page validation, save and redirect.
	 *
	 * @since TBD
	 * @param string    $action Nonce action checked by WordPress.
	 * @param int|false $result Nonce verification result.
	 * @return void
	 */
	public function save_screen_settings( string $action, $result ): void {
		// phpcs:disable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Core verified the nonce; inputs are compared to fixed values or range-checked integers.
		global $pagenow;
		if ( 'screen-options-nonce' !== $action || ! $result || 'edit.php' !== $pagenow
			|| TEC::POSTTYPE !== ( $_GET['post_type'] ?? '' ) || '1' !== ( $_POST['tec_occurrence_screen_options'] ?? '' ) ) {
			return;
		}
		$option = $_POST['wp_screen_options']['option'] ?? '';
		$value  = $_POST['wp_screen_options']['value'] ?? '';
		$type   = get_post_type_object( TEC::POSTTYPE );
		if ( 'edit_tribe_events_per_page' !== $option || ! is_scalar( $value ) || (int) $value < 1 || (int) $value > 999
			|| ! $type || ! current_user_can( $type->cap->edit_posts ) ) {
			return;
		}
		update_user_option( get_current_user_id(), 'tec_events_display_occurrences', '1' === ( $_POST['tec_display_occurrences'] ?? '' ) ? '1' : '0' );
		add_filter( 'wp_redirect', [ $this, 'screen_settings_redirect' ] );
		// phpcs:enable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Replaces a stale explicit mode in core's Screen Options return URL.
	 *
	 * @since TBD
	 * @param string $url Core's redirect, with pagination already reset.
	 * @return string URL honoring the preference just saved.
	 */
	public function screen_settings_redirect( string $url ): string {
		remove_filter( 'wp_redirect', [ $this, 'screen_settings_redirect' ] );
		return add_query_arg( 'tec_events_view', '0' === get_user_option( 'tec_events_display_occurrences' ) ? 'events' : 'occurrences', $url );
	}

	/** The requested date range, defaulting to ongoing and upcoming dates. @since TBD @return string */
	public static function range(): string {
		$range = tribe_get_request_var( 'tec_dates', 'upcoming' );
		return in_array( $range, [ 'upcoming', 'past', 'all' ], true ) ? $range : 'upcoming';
	}

	/**
	 * Builds a list URL retaining supported search and filter inputs, resetting pagination.
	 *
	 * @since TBD
	 * @param array $overrides Values to replace; false removes a value.
	 * @return string Unescaped URL.
	 */
	public static function url( array $overrides = [] ): string {
		$args = [
			'post_type'       => TEC::POSTTYPE,
			'tec_events_view' => self::view(),
			'tec_dates'       => self::range(),
		];
		foreach ( [ 's', 'post_status', 'author', 'tribe_events_cat', 'tag', 'm', 'orderby', 'order', 'tec_event' ] as $key ) {
			$value = tribe_get_request_var( $key, '' );
			if ( is_scalar( $value ) && '' !== $value ) {
				$args[ $key ] = sanitize_text_field( (string) $value );
			}
		}
		$args = array_map(
			static function ( $value ) {
				return false === $value ? false : rawurlencode( (string) $value );
			},
			array_merge( $args, $overrides )
		);
		return add_query_arg( $args, admin_url( 'edit.php' ) );
	}

	/**
	 * Uses distinct column keys so Pro's parent-date renderer cannot overwrite occurrence dates.
	 * Pro's Series column and cache hooks remain registered and receive the original row identity.
	 *
	 * @since TBD
	 * @param array $columns Existing columns.
	 * @return array Shared columns with Pro additions preserved.
	 */
	public function columns( array $columns ): array {
		// Own the author destination so selecting an author retains this view's date range.
		if ( isset( $columns['author'] ) ) {
			$keys = array_keys( $columns );
			$keys[ array_search( 'author', $keys, true ) ] = 'tec-author';
			$columns                                       = array_combine( $keys, array_values( $columns ) );
		}
		unset( $columns['start-date'], $columns['end-date'] );
		if ( 'occurrences' === self::view() ) {
			unset( $columns['cb'] );
		}
		$columns['tec-start-date'] = __( 'Start Date', 'the-events-calendar' );
		$columns['tec-end-date']   = __( 'End Date', 'the-events-calendar' );
		return $columns;
	}

	/**
	 * Maps the new date columns to the existing sortable query keys.
	 *
	 * @since TBD
	 * @param array $columns Sortable columns.
	 * @return array Updated mapping.
	 */
	public function sortable( array $columns ): array {
		$columns['tec-author'] = 'author';
		unset( $columns['start-date'], $columns['end-date'] );
		$columns['tec-start-date'] = 'start-date';
		$columns['tec-end-date']   = 'end-date';
		return $columns;
	}

	/**
	 * Renders only the Free-owned columns.
	 *
	 * @since TBD
	 * @param string $column Column key.
	 * @param int    $id     Row identity.
	 * @return void
	 */
	public function column( string $column, int $id ): void {
		if ( 'tec-author' === $column ) {
			$author = get_userdata( get_post_field( 'post_author', $id ) );
			if ( $author ) {
				echo '<a href="' . esc_url( self::url( [ 'author' => $author->ID ] ) ) . '">' . esc_html( $author->display_name ) . '</a>';
			}
			return;
		}
		if ( ! in_array( $column, [ 'tec-start-date', 'tec-end-date' ], true ) ) {
			return;
		}
		$data = $this->container->make( Presentation::class )->get( $id );
		echo esc_html( $data[ 'tec-end-date' === $column ? 'end' : 'start' ] );
	}

	/**
	 * Shows row identity outside hover-only actions and labels each editing destination.
	 *
	 * @since TBD
	 * @param array   $actions Existing supported actions.
	 * @param WP_Post $post    Row post.
	 * @return array Explicit editing destinations.
	 */
	public function row_actions( array $actions, WP_Post $post ): array {
		if ( ! self::is_list() || TEC::POSTTYPE !== $post->post_type ) {
			return $actions;
		}
		$data = $this->container->make( Presentation::class )->get( $post->ID );
		if ( 'occurrences' === self::view() ) {
			unset( $actions['inline hide-if-no-js'] );
		}
		echo '<div class="tec-occurrence-admin__identity">';
		echo esc_html( $data['isOccurrence'] ? __( 'Occurrence', 'the-events-calendar' ) : __( 'Event', 'the-events-calendar' ) );
		echo ' <span class="tec-occurrence-admin__badge tec-occurrence-admin__badge--' . esc_attr( $data['schedule'] ) . '">' . esc_html( $data['scheduleLabel'] );
		if ( $data['locked'] ) {
			$status     = $this->container->make( Pro_Status::class )->get( true );
			$tooltip_id = 'tec-recurrence-lock-' . $post->ID;
			echo ' <button type="button" class="tec-occurrence-admin__lock" aria-label="' . esc_attr__( 'Why recurrence editing is locked', 'the-events-calendar' ) . '" aria-describedby="' . esc_attr( $tooltip_id ) . '"><span class="dashicons dashicons-lock" aria-hidden="true"></span></button>';
			echo '<span class="tec-occurrence-admin__tooltip" role="tooltip" id="' . esc_attr( $tooltip_id ) . '">' . esc_html( $status['title'] . '. ' . $status['message'] ) . '</span>';
		}
		echo '</span>';
		if ( $data['isOccurrence'] ) {
			echo '<span class="tec-occurrence-admin__parent">' . esc_html__( 'Event:', 'the-events-calendar' ) . ' ';
			if ( $data['parentEditLink'] ) {
				echo '<a href="' . esc_url( $data['parentEditLink'] ) . '">' . esc_html( $data['eventTitle'] ) . '</a>';
			} else {
				echo esc_html( $data['eventTitle'] );
			}
			echo '</span>';
		}
		echo '</div>';
		if ( isset( $actions['edit'] ) && $data['isOccurrence'] ) {
			$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'Edit occurrence', 'the-events-calendar' ) . '</a>';
		}
		if ( $data['isOccurrence'] && $data['parentEditLink'] ) {
			$actions['tec-edit-event'] = '<a href="' . esc_url( $data['parentEditLink'] ) . '">' . esc_html__( 'Edit event details', 'the-events-calendar' ) . '</a>';
		}
		if ( 'single' !== $data['schedule'] ) {
			$actions['tec-dates'] = '<a href="' . esc_url( $data['datesLink'] ) . '">' . esc_html__( 'View all dates', 'the-events-calendar' ) . '</a>';
		}
		if ( $data['locked'] ) {
			$status = $this->container->make( Pro_Status::class )->get( true );
			if ( $status['url'] ) {
				$actions['tec-pro-recovery'] = '<a href="' . esc_url( $status['url'] ) . '" title="' . esc_attr( $status['title'] ) . '">' . esc_html( $status['label'] ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * Carries the parent-management view through WordPress's Quick Edit row refresh.
	 *
	 * @since TBD
	 * @param string $column    Current custom column.
	 * @param string $post_type Edited post type.
	 * @return void
	 */
	public function inline_context( string $column, string $post_type ): void {
		if ( 'tec-start-date' === $column && TEC::POSTTYPE === $post_type ) {
			echo '<input type="hidden" name="tec_events_view" value="' . esc_attr( self::view() ) . '" />';
		}
	}

	/**
	 * Preserves the selected view and date range when following WordPress taxonomy links.
	 *
	 * @since TBD
	 * @param array $links Existing escaped term links.
	 * @return array Links with the list's supported filters.
	 */
	public function taxonomy_links( array $links ): array {
		if ( ! self::is_list() ) {
			return $links;
		}
		return array_map(
			static function ( $link ) {
				return preg_replace_callback(
					'/href="([^"]+)"/',
					static function ( $attribute ) {
						parse_str( wp_parse_url( html_entity_decode( $attribute[1], ENT_QUOTES ), PHP_URL_QUERY ) ?: '', $args );
						return 'href="' . esc_url( self::url( $args ) ) . '"';
					},
					$link
				);
			},
			$links
		);
	}

	/**
	 * Renders parent context and builds counts for the selected record type.
	 *
	 * @since TBD
	 * @param array $views Existing WordPress publication-status links.
	 * @return array Status links.
	 */
	public function views( array $views ): array {
		if ( ! self::is_list() ) {
			return $views;
		}
		$occurrences = 'occurrences' === self::view();
		if ( ! $this->rendered ) {
			$this->rendered = true;
			global $wp_query;
			$posts = $wp_query instanceof \WP_Query ? $wp_query->posts : [];
			$this->container->make( Presentation::class )->prime( $posts );
			$parent = absint( tribe_get_request_var( 'tec_event', 0 ) );
			if ( $occurrences && $parent && current_user_can( 'edit_post', $parent ) ) {
				echo '<p>' . esc_html( sprintf( /* translators: %s: the parent event title. */ __( 'Dates for: %s', 'the-events-calendar' ), get_the_title( $parent ) ) ) . ' <a href="' . esc_url( self::url( [ 'tec_event' => false ] ) ) . '">' . esc_html__( 'Show all events', 'the-events-calendar' ) . '</a></p>';
			}
		}
		$views  = [];
		$counts = $this->container->make( List_Query::class )->counts();
		$states = get_post_stati( [], 'objects' );
		foreach ( array_merge( [ 'all' => $counts['all'] ?? 0 ], $counts ) as $key => $count ) {
			if ( ! $count && 'all' !== $key ) {
				continue;
			}
			$label         = 'all' === $key ? __( 'All statuses', 'the-events-calendar' ) : $states[ $key ]->label;
			$current       = tribe_get_request_var( 'post_status', 'all' ) === $key;
			$views[ $key ] = '<a href="' . esc_url( self::url( [ 'post_status' => 'all' === $key ? false : $key ] ) ) . '"' . ( $current ? ' class="current" aria-current="page"' : '' ) . '>' . esc_html( $label ) . ' <span class="count">(' . esc_html( number_format_i18n( $count ) ) . ')</span></a>';
		}
		return $views;
	}

	/**
	 * Retains custom filters on submission and provides a date-range selector.
	 *
	 * @since TBD
	 * @param string $post_type Current post type.
	 * @param string $which     Top or bottom navigation.
	 * @return void
	 */
	public function filters( string $post_type, string $which ): void {
		if ( TEC::POSTTYPE !== $post_type || 'top' !== $which ) {
			return;
		}
		echo '<input type="hidden" name="tec_events_view" value="' . esc_attr( self::view() ) . '" />';
		echo '<label class="screen-reader-text" for="tec-occurrence-category">' . esc_html__( 'Event category', 'the-events-calendar' ) . '</label>';
		wp_dropdown_categories(
			[
				'taxonomy'        => TEC::TAXONOMY,
				'name'            => TEC::TAXONOMY,
				'id'              => 'tec-occurrence-category',
				'value_field'     => 'slug',
				'selected'        => sanitize_title( tribe_get_request_var( TEC::TAXONOMY, '' ) ),
				'show_option_all' => __( 'All event categories', 'the-events-calendar' ),
				'hide_empty'      => false,
				'hierarchical'    => true,
			]
		);
		if ( 'occurrences' !== self::view() ) {
			return;
		}
		echo '<input type="hidden" name="tec_event" value="' . absint( tribe_get_request_var( 'tec_event', 0 ) ) . '" />';
		echo '<label class="screen-reader-text" for="tec-occurrence-range">' . esc_html__( 'Occurrence dates', 'the-events-calendar' ) . '</label><select id="tec-occurrence-range" name="tec_dates">';
		foreach ( [
			'upcoming' => __( 'Ongoing and upcoming', 'the-events-calendar' ),
			'past'     => __( 'Past', 'the-events-calendar' ),
			'all'      => __( 'All dates', 'the-events-calendar' ),
		] as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( self::range(), $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Keeps bulk operations on the event view; occurrence mutations require explicit scope.
	 *
	 * @since TBD
	 * @param array $actions Registered bulk actions.
	 * @return array Available actions.
	 */
	public function bulk_actions( array $actions ): array {
		return 'occurrences' === self::view() ? [] : $actions;
	}

	/**
	 * The native month selector filters publication dates, not occurrence dates.
	 *
	 * @since TBD
	 * @param bool   $hide      Existing decision.
	 * @param string $post_type List post type.
	 * @return bool Whether to hide the publication-month selector.
	 */
	public function hide_months( bool $hide, string $post_type ): bool {
		return TEC::POSTTYPE === $post_type && 'occurrences' === self::view() ? true : $hide;
	}

	/**
	 * Hides selection when this view has no supported bulk operations.
	 *
	 * @since TBD
	 * @param bool    $show Original permission result.
	 * @param WP_Post $post Row post.
	 * @return bool Whether to show a checkbox.
	 */
	public function checkbox( bool $show, WP_Post $post ): bool {
		return self::is_list() && 'occurrences' === self::view() && TEC::POSTTYPE === $post->post_type ? false : $show;
	}

	/**
	 * Keeps deliberate parent-event destinations from being rewritten to an occurrence.
	 *
	 * @since TBD
	 * @param string $link    Filtered edit link.
	 * @param int    $id      Requested post ID.
	 * @param string $context Display or raw.
	 * @return string The explicit event link when occurrences are hidden, otherwise the original link.
	 */
	public function event_edit_link( string $link, int $id, string $context ): string {
		if ( self::is_list() && 'events' === self::view() && TEC::POSTTYPE === get_post_type( $id ) && ! tribe( Provisional_Post::class )->is_provisional_post_id( $id ) ) {
			$link = admin_url( 'post.php?post=' . $id . '&action=edit' );
			return 'display' === $context ? esc_url( $link ) : $link;
		}
		return $link;
	}

	/**
	 * Labels native pagination totals with the record type being counted.
	 *
	 * @since TBD
	 * @param string $translation Original translation.
	 * @param string $single      Singular source string.
	 * @param string $plural      Plural source string.
	 * @param int    $number      Number of records.
	 * @param string $domain      Translation domain.
	 * @return string Contextual translation.
	 */
	public function item_label( string $translation, string $single, string $plural, int $number, string $domain ): string {
		if ( ! self::is_list() || 'default' !== $domain || '%s item' !== $single || '%s items' !== $plural ) {
			return $translation;
		}
		if ( 'occurrences' === self::view() ) {
			/* translators: %s: number of occurrences. */
			return _n( '%s occurrence', '%s occurrences', $number, 'the-events-calendar' );
		}
		/* translators: %s: number of events. */
		return _n( '%s event', '%s events', $number, 'the-events-calendar' );
	}

	/** Enqueues the shared admin presentation stylesheet. @since TBD @return void */
	public function assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || TEC::POSTTYPE !== $screen->post_type || ! in_array( $screen->base, [ 'edit', 'post' ], true ) ) {
			return;
		}
		if ( self::is_list() ) {
			wp_enqueue_script( 'tec-occurrence-admin', TEC::instance()->plugin_url . 'build/js/recurrence-admin.js', [], TEC::VERSION, true );
		}
		wp_enqueue_style( 'tec-occurrence-admin', TEC::instance()->plugin_url . 'build/css/recurrence-admin.css', [], TEC::VERSION );
	}
}
