<?php
/**
 * Manages the table creation and update.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Tables
 */

namespace TEC\Custom_Tables\V1\Tables;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD
 * @package TEC\Custom_Tables\V1\Tables
 */
class Provider extends Service_Provider {
	use Custom_Tables_Provider;

	/**
	 * The Custom Tables version.
	 * This is NOT the same as the plugin version.
	 *
	 * @since TBD
	 */
	const VERSION = '1.0.0';

	/**
	 * The name of the option that will store the version of the Custom Tables
	 * for the plugin.
	 *
	 * @since TBD
	 */
	const VERSION_OPTION = 'tec_custom_tables_v1_version';

	/**
	 * A list of the table classes this provider will handle the registration for.
	 *
	 * @since TBD
	 *
	 * @var array<string>
	 */
	private $table_classes = [
		Events::class,
		Occurrences::class,
	];
}
