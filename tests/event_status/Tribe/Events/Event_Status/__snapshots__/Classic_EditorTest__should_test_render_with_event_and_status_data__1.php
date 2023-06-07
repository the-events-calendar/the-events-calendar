<?php return '<div class="tribe-events-status_metabox__container">
	<input type="hidden" id="tribe-events-status[nonce]" name="tribe-events-status[nonce]" value="123123" /><input type="hidden" name="_wp_http_referer" value="" />
	<label for="tribe-events-status-status">
		Set status:	</label>
	<div
	class="tribe-events-status tribe-events-status-select"
>
	<label
		class="screen-reader-text tribe-events-status-label__text"
		for="tribe-events-status-status"
	>
		Set status:	</label>
	<select
		id="tribe-events-status-status"
		name="tribe-events-status[status]"
		class="tribe-dropdown tribe-events-status__status-select"
		value=""
		 data-placeholder="Select an Event Status" data-hide-search data-prevent-clear data-options="[{&quot;text&quot;:&quot;Scheduled&quot;,&quot;id&quot;:&quot;scheduled&quot;,&quot;value&quot;:&quot;scheduled&quot;,&quot;selected&quot;:false},{&quot;text&quot;:&quot;Canceled&quot;,&quot;id&quot;:&quot;canceled&quot;,&quot;value&quot;:&quot;canceled&quot;,&quot;selected&quot;:false},{&quot;text&quot;:&quot;Postponed&quot;,&quot;id&quot;:&quot;postponed&quot;,&quot;value&quot;:&quot;postponed&quot;,&quot;selected&quot;:false}]" 	>
					<option value="scheduled" >Scheduled</option>
					<option value="canceled" >Canceled</option>
					<option value="postponed" >Postponed</option>
			</select>
</div>
	<div
		class="tribe-dependent"
		data-depends="#tribe-events-status-status"
		data-condition=\'["canceled", "postponed"]\'
	>
		<div class="tribe-events-status-components-textarea-control__container">
			<label
				class="tribe-events-status-components-textarea-control__label"
				for="tribe-events-status-status-reason"
			>
				Reason (optional).
			</label>
			<textarea
				class="tribe-events-status-components-textarea-control__input"
				id="tribe-events-status-status-reason"
				name="tribe-events-status[status-reason]"
			></textarea>
		</div>
	</div>
</div>
';
