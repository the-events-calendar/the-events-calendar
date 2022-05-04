<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var array<Event_Report> $event_reports A list of the event report data.
 * @var String_Dictionary   $text          Our text dictionary.
 */
?>
<ul>
	<?php foreach ( $event_reports as $event ) : ?>
		<li>
			<?php
			if ( $event->error ) {
				echo $event->error;
			} else {
				?>
				<a target="_blank"
				   href="<?php echo get_edit_post_link( $event->source_event_post->ID, false ) ?>"><?php echo esc_html( $event->source_event_post->post_title ); ?></a>
				—
				<?php
				foreach ( $event->strategies_applied as $action ) {
					switch ( $action ) {
						case 'split':
							echo sprintf(
									esc_html( $text->get( "migration-prompt-strategy-$action" ) ),
									'<strong>',
									count( $event->created_events ),
									'</strong>'
							);
							echo sprintf(
									esc_html( $text->get( "migration-prompt-strategy-$action-new-series" ) ),
									$event->series[0]->post_title // @todo This ok?
							);
							break;
						default:
							// Do we have language for this strategy?
							$output = sprintf(
									esc_html( $text->get( "migration-prompt-strategy-$action" ) ),
									'<strong>',
									'</strong>'
							);
							if ( $output ) {
								echo $output;
							} else {
								echo esc_html( $text->get( "migration-prompt-unknown-strategy" ) );
							}
							break;
					}
				}
			}
			?>
		</li>
	<?php endforeach; ?>
</ul>