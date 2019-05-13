<main>
	<header>
		<h3>List View (WIP)</h3>
	</header>
	<main>
		<?php foreach ( $events as $event ) : ?>
			<article>
				<header>
					<h4><?php echo $event->post_title ?></h4>
					<main>
						<?php echo $event->post_content ?>
					</main>
				</header>
			</article>
		<?php endforeach; ?>
	</main>
</main>