<?php return '<div class="tribe-events__events-bar">

	<h2 class="tribe-common-a11y-visual-hide">Events Search and Views Navigation</h2>

	<div class="tribe-events__events-bar-form">

	<div class="tribe-common-c-search">
		<div class="tribe-common-form-control-text-group">
			<div class="tribe-common-form-control-text">
	<label for="keyword">Enter Keyword. Search for Events by Keyword.</label>
	<input
		type="text"
		id="keyword"
		name="keyword"
		placeholder="Keyword"
	/>
</div>
			<div class="tribe-common-form-control-text">
	<label for="location">Enter Location. Search for Events by Location.</label>
	<input
		type="text"
		id="location"
		name="location"
		placeholder="Location"
	/>
</div>			<div class="tribe-common-form-control-text">
	<label for="date">Enter date. Please use the format 4 digit year hyphen 2 digit month hyphen 2 digit day.</label>
	<input
		type="text"
		id="tribe-bar-date"
		name="tribe-bar-date"
		placeholder="Enter date"
	/>
</div>		</div>
		<button
	class="tribe-common-c-btn"
	type="submit"
	name="submit-bar"
>Find Events</button>	</div>
</div>
	<div class="tribe-events__events-bar-views">
	<h3 class="tribe-common-a11y-visual-hide">Event Views Navigation</h3>
	<div class="tribe-common-form-control-tabs">
		<button id="tribe-views-button" aria-haspopup="listbox" aria-labelledby="tribe-views-button" aria-expanded="true">Views</button>
		<ul tabindex="-1" role="listbox" aria-activedescendant="tribe-views-list-label">
			<li role="presentation">
				<input id="tribe-views-list" name="tribe-views" type="radio" value="tribe-views-list" checked="checked" />
				<label id="tribe-views-list-label" for="tribe-views-list" role="option" aria-selected="true">List</label>
			</li>
			<li role="presentation">
				<input id="tribe-views-month" name="tribe-views" type="radio" value="tribe-views-month" />
				<label id="tribe-views-month-label" for="tribe-views-month" role="option">Month</label>
			</li>
			<li role="presentation">
				<input id="tribe-views-week" name="tribe-views" type="radio" value="tribe-views-week" />
				<label id="tribe-views-week-label" for="tribe-views-week" role="option">Week</label>
			</li>
		</ul>
	</div>
</div>
	<div class="tribe-events__events-bar-filters">
	<div class="tribe-events__events-bar-filters--search">
		<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--search "></button>
	</div>
	<div class="tribe-events__events-bar-filters--filters">
		<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--filters"></button>
	</div>
</div>
</div><div class="tribe-events__top-bar">

	<div class="tribe-events__top-bar-nav-wrapper">
	<nav class="tribe-events__top-bar-nav">
		<ul>
			<li class="tribe-events__top-bar-nav-prev">
				<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-common-b3"></a>
			</li>
			<li class="tribe-events__top-bar-nav-next">
				<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-common-b3"></a>
			</li>
		</ul>
	</nav>
</div>
	<div class="tribe-events__top-bar-today">
	<a href="#" class="tribe-common-c-btn-border tribe-events__top-bar-today--button">
		Today	</a>

	<span class="tribe-common-h3 tribe-common-h3--alt">
		Now &mdash; January 1st, 2019	</span>
</div>
	<div class="tribe-events__top-bar-actions">
	<div class="tribe-common-form-control-toggle">
		<input id="hide-recurring" name="hide-recurring" type="checkbox" value="false" />
		<label for="hide-recurring">Hide Recurring Events</label>
	</div>
</div>
</div>
<div class="tribe-events-calendar-list">

	<div class="tribe-events-calendar-list__separator--month">
	<span>Jan</span>
</div>
	
</div>

<nav class="tribe-common-c-nav">
	<ul>
					</ul>
</nav>';
