<?php $venueEvents = sp_get_events(array('venue'=>get_the_ID())); ?>
<?php foreach( $venueEvents as $event ): setup_postdata($event);  ?>
  <?php the_title() ?>
<?php endforeach; ?>
