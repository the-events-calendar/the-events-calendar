<div class="tribe-events-c-ical tribe-common-b2 tribe-common-b3--min-medium">
	<a
		class="tribe-events-c-ical__link"
		title="<?php echo esc_attr( $item['label'] ); ?>"
		href="<?php echo esc_url( $item['uri'] ); ?>"
	>
		<?php $this->template( 'components/icons/plus', [ 'classes' => [ 'tribe-events-c-ical__link-icon-svg' ] ] ); ?>
		<?php echo esc_html( $item['label'] ); ?>
	</a>
</div>
