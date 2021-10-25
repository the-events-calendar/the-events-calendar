<?php return '<div class="tribe-events-control-metabox-container" style="margin-top: 24px;">
	<input type="hidden" id="tribe-events-control[nonce]" name="tribe-events-control[nonce]" value="123123" /><input type="hidden" name="_wp_http_referer" value="" />
	<label for="tribe-events-control-status">
		Set status:	</label>
	<div
	class="tribe-events--control tribe-events-control--select"
>
	<label
		class="screen-reader-text tribe-events-control__label"
		for="tribe-events-control-status"
	>
		Set status:	</label>
	<select
		id="tribe-events-control-status"
		name="tribe-events-control[status]"
		class="tribe-dropdown tribe-events-status__status-select"
		value=""
		style="width: 100%;" 		 data-placeholder="Select an Event Status" data-hide-search data-prevent-clear data-options="[{&quot;text&quot;:&quot;Scheduled&quot;,&quot;id&quot;:&quot;scheduled&quot;,&quot;value&quot;:&quot;scheduled&quot;,&quot;selected&quot;:false},{&quot;text&quot;:&quot;Canceled&quot;,&quot;id&quot;:&quot;canceled&quot;,&quot;value&quot;:&quot;canceled&quot;,&quot;selected&quot;:false},{&quot;text&quot;:&quot;Postponed&quot;,&quot;id&quot;:&quot;postponed&quot;,&quot;value&quot;:&quot;postponed&quot;,&quot;selected&quot;:false}]" 	>
	</select>
</div>
	<div
		class="tribe-dependent"
		data-depends="#tribe-events-control-status"
		data-condition=\'["canceled", "postponed"]\'
	>
		<p>
			<label for="tribe-events-control-status-reason">
				Reason (optional).
			</label>
			<textarea
				class="components-textarea-control__input"
				id="tribe-events-control-status-reason"
				name="tribe-events-control[status-reason]"
			></textarea>
		</p>
	</div>
</div>
';
