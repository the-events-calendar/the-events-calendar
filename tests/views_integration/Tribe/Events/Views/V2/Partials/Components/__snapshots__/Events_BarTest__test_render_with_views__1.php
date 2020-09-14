<?php return '<div
	 class="tribe-events-header__events-bar tribe-events-c-events-bar tribe-events-c-events-bar--border" 	data-js="tribe-events-events-bar"
>

	<h2 class="tribe-common-a11y-visual-hide">
		Events Search and Views Navigation	</h2>

			<button
	class="tribe-events-c-events-bar__search-button"
	aria-controls="tribe-events-search-container"
	aria-expanded="false"
	data-js="tribe-events-search-button"
>
	<span class="tribe-events-c-events-bar__search-button-icon tribe-common-svgicon"></span>
	<span class="tribe-events-c-events-bar__search-button-text tribe-common-a11y-visual-hide">
		Search	</span>
</button>

		<div
			class="tribe-events-c-events-bar__search-container"
			id="tribe-events-search-container"
			data-js="tribe-events-search-container"
		>
			<div
	class="tribe-events-c-events-bar__search"
	id="tribe-events-events-bar-search"
	data-js="tribe-events-events-bar-search"
>
	<form
		class="tribe-events-c-search tribe-events-c-events-bar__search-form"
		method="get"
		data-js="tribe-events-view-form"
		role="search"
	>
		<input type="hidden" id="tribe-events-views[_wpnonce]" name="tribe-events-views[_wpnonce]" value="2ab7cc6b39" /><input type="hidden" name="_wp_http_referer" value="" />		<input type="hidden" name="tribe-events-views[url]" value="http://test.tri.be" />

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
		aria-label="Enter Keyword. Search for events by Keyword."
	/>
</div>
		</div>

		<button
	class="tribe-common-c-btn tribe-events-c-search__button"
	type="submit"
	name="submit-bar"
>
	Find Events</button>
	</form>
</div>
		</div>
	
	<div class="tribe-events-c-events-bar__views">
	<h3 class="tribe-common-a11y-visual-hide">
		Event Views Navigation	</h3>
	<div  class="tribe-events-c-view-selector tribe-events-c-view-selector--labels tribe-events-c-view-selector--tabs"  data-js="tribe-events-view-selector">
		<button
			class="tribe-events-c-view-selector__button"
			data-js="tribe-events-view-selector-button"
		>
			<span class="tribe-events-c-view-selector__button-icon tribe-common-svgicon tribe-common-svgicon--list"></span>
			<span class="tribe-events-c-view-selector__button-text tribe-common-a11y-visual-hide">
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
		href="https://test.tri.be/events/list/"
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
		href="https://test.tri.be/events/month/"
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
		href="https://test.tri.be/events/today/"
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
';
