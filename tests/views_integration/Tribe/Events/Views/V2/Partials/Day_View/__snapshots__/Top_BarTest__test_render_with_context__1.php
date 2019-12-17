<?php return '<div class="tribe-events-c-top-bar tribe-events-header__top-bar">

	<nav class="tribe-events-c-top-bar__nav tribe-common-a11y-hidden">
	<ul class="tribe-events-c-top-bar__nav-list">
		<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="Prev"
		title="Prev"
		disabled
	>
	</button>
</li>

		<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		aria-label="Next"
		title="Next"
		disabled
	>
	</button>
</li>
	</ul>
</nav>

	<a
	href="http://test.tri.be"
	class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button tribe-common-a11y-hidden"
	data-js="tribe-events-view-link"
>
	Today</a>

		<form
		class="tribe-events-c-top-bar__datepicker-form"
		method="get"
		data-js="tribe-events-view-form"
	>
		<input type="hidden" id="tribe-events-views[_wpnonce]" name="tribe-events-views[_wpnonce]" value="2ab7cc6b39" /><input type="hidden" name="_wp_http_referer" value="" />		<input type="hidden" name="tribe-events-views[url]" value="" />

	<div class="tribe-events-c-top-bar__datepicker">
		<button
			class="tribe-common-h3 tribe-common-h--alt tribe-events-c-top-bar__datepicker-button"
			data-js="tribe-events-top-bar-datepicker-button"
			type="button"
		>
			<time
				datetime="2018-01-01"
				class="tribe-events-c-top-bar__datepicker-time"
			>
				January 1, 2018			</time>
		</button>
		<label
			class="tribe-events-c-top-bar__datepicker-label tribe-common-a11y-visual-hide"
			for="tribe-events-top-bar-date"
		>
			Select date.		</label>
		<input
			type="text"
			class="tribe-events-c-top-bar__datepicker-input tribe-common-a11y-visual-hide"
			data-js="tribe-events-top-bar-date"
			id="tribe-events-top-bar-date"
			name="tribe-events-views[tribe-bar-date]"
			value="01/01/2018"
			tabindex="-1"
			autocomplete="off"
		/>
		<div class="tribe-events-c-top-bar__datepicker-container" data-js="tribe-events-top-bar-datepicker-container"></div>
	</div>

		<button
	class="tribe-common-c-btn tribe-common-a11y-hidden tribe-events-c-top-bar__datepicker-submit"
	type="submit"
	name="submit-bar"
>
	Find Events</button>
	</form>

	<div class="tribe-events-c-top-bar__actions">
	</div>

</div>
';
