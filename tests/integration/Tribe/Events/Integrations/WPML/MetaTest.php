<?php

namespace Tribe\Events\Integrations\WPML;

use Tribe__Events__Integrations__WPML__Meta as Meta;

class MetaTest extends \Codeception\TestCase\WPTestCase {

	private function given_an_event(): \WP_Post {
		return tribe_events()->set_args( [
			'title'      => 'Test Event',
			'start_date' => '2019-01-01 10:00:00',
			'end_date'   => '2019-01-01 11:00:00',
		] )->create();
	}

	public function translate_post_id_data(): \Generator {
		yield 'empty $_POST' => [
			function () {
				unset( $_POST );
				$post_id = $this->given_an_event()->ID;

				return [ 'input', $post_id, '_EventOrganizerID', true, 'input' ];
			}
		];
		yield 'not a supported meta' => [
			function () {
				$_POST   = [];
				$post_id = $this->given_an_event()->ID;

				return [ 'input', $post_id, '_CustomField', true, 'input' ];
			}
		];
		yield 'supported meta, null input value, single' => [
			function () {
				$_POST   = [];
				$post_id = $this->given_an_event()->ID;

				return [ null, $post_id, '_EventOrganizerID', true, null ];
			}
		];
		yield 'supported meta, null input value, multiple' => [
			function () {
				$_POST   = [];
				$post_id = $this->given_an_event()->ID;

				return [ null, $post_id, '_EventOrganizerID', false, null ];
			}
		];
		yield 'supported meta, db value is serialized set of post IDs' => [
			function () {
				$_POST          = [];
				$post_id        = $this->given_an_event()->ID;
				$organizer_1_id = tribe_organizers()->set_args( [
					'title' => 'Test Organizer',
				] )->create()->ID;
				$organizer_2_id = tribe_organizers()->set_args( [
					'title' => 'Test Organizer',
				] )->create()->ID;
				update_post_meta( $post_id, '_EventOrganizerID', [ $organizer_1_id, $organizer_2_id ] );
				add_filter( 'wpml_object_id', static fn( $id ) => $id + 23 );

				return [
					null,
					$post_id,
					'_EventOrganizerID',
					true,
					[ $organizer_1_id + 23, $organizer_2_id + 23 ]
				];
			}
		];
		yield 'supported meta, db value is single post ID' => [
			function () {
				$_POST          = [];
				$post_id        = $this->given_an_event()->ID;
				$organizer_1_id = tribe_organizers()->set_args( [
					'title' => 'Test Organizer',
				] )->create()->ID;
				update_post_meta( $post_id, '_EventOrganizerID', $organizer_1_id );
				add_filter( 'wpml_object_id', static fn( $id ) => $id + 23 );

				return [
					null,
					$post_id,
					'_EventOrganizerID',
					true,
					$organizer_1_id + 23
				];
			}
		];
	}

	/**
	 * @dataProvider translate_post_id_data
	 */
	public function test_translate_post_id( \Closure $fixture ): void {
		[ $value, $object_id, $meta_key, $single, $expected ] = $fixture();

		$meta                = tribe( Meta::class );
		$filtered_meta_value = $meta->translate_post_id( $value, $object_id, $meta_key, $single );

		$this->assertEquals( $expected, $filtered_meta_value );
	}
}
