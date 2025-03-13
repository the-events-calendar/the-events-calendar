<?php
/**
 * Content for the Embed Snippet column within the Calendar Embeds List Table.
 *
 * @since TBD
 *
 * @version TBD
 *
 * @var int    $post_id   The post ID.
 */

use TEC\Events\Calendar_Embeds\Calendar_Embeds;

$snippet = Calendar_Embeds::get_iframe( $post_id );

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
		<button
			class="button button-primary tec-events-calendar-embeds__snippet-modal-copy-button"
			aria-controls="tec_events_calendar_embeds_snippet_code_<?php echo esc_attr( $post_id ); ?>"
			data-clipboard-action="copy"
			data-clipboard-target="#tec_events_calendar_embeds_snippet_code_<?php echo esc_attr( $post_id ); ?>"
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
