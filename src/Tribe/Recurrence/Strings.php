<?php


class Tribe__Events__Pro__Recurrence__Strings {

	/**
	 * Build possible strings for recurrence
	 *
	 * @return array
	 */
	public static function recurrence_strings() {
		$strings = array(
			'simple-every-day-on'                      => __( 'Daily until %1$s', 'tribe-events-calendar-pro' ),
			'simple-every-week-on'                     => __( 'Weekly on the same day until %1$s', 'tribe-events-calendar-pro' ),
			'simple-every-month-on'                    => __( 'Monthly on the same day until %1$s', 'tribe-events-calendar-pro' ),
			'simple-every-year-on'                     => __( 'Yearly on the same date until %1$s', 'tribe-events-calendar-pro' ),
			'simple-every-day-after'                   => __( 'Daily', 'tribe-events-calendar-pro' ),
			'simple-every-week-after'                  => __( 'Weekly on the same day', 'tribe-events-calendar-pro' ),
			'simple-every-month-after'                 => __( 'Monthly on the same day', 'tribe-events-calendar-pro' ),
			'simple-every-year-after'                  => __( 'Yearly on the same date', 'tribe-events-calendar-pro' ),
			'simple-every-day-never'                   => __( 'Daily', 'tribe-events-calendar-pro' ),
			'simple-every-week-never'                  => __( 'Weekly on the same day', 'tribe-events-calendar-pro' ),
			'simple-every-month-never'                 => __( 'Monthly on the same day', 'tribe-events-calendar-pro' ),
			'simple-every-year-never'                  => __( 'Yearly on the same date', 'tribe-events-calendar-pro' ),
			'every-day-on'                             => __( 'An event every day that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-day-after'                          => __( 'An event every day that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-day-never'                          => __( 'An event every day that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'every-week-on'                            => __( 'An event every week on the same day that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-week-after'                         => __( 'An event every week on the same day that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-week-never'                         => __( 'An event every week on the same day that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'every-month-on'                           => __( 'An event every month on the same day that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-month-after'                        => __( 'An event every month on the same day that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-month-never'                        => __( 'An event every month on the same day that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'every-year-on'                            => __( 'An event every year on the same date that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-year-after'                         => __( 'An event every year on the same date that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-year-never'                         => __( 'An event every year on the same date that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-daily-on-same-time'                => __( 'An event every %1$s day(s) that lasts %2$s day(s) and %3$s hour(s), the last of which will begin on %4$s', 'tribe-events-calendar-pro' ),
			'custom-daily-after-same-time'             => __( 'An event every %1$s day(s) that lasts %2$s day(s) and %3$s hour(s), but only create %4$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-daily-never-same-time'             => __( 'An event every %1$s day(s) that lasts %2$s day(s) and %3$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-daily-on-diff-time'                => __( 'An event every %1$s day(s) that begins at %2$s and lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-daily-after-diff-time'             => __( 'An event every %1$s day(s) that begins at %2$s and lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-daily-never-diff-time'             => __( 'An event every %1$s day(s) that begins at %2$s and lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-weekly-on-same-time'               => __( 'An event every %1$s week(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-weekly-after-same-time'            => __( 'An event every %1$s week(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-weekly-never-same-time'            => __( 'An event every %1$s week(s) on %2$s that lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-weekly-on-diff-time'               => __( 'An event every %1$s week(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-weekly-after-diff-time'            => __( 'An event every %1$s week(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-weekly-never-diff-time'            => __( 'An event every %1$s week(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-same-time-numeric'      => __( 'An event every %1$s month(s) on day %2$s that lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-same-time-numeric'   => __( 'An event every %1$s month(s) on day %2$s that lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-same-time-numeric'   => __( 'An event every %1$s month(s) on day %2$s that lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-diff-time-numeric'      => __( 'An event every %1$s month(s) on day %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-diff-time-numeric'   => __( 'An event every %1$s month(s) on day %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-diff-time-numeric'   => __( 'An event every %1$s month(s) on day %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-same-time'              => __( 'An event every %1$s month(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-same-time'           => __( 'An event every %1$s month(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-same-time'           => __( 'An event every %1$s month(s) on %2$s that lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-diff-time'              => __( 'An event every %1$s month(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-diff-time'           => __( 'An event every %1$s month(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-diff-time'           => __( 'An event every %1$s month(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-same-time-unfiltered'    => __( 'An event every %1$s year(s) in %2$s on day %3$s that lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-same-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-same-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-diff-time-unfiltered'    => __( 'An event every %1$s year(s) in %2$s on day %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), the last of which will begin on %7$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-diff-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), but only create %7$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-diff-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-same-time'               => __( 'An event every %1$s year(s) in %2$s on %3$s that lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-same-time'            => __( 'An event every %1$s year(s) in %2$s on %3$s that lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-same-time'            => __( 'An event every %1$s year(s) in %2$s on %3$s that lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-diff-time'               => __( 'An event every %1$s year(s) in %2$s on %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), the last of which will begin on %7$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-diff-time'            => __( 'An event every %1$s year(s) in %2$s on %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), but only create %7$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-diff-time'            => __( 'An event every %1$s year(s) in %2$s on %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
		);

		return $strings;
	}
}