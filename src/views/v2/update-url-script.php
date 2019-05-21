<?php
// Only print this when doing ajax
if (
	! $this->get( '_context' )
	|| ! $this->get( '_context' )->doing_ajax()
) {
	return;
}
?>
<script type="text/javascript">
	console.log( 'developers', jQuery( this ), this );
	jQuery( this )
		.parents( tribe.events.views.manager.selectors.container )
		.trigger(
			'updateUrl.tribeEvents',
			[
				'<?php echo esc_url( $this->get( 'url' ) ); ?>',
				'<?php echo esc_attr( $this->get( 'title' ) ); ?>'
			]
		);
</script>
