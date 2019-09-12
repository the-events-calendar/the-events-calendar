<?php
/**
 * View: Base View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/base.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 *
 * @var string $view_slug This View slug.
 * @var string $view_class The fully-qualified name of the View.
 * @var array $lookup_folders A list of the folders that have been searched to find the View template, including
 *                            type and priority.
 * @var array $_context The current context array dump.
 */
?>
<div class="tribe-view tribe-view--base tribe-view--<?php echo esc_attr( $view_slug ) ?>">
	<p>We looked for a template file for the <code><?php echo esc_html( $view_slug ) ?></code> slug but could not find one.
	</p>
	<p>This was rendered by the <code><?php echo esc_html( $view_class ) ?></code> View.</p>
	<p>We've looked in the following locations:</p>
	<ul>
		<?php foreach ( $lookup_folders as $folder ) : ?>
			<li>
				<ul>
					<li>Id: <code><?php echo esc_html( $folder['id'] ) ?></code></li>
					<li>Priority: <code><?php echo esc_html( $folder['priority'] ) ?></code></li>
					<li>Path: <code><?php echo esc_html( $folder['path'] ) ?></code></li>
				</ul>
			</li>
		<?php endforeach; ?>
	</ul>
	<p>Template context:</p>
	<pre>
		<code>
			<?php echo esc_html( json_encode( $_context, JSON_PRETTY_PRINT ) ) ?>
		</code>
	</pre>
</div>

