<?php

if ( ! class_exists( 'Tribe__Events__Advanced_Functions__Register_Meta' ) ) {

	/**
	 * Event Meta Register
	 *
	 * Handle retrieval of event meta.
	 */
	class Tribe__Events__Advanced_Functions__Register_Meta {

		/**
		 * The the title
		 *
		 * @return string title
		 */
		public static function the_title() {
			return get_the_title( get_the_ID() );
		}


		/**
		 * Get the event date
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function event_date( $meta_id ) {
			$time_format = get_option( 'time_format', Tribe__Events__Date_Utils::TIMEFORMAT );
			$start_time  = tribe_get_start_date( null, false, $time_format );
			$end_time    = tribe_get_end_date( null, false, $time_format );

			if ( tribe_event_is_all_day() ) {
				if ( tribe_event_is_multiday() ) {
					$html = Tribe__Events__Meta_Factory::template(
						__( 'Start:', 'tribe-events-calendar' ),
						sprintf(
							'<abbr class="tribe-events-abbr updated published dtstart" title="%s">%s</abbr>',
							tribe_get_start_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
							tribe_get_start_date()
						),
						$meta_id
					);
					$html .= Tribe__Events__Meta_Factory::template(
						__( 'End:', 'tribe-events-calendar' ),
						sprintf(
							'<abbr class="tribe-events-abbr dtend" title="%s">%s</abbr>',
							tribe_get_end_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
							tribe_get_end_date()
						),
						$meta_id
					);
				} else {
					// If all day event, show only start date
					$html = Tribe__Events__Meta_Factory::template(
						__( 'Date:', 'tribe-events-calendar' ),
						sprintf(
							'<abbr class="tribe-events-abbr updated published dtstart" title="%s">%s</abbr>',
							tribe_get_start_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
							tribe_get_start_date()
						),
						$meta_id
					);
				}
			} elseif ( tribe_event_is_multiday() ) {
				// If multiday, show start date+time and end date+time
				$html = Tribe__Events__Meta_Factory::template(
					__( 'Start:', 'tribe-events-calendar' ),
					sprintf(
						'<abbr class="tribe-events-abbr updated published dtstart" title="%s">%s</abbr>',
						tribe_get_start_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
						tribe_get_start_date()
					),
					$meta_id
				);
				$html .= Tribe__Events__Meta_Factory::template(
					__( 'End:', 'tribe-events-calendar' ),
					sprintf(
						'<abbr class="tribe-events-abbr dtend" title="%s">%s</abbr>',
						tribe_get_end_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
						tribe_get_end_date()
					),
					$meta_id
				);
			} else {
				// show start date
				$html = Tribe__Events__Meta_Factory::template(
					__( 'Date:', 'tribe-events-calendar' ),
					sprintf(
						'<abbr class="tribe-events-abbr updated published dtstart" title="%s">%s</abbr>',
						tribe_get_start_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
						tribe_get_start_date( null, false )
					),
					$meta_id
				);
				if ( $start_time == $end_time ) {
					// if start and end time are the same, just show the start time
					$html .= Tribe__Events__Meta_Factory::template(
						__( 'Time:', 'tribe-events-calendar' ),
						sprintf(
							'<abbr class="tribe-events-abbr dtend" title="%s">%s</abbr>',
							tribe_get_end_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
							$start_time
						),
						$meta_id
					);
				} else {
					// show start and end time
					$html .= Tribe__Events__Meta_Factory::template(
						__( 'Time:', 'tribe-events-calendar' ),
						sprintf(
							'<abbr class="tribe-events-abbr dtend" title="%s">%s</abbr>',
							tribe_get_end_date( null, false, Tribe__Events__Date_Utils::DBDATEFORMAT ),
							$start_time . ' - ' . $end_time
						),
						$meta_id
					);
				}
			}

			return apply_filters( 'tribe_event_meta_event_date', $html );
		}


		/**
		 * Get event categories
		 *
		 * @param int $meta_id
		 *
		 * @return array
		 */
		public static function event_category( $meta_id ) {
			global $_tribe_meta_factory;
			$post_id = get_the_ID();

			// setup classes in the template
			$template = Tribe__Events__Meta_Factory::embed_classes( $_tribe_meta_factory->meta[$meta_id]['wrap'], $_tribe_meta_factory->meta[$meta_id]['classes'] );

			$args = array(
				'before'       => '',
				'sep'          => ', ',
				'after'        => '',
				'label'        => $_tribe_meta_factory->meta[$meta_id]['label'],
				'label_before' => $template['label_before'],
				'label_after'  => $template['label_after'],
				'wrap_before'  => $template['meta_before'],
				'wrap_after'   => $template['meta_after']
			);

			// Event categories
			return apply_filters( 'tribe_event_meta_event_category', tribe_get_event_categories( $post_id, $args ) );
		}


		/**
		 * Get event tags
		 *
		 * @param int $meta_id
		 *
		 * @return array
		 */
		public static function event_tag( $meta_id ) {
			global $_tribe_meta_factory;

			return apply_filters( 'tribe_event_meta_event_tag', tribe_meta_event_tags( $_tribe_meta_factory->meta[$meta_id]['label'], ', ', false ) );
		}


		/**
		 * Get the event link
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function event_website( $meta_id ) {
			global $_tribe_meta_factory;
			$link         = tribe_get_event_website_link();
			$website_link = empty( $link ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$link,
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_event_website', $website_link );
		}

		/**
		 * Get event origin
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function event_origin( $meta_id ) {
			global $_tribe_meta_factory;
			$origin_to_display = apply_filters( 'tribe_events_display_event_origin', '', get_the_ID() );
			$origin            = empty( $link ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$origin_to_display,
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_event_origin', $origin );
		}


		/**
		 * Get organizer name
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function organizer_name( $meta_id ) {
			global $_tribe_meta_factory;
			$post_id        = get_the_ID();
			$name           = tribe_get_organizer( $post_id );
			$organizer_name = empty( $name ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$name,
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_organizer_name', $organizer_name, $meta_id );
		}


		/**
		 * Get organizer email
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function organizer_email( $meta_id ) {
			global $_tribe_meta_factory;
			$email           = tribe_get_organizer_email();
			$organizer_email = empty( $email ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				'<a href="mailto:' . $email . '">' . $email . '</a>',
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_organizer_email', $organizer_email );
		}

		/**
		 * Get the venue name
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function venue_name( $meta_id ) {
			global $_tribe_meta_factory;
			$post_id    = get_the_ID();
			$name       = tribe_get_venue( $post_id );
			$venue_name = empty( $name ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$name,
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_venue_name', $venue_name, $meta_id );
		}

		/**
		 * Get the venue address
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function venue_address( $meta_id ) {
			global $_tribe_meta_factory;

			$address = tribe_address_exists( get_the_ID() ) ? '<address class="tribe-events-address">' . tribe_get_full_address( get_the_ID() ) . '</address>' : '';

			// Google map link
			$gmap_link = tribe_show_google_map_link( get_the_ID() ) ? self::gmap_link() : '';
			$gmap_link = apply_filters( 'tribe_event_meta_venue_address_gmap', $gmap_link );

			$venue_address = empty( $address ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$address . $gmap_link,
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_venue_address', $venue_address );
		}

		/**
		 * Get the venue map
		 *
		 * @param int $meta_id
		 *
		 * @return string
		 */
		public static function venue_map( $meta_id ) {
			global $_tribe_meta_factory;
			$post_id   = get_the_ID();
			$map       = tribe_get_embedded_map( $post_id );
			$venue_map = empty( $map ) ? '' : Tribe__Events__Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$map,
				$meta_id
			);

			return apply_filters( 'tribe_event_meta_venue_map', $venue_map );
		}

		/**
		 * Get the venue map link
		 *
		 * @deprecated since 3.6 use tribe_get_map_link_html() instead
		 * @return string
		 */
		public static function gmap_link() {
			$link = sprintf(
				'<a class="tribe-events-gmap" href="%s" title="%s" target="_blank">%s</a>',
				esc_url( tribe_get_map_link() ),
				__( 'Click to view a Google Map', 'tribe-events-calendar' ),
				__( '+ Google Map', 'tribe-events-calendar' )
			);

			return apply_filters( 'tribe_event_meta_gmap_link', $link );
		}

	}

}
