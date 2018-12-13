<p>
<?php esc_html_e( 'If this event was made in the block editor but you want to edit it in the classic editor instead, you can use this tool to clean up remnants of the block editor interface.', 'the-events-calendar' ); ?>
</p>
<p>
<?php esc_html_e( 'Note that this is not reversible and you may lose block data.', 'the-events-calendar' ); ?>
</p>
<label class="button button-primary" for="tribe-events-editor-compatibility-purge-events">
	<input type="checkbox" id="tribe-events-editor-compatibility-purge-events" class="tribe-hidden">
	<?php esc_html_e( 'Clean Up', 'the-events-calendar' ); ?>
</label>

<?php
$args = array(
	'action' => 'edit',
	'post' => $this->get( 'post' )->ID,
	'tribe-action' => 'purge-event-blocks',
);
$clean_up_url = add_query_arg( $args, admin_url( 'post.php' ) );
?>
<div
	class="tribe-dependent tribe-events-editor-compatibility-purge-events-confirm"
	data-depends="#tribe-events-editor-compatibility-purge-events"
	data-condition-is-checked
>
	<div class="tribe-events-editor-compatibility-purge-events-confirm-container">
		<a class="button button-primary" href="<?php echo esc_url( $clean_up_url ); ?>">
			<?php esc_html_e( 'Confirm', 'the-events-calendar' ); ?>
		</a>
		<label class="button button-secondary" for="tribe-events-editor-compatibility-purge-events">
			<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
		</label>
	</div>
</div>
