<?php
/**
 * Class File_Importer_Events_CostTest
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Importer
 */
namespace Tribe\Events\Importer;

require_once 'File_Importer_EventsTest.php';

class File_Importer_Events_CostTest extends File_Importer_EventsTest {

	/**
	 * @test
	 */
	public function it_should_not_mark_record_as_invalid_if_missing_currency_code() {
		$this->data        = [
			'currency_code_1' => '',
		];
		$this->field_map[] = 'event_currency_code';

		$sut = $this->make_instance();

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	/**
	 * @test
	 */
	public function it_should_insert_currency_code_if_provided() {
		$this->data = [
			'currency_code_1' => 'AUD',
		];

		$this->field_map[] = 'event_currency_code';

		$sut = $this->make_instance( 'event-currency-code' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( 'AUD', get_post_meta( $post_id, '_EventCurrencyCode', true ) );
	}

	/**
	 * @test
	 */
	public function it_should_overwrite_currency_code_when_reimporting() {
		$this->data = [
			'currency_code_1' => 'NZD',
		];

		$this->field_map[] = 'event_currency_code';

		$sut = $this->make_instance( 'event-currency-code' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( 'NZD', get_post_meta( $post_id, '_EventCurrencyCode', true ) );

		$this->data = [
			'currency_code_1' => 'CAD',
		];

		$sut = $this->make_instance( 'event-currency-code' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'CAD', get_post_meta( $post_id, '_EventCurrencyCode', true ) );
	}

	/**
	 * @test
	 */
	public function it_should_restore_a_currency_code_that_has_been_emptied() {
		$this->data = [
			'currency_code_1' => 'NZD',
		];

		$this->field_map[] = 'event_currency_code';

		$sut = $this->make_instance( 'event-currency-code' );

		$post_id = $sut->import_next_row();

		update_post_meta( $post_id, '_EventCurrencyCode', '' );
		$this->assertEquals( '', get_post_meta( $post_id, '_EventCurrencyCode', true ) );

		$this->data = [
			'currency_code_1' => 'CAD',
		];

		$sut = $this->make_instance( 'event-currency-code' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'CAD', get_post_meta( $post_id, '_EventCurrencyCode', true ) );
	}

	/**
	 * @test
	 */
	public function it_should_overwrite_the_currency_code_if_currency_code_import_is_empty() {
		$this->data = [
			'currency_code_1' => 'AUD',
		];

		$this->field_map[] = 'event_currency_code';

		$sut     = $this->make_instance( 'event-currency-code' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( 'AUD', get_post_meta( $post_id, '_EventCurrencyCode', true ) );

		$this->data = [
			'currency_code_1' => '',
		];

		$sut              = $this->make_instance( 'event-currency-code' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( '', get_post_meta( $post_id, '_EventCurrencyCode', true ) );
	}
}
