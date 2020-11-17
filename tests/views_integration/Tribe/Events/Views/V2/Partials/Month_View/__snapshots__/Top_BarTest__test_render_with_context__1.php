<?php return '<div class="tribe-events-c-top-bar tribe-events-header__top-bar">

	<nav class="tribe-events-c-top-bar__nav tribe-common-a11y-hidden">
	<ul class="tribe-events-c-top-bar__nav-list">
		<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="Previous month"
		title="Previous month"
		disabled
	>
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-left tribe-common-c-btn-icon-svg tribe-common-c-btn-icon-svg--caret-left tribe-events-c-top-bar__nav-link-icon-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M9.7 14.4l-1.5 1.5L.3 8 8.2.1l1.5 1.5L3.3 8l6.4 6.4z"/></svg>
	</button>
</li>

		<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		aria-label="Next month"
		title="Next month"
		disabled
	>
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-right tribe-common-c-btn-icon-svg tribe-common-c-btn-icon-svg--caret-right tribe-events-c-top-bar__nav-link-icon-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M.3 1.6L1.8.1 9.7 8l-7.9 7.9-1.5-1.5L6.7 8 .3 1.6z"/></svg>
	</button>
</li>
	</ul>
</nav>

	<a
	href="http://test.tri.be"
	class="tribe-common-c-btn-border-small tribe-events-c-top-bar__today-button tribe-common-a11y-hidden"
	data-js="tribe-events-view-link"
	aria-label="Click to select today&#039;s date"
	title="Click to select today&#039;s date"
>
	Today</a>

	<div class="tribe-events-c-top-bar__datepicker">
	<button
		class="tribe-common-h3 tribe-common-h--alt tribe-events-c-top-bar__datepicker-button"
		data-js="tribe-events-top-bar-datepicker-button"
		type="button"
		aria-label="Click to toggle datepicker"
		title="Click to toggle datepicker"
	>
		<time
			datetime="2018-01"
			class="tribe-events-c-top-bar__datepicker-time"
		>
			<span class="tribe-events-c-top-bar__datepicker-mobile">
				1/2018			</span>
			<span class="tribe-events-c-top-bar__datepicker-desktop tribe-common-a11y-hidden">
				January 2018			</span>
		</time>
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-down tribe-events-c-top-bar__datepicker-button-icon-svg"  viewBox="0 0 10 7" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.008.609L5 4.6 8.992.61l.958.958L5 6.517.05 1.566l.958-.958z" class="tribe-common-c-svgicon__svg-fill"/></svg>
	</button>
	<label
		class="tribe-events-c-top-bar__datepicker-label tribe-common-a11y-visual-hide"
		for="tribe-events-top-bar-date"
	>
		Select date.	</label>
	<input
		type="text"
		class="tribe-events-c-top-bar__datepicker-input tribe-common-a11y-visual-hide"
		data-js="tribe-events-top-bar-date"
		id="tribe-events-top-bar-date"
		name="tribe-events-views[tribe-bar-date]"
		value="01/01/2018"
		tabindex="-1"
		autocomplete="off"
		readonly="readonly"
	/>
	<div class="tribe-events-c-top-bar__datepicker-container" data-js="tribe-events-top-bar-datepicker-container"></div>
	<template class="tribe-events-c-top-bar__datepicker-template-prev-icon">
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-left tribe-events-c-top-bar__datepicker-nav-icon-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M9.7 14.4l-1.5 1.5L.3 8 8.2.1l1.5 1.5L3.3 8l6.4 6.4z"/></svg>
	</template>
	<template class="tribe-events-c-top-bar__datepicker-template-next-icon">
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-right tribe-events-c-top-bar__datepicker-nav-icon-svg"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 16"><path d="M.3 1.6L1.8.1 9.7 8l-7.9 7.9-1.5-1.5L6.7 8 .3 1.6z"/></svg>
	</template>
</div>

	<div class="tribe-events-c-top-bar__actions tribe-common-a11y-hidden">
	</div>

</div>
';
