<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\Event_Category_Meta;

/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {

	use Utilities;

	/**
	 * Register the provider.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->singleton( Pre_Processor::class );
		$this->container->singleton( Validator::class );
		$this->container->singleton( Worker::class );
		$this->container->singleton( Post_Processor::class );
		$this->container->singleton( Logger::class );
		$this->container->singleton( Handler::class );
		$this->container->singleton( Errors::class );
		$this->add_filters();
	}

	/**
	 * Adds the filters required.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		add_action( 'admin_init', [ $this, 'debug_migration_process' ] );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
	}

	/**
	 * @TODO  - Remove when not needed anymore.
	 * Handles debugging and manually triggering the category color migration process.
	 * This method runs when the URL contains `category_color_migration=1`.
	 *
	 * Supports:
	 * - `reset=1` → Resets migration before running.
	 * - `dry_run=true/false` → Controls dry-run execution.
	 *
	 * @since TBD
	 */
	public function debug_migration_process(): void {
		// phpcs:disable
		if ( '1' !== tec_get_request_var( 'category_color_migration', '' ) ) {
			return;
		}

		// Retrieve all categories before reset (in case we need to delete meta).
		$categories = get_terms(
			[
				'taxonomy'   => 'tribe_events_cat',
				'hide_empty' => false,
			]
		);

		// Optional: Reset migration if requested.
		if ( '1' === tec_get_request_var( 'reset', '' ) ) {
			delete_option( $this->migration_data_option );
			delete_option( $this->migration_status_option );

			// Remove all inserted category meta data using the Event_Category_Meta class.
			foreach ( $categories as $category ) {
				$category_meta = tribe( Event_Category_Meta::class )->set_term( $category->term_id );
				$category_meta->delete( 'tec-events-cat-colors-primary' );
				$category_meta->delete( 'tec-events-cat-colors-secondary' );
				$category_meta->delete( 'tec-events-cat-colors-text' );
				$category_meta->save();
			}

			// Remove settings stored in tribe_events_calendar_options.
			$existing_settings = get_option( 'tribe_events_calendar_options', [] );

			foreach ( $this->settings_mapping as $mapping ) {
				if ( ! $mapping['import'] ) {
					continue; // Skip non-imported keys.
				}

				$mapped_key = $mapping['mapped_key'];

				if ( isset( $existing_settings[ $mapped_key ] ) ) {
					unset( $existing_settings[ $mapped_key ] );
				}
			}

			update_option( 'tribe_events_calendar_options', $existing_settings );

			echo 'Migration has been reset and all inserted meta data has been deleted.<br>';
		}

		// Determine if this is a dry run.
		$dry_run = filter_var( tec_get_request_var( 'dry_run', 'true' ), FILTER_VALIDATE_BOOLEAN );
		echo '<h2>Dry Mode Activated</h2>';

		// Run the migration.
		echo 'Starting migration process...<br>';

		tribe( Handler::class )->migrate( $dry_run );

		// Output logs for debugging.
		$logs = Logger::get_logs();
		echo '<h3>Logs:</h3><pre>' . print_r( $logs, true ) . '</pre>';

		// Output inserted meta data for all categories.
		$categories = get_terms( [ 'taxonomy' => 'tribe_events_cat', 'hide_empty' => false ] );
		$meta_data  = [];

		foreach ( $categories as $category ) {
			$meta_data[ $category->term_id ] = [
				'primary'   => get_term_meta( $category->term_id, 'tec-events-cat-colors-primary', true ),
				'secondary' => get_term_meta( $category->term_id, 'tec-events-cat-colors-secondary', true ),
				'text'      => get_term_meta( $category->term_id, 'tec-events-cat-colors-text', true ),
			];
		}

		echo '<h3>Inserted Meta Data:</h3><textarea>' . print_r( $meta_data, true ) . '</textarea>';

		$migrated_options = get_option('tribe_events_calendar_options',[]);
		echo '<h3> Output migrated options</h3><textarea>' . print_r( $migrated_options, true ).'</textarea>';
		// Terminate execution.
		exit;
		// phpcs:enable
	}
}
