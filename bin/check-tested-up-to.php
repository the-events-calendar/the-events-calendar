<?php
/**
 * Fails when readme.txt's "Tested up to" header trails the current WordPress release.
 *
 * One file gates both halves of a release. The pull request workflow,
 * .github/workflows/check-tested-up-to.yml, runs it as a plain script, so the gate needs
 * nothing registered in .puprc. Registering it there as a simple check additionally puts
 * it in front of a production zip build, through the `pup check` step in zip.yml.
 * Repositories whose readme.txt carries no plugin header block, tribe-common among them,
 * pass without reaching the network.
 *
 * Available variables when pup runs it:
 *
 * @var Symfony\Component\Console\Input\InputInterface $input
 * @var \StellarWP\Pup\Command\Io                      $output
 * @var \StellarWP\Pup\Commands\Checks\SimpleCheck     $this
 *
 * The VIP performance sniffs below govern runtime plugin code serving a web request. This
 * runs in CI and in a release build, where the ten second timeout is the safer number: the
 * check fails closed, so a tighter one turns a merely slow api.wordpress.org into a blocked
 * release pull request across every repository at once.
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, Squiz.Commenting.FileComment.MissingPackageTag, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsRemoteFile, WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
 */

/*
 * pup defines $output and turns the returned int into an exit code. Neither happens when
 * php runs the file directly, so stand in an $output carrying the three levels used below
 * and exit with the status at the end.
 */
$is_pup_check = isset( $output );

if ( ! $is_pup_check ) {
	$output = new class() {
		public function warning( string $message ): void {
			fwrite( STDOUT, $message . PHP_EOL );
		}

		public function error( string $message ): void {
			fwrite( STDERR, $message . PHP_EOL );
		}

		public function writeln( string $message ): void {
			fwrite( STDOUT, $message . PHP_EOL );
		}
	};
}

/*
 * readme.txt headers name a WordPress branch, not a patch: the repositories in this sync
 * group carry "Tested up to: 7.1" while the current release is 7.1.1. Comparing the two
 * verbatim fails a repository that is current, and the hint would then ask for a patch
 * number that release-update-wp-version.yml writes into readme.txt verbatim, going stale
 * again on the next patch. Both sides are cut to major.minor instead.
 */
$branch_of = static function ( string $version ): string {
	return implode( '.', array_slice( explode( '.', $version ), 0, 2 ) );
};

$check = static function ( $output ) use ( $branch_of ): int {
	$github_base_ref = getenv( 'GITHUB_BASE_REF' );

	/*
	 * No base ref means no pull request is in play — a production zip build, or a local run —
	 * and the header still has to be current. On a pull request it only matters on the
	 * branches a release is cut from.
	 */
	if (
		is_string( $github_base_ref )
		&& '' !== $github_base_ref
		&& 'main' !== $github_base_ref
		&& 0 !== strpos( $github_base_ref, 'release/' )
	) {
		$output->warning( sprintf( 'Skipping the "Tested up to" check: "%s" is neither main nor a release branch.', $github_base_ref ) );

		return 0;
	}

	/*
	 * The header lives in readme.txt here; the main plugin file carries only "Requires at
	 * least". tribe-common ships a changelog-only readme.txt with no header block and is in
	 * the same sync group, so a missing header is a pass. Resolved before the API call so
	 * those repositories never reach the network.
	 */
	if ( ! file_exists( 'readme.txt' ) ) {
		$output->warning( 'Skipping the "Tested up to" check: no readme.txt in this repository.' );

		return 0;
	}

	/* Anchored per line so a version quoted in the changelog body cannot pass for the header. */
	preg_match( '/^Tested up to:\s*([0-9.]+)/m', file_get_contents( 'readme.txt' ), $matches );

	$tested_up_to = $matches[1] ?? '';

	if ( '' === $tested_up_to ) {
		$output->warning( 'Skipping the "Tested up to" check: readme.txt carries no "Tested up to" header.' );

		return 0;
	}

	/*
	 * Ten seconds: the default socket timeout would hold a release pull request open for a
	 * minute when api.wordpress.org is slow.
	 */
	$context  = stream_context_create( [ 'http' => [ 'timeout' => 10 ] ] );
	$response = file_get_contents( 'https://api.wordpress.org/core/version-check/1.7/', false, $context );

	if ( false === $response ) {
		$output->error( 'Failed to fetch the current WordPress version from api.wordpress.org.' );

		return 1;
	}

	$payload = json_decode( $response );
	$latest  = $payload->offers[0]->version ?? '';

	if ( '' === $latest ) {
		$output->error( 'The WordPress version-check API returned no version.' );

		return 1;
	}

	$latest_branch = $branch_of( $latest );

	if ( version_compare( $branch_of( $tested_up_to ), $latest_branch, '<' ) ) {
		$output->error(
			sprintf(
				'The "Tested up to" header in readme.txt trails the current WordPress release. Found: %1$s, expected: %2$s. Run the "Release: Update WordPress Version" workflow with tested_up_to: %2$s.',
				$tested_up_to,
				$latest_branch
			)
		);

		return 1;
	}

	$output->writeln( sprintf( 'readme.txt is tested up to %s; the current WordPress release is %s.', $tested_up_to, $latest ) );

	return 0;
};

$status = $check( $output );

if ( ! $is_pup_check ) {
	exit( $status );
}

return $status;
