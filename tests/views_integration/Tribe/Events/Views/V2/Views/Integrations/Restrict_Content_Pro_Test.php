<?php

namespace Tribe\Events\Integrations\Restrict_Content_Pro;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main as TEC;
use Tribe\Events\Views\V2\Views\Month_View as Month;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe\Events\Integrations\Restrict_Content_Pro\Service_Provider as RCP_Integration;


class Restrict_Content_Pro_Test extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	/**
	 * The date we use for the test views.
	 * Here so it only has to be changed once.
	 *
	 * @var string
	 */
	protected $base_date = '2019-01-01';

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

		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();

		tribe( 'cache' )->reset();
		$this->provider = tribe( RCP_Integration::class );
		$this->provider->hook();
	}

	public function restrict_all() {
		$option_slug = tribe( RCP_Integration::class )::get_slug();
		add_filter( "tribe_get_option_{$option_slug}", function() { return '1'; } );
		uopz_add_function( 'rcp_user_can_access', function () { return false; } );
		uopz_set_return( RCP_Integration::class, 'filter_view_events', false );
	}

	public function clear_restrictions() {
		$option_slug = tribe( RCP_Integration::class )::get_slug();
		remove_filter( "tribe_get_option_{$option_slug}", function() { return '1'; } );
		uopz_unset_return( RCP_Integration::class, 'filter_view_events' );
		uopz_del_function( 'rcp_user_can_access' );
	}

	/**
	 * Test render with restrictions removed
	 */
	public function test_render_unrestricted() {
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map(
			static function ( $i ) use ( $now, $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $now->setTime( 10 + $i, 0 ),
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . $i,
						'status'     => 'publish',
					]
				)->create();
			},
			range( 1, 3 )
		);
		$event_ids = wp_list_pluck($events,'ID') ;
		$mock_and_insert = function($template, $id){
			$this->wp_insert_post($this->get_mock_event( $template, [ 'id' => $id ] ));

			return $id;
		};
		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/featured/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
		] );

		add_filter(
			'tribe_events_views_v2_view_data',
			function ( array $data ) use ( $remapped_post_ids ) {
				if ( ! empty( $data['events'] ) ) {
					foreach ( $data['events'] as &$day_events_ids ) {
						$day_events_ids = $this->remap_post_id_array( (array) $day_events_ids, $remapped_post_ids );
					}
				}

				return $data;
			}
		);
		add_filter( 'tribe_events_views_v2_view_month_template_vars', function ( $vars ) use ( $remapped_post_ids )
		{
			$vars['events'][ $this->base_date ]         = $this->remap_post_id_array( $vars['events'][ $this->base_date ],
				$remapped_post_ids );
			$vars['days'][ $this->base_date ]['events'] = array_combine(
				$remapped_post_ids,
				array_map( 'tribe_get_event', $remapped_post_ids )
			);

			return $vars;
		} );

		/** @var Month $month_view */
		$month_view      = View::make( Month::class, $this->context );
		$html = $month_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Test render with restrictions in place
	 */
	public function test_render_restricted() {
		$this->restrict_all();
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map(
			static function ( $i ) use ( $now, $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $now->setTime( 10 + $i, 0 ),
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . $i,
						'status'     => 'publish',
					]
				)->create();
			},
			range( 1, 3 )
		);
		$event_ids = wp_list_pluck($events,'ID') ;
		$mock_and_insert = function($template, $id){
			$this->wp_insert_post($this->get_mock_event( $template, [ 'id' => $id ] ));

			return $id;
		};
		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/featured/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
		] );

		add_filter(
			'tribe_events_views_v2_view_data',
			function ( array $data ) use ( $remapped_post_ids ) {
				if ( ! empty( $data['events'] ) ) {
					foreach ( $data['events'] as &$day_events_ids ) {
						$day_events_ids = $this->remap_post_id_array( (array) $day_events_ids, $remapped_post_ids );
					}
				}

				return $data;
			}
		);

		add_filter(
			'tribe_events_views_v2_view_month_template_vars',
			function ( $vars ) use ( $remapped_post_ids ) {
				$vars['events'][ $this->base_date ] = $this->remap_post_id_array( $vars['events'][ $this->base_date ],
					$remapped_post_ids );
				$vars['days'][ $this->base_date ]['events'] = array_combine(
					$remapped_post_ids,
					array_map( 'tribe_get_event', $remapped_post_ids )
				);

				return $vars;
			}
		);

		/** @var Month $month_view */
		$month_view      = View::make( Month::class, $this->context );
		$html = $month_view->get_html();

		 $this->assertMatchesSnapshot( $html );

		 $this->clear_restrictions();
	}

	/**
	 * Make sure we didn't get the same thing for both of the above tests.
	 */
	public function test_comparison() {
		$snapshot_file_path_1 = file_get_contents( $this->getSnapshotDirectory() . '/Restrict_Content_Pro_Test__test_render_unrestricted__1.php' );
		$snapshot_file_path_2 = file_get_contents( $this->getSnapshotDirectory() . '/Restrict_Content_Pro_Test__test_render_restricted__1.php' );

		$this->assertNotEquals( $snapshot_file_path_1, $snapshot_file_path_2 );
	}


}
