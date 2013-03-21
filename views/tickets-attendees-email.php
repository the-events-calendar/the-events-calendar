<html>
<body>
<div style="width: 90%; text-align: center;">
	<h1><?php _e( 'Attendee List', 'tribe-events-calendar' ); ?></h1>
	<h2><?php echo $event->post_title; ?></h2>
</div>
<table align="center" width="90%">
	<?php
	$count      = 0;
	/* Sam, your styles here */ 
	$odd_style  = '';
	$even_style = '';

	foreach ( $items as $item ) {
		$count ++;
		if ( $count === 1 ) echo '<thead><th>';
		if ( $count === 2 ) echo '<tbody>';
		if ( $count > 1 ) echo '<tr>';

		if ( $count % 2 == 0 )
			$style = $odd_style;
		else
			$style = $even_style;

		foreach ( $item as $field ) {
			echo sprintf( '<td style="%s">%s</td>', esc_attr( $style ), esc_html( $field ) );
		}

		if ( $count === 1 ) echo '</th></thead>';
		if ( $count > 1 ) echo '</tr>';
		if ( $count === count( $items ) ) echo '</tbody>';

	}
	?>
</table>

</body>
</html>
