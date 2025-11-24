<?php
/**
 * Single Event Meta (Organizer) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/organizer.php
 *
 * @version 6.15.11
 *
 * @since 4.6.19
 * @since 6.15.3 Added post password protection.
 * @since 6.15.11 Replaced definition list markup with unordered list and removed empty dt tags for improved accessibility.
 *
 * @package TribeEventsCalendar
 */

$organizer_ids = tribe_get_organizer_ids();
$multiple = count( $organizer_ids ) > 1;

$phone = tribe_get_organizer_phone();
$email = tribe_get_organizer_email();
$website = tribe_get_organizer_website_link();
$website_title = tribe_events_get_organizer_website_title();
?>

<div class="tribe-events-meta-group tribe-events-meta-group-organizer">
	<h2 class="tribe-events-single-section-title"><?php echo tribe_get_organizer_label( ! $multiple ); ?></h2>
	<ul class="tribe-events-meta-list">
		<?php
		do_action( 'tribe_events_single_meta_organizer_section_start' );

		foreach ( $organizer_ids as $organizer ) {
			if ( ! $organizer ) {
				continue;
			}

			?>
			<li class="tribe-events-meta-item tribe-organizer">
				<?php echo tribe_get_organizer_link( $organizer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
			</li>
			<?php
		}

		if ( ! $multiple && ! post_password_required( $organizer ) ) { // only show organizer details if there is one
			if ( ! empty( $phone ) ) {
				?>
				<li class="tribe-events-meta-item">
					<span class="tribe-organizer-tel-label tribe-events-meta-label">
						<?php esc_html_e( 'Phone', 'the-events-calendar' ); ?>
					</span>
					<span class="tribe-organizer-tel tribe-events-meta-value">
						<?php echo esc_html( $phone ); ?>
					</span>
				</li>
				<?php
			}//end if

			if ( ! empty( $email ) ) {
				?>
				<li class="tribe-events-meta-item">
					<span class="tribe-organizer-email-label tribe-events-meta-label">
						<?php esc_html_e( 'Email', 'the-events-calendar' ); ?>
					</span>
					<span class="tribe-organizer-email tribe-events-meta-value">
						<?php echo esc_html( $email ); ?>
					</span>
				</li>
				<?php
			}//end if

			if ( ! empty( $website ) ) {
				?>
				<li class="tribe-events-meta-item">
					<?php if ( ! empty( $website_title ) ) : ?>
						<span class="tribe-organizer-url-label tribe-events-meta-label">
							<?php echo esc_html( $website_title ); ?>
						</span>
					<?php endif; ?>
					<span class="tribe-organizer-url tribe-events-meta-value">
						<?php echo $website; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
					</span>
				</li>
				<?php
			}//end if
		}//end if

		do_action( 'tribe_events_single_meta_organizer_section_end' );
		?>
	</ul>
</div>
