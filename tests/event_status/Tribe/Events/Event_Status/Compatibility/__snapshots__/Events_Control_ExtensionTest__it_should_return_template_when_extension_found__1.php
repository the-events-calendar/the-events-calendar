<?php return '<div class="tribe-events-control-metabox-container" style="margin-top: 24px;">
	<input type="hidden" id="tribe-events-control[nonce]" name="tribe-events-control[nonce]" value="123123" /><input type="hidden" name="_wp_http_referer" value="" />	<div>
		<p>
			<label for="tribe-events-control-online">
				<input
					id="tribe-events-control-online"
					name="tribe-events-control[online]"
					type="checkbox"
					value="yes"
									>
				Mark as an online event			</label>
		</p>
		<div
			class="tribe-dependent"
			data-depends="#tribe-events-control-online"
			data-condition-checked
		>
			<p>
				<label for="tribe-events-control-online-url">
					Live Stream URL				</label>
				<input
					id="tribe-events-control-online-url"
					name="tribe-events-control[online-url]"
					value=""
					type="url"
					class="components-text-control__input"
				>
			</p>
		</div>
	</div>
</div>
';
