<?php
/**
 * The base Model, implementing base methods useful in each Model implementation.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use Closure;
use Generator;
use Serializable;
use tad_DI52_Container;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Formatter;
use TEC\Events\Custom_Tables\V1\Models\Validators\ValidatorInterface;

/**
 * Class Model
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 *
 * @method static bool upsert( array $unique_by, array $data ) Update or Insert a new record into the table.
 * @method static Model|null find( mixed $value, string $column = null ) Find a record based on the $value and column provided
 * @method static Builder set_batch_size( int $size ) Sets the size of the batch the Builder should use to fetch models in unbound query methods like `find_all`.
 * @method static Generator find_all( mixed $value, string $column = null ) Find all the records based one the $value and column provided.
 * @method bool delete() Run a delete operation
 * @method static array get() An array with the result of a select constructed out with the current filters.
 * @method static int insert( array $data ) All the data than is going to be inserted.
 * @method static Builder where_in( string $column, array $in_values ) A list of all the records that match the query.
 * @method static Builder where_not_in( string $column, array $not_in_values ) A list of values that shouldn't match the query.
 * @method static Builder where( string $column, string $operator = null, string $value = null ) Set a where clause to filter the results.
 * @method static Builder where_raw( string $where, mixed ...$args ) Prepare and set a custom WHERE clause to filter the results.
 * @method static Builder limit( int $limit ) Sets the limit clause on the Query.
 * @method static Builder offset( int $offset ) Set the OFFSET clause on the Query.
 * @method static Builder order_by( string $column = null, string $order = 'ASC' ) Set the order by clause on the Query.
 * @method static Builder builder_instance() Get an instance from the Builder class.
 * @method static Builder count( string $column_name = null ) Count all the records that match the query.
 * @method static bool exists() If the SQL Query has at least one result on the Database.
 * @method static Builder join( string $table_name, string $left_column, string $right_column ) Creates an INNER JOIN statement.
 * @method static Builder output( string $output ) Sets the format that should be used to format results in SELECT queries.
 * @method static Builder all( ) Find all the records based on the built query.
 * @method static int upsert_set( array $data ) Update or Insert a multiple records into the table.
 */
abstract class Model implements Serializable {
	/**
	 * A map of the Model extensions, `null` when not yet initialized.
	 *
	 * @since TBD
	 *
	 * @var array<string,array<string,array<string,mixed>>>|null
	 */
	protected static $extensions;

	/**
	 * A map relating the columns of this model with a validation class.
	 *
	 * @since TBD
	 * @var array<string,ValidatorInterface>
	 */
	protected $validations = [];

	/**
	 * A map relating the column of this model to the Formatter implementation for each.
	 *
	 * @since TBD
	 *
	 * @var array<string,Formatter>
	 */
	protected $formatters = [];

	/**
	 * Hold the name of the table for this model.
	 *
	 * @since TBD
	 *
	 * @var string The table where this model is persisted.
	 */
	protected $table = '';

	/**
	 * Get a list of the columns that passed the validation.
	 *
	 * @since TBD
	 *
	 * @var array<string> An array with the name of the columns that are valid.
	 */
	private $valid_columns = [];

	/**
	 * An associative array with the key of the error and the error message when validation fails for that particular
	 * column.
	 *
	 * @since TBD
	 *
	 * @var array<string, string>
	 */
	protected $errors = [];

	/**
	 * A reference to the current Service Provider instance.
	 *
	 * @since TBD
	 *
	 * @var tad_DI52_Container
	 */
	private $container;

	/**
	 * Array holding all the dynamic values attached to the object, before running the validation.
	 *
	 * @since TBD
	 *
	 * @var array<string, mixed> An array holding the dynamic values set to this model.
	 */
	protected $data = [];

	/**
	 * A name of the column holding a reference to the primary key on this object.
	 *
	 * @since TBD
	 *
	 * @var string $primary_key The name of the primary key.
	 */
	protected $primary_key = '';
	/**
	 * Define the properties or the columns used to create a hash associated with this model.
	 *
	 * @since TBD
	 *
	 * @var array<string> A list with all the keys that are used to generate a hash for this model.
	 */
	protected $hashed_keys = [];

	/**
	 * Enable the model to indicate only a single field validation is happening, useful when we are just checking
	 * the value of a single column instead of a composer series of values.
	 *
	 * @since TBD
	 *
	 * @var array<string, bool> $single_validation
	 */
	public $single_validations = [];

	/**
	 * Model constructor.
	 *
	 * @param  array                    $data       An array with key => value pairs used to populate the model on creation of the object.
	 * @param  tad_DI52_Container|null  $container  A reference to the current Dependency Injection container instance.
	 */
	public function __construct( array $data = [], tad_DI52_Container $container = null ) {
		$this->data             = $data;
		$this->container        = $container ?: tribe();

		$this->filter_extensions();

		$extended_validators  = isset( static::$extensions[ $this->table ]['validators'] ) ?
			static::$extensions[ $this->table ]['validators']
			: [];
		$extended_formatters  = isset( static::$extensions[ $this->table ]['formatters'] ) ?
			static::$extensions[ $this->table ]['formatters']
			: [];
		$extended_hashed_keys = isset( static::$extensions[ $this->table ]['hashed_keys'] ) ?
			static::$extensions[ $this->table ]['hashed_keys']
			: [];

		$this->validations    = array_merge( $this->validations, $extended_validators );
		$this->formatters     = array_merge( $this->formatters, $extended_formatters );
		$this->hashed_keys    = array_merge( $this->hashed_keys, $extended_hashed_keys );
	}

	/**
	 * Get the name of the table that is being affected by this model.
	 *
	 * @since TBD
	 *
	 * @return string The name of the table used for this model.
	 */
	public function table_name() {
		return $this->table;
	}

	/**
	 * Get the name of the primary column of this model.
	 *
	 * @since TBD
	 *
	 * @return string The name of the column with the primary key value.s
	 */
	public function primary_key_name() {
		return $this->primary_key;
	}

	/**
	 * Validates the Entry to make sure all of its data is valid and consistent.
	 *
	 * @since TBD
	 *
	 * @param  array|null  $columns
	 *
	 * @return bool Whether the Entry is valid and consistent or not.
	 */
	public function validate( array $columns = null ) {
		// Reset all the columns before start.
		$this->valid_columns = [];
		$this->errors        = [];

		if ( is_array( $columns ) ) {
			$validations = $columns;
		} else {
			$validations = array_keys( $this->validations );
		}

		foreach ( $validations as $name ) {
			// This validation does not exist don't use this property.
			if ( empty( $this->validations[ $name ] ) ) {
				continue;
			}

			$validator = $this->container->make( $this->validations[ $name ] );

			if ( ! $validator instanceof ValidatorInterface ) {
				continue;
			}

			if ( $validator->validate( $this, $name, $this->{$name} ) ) {
				$this->valid_columns[] = $name;
				continue;
			}

			$this->errors[ $name ] = implode( " : ", $validator->get_error_messages() );
		}

		// No errors were found.
		return empty( $this->errors() );
	}

	/**
	 * If this function passed the validation or not.
	 *
	 * @since TBD
	 *
	 * @return bool If this function passed the validation or not.
	 */
	public function is_valid() {
		return empty( $this->errors() );
	}

	/**
	 * Whether the validations on this model failed.
	 *
	 * @since TBD
	 *
	 * @return bool If the validation on this model failed.
	 */
	public function is_invalid() {
		return ! $this->is_valid();
	}

	/**
	 * Returns the set of errors, if any, generated during the Entry validation.
	 *
	 * @since TBD
	 *
	 * @return array<string> The set of errors, if any, generated during the Entry validation.
	 */
	public function errors() {
		// Remove all empty strings from the errors.
		return array_filter( $this->errors );
	}

	/**
	 * Format only the column that were considered or are marked as valid, only valid and formatted columns are considered
	 * as valid.
	 *
	 * @since TBD
	 *
	 * @return array<array<string, mixed>, array<string>> An array with 2 elements, first the data as column => value, second array
	 *                                                    is the format of each column like '%d' and such.
	 */
	public function format() {
		$data   = [];
		$format = [];

		foreach ( $this->valid_columns as $name ) {
			// Not found on the format, don't save this value.
			if ( ! isset( $this->formatters[ $name ] ) ) {
				continue;
			}

			$formatter = $this->container->make( $this->formatters[ $name ] );
			if ( $formatter instanceof Formatter ) {
				$data[ $name ] = $formatter->format( $this->{$name} );
				if ( $data[ $name ] !== null ) {
					$format[ $name ] = $formatter->prepare();
				}
			}
		}

		return [ $data, $format ];
	}

	/**
	 * Create a unique hash for this occurrence.
	 *
	 * @since TBD
	 *
	 * @param  array  $keys
	 *
	 * @return string|null The generated hash if valid, null otherwise.
	 */
	public function generate_hash() {
		if ( empty ( $this->hashed_keys ) ) {
			return null;
		}

		$this->validate( $this->hashed_keys );
		list( $data ) = $this->format();

		if ( $this->is_invalid() ) {
			return null;
		}

		$pieces = [];
		foreach ( $data as $column => $value ) {
			if ( $value !== null && in_array( $column, $this->hashed_keys, true ) ) {
				$pieces[] = $value;
			}
		}

		return sha1( implode( ':', $pieces ) );
	}

	/**
	 * Reset the data of the model back to a clear state.
	 *
	 * @since TBD
	 */
	public function reset() {
		$this->data               = [];
		$this->errors             = [];
		$this->single_validations = [];

		return $this;
	}

	/**
	 * Any static method call that is not found is proxies to this magic method that creates a new instance of this model
	 * and forwards the call to the builder.
	 *
	 * @since TBD
	 *
	 * @param  string  $name       The name of the method.
	 * @param  array   $arguments  An array with all the parameters to the method.
	 *
	 * @return mixed The result of calling a not found static method on this class.
	 */
	public static function __callStatic( $name, $arguments ) {
		return ( new static() )->{$name}( ...$arguments );
	}

	/**
	 * Any method that was not found on the model, pass it through the builder class.
	 *
	 * @since TBD
	 *
	 * @param  string  $name       The name of the method.
	 * @param  array   $arguments  An array with all the parameters to the method.
	 *
	 * @return mixed The result of calling the method inside of the builder class.
	 */
	public function __call( $name, $arguments ) {
		if ( isset( static::$extensions[$this->table]['methods'][ $name ] ) ) {
			$method = static::$extensions[$this->table]['methods'][ $name ];

			if ( $method instanceof Closure ) {
				/*
				 * Extensions can define new methods on the Model using the `methods`
				 * entry of the extensions filter. To make the extension easier, bind
				 * this instance to the method.
				 */
				$method = Closure::bind( $method, $this );
			}

			// Use `call_user_func` as the method might have been provided as array.
			return call_user_func( $method, ...$arguments );
		}

		return ( new Builder( $this ) )->{$name}( ...$arguments );
	}

	/**
	 * Set a value to a dynamic property.
	 *
	 * @since TBD
	 *
	 * @param  string  $name   The name of the property.
	 * @param  mixed   $value  The value of the property.
	 */
	public function __set( $name, $value ) {
		$this->data[ $name ] = $value;
	}

	/**
	 * Getter to access dynamic properties, to allow to change the value when reading from MySQL, due all properties
	 * are returned as string or NULL when those are null on the columns of each row.
	 *
	 * @since TBD
	 *
	 * @param  string  $name  The name of the property.
	 *
	 * @return mixed|null null if the value does not exists mixed otherwise the the value to the dynamic property.
	 */
	public function __get( $name ) {
		if ( array_key_exists( $name, $this->data ) ) {
			// Try to fin a method on this instance like `get_property_name_attribute
			$method = 'get_' . strtolower( $name ) . '_attribute';

			if ( method_exists( $this, $method ) ) {
				return $this->{$method}( $this->data[ $name ] );
			}

			return $this->data[ $name ];
		}

		return null;
	}

	/**
	 * Getter to retrieve all currently stored model values.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> The model data, in array format.
	 */
	public function get_values() {
		return $this->data;
	}

	/**
	 * Make sure when using `isset` or `empty` with a model the value reviews the dynamic properties instead.
	 *
	 * @since TBD
	 *
	 * @param  string  $name  The name of the property.
	 *
	 * @return bool If the property has been defined as dynamic attribute.
	 */
	public function __isset( $name ) {
		return array_key_exists( $name, $this->data );
	}

	/**
	 * Create a new method to transform the values from a model into an array.
	 *
	 * @since TBD
	 * @return array An array with the result of the data associated with this model.
	 */
	public function to_array() {
		$result = [];

		foreach ( $this->data as $key => $value ) {
			// Use dynamic getter to fire all the accessors.
			$result[ $key ] = $this->{$key};
		}

		return $result;
	}

	/**
	 * Returns whether a Column is valid for the model or not.
	 *
	 * @since TBD
	 *
	 * @param  string  $column  The column to check against the Model.
	 *
	 * @return bool Whether a Column is valid for the model or not.
	 */
	public function valid_column( $column ) {
		return array_key_exists( $column, $this->validations );
	}

	/**
	 * If a model is cached, make sure only the important data is serialized, to reduce the amount of space that the
	 * object uses when stored as a string.
	 *
	 * @since TBD
	 * @return string The string representing the object.
	 */
	public function serialize() {
		$encode = wp_json_encode( $this->to_array() );

		return is_string( $encode ) ? $encode : '';
	}

	/**
	 * If this object is constructed out of a `unserialize` call make sure the properties are set up correctly on the
	 * object.
	 *
	 * @since TBD
	 *
	 * @param  string  $serialized
	 */
	public function unserialize( $serialized ) {
		$data = json_decode( $serialized, true );

		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( $data as $column => $value ) {
			$this->{$column} = $value;
		}
	}

	/**
	 * Mechanism to detect if the specified column was set as single validation.
	 *
	 * @since TBD
	 *
	 * @param string $column The name of the column on the database where we are checking validation against with.
	 *
	 * @return bool `true` if the column was set as single validation `false` otherwise.
	 */
	public function has_single_validation( $column ) {
		return isset( $this->single_validations[ $column ] );
	}

	/**
	 * Mechanism to set a column with single validation
	 *
	 * @since TBD
	 *
	 * @param string $column The name of the column on the database where we are checking validation against with.
	 *
	 * @return $this An instance to the current model.
	 */
	public function enable_single_validation( $column ) {
		$this->single_validations[ $column ] = true;

		return $this;
	}

	/**
	 * Mechanism to revert a single validation into a column.
	 *
	 * @since TBD
	 *
	 * @param string $column The name of the column on the database where we are checking validation against with.
	 *
	 * @return $this An instance to the current model.
	 */
	public function disable_single_validation( $column ) {
		unset( $this->single_validations[ $column ] );

		return $this;
	}

	/**
	 * Allow to convert this model into a string value.
	 *
	 * @since TBD
	 * @return string
	 */
	public function __toString() {
		return $this->serialize();
	}

	/**
	 * Filters the Model to allow its extension.
	 *
	 * To avoid performance issues, Models can only be extended before
	 * the `init` action.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and has the side effect of
	 *              setting the static extensions property that will be applied to all
	 *              instances of the Model.
	 */
	private function filter_extensions() {
		// Filter at least once, and no more after `init`.
		if ( isset( static::$extensions[ $this->table_name() ] ) && did_action( 'init' ) ) {
			return;
		}

		/**
		 * Allows extending the Model to add fields, and required functionality, to it.
		 *
		 * @since TBD
		 *
		 * @param array<string,array<string,mixed>> An array of possible extensions.
		 */
		$extensions = apply_filters( "tec_custom_tables_{$this->table}_model_v1_extensions", [
			'validators'  => [],
			'formatters'  => [],
			'hashed_keys' => [],
		] );

		static::$extensions[ $this->table ] = $extensions;
	}
}
