<?php


class DuplicateIdAttributesCest {

	public function _before( PhantomjsTester $I ) {

		// set the theme to the default one
		$default_theme_slug = $I->getDefaultThemeSlug();
		$I->haveOptionInDatabase( 'template', $default_theme_slug );
		$I->haveOptionInDatabase( 'stylesheet', $default_theme_slug );

		// add 2 of each widget
		$first_sidebar = reset( $I->getThemeSidebars() );
		$widgets       = [
			'wp_inactive_widgets' => [
			],
			$first_sidebar        => [
				$I->getWidgetSlug( 'mini calendar' ),
				$I->getWidgetSlug( 'mini calendar' ),
				$I->getWidgetSlug( 'countdown' ),
				$I->getWidgetSlug( 'countdown' ),
				$I->getWidgetSlug( 'venue' ),
				$I->getWidgetSlug( 'venue' ),
				$I->getWidgetSlug( 'list' ),
				$I->getWidgetSlug( 'list' ),
			],
			'array_version'       => 1,
		];
		$I->haveOptionInDatabase( 'sidebars_widgets', $widgets );
		$adv_list_widget_settings      = [
			1              => $I->getAdvancedListWidgetSettings(),
			2              => $I->getAdvancedListWidgetSettings(),
			'_multiwidget' => 1,
		];
		$countdown_widget_settings     = [
			1              => $I->getCountdownWidgetSettings(),
			2              => $I->getCountdownWidgetSettings(),
			'_multiwidget' => 1,
		];
		$venue_widget_settings         = [
			1              => $I->getVenueWidgetSettings(),
			2              => $I->getVenueWidgetSettings(),
			'_multiwidget' => 1,
		];
		$mini_calendar_widget_settings = [
			1              => $I->getMiniCalendarWidgetSettings(),
			2              => $I->getMiniCalendarWidgetSettings(),
			'_multiwidget' => 1,
		];
		$I->haveOptionInDatabase( 'widget_tribe-events-adv-list-widget', $adv_list_widget_settings );
		$I->haveOptionInDatabase( 'widget_tribe-events-countdown-widget', $countdown_widget_settings );
		$I->haveOptionInDatabase( 'widget_tribe-events-venue-widget', $venue_widget_settings );
		$I->haveOptionInDatabase( 'widget_tribe-mini-calendar', $mini_calendar_widget_settings );

		// set the options to use default calendar template and the month view
		$options = $I->getDefaultProOptions( [ 'tribeEventsTemplate' => '', 'viewOption' => 'month' ] );
		$I->haveOptionInDatabase( 'tribe_events_calendar_options', $options );

		$I->resizeWindow( 1200, 1000 );
	}

	public function _after( PhantomjsTester $I ) {
	}

	/**
	 * @test
	 * it should not have duplicate IDs when visiting the calendar view
	 */
	public function it_should_not_have_duplicate_ids_when_visiting_the_calendar_view( PhantomjsTester $I ) {
		$I->amOnPage( '/events' );
		$this->make_sure_there_are_no_duplicate_ids( $I );
	}

	/**
	 * @param PhantomjsTester $I
	 *
	 * @return array
	 */
	protected function get_all_the_ids_on_the_page( PhantomjsTester $I ) {
		$fetch_script = <<< JS
			var out = [];
			jQuery('[id]').each(function(){
				out.push(jQuery(this).attr('id'));
			});
			return out.join(',');
JS;
		$ids          = explode( ',', $I->executeJS( $fetch_script ) );

		return $ids;
	}

	/**
	 * @param PhantomjsTester $I
	 * @param                 $id
	 *
	 * @return mixed
	 */
	protected function count_elements_with_id( PhantomjsTester $I, $id ) {
		$count = $I->executeJS( sprintf( "return jQuery('#%s').length;", $id ) );

		return $count;
	}

	/**
	 * @param PhantomjsTester $I
	 */
	protected function make_sure_there_are_no_duplicate_ids( PhantomjsTester $I ) {
		$ids = $this->get_all_the_ids_on_the_page( $I );
		foreach ( $ids as $id ) {
			$count = $this->count_elements_with_id( $I, $id );
			$I->assertEquals( 1, $count, "Multiple instances ({$count}) of the '{$id}' id attribute were found on the page." );
		}
	}
}
