<?php

namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as TEC;

/**
 * Class Month_ViewTest
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Month_ViewTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * When mocking the `date` function this is the value that will be used to generate the date in place of the real
	 * one.
	 *
	 * @var string
	 */
	protected $mock_date_value = '2019-01-01 09:00:00';

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
		parent::setUp();
		tribe( 'cache' )->reset();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter(
			[
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $now->format( 'Y-m-d' ),
			]
		);

		// Remove v1 filtering to have consistent results.
		remove_filter( 'tribe_events_before_html', [ TEC::instance(), 'before_html_data_wrapper' ] );
		remove_filter( 'tribe_events_after_html', [ TEC::instance(), 'after_html_data_wrapper' ] );

		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();

		tribe( 'cache' )->reset();
	}


	public function should_have_next_date() {
		$event_next_month = $this->get_mock_event( 'events/single/1.json', [
			'start_date' => '2019-02-10',
			'end_date'   => '2019-02-10',
		] );
		$event_one_this_month = $this->get_mock_event( 'events/single/2.json', [
			'start_date' => '2019-01-15',
			'end_date'   => '2019-01-15',
		]  );
		$event_two_this_month = $this->get_mock_event( 'events/single/3.json', [
			'start_date' => '2019-01-10',
			'end_date'   => '2019-01-10',
		]  );
	}

}