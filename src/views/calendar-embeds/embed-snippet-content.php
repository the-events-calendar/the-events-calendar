<?php
/**
 * Content for the Embed Snippet column within the Calendar Embeds List Table.
 *
 * @since 6.11.0
 *
 * @version 6.11.0
 *
 * @var int $post_id The post ID.
 */

use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use TEC\Events\Calendar_Embeds\NotPublishedCalendarException;

defined( 'ABSPATH' ) || exit;

try {
	$snippet = Calendar_Embeds::get_iframe( $post_id, true );
} catch ( NotPublishedCalendarException $e ) {
	// No snippet should be generated for unpublished ECEs.
	return;
}

?>
<div id="tec_events_calendar_embeds_snippet_<?php echo esc_attr( $post_id ); ?>" class="hidden">
	<div>
		<div class="tec-events-calendar-embeds__snippet-modal-text">
			<?php esc_html_e( 'Copy and paste this code to embed the calendar on your website:', 'the-events-calendar' ); ?>
		</div>
		<textarea
			id="tec_events_calendar_embeds_snippet_code_<?php echo esc_attr( $post_id ); ?>"
			class="tec-events-calendar-embeds__snippet-modal-textarea"
			aria-label="<?php esc_attr_e( 'Embed snippet code', 'the-events-calendar' ); ?>"
			rows="3"
			readonly><?php echo esc_textarea( $snippet ); ?></textarea>
		<?php
		$copy_button_target = tec_copy_to_clipboard_button( $snippet, false );
		$notice_target      = str_replace( 'tec-copy-text-target-', 'tec-copy-to-clipboard-notice-content-', $copy_button_target );
		?>
		<button
			data-notice-target=".<?php echo esc_attr( $notice_target ); ?>"
			class="button button-primary tec-events-calendar-embeds__snippet-modal-copy-button tec-copy-to-clipboard"
			aria-controls="tec_events_calendar_embeds_snippet_code_<?php echo esc_attr( $post_id ); ?>"
			data-clipboard-action="copy"
			data-clipboard-target=".<?php echo esc_attr( $copy_button_target ); ?>"
		>
			<?php esc_html_e( 'Copy Embed Snippet', 'the-events-calendar' ); ?>
		</button>
	</div>
</div>
<a
	name="Embed Snippet"
	href="/?TB_inline&width=370&height=200&inlineId=tec_events_calendar_embeds_snippet_<?php echo esc_attr( $post_id ); ?>"
	class="thickbox button"
>
	<?php esc_html_e( 'Get Embed Snippet', 'the-events-calendar' ); ?>
</a>
