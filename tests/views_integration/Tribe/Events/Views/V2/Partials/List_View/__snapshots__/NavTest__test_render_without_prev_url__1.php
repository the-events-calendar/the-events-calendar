<?php return '<nav class="tribe-events-calendar-list-nav tribe-events-c-nav">
	<ul class="tribe-events-c-nav__list">
		<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		aria-label="Previous Events"
		title="Previous Events"
		disabled
	>
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-left tribe-events-c-nav__prev-icon-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M9.7 14.4l-1.5 1.5L.3 8 8.2.1l1.5 1.5L3.3 8l6.4 6.4z"/></svg>
		<span class="tribe-events-c-nav__prev-label">
			Previous <span class="tribe-events-c-nav__prev-label-plural tribe-common-a11y-visual-hide">Events</span>		</span>
	</button>
</li>

		<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--today">
	<a
		href="http://test.tri.be"
		class="tribe-events-c-nav__today tribe-common-b2"
		data-js="tribe-events-view-link"
		aria-label="Click to select today&#039;s date"
		title="Click to select today&#039;s date"
	>
		Today	</a>
</li>

		<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<a
		href="http://test.tri.be"
		rel="next"
		class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium"
		data-js="tribe-events-view-link"
		aria-label="Next Events"
		title="Next Events"
	>
		<span class="tribe-events-c-nav__next-label">
			Next <span class="tribe-events-c-nav__next-label-plural tribe-common-a11y-visual-hide">Events</span>		</span>
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-right tribe-events-c-nav__next-icon-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M.3 1.6L1.8.1 9.7 8l-7.9 7.9-1.5-1.5L6.7 8 .3 1.6z"/></svg>
	</a>
</li>
	</ul>
</nav>
';
