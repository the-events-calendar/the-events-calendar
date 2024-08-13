<?php // phpcs:ignoreFile StellarWP.Classes.ValidClassName.NotSnakeCaseClass
/**
 * Modified Semver versioning plugin.
 *
 * Allows for x.y.z.T where T is a hotfix version
 *
 * @package TEC\Actions\Changelogger
 */

namespace TEC\Actions\Changelogger\Plugins;

use Automattic\Jetpack\Changelog\ChangeEntry;
use Automattic\Jetpack\Changelogger\PluginTrait;
use Automattic\Jetpack\Changelogger\VersioningPlugin;
use InvalidArgumentException;

/**
 * ModifiedSemver versioning plugin.
 *
 * Allows for x.y.z.T where T is a hotfix version
 */
class ModifiedSemverVersioning implements VersioningPlugin {
	use PluginTrait;

	/**
	 * Parse a semver version.
	 *
	 * @param string $version Version.
	 * @return array With components:
	 *  - major: (int) Major version.
	 *  - minor: (int) Minor version.
	 *  - patch: (int) Patch version.
	 *  - hotfix: (?int) Hotfix version.
	 *  - version: (string) Major.minor.patch.
	 *  - prerelease: (string|null) Pre-release string.
	 *  - buildinfo: (string|null) Build metadata string.
	 * @throws InvalidArgumentException If the version number is not in a recognized format.
	 */
	public function parseVersion( $version ) {
		// This is slightly looser than the official version from semver.org, in that leading zeros are allowed.
		if ( ! preg_match( '/^(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)(?:\.(?P<hotfix>\d+))?(?:-(?P<prerelease>(?:[0-9a-zA-Z-]+)(?:\.(?:[0-9a-zA-Z-]+))*))?(?:\+(?P<buildinfo>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/', $version, $m ) ) {
			throw new InvalidArgumentException( "Version number \"$version\" is not in a recognized format." );
		}
		$info = [
			'major'      => (int) $m['major'],
			'minor'      => (int) $m['minor'],
			'patch'      => (int) $m['patch'],
			'hotfix'     => isset( $m['hotfix'] ) && '' !== $m['hotfix'] ? (int) $m['hotfix'] : null,
			'version'    => sprintf( '%d.%d.%d', $m['major'], $m['minor'], $m['patch'] ) . ( ! empty( $m['hotfix'] ) ? '.' . (int) $m['hotfix'] : '' ),
			'prerelease' => isset( $m['prerelease'] ) && '' !== $m['prerelease'] ? $m['prerelease'] : null,
			'buildinfo'  => isset( $m['buildinfo'] ) && '' !== $m['buildinfo'] ? $m['buildinfo'] : null,
		];

		if ( null !== $info['prerelease'] ) {
			$sep        = '';
			$prerelease = '';
			foreach ( explode( '.', $info['prerelease'] ) as $part ) {
				if ( ctype_digit( $part ) ) {
					$part = (int) $part;
				}
				$prerelease .= $sep . $part;
				$sep         = '.';
			}
			$info['prerelease'] = $prerelease;
		}

		return $info;
	}

	/**
	 * Check and normalize a version number.
	 *
	 * @param string|array $version Version string, or array as from `parseVersion()` (ignoring a `version` key).
	 * @param array        $extra Extra components for the version, replacing any in `$version`.
	 * @return string Normalized version.
	 * @throws InvalidArgumentException If the version number is not in a recognized format or extra is invalid.
	 */
	public function normalizeVersion( $version, $extra = [] ) {
		if ( is_array( $version ) ) {
			$info = $version + [
					'prerelease' => null,
					'buildinfo'  => null,
				];
			$test = $this->parseVersion( '0.0.0' );
			unset( $test['version'] );
			if ( array_intersect_key( $test, $info ) !== $test ) {
				throw new InvalidArgumentException( 'Version array is not in a recognized format.' );
			}

			if ( null !== $info['prerelease'] ) {
				$info['prerelease'] = $this->parseVersion( '0.0.0-' . $info['prerelease'] )['prerelease'];
			}
		} else {
			$info = $this->parseVersion( $version );
		}
		$info = array_merge( $info, $this->validateExtra( $extra, false ) );

		$ret = sprintf( '%d.%d.%d', $info['major'], $info['minor'], $info['patch'] ) . ( ! empty( $info['hotfix'] ) ? '.' . (int) $info['hotfix'] : '' );
		if ( null !== $info['prerelease'] ) {
			$ret .= '-' . $info['prerelease'];
		}
		if ( null !== $info['buildinfo'] ) {
			$ret .= '+' . $info['buildinfo'];
		}
		return $ret;
	}

	/**
	 * Validate an `$extra` array.
	 *
	 * @param array $extra Extra components for the version. See `nextVersion()`.
	 * @param bool  $nulls Return nulls for unset fields.
	 * @return array
	 * @throws InvalidArgumentException If the `$extra` data is invalid.
	 */
	private function validateExtra( array $extra, $nulls = true ) {
		$info = [];

		if ( isset( $extra['prerelease'] ) ) {
			try {
				$info['prerelease'] = $this->parseVersion( '0.0.0-' . $extra['prerelease'] )['prerelease'];
			} catch ( InvalidArgumentException $ex ) {
				throw new InvalidArgumentException( 'Invalid prerelease data' );
			}
		} elseif ( $nulls || array_key_exists( 'prerelease', $extra ) ) {
			$info['prerelease'] = null;
		}
		if ( isset( $extra['buildinfo'] ) ) {
			try {
				$info['buildinfo'] = $this->parseVersion( '0.0.0+' . $extra['buildinfo'] )['buildinfo'];
			} catch ( InvalidArgumentException $ex ) {
				throw new InvalidArgumentException( 'Invalid buildinfo data' );
			}
		} elseif ( $nulls || array_key_exists( 'buildinfo', $extra ) ) {
			$info['buildinfo'] = null;
		}

		return $info;
	}

	/**
	 * Determine the next version given a current version and a set of changes.
	 *
	 * @param string        $version Current version.
	 * @param ChangeEntry[] $changes Changes.
	 * @param array         $extra Extra components for the version.
	 *  - prerelease: (string|null) Prerelease version, e.g. "dev", "alpha", or "beta", if any. See semver docs for accepted values.
	 *  - buildinfo: (string|null) Build info, if any. See semver docs for accepted values.
	 * @return string
	 * @throws InvalidArgumentException If the version number is not in a recognized format, or other arguments are invalid.
	 */
	public function nextVersion( $version, array $changes, array $extra = [] ) {
		$info = array_merge(
			$this->parseVersion( $version ),
			$this->validateExtra( $extra )
		);

		$significances = [];
		foreach ( $changes as $change ) {
			$significances[ (string) $change->getSignificance() ] = true;
		}
		if ( isset( $significances['major'] ) ) {
			$info['patch'] = 0;
			if ( 0 === (int) $info['major'] ) {
				if ( is_callable( [ $this->output, 'getErrorOutput' ] ) ) { // @phan-suppress-current-line PhanUndeclaredMethodInCallable -- See https://github.com/phan/phan/issues/1204.
					$out = $this->output->getErrorOutput(); // @phan-suppress-current-line PhanUndeclaredMethod -- See https://github.com/phan/phan/issues/1204.
					$out->writeln( '<warning>Semver does not automatically move version 0.y.z to 1.0.0.</>' );
					$out->writeln( '<warning>You will have to do that manually when you\'re ready for the first release.</>' );
				}
				++$info['minor'];
			} else {
				$info['minor'] = 0;
				++$info['major'];
			}
		} elseif ( isset( $significances['minor'] ) ) {
			$info['patch'] = 0;
			++$info['minor'];
		} else {
			++$info['patch'];
		}

		return $this->normalizeVersion( $info );
	}

	/**
	 * Compare two version numbers.
	 *
	 * @param string $a First version.
	 * @param string $b Second version.
	 * @return int Less than, equal to, or greater than 0 depending on whether `$a` is less than, equal to, or greater than `$b`.
	 * @throws InvalidArgumentException If the version numbers are not in a recognized format.
	 */
	public function compareVersions( $a, $b ) {
		$aa = $this->parseVersion( $a );
		$bb = $this->parseVersion( $b );
		if ( $aa['major'] !== $bb['major'] ) {
			return $aa['major'] - $bb['major'];
		}
		if ( $aa['minor'] !== $bb['minor'] ) {
			return $aa['minor'] - $bb['minor'];
		}
		if ( $aa['patch'] !== $bb['patch'] ) {
			return $aa['patch'] - $bb['patch'];
		}

		if ( ! empty( $aa['hotfix'] ) || ! empty( $bb['hotfix'] ) ) {
			if ( empty( $aa['hotfix'] ) ) {
				return -1 * $bb['hotfix'];
			}
			if ( empty( $bb['hotfix'] ) ) {
				return $aa['hotfix'];
			}

			if ( $aa['hotfix'] !== $bb['hotfix'] ) {
				return $aa['hotfix'] - $bb['hotfix'];
			}
		}

		if ( null === $aa['prerelease'] ) {
			return null === $bb['prerelease'] ? 0 : 1;
		}
		if ( null === $bb['prerelease'] ) {
			return -1;
		}

		$aaa = explode( '.', $aa['prerelease'] );
		$bbb = explode( '.', $bb['prerelease'] );
		$al  = count( $aaa );
		$bl  = count( $bbb );
		for ( $i = 0; $i < $al && $i < $bl; $i++ ) {
			$a = $aaa[ $i ];
			$b = $bbb[ $i ];
			if ( ctype_digit( $a ) ) {
				if ( ctype_digit( $b ) ) {
					if ( (int) $a !== (int) $b ) {
						return (int) $a - (int) $b;
					}
				} else {
					return -1;
				}
			} elseif ( ctype_digit( $b ) ) {
				return 1;
			} else {
				$tmp = strcmp( $a, $b );
				if ( 0 !== $tmp ) {
					return $tmp;
				}
			}
		}
		return $al - $bl;
	}

	/**
	 * Return a valid "first" version number.
	 *
	 * @param array $extra Extra components for the version, as for `nextVersion()`.
	 * @return string
	 */
	public function firstVersion( array $extra = [] ) {
		return $this->normalizeVersion(
			[
				'major'  => 0,
				'minor'  => 1,
				'patch'  => 0,
				'hotfix' => null,
			] + $this->validateExtra( $extra )
		);
	}
}
