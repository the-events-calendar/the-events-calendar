<?php

/**
 * Common helper methods for PRO widgets.
 */
class TribeEventsPro_Widgets {
	/**
	 * @param $filters
	 * @param $operand
	 *
	 * @return array|null
	 */
	public static function form_tax_query( $filters, $operand ) {
		if ( empty( $filters ) ) {
			return null;
		}

		$tax_query = array();

		foreach ( $filters as $tax => $terms ) {
			if ( empty( $terms ) ) {
				continue;
			}

			$tax_operand = 'AND';
			if ( $operand == 'OR' ) {
				$tax_operand = 'IN';
			}
			$arr         = array( 'taxonomy' => $tax, 'field' => 'id', 'operator' => $tax_operand, 'terms' => $terms );
			$tax_query[] = $arr;
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = $operand;
		}

		return $tax_query;
	}

	/**
	 * Enqueue the appropriate CSS for the calendar/advanced list widgets, which share
	 * the same basic appearance.
	 */
	public static function enqueue_calendar_widget_styles() {
		// CSS file
		$event_file        = 'widget-calendar.css';
		$event_file_option = 'widget-calendar-theme.css';
		$stylesheet_option = tribe_get_option( 'stylesheetOption', 'tribe' );

		// Choose the appropriate stylesheet in light of the current styling options
		switch ( $stylesheet_option ) {
			case 'skeleton':
			case 'full':
				$event_file_option = "widget-calendar-$stylesheet_option.css";
				break;
		}

		$style_url = TribeEventsPro::instance()->pluginUrl . 'resources/' . $event_file_option;
		$style_url = apply_filters( 'tribe_events_pro_widget_calendar_stylesheet_url', $style_url );

		$style_override_url = TribeEventsTemplates::locate_stylesheet( 'tribe-events/pro/' . $event_file, $style_url );

		// Load up stylesheet from theme or plugin
		if ( $style_url && 'tribe' === $stylesheet_option ) {
			wp_enqueue_style( 'widget-calendar-pro-style', TribeEventsPro::instance()->pluginUrl . 'resources/widget-calendar-full.css', array(), apply_filters( 'tribe_events_pro_css_version', TribeEventsPro::VERSION ) );
			wp_enqueue_style( TribeEvents::POSTTYPE . '-widget-calendar-pro-style', $style_url, array(), apply_filters( 'tribe_events_pro_css_version', TribeEventsPro::VERSION ) );
		} else {
			wp_enqueue_style( TribeEvents::POSTTYPE . '-widget-calendar-pro-style', $style_url, array(), apply_filters( 'tribe_events_pro_css_version', TribeEventsPro::VERSION ) );
		}

		if ( $style_override_url ) {
			wp_enqueue_style( TribeEvents::POSTTYPE . '--widget-calendar-pro-override-style', $style_override_url, array(), apply_filters( 'tribe_events_pro_css_version', TribeEventsPro::VERSION ) );
		}
	}
}