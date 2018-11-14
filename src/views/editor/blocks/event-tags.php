<?php
$event_id = $this->get( 'post_id' );
?>
<div class="tribe-events-single-section tribe-events-section-tags tribe-clearfix">
	<?php echo tribe_meta_event_tags( sprintf( esc_html__( '%s Tags:', 'events-gutenberg' ), tribe_get_event_label_singular() ), ', ', false ) ?>
</div>
