<?php
/**
 * Organizer template part for the Block Classic Event Details
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/organizer.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.11
 *
 * @since 4.7
 * @since 6.15.11 Replaced definition list markup with unordered list and removed empty dt tags for improved accessibility.
 *
 */

if ( ! tribe_has_organizer() ) {
	return;
}
$attributes = $this->get( 'attributes', [] );

$organizer_ids = tribe_get_organizer_ids();
$multiple = count( $organizer_ids ) > 1;

$phone = tribe_get_organizer_phone();
$email = tribe_get_organizer_email();
$website = tribe_get_organizer_website_link();
?>

<div class="tribe-events-meta-group tribe-events-meta-group-organizer">
	<h3 class="tribe-events-single-section-title">
		<?php if ( empty( $attributes['organizerTitle'] ) ) : ?>
			<?php echo tribe_get_organizer_label( ! $multiple ); ?>
		<?php else : ?>
			<?php echo is_array( $attributes['organizerTitle'] ) ? reset( $attributes['organizerTitle'] ) : esc_html( $attributes['organizerTitle'] ) ?>
		<?php endif; ?>
	</h3>
	<ul class="tribe-events-meta-list tribe-events-meta-list-organizer">
		<?php
		do_action( 'tribe_events_single_meta_organizer_section_start' );

		foreach ( $organizer_ids as $organizer ) {
			if ( ! $organizer ) {
				continue;
			}

			?>
			<li class="tribe-events-meta-item">
				<span class="tribe-organizer tribe-events-meta-value">
					<?php echo tribe_get_organizer_link( $organizer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
				</span>
			</li>
			<?php
		}

		if ( ! $multiple ) { // Only show organizer details if there is one.
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
					<span class="tribe-organizer-url-label tribe-events-meta-label">
						<?php esc_html_e( 'Website', 'the-events-calendar' ); ?>
					</span>
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
