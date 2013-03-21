<html>
<body text="#222222" bgcolor="#ffffff" style="width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0 auto; padding:0; background:#ffffff; min-height:1000px;">
<table align="center" width="100%" style="border-width: 1px; padding:0; border-spacing: 0px; border-style: none; border-color: #cccccc; border-collapse: collapse; background-color: #f7f7f7;">
	<tr>
		<td align="left" style="padding:20px; background-color: #dddddd;">
			<h1 style="color:#0a0a0e; margin:0 0 20px 0 !important; font-family: 'Helvetica Neue', Helvetica, sans-serif; font-style:normal; font-weight:700; font-size:32px; letter-spacing:normal; text-align:left; line-height: 100%;"><?php _e( 'Attendee List', 'tribe-events-calendar' ); ?></h1>
			<h2 style="color:#0a0a0e; margin:0 !important; font-family: 'Helvetica Neue', Helvetica, sans-serif; font-style:normal; font-weight:700; font-size:18px; letter-spacing:normal; text-align:left; line-height: 100%;"><?php echo $event->post_title; ?></h2>
		</td>
	</tr>
</table>
<table align="center" cellpadding="5" width="100%" style="border-collapse: collapse; padding:0; font-family: 'Helvetica Neue', Helvetica, sans-serif; font-style:normal; font-weight:400; font-size:13px; letter-spacing:normal; text-align:left; line-height: 100%;">
	<?php
	$count      = 0;
	$head_style  = 'background:#444444; color:#ffffff; padding:15px;';
	$odd_style  = 'background:#eeeeee; color:#222222; padding:15px; border-bottom:1px solid #ccc;';
	$even_style = 'background:#ffffff; color:#222222; padding:15px; border-bottom:1px solid #ccc;';
	$cell_type = 'th';

	foreach ( $items as $item ) {
		$count ++;
		if ( $count === 1 ) echo '<thead><tr>';
		if ( $count === 2 ) echo '<tbody>';
		if ( $count > 1 ) {
			echo '<tr>';
			$cell_type = 'td';
			if ( $count % 2 == 0 )
				$style = $odd_style;
			else
				$style = $even_style;
		} else {
			$style = $head_style;
		}

		foreach ( $item as $field ) {
			echo sprintf( '<%1$s valign="top" style="%2$s">%3$s</%1$s>', esc_attr( $cell_type ), esc_attr( $style ), esc_html( $field ) );
		}

		echo '</tr>';
		if ( $count === 1 ) echo '</thead>';
		if ( $count === count( $items ) ) echo '</tbody>';

	}
	?>
	</table>
</body>
</html>