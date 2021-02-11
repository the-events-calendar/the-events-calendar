<?php return '<div
	class="tribe-widget-form-control tribe-widget-form-control--text"
	>
	<label
		class="tribe-common-form-control__label"
		for="widget-tribe-events-list-widget--title"
	>
		Title:	</label>
	<input
		class="tribe-common-form-control__input widefat"
		id="widget-tribe-events-list-widget--title"
		name="widget-tribe-events-list-widget[][title]"
		type="text"
		value="Upcoming Events"
	/>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--dropdown"
	>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-events-list-widget--limit"
	>
		Show:	</label>
	<select
		id="widget-tribe-events-list-widget--limit"
		name="widget-tribe-events-list-widget[][limit]"
		class="tribe-widget-form-control__input widefat"
	>
					<option
				value="1"
							>
				1			</option>
					<option
				value="2"
							>
				2			</option>
					<option
				value="3"
							>
				3			</option>
					<option
				value="4"
							>
				4			</option>
					<option
				value="5"
				 selected=\'selected\'			>
				5			</option>
					<option
				value="6"
							>
				6			</option>
					<option
				value="7"
							>
				7			</option>
					<option
				value="8"
							>
				8			</option>
					<option
				value="9"
							>
				9			</option>
					<option
				value="10"
							>
				10			</option>
			</select>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	>
	<input
		class="tribe-widget-form-control__input"
		id="widget-tribe-events-list-widget--no_upcoming_events"
		name="widget-tribe-events-list-widget[][no_upcoming_events]"
		type="checkbox"
		value="1"
			/>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-events-list-widget--no_upcoming_events"
	>
		Hide this widget if there are no upcoming events.	</label>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	>
	<input
		class="tribe-widget-form-control__input"
		id="widget-tribe-events-list-widget--featured_events_only"
		name="widget-tribe-events-list-widget[][featured_events_only]"
		type="checkbox"
		value="1"
			/>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-events-list-widget--featured_events_only"
	>
		Limit to featured events only	</label>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	>
	<input
		class="tribe-widget-form-control__input"
		id="widget-tribe-events-list-widget--jsonld_enable"
		name="widget-tribe-events-list-widget[][jsonld_enable]"
		type="checkbox"
		value="1"
		 checked=\'checked\'	/>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-events-list-widget--jsonld_enable"
	>
		Generate JSON-LD data	</label>
</div>
';
