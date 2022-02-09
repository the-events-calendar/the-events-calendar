<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use TEC\Events\Custom_Tables\V1\Models\Validators\Validator;
use TEC\Events\Custom_Tables\V1\Models\Validators\ValidatorInterface;

class ValidatorTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * Verify our interface has not changed, and we can extend properly.
	 *
	 * @test
	 */
	public function should_comply_with_interface() {
		$validator = new class extends Validator {
			public function validate( Model $model, $name, $value ) {
				return true;
			}
		};
		$this->assertInstanceOf( ValidatorInterface::class, $validator );
	}

	/**
	 * Should report more than one year.
	 *
	 * @test
	 */
	public function should_report_multiple_errors() {
		// Setup
		$model     = new class extends Model {
		};
		$validator = new class extends Validator {
			public $messages_to_create = [
				'Message A',
				'Message B'
			];

			public function validate( Model $model, $name, $value ) {
				foreach ( $this->messages_to_create as $message ) {
					$this->add_error_message( $message );
				}
			}
		};
		// Trigger errors
		$validator->validate( $model, 'faux', false );
		$errors = $validator->get_error_messages();
		// Should match
		$this->assertCount( count( $validator->messages_to_create ), $errors, "Should be same number of messages" );
		foreach ( $errors as $index => $error_message ) {
			$expected = $validator->messages_to_create[ $index ];
			$this->assertEquals( $expected, $error_message, "Error messages should match" );
		}
	}

	/**
	 * Should clear errors.
	 *
	 * @test
	 */
	public function should_clear_errors() {
		// Setup
		$model     = new class extends Model {
		};
		$validator = new class extends Validator {
			public $messages_to_create = [
				'Message A',
				'Message B'
			];

			public function validate( Model $model, $name, $value ) {
				foreach ( $this->messages_to_create as $message ) {
					$this->add_error_message( $message );
				}
			}
		};
		// Trigger errors
		$validator->validate( $model, 'faux', false );
		$errors = $validator->get_error_messages();

		// Make sure we have some errors to clear
		$this->assertCount( count( $validator->messages_to_create ), $errors, "Should be same number of messages." );

		// Clear our errors
		$validator->clear_error_messages();
		$errors = $validator->get_error_messages();
		$this->assertCount( 0, $errors, "Should be cleared out." );
	}

	/**
	 * Validation compatibility with our ORM.
	 *
	 * @test
	 */
	public function should_validate_model() {
		// Setup
		$faux_field_name        = 'field';
		$model                  = new class extends Model {
			// Method to add our anonymous validator
			public function add_validator( $field, $class ) {
				$this->validations[ $field ] = $class;
			}
		};
		$over_hundred_validator = new class extends Validator {
			public function validate( Model $model, $name, $value ) {
				$valid = true;
				if ( ! is_int( $value ) ) {
					$this->add_error_message( "Must be an integer" );
					$valid = false;
				}
				if ( $value < 100 ) {
					$this->add_error_message( "Must be more than 100" );
					$valid = false;
				}

				return $valid;
			}
		};
		$model->add_validator( 'field', get_class( $over_hundred_validator ) );
		// Invalid value
		$model->$faux_field_name = 'invalid value';
		// Trigger errors
		$model->validate( [ $faux_field_name ] );

		// Should match
		$errors = $model->errors();
		$this->assertTrue( $model->is_invalid() );
		$this->assertCount( 1, $errors );
		$this->assertContains( "Must be an integer", $errors[ $faux_field_name ] );
		$this->assertContains( "Must be more than 100", $errors[ $faux_field_name ] );

		// Valid value
		$model->$faux_field_name = 1234;

		// Trigger errors
		$model->validate( [ $faux_field_name ] );
		$errors = $model->errors();
		$this->assertFalse( $model->is_invalid() );
		$this->assertCount( 0, $errors );
	}

}