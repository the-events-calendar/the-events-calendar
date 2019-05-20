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
		Now &mdash; May 20th, 2019	</span>
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
	<span>May</span>
</div>
	
		<div class="tribe-events-calendar-list__event">

	
	<div class="tribe-events-calendar-list__event--details">

		<header>
			<div class="tribe-events-calendar-list__event--datetime">
	<time datetime="1970-01-01T00:00:00+00:00">
		<span class="tribe-event-date-start">January 1, 2018 @ 10:00 am</span> - <span class="tribe-event-time">1:00 pm</span>	</time>
</div>			<h3 class="tribe-events-calendar-list__event--title">
	<a
		href="http://test.tri.be/?tribe_events=test-event-fa95745058001b098f83649aca569bf6%2F"
		title=""
		rel="bookmark"
		class="tribe-events-calendar-list__event--title-link"
	>
		Test Event fa95745058001b098f83649aca569bf6	</a>
</h3>			<div class="tribe-events-calendar-list__event--venue">
	<span class="tribe-events-calendar-list__event--venue--title">
		Venue Name	</span>
	<span class="tribe-address">






</span>
</div>		</header>

		<div class="tribe-events-calendar-list__event--description">
	</div>
	</div>

</div>
	
		<div class="tribe-events-calendar-list__event">

	
	<div class="tribe-events-calendar-list__event--details">

		<header>
			<div class="tribe-events-calendar-list__event--datetime">
	<time datetime="1970-01-01T00:00:00+00:00">
		<span class="tribe-event-date-start">January 2, 2018 @ 8:00 am</span> - <span class="tribe-event-time">11:00 am</span>	</time>
</div>			<h3 class="tribe-events-calendar-list__event--title">
	<a
		href="http://test.tri.be/?tribe_events=test-event-518de7f46caf4882f39a2fc6e3f8dca1%2F"
		title=""
		rel="bookmark"
		class="tribe-events-calendar-list__event--title-link"
	>
		Test Event 518de7f46caf4882f39a2fc6e3f8dca1	</a>
</h3>			<div class="tribe-events-calendar-list__event--venue">
	<span class="tribe-events-calendar-list__event--venue--title">
		Venue Name	</span>
	<span class="tribe-address">






</span>
</div>		</header>

		<div class="tribe-events-calendar-list__event--description">
	</div>
	</div>

</div>
	
		<div class="tribe-events-calendar-list__event">

	
	<div class="tribe-events-calendar-list__event--details">

		<header>
			<div class="tribe-events-calendar-list__event--datetime">
	<time datetime="1970-01-01T00:00:00+00:00">
		<span class="tribe-event-date-start">February 2, 2018 @ 11:00 am</span> - <span class="tribe-event-time">2:00 pm</span>	</time>
</div>			<h3 class="tribe-events-calendar-list__event--title">
	<a
		href="http://test.tri.be/?tribe_events=test-event-4cd179b5da9deb2d6f07e60842a35326%2F"
		title=""
		rel="bookmark"
		class="tribe-events-calendar-list__event--title-link"
	>
		Test Event 4cd179b5da9deb2d6f07e60842a35326	</a>
</h3>			<div class="tribe-events-calendar-list__event--venue">
	<span class="tribe-events-calendar-list__event--venue--title">
		Venue Name	</span>
	<span class="tribe-address">






</span>
</div>		</header>

		<div class="tribe-events-calendar-list__event--description">
	</div>
	</div>

</div>
	
</div>

<nav class="tribe-common-c-nav">
	<ul>
		<li>
	<a
		href="http://test.tri.be/events/list/?tribe_event_display=past&#038;tribe_paged=1"
		rel="prev"
		class="tribe-common-c-nav__prev"
	>
		Previous Events	</a>
</li>			</ul>
</nav>';
