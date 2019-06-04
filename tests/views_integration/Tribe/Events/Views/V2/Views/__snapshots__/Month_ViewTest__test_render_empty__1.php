<?php return '<div class="tribe-events-c-events-bar">

	<h2 class="tribe-common-a11y-visual-hide">Events Search and Views Navigation</h2>

	<div class="tribe-events-c-events-bar__views">
	<h3 class="tribe-common-a11y-visual-hide">Event Views Navigation</h3>
	<div class="tribe-common-form-control-tabs tribe-events-c-events-bar__views-tabs">
		<button class="tribe-common-form-control-tabs__button tribe-events-c-events-bar__views-tabs-button" id="tribe-views-button" aria-haspopup="listbox" aria-labelledby="tribe-views-button" aria-expanded="true">Views</button>
		<ul class="tribe-common-form-control-tabs__list tribe-events-c-events-bar__views-tabs-list" tabindex="-1" role="listbox" aria-activedescendant="tribe-views-list-label">
			<li class="tribe-common-form-control-tabs__list-item" role="presentation">
				<input class="tribe-common-form-control-tabs__input" id="tribe-views-list" name="tribe-views" type="radio" value="tribe-views-list" checked="checked" />
				<label class="tribe-common-form-control-tabs__label" id="tribe-views-list-label" for="tribe-views-list" role="option" aria-selected="true">List</label>
			</li>
			<li class="tribe-common-form-control-tabs__list-item" role="presentation">
				<input class="tribe-common-form-control-tabs__input" id="tribe-views-month" name="tribe-views" type="radio" value="tribe-views-month" />
				<label class="tribe-common-form-control-tabs__label" id="tribe-views-month-label" for="tribe-views-month" role="option">Month</label>
			</li>
			<li class="tribe-common-form-control-tabs__list-item" role="presentation">
				<input class="tribe-common-form-control-tabs__input" id="tribe-views-week" name="tribe-views" type="radio" value="tribe-views-week" />
				<label class="tribe-common-form-control-tabs__label" id="tribe-views-week-label" for="tribe-views-week" role="option">Week</label>
			</li>
		</ul>
	</div>
</div>

	<div class="tribe-events-c-events-bar__filters">
	<div class="tribe-events-c-events-bar__filters-button-wrapper tribe-events-c-events-bar__filters-button-wrapper--search">
		<button
			class="tribe-common-c-btn-icon tribe-common-c-btn-icon--search tribe-events-c-events-bar__filters-button tribe-events-c-events-bar__filters-button--search"
			aria-label="Search"
			title="Search"
		>
		</button>
	</div>
	<div class="tribe-events-c-events-bar__filters-button-wrapper tribe-events-c-events-bar__filters-button-wrapper--filter">
		<button
			class="tribe-common-c-btn-icon tribe-common-c-btn-icon--filters tribe-events-c-events-bar__filters-button tribe-events-c-events-bar__filters-button--filter"
			aria-label="Filter"
			title="Filter"
		>
		</button>
	</div>
</div>

	<div class="tribe-events-c-events-bar__form">
	<form
		class="tribe-common-c-search"
		method="get"
		data-js="tribe-events-view-form"
	>
		<div class="tribe-common-form-control-text-group tribe-common-c-search__input-group">
			<div class="tribe-common-form-control-text">
	<label class="tribe-common-form-control-text__label" for="keyword">Enter Keyword. Search for Events by Keyword.</label>
	<input
		class="tribe-common-form-control-text__input tribe-common-c-search__input"
		type="text"
		id="keyword"
		name="keyword"
		placeholder="Keyword"
	/>
</div>
			<div class="tribe-common-form-control-text">
	<label class="tribe-common-form-control-text__label" for="location">Enter Location. Search for Events by Location.</label>
	<input
		class="tribe-common-form-control-text__input tribe-common-c-search__input"
		type="text"
		id="location"
		name="location"
		placeholder="Location"
	/>
</div>
			<div class="tribe-common-form-control-text">
	<label class="tribe-common-form-control-text__label" for="tribe-bar-date">Enter date. Please use the format 4 digit year hyphen 2 digit month hyphen 2 digit day.</label>
	<input
		class="tribe-common-form-control-text__input tribe-common-c-search__input"
		type="text"
		id="tribe-bar-date"
		name="tribe-bar-date"
		placeholder="Enter date"
	/>
</div>
		</div>
		<button
	class="tribe-common-c-btn tribe-common-c-search__button"
	type="submit"
	name="submit-bar"
>Find Events</button>
	</form>
</div>

</div>
<div class="tribe-events-c-top-bar">

	<div class="tribe-events-c-top-bar__nav-wrapper">
	<nav class="tribe-events-c-top-bar__nav">
		<ul class="tribe-events-c-top-bar__nav-list">
								</ul>
	</nav>
</div>

	<div class="tribe-events-c-top-bar__today">
	<a href="#" class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button">
		Today	</a>

	<span class="tribe-common-h3 tribe-common-h3--alt tribe-events-c-top-bar__today-title">
		Now &mdash; <time datetime="2019-01-01">January 1st, 2019</time>
	</span>
</div>

	<div class="tribe-events-c-top-bar__actions">
	<div class="tribe-common-form-control-toggle">
		<input class="tribe-common-form-control-toggle__input" id="hide-recurring" name="hide-recurring" type="checkbox" value="false" />
		<label class="tribe-common-form-control-toggle__label" for="hide-recurring">Hide Recurring Events</label>
	</div>
</div>

</div>

<div class="tribe-events-calendar-month" role="grid" aria-labelledby="tribe-calendar-header" aria-readonly="true">

	<header role="rowgroup">

	<h2 class="tribe-common-a11y-visual-hide" id="tribe-calendar-header">Calendar of Events</h2>

	<div role="row" class="tribe-events-calendar-month__header">
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Monday"
				>
					<h3 class="tribe-common-b3">
						Mon					</h3>
				</div>
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Tuesday"
				>
					<h3 class="tribe-common-b3">
						Tue					</h3>
				</div>
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Wednesday"
				>
					<h3 class="tribe-common-b3">
						Wed					</h3>
				</div>
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Thursday"
				>
					<h3 class="tribe-common-b3">
						Thu					</h3>
				</div>
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Friday"
				>
					<h3 class="tribe-common-b3">
						Fri					</h3>
				</div>
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Saturday"
				>
					<h3 class="tribe-common-b3">
						Sat					</h3>
				</div>
						<div
					class="tribe-events-calendar-month__header-column"
					role="columnheader"
					aria-label="Sunday"
				>
					<h3 class="tribe-common-b3">
						Sun					</h3>
				</div>
			</div>
</header>

	<div class="tribe-events-calendar-month__body" role="rowgroup">

				
			<div class="tribe-events-calendar-month__week" role="row">

				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-1">
	<div id="tribe-events-calendar-day-5-1">
		<h3 class="tribe-events-calendar-month__day-date tribe-events-calendar-month__day-date--current">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link tribe-events-calendar-month__day-date-link--current"
				>
					1				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-2">
	<div id="tribe-events-calendar-day-5-2">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					2				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-3">
	<div id="tribe-events-calendar-day-5-3">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					3				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-4">
	<div id="tribe-events-calendar-day-5-4">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					4				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-5">
	<div id="tribe-events-calendar-day-5-5">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					5				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-6">
	<div id="tribe-events-calendar-day-5-6">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					6				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-7">
	<div id="tribe-events-calendar-day-5-7">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					7				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
			</div>

		
			<div class="tribe-events-calendar-month__week" role="row">

				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-8">
	<div id="tribe-events-calendar-day-5-8">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					8				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-9">
	<div id="tribe-events-calendar-day-5-9">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					9				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-10">
	<div id="tribe-events-calendar-day-5-10">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					10				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-11">
	<div id="tribe-events-calendar-day-5-11">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					11				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-12">
	<div id="tribe-events-calendar-day-5-12">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					12				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-13">
	<div id="tribe-events-calendar-day-5-13">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					13				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-14">
	<div id="tribe-events-calendar-day-5-14">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					14				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
			</div>

		
			<div class="tribe-events-calendar-month__week" role="row">

				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-15">
	<div id="tribe-events-calendar-day-5-15">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					15				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-16">
	<div id="tribe-events-calendar-day-5-16">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					16				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-17">
	<div id="tribe-events-calendar-day-5-17">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					17				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-18">
	<div id="tribe-events-calendar-day-5-18">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					18				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-19">
	<div id="tribe-events-calendar-day-5-19">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					19				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-20">
	<div id="tribe-events-calendar-day-5-20">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					20				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-21">
	<div id="tribe-events-calendar-day-5-21">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					21				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
			</div>

		
			<div class="tribe-events-calendar-month__week" role="row">

				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-22">
	<div id="tribe-events-calendar-day-5-22">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					22				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-23">
	<div id="tribe-events-calendar-day-5-23">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					23				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-24">
	<div id="tribe-events-calendar-day-5-24">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					24				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-25">
	<div id="tribe-events-calendar-day-5-25">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					25				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-26">
	<div id="tribe-events-calendar-day-5-26">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					26				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-27">
	<div id="tribe-events-calendar-day-5-27">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					27				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
					
<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="tribe-events-calendar-day-5-28">
	<div id="tribe-events-calendar-day-5-28">
		<h3 class="tribe-events-calendar-month__day-date">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					28				</a>
			</time>
		</h3>
	</div>
	<!-- Events for this day will be listed here -->
</div>
				
			</div>

		
	</div>
</div>
';
