<?php return '
<div
	 class="tribe-widget-form" >
	
<div
	 class="tribe-widget-fields" >
	<div
	class="tribe-widget-form-control tribe-widget-form-control--text"
	>
	<label
		class="tribe-common-form-control__label"
		for="widget-tribe-widget-events-list--title"
	>
		Title:	</label>
	<input
		class="tribe-common-form-control__input widefat"
		id="widget-tribe-widget-events-list--title"
		name="widget-tribe-widget-events-list[][title]"
		type="text"
		value=""
	/>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--text"
	>
	<label
		class="tribe-common-form-control__label"
		for="widget-tribe-widget-events-list--limit"
	>
		Show:	</label>
	<input
		class="tribe-common-form-control__input widefat"
		id="widget-tribe-widget-events-list--limit"
		name="widget-tribe-widget-events-list[][limit]"
		type="number"
		min="1"
		max="10"
		step="1"
		value=""
	/>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	>
	<input
		class="tribe-widget-form-control__input"
		id="widget-tribe-widget-events-list--no_upcoming_events"
		name="widget-tribe-widget-events-list[][no_upcoming_events]"
		type="checkbox"
		value="1"
			/>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-widget-events-list--no_upcoming_events"
	>
		Hide this widget if there are no upcoming events.	</label>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	>
	<input
		class="tribe-widget-form-control__input"
		id="widget-tribe-widget-events-list--featured_events_only"
		name="widget-tribe-widget-events-list[][featured_events_only]"
		type="checkbox"
		value="1"
			/>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-widget-events-list--featured_events_only"
	>
		Limit to featured events only	</label>
</div>
<div
	class="tribe-widget-form-control tribe-widget-form-control--checkbox"
	>
	<input
		class="tribe-widget-form-control__input"
		id="widget-tribe-widget-events-list--jsonld_enable"
		name="widget-tribe-widget-events-list[][jsonld_enable]"
		type="checkbox"
		value="1"
			/>
	<label
		class="tribe-widget-form-control__label"
		for="widget-tribe-widget-events-list--jsonld_enable"
	>
		Generate JSON-LD data	</label>
</div>
</div>
</div>

';
