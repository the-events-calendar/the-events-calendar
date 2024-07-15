<?php
/**
 * Jetpack Changelogger Formatter for The Events Calendar
 *
 * @package The Events Calendar
 */

use Automattic\Jetpack\Changelog\Changelog;
use Automattic\Jetpack\Changelog\Parser;
use Automattic\Jetpack\Changelogger\FormatterPlugin;
use Automattic\Jetpack\Changelogger\PluginTrait;

/**
 * Jetpack Changelogger Formatter for The Events Calendar
 *
 * Class TEC_Changelog_Formatter
 */
class TEC_Changelog_Formatter extends Parser implements FormatterPlugin {
	use PluginTrait;

	/**
	 * Bullet for changes.
	 *
	 * @var string
	 */
	private $bullet = '*';

	/**
	 * Output date format.
	 *
	 * @var string
	 */
	private $date_format = 'Y-m-d';

	/**
	 * Title for the changelog.
	 *
	 * @var string
	 */
	private $title = '# Changelog';

	/**
	 * Separator used in headings and change entries.
	 *
	 * @var string
	 */
	private $separator = '-';

	/**
	 * Modified version of parse() from KeepAChangelogParser.
	 *
	 * @throws Exception If a heading has invalid timestamp.
	 * @throws InvalidArgumentException If the changelog data cannot be parsed.
	 *
	 * @param string $changelog Changelog contents.
	 * @return Changelog
	 */
	public function parse( $changelog ) {
		$ret = new Changelog();

		// Fix newlines and expand tabs.
		$changelog = strtr( $changelog, [ "\r\n" => "\n" ] );
		$changelog = strtr( $changelog, [ "\r" => "\n" ] );
		while ( strpos( $changelog, "\t" ) !== false ) {
			$changelog = preg_replace_callback(
				'/^([^\t\n]*)\t/m',
				function ( $m ) {
					return $m[1] . str_repeat( ' ', 4 - ( mb_strlen( $m[1] ) % 4 ) );
				},
				$changelog
			);
		}

		// Remove title. Check if the first line containing the defined title, and remove it.
		$changelog_parts = explode( "\n", $changelog, 2 );
		$first_line      = $changelog_parts[0] ?? '';
		$remaining       = $changelog_parts[1] ?? '';

		if ( false !== strpos( $first_line, $this->title ) ) {
			$changelog = $remaining;
		}

		// Entries make up the rest of the document.
		$entries = [];
		preg_match_all( '/^###\s+\[([^\n=]+)\]\s+([^\n=]+)([\s\S]*?)(?=^###\s+|\z)/m', $changelog, $version_sections );

		foreach ( $version_sections[0] as $section ) {
			$heading_pattern = '/^### +\[([^\] ]+)\] (.+)/';
			// Parse the heading and create a ChangelogEntry for it.
			preg_match( $heading_pattern, $section, $heading );
			if ( ! count( $heading ) ) {
				throw new InvalidArgumentException( "Invalid heading: $heading" );
			}

			$version   = $heading[1];
			$timestamp = $heading[2];
			if ( $timestamp === $this->get_unreleased_date() ) {
				$timestamp       = null;
				$entry_timestamp = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
			} else {
				try {
					$timestamp = new DateTime( $timestamp, new DateTimeZone( 'UTC' ) );
				} catch ( \Exception $ex ) {
					throw new InvalidArgumentException( "Heading has an invalid timestamp: $heading", 0, $ex );
				}
				if ( strtotime( $heading[2], 0 ) !== strtotime( $heading[2], 1000000000 ) ) {
					throw new InvalidArgumentException( "Heading has a relative timestamp: $heading" );
				}
				$entry_timestamp = $timestamp;
			}

			$entry = $this->newChangelogEntry(
				$version,
				[
					'timestamp' => $timestamp,
				]
			);

			$entries[] = $entry;
			$content   = trim( preg_replace( $heading_pattern, '', $section ) );

			if ( '' === $content ) {
				// Huh, no changes.
				continue;
			}

			// Now parse all the subheadings and changes.
			while ( '' !== $content ) {
				$changes = [];
				$rows    = explode( "\n", $content );
				foreach ( $rows as $row ) {
					$is_entry = substr( $row, 0, 1 ) === $this->bullet;

					// It's a multi line entry - add them to previous as content unformatted.
					if ( ! $is_entry ) {
						$changes[ count( $changes ) - 1 ]['content'] .= "\n" . $row;
						continue;
					}

					$row = trim( $row );
					$row = preg_replace( '/\\' . $this->bullet . '/', '', $row, 1 );

					$row_segments = explode( $this->separator, $row, 2 );

					if ( count( $row_segments ) !== 2 ) {
						// Current row (change entry) does not have correct format.
						// It usually happens before migrating to Jetpack Changelogger.
						throw new Exception( 'Change entry does not have the correct format. Please update it manually and run this command again. Change entry: ' . $row );
					}

					array_push(
						$changes,
						[
							'subheading' => trim( $row_segments[0] ),
							'content'    => trim( $row_segments[1] ),
						]
					);
				}

				foreach ( $changes as $change ) {
					$entry->appendChange(
						$this->newChangeEntry(
							[
								'subheading' => $change['subheading'],
								'content'    => $change['content'],
								'timestamp'  => $entry_timestamp,
							]
						)
					);
				}
				$content = '';
			}
		}

		$ret->setEntries( $entries );

		return $ret;
	}

	/**
	 * Write a Changelog object to a string.
	 *
	 * @param Changelog $changelog Changelog object.
	 * @return string
	 */
	public function format( Changelog $changelog ) {
		$ret = '';

		foreach ( $changelog->getEntries() as $entry ) {
			$timestamp    = $entry->getTimestamp();
			$release_date = null === $timestamp ? $this->get_unreleased_date() : $timestamp->format( $this->date_format );

			$ret .= '### [' . $entry->getVersion() . '] ' . $release_date . "\n\n";

			$prologue = trim( $entry->getPrologue() );
			if ( '' !== $prologue ) {
				$ret .= "\n$prologue\n\n";
			}

			foreach ( $entry->getChanges() as $change ) {
				$text = trim( $change->getContent() );
				if ( '' !== $text ) {
					$ret .= $this->bullet . ' ' . $change->getSubheading() . ' ' . $this->separator . ' ' . $text . "\n";
				}
			}

			$ret = trim( $ret ) . "\n\n";
		}

		$ret = $this->title . "\n\n" . trim( $ret ) . "\n";

		return $ret;
	}

	/**
	 * Get string used as the date for an unreleased version.
	 *
	 * @return string
	 */
	private function get_unreleased_date(): string {
		return gmdate( 'Y' ) . '-xx-xx';
	}
}
