<?php return '<div
	class="tribe-common tribe-events tribe-events-view tribe-events-view--list"
	data-js="tribe-events-view"
	data-view-rest-nonce="2ab7cc6b39"
	data-view-rest-url="http://test.tri.be/index.php?rest_route=/tribe/views/v2/html"
	data-view-manage-url="1"
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<div
	class="tribe-events-view-loader tribe-common-a11y-hidden"
	role="alert"
	aria-live="assertive"
>
	<div class="tribe-events-view-loader__spinner">
		Loading...	</div>
</div>

		<script
	data-js="tribe-events-view-data"
	type="application/json"
>{"slug":"list","prev_url":"","next_url":"","view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\List_View","view_slug":"list","view":{},"title":"","events":[],"url":"http:\\/\\/test.tri.be\\/events\\/list\\/","bar":{"keyword":"","date":""},"today":"2019-01-01 09:00:00","now":"2019-01-01 09:00:00","rest_url":"http:\\/\\/test.tri.be\\/index.php?rest_route=\\/tribe\\/views\\/v2\\/html","rest_nonce":"2ab7cc6b39","should_manage_url":true,"today_url":"http:\\/\\/test.tri.be\\/events\\/list\\/"}</script>

		<header class="tribe-events-header">
			<div
	class="tribe-events-header__events-bar tribe-events-c-events-bar"
	data-js="tribe-events-events-bar"
>

	<h2 class="tribe-common-a11y-visual-hide">Events Search and Views Navigation</h2>

	<button
	class="tribe-events-c-events-bar__search-button"
	aria-controls="tribe-events-search-filter-container"
	aria-expanded="false"
	aria-selected="false"
	data-js="tribe-events-search-button"
>
	<span class="tribe-events-c-events-bar__search-button-icon tribe-common-svgicon"></span>
	<span class="tribe-events-c-events-bar__search-button-text tribe-common-a11y-visual-hide">
		Search	</span>
</button>

	<div
		class="tribe-events-c-events-bar__search-filters-container"
		id="tribe-events-search-filters-container"
		data-js="tribe-events-search-filters-container"
	>
		
		<div
	class="tribe-events-c-events-bar__search"
	id="tribe-events-events-bar-search"
	data-js="tribe-events-events-bar-tabpanel tribe-events-events-bar-search"
>
	<form
		class="tribe-events-c-search tribe-events-c-events-bar__search-form"
		method="get"
		data-js="tribe-events-view-form"
		role="search"
	>
		<input type="hidden" id="tribe-events-views[_wpnonce]" name="tribe-events-views[_wpnonce]" value="2ab7cc6b39" /><input type="hidden" name="_wp_http_referer" value="/events/list/" />		<input type="hidden" name="tribe-events-views[url]" value="http://test.tri.be/events/list/" />

		<div class="tribe-events-c-search__input-group">
			<div
	class="tribe-common-form-control-text tribe-events-c-search__input-control tribe-events-c-search__input-control--keyword"
	data-js="tribe-events-events-bar-input-control"
>
	<label class="tribe-common-form-control-text__label" for="tribe-events-events-bar-keyword">
		Enter Keyword. Search for Events by Keyword.	</label>
	<input
		class="tribe-common-form-control-text__input tribe-events-c-search__input tribe-events-c-search__input--icon"
		data-js="tribe-events-events-bar-input-control-input"
		type="text"
		id="tribe-events-events-bar-keyword"
		name="tribe-events-views[tribe-bar-search]"
		value=""
		placeholder="Search for events"
	/>
</div>
		</div>

		<button
	class="tribe-common-c-btn tribe-events-c-search__button"
	type="submit"
	name="submit-bar"
>Find Events</button>
	</form>
</div>

			</div>

	<div class="tribe-events-c-events-bar__views">
	<h3 class="tribe-common-a11y-visual-hide">
		Event Views Navigation	</h3>
	<div  class="tribe-events-c-view-selector tribe-events-c-view-selector--tabs"  data-js="tribe-events-view-selector">
		<button
			class="tribe-events-c-view-selector__button"
			data-js="tribe-events-view-selector-button"
		>
			<span class="tribe-events-c-view-selector__button-icon tribe-common-svgicon tribe-common-svgicon--list"></span>
			<span class="tribe-events-c-view-selector__button-text">
				List			</span>
		</button>
		<div
	class="tribe-events-c-view-selector__content"
	id="tribe-events-view-selector-content"
	data-js="tribe-events-view-selector-list-container"
>
	<ul class="tribe-events-c-view-selector__list">
					<li class="tribe-events-c-view-selector__list-item tribe-events-c-view-selector__list-item--list tribe-events-c-view-selector__list-item--active">
	<a
		href="http://test.tri.be/events/list/"
		class="tribe-events-c-view-selector__list-item-link"
		data-js="tribe-events-view-link"
	>
		<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--list"></span>
		<span class="tribe-events-c-view-selector__list-item-text">
			List		</span>
	</a>
</li>
					<li class="tribe-events-c-view-selector__list-item tribe-events-c-view-selector__list-item--month">
	<a
		href="http://test.tri.be/events/month/"
		class="tribe-events-c-view-selector__list-item-link"
		data-js="tribe-events-view-link"
	>
		<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--month"></span>
		<span class="tribe-events-c-view-selector__list-item-text">
			Month		</span>
	</a>
</li>
					<li class="tribe-events-c-view-selector__list-item tribe-events-c-view-selector__list-item--day">
	<a
		href="http://test.tri.be/events/today/"
		class="tribe-events-c-view-selector__list-item-link"
		data-js="tribe-events-view-link"
	>
		<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--day"></span>
		<span class="tribe-events-c-view-selector__list-item-text">
			Day		</span>
	</a>
</li>
			</ul>
</div>
	</div>
</div>

</div>

			<div class="tribe-events-c-top-bar tribe-events-header__top-bar">

	<nav class="tribe-events-c-top-bar__nav">
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
	href="http://test.tri.be/events/list/"
	class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button"
	data-js="tribe-events-view-link"
>
	Today</a>

	<div class="tribe-events-c-top-bar__datepicker">
	<button
		class="tribe-common-h2 tribe-common-h3--min-medium tribe-common-h--alt tribe-events-c-top-bar__datepicker-button"
		data-js="tribe-events-top-bar-datepicker-button"
	>
					Now				&mdash;
		<time datetime="2019-01-01">
			January 1		</time>
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
		name="tribe-events-views[tribe-bar-search]"
		value=""
		tabindex="-1"
		autocomplete="off"
	/>
	<div class="tribe-events-c-top-bar__datepicker-container" data-js="tribe-events-top-bar-datepicker-container"></div>
</div>

	<div class="tribe-events-c-top-bar__actions">
	</div>

</div>
		</header>

		<div class="tribe-events-calendar-list">

			
		</div>

		<nav class="tribe-events-calendar-list-nav tribe-events-c-nav">
	<ul class="tribe-events-c-nav__list">
		<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium" disabled>
		Previous <span class="tribe-events-c-nav__prev-label-plural"> Events</span>	</button>
</li>

		<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--today">
	<a
		href="#"
		class="tribe-events-c-nav__today tribe-common-b2"
		data-js="tribe-events-view-link"
	>
		Today	</a>
</li>

		<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<button class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium" disabled>
		Next <span class="tribe-events-c-nav__next-label-plural"> Events</span>	</button>
</li>
	</ul>
</nav>
	</div>
</div>
';
