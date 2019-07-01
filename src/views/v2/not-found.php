<div class="tribe-view tribe-view--not-found-slug tribe-view--<?php echo esc_attr( $requested_view ) ?>">
	<p>There is no view registered for the <code><?php echo esc_html( $requested_view ) ?></code> slug.</p>
	<p>Make sure to register a view for the <code><?php echo esc_html( $requested_view ) ?></code> in the
		<code>tribe_events_views</code> filter, you can use code like this one in your plugin or theme <code>functions.php</code>
		file:</p>
	<pre>
		<code>
			&lt;&quest;php
			function my_plugin_add_custom_views( array $views ){
				$views['<?php echo esc_html( $requested_view ) ?>'] => My_Custom_View::class;

				return $views;
			}

			add_filter( 'tribe_events_views', 'my_plugin_add_custom_views' );
		</code>
	</pre>
</div>