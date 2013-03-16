<html>
<body>

<div style="width: 90%; text-align: center;">
	<h1><?php _e( 'Attendee List', 'tribe-events-calendar' ); ?></h1>
	<h2><?php echo $event->post_title; ?></h2>
</div>
<table align="center" width="90%">
	<?php
	$count = 0;
	foreach ( $items as $item ) {
		$count ++;
		if ( $count === 1 ) echo '<thead><th>';
		if ( $count === 2 ) echo '<tbody>';
		if ( $count > 1 ) echo '<tr>';
		foreach ( $item as $field ) {
			echo sprintf( '<td>%s</td>', $field );
		}
		if ( $count === 1 ) echo '</th></thead>';
		if ( $count > 1 ) echo '</tr>';
		if ( $count === count( $items ) ) echo '</tbody>';

	}
	?>
</table>

</body>
</html>
