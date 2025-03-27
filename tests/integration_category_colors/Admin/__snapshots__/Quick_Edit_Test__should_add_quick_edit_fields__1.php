<?php return '
<div class="tec-events-category-colors__wrap">
	<input type="hidden" id="tec-category-colors-nonce" name="tec-category-colors-nonce" value="12345678" /><input type="hidden" name="_wp_http_referer" value="" />	<div class="tec-events-category-colors__container">
		<div class="tec-events-category-colors__grid">
			<div class="tec-events-category-colors__field">
				<label for="tec-events-category-colors-primary">Primary Color</label>
				<input
					type="text"
					id="tec-events-category-colors-primary"
					name="tec_events_category-color[primary]"
					value=""
					class="tec-events-category-colors__input wp-color-picker"
				>
			</div>
			<div class="tec-events-category-colors__field">
				<label for="tec-events-category-colors-background">Background Color</label>
				<input
					type="text"
					id="tec-events-category-colors-background"
					name="tec_events_category-color[secondary]"
					value=""
					class="tec-events-category-colors__input wp-color-picker"
				>
			</div>
			<div class="tec-events-category-colors__field">
				<label for="tec-events-category-colors-text">Font Color</label>
				<input
					type="text"
					id="tec-events-category-colors-text"
					name="tec_events_category-color[text]"
					value=""
					class="tec-events-category-colors__input wp-color-picker"
				>
			</div>
			<div class="tec-events-category-colors__field--preview">
				<label>Preview</label>
				<div class="tec-events-category-colors__preview-box">
					<span class="tec-events-category-colors__preview-text" data-default-text="Example"></span>
				</div>
				<p class="tec-events-category-colors__description">
					Select a primary color of your choice and a recommended background and font color will be generated. You can further customize your color choices afterwards.					<a href="#">Learn more about color selection and accessibility</a>
				</p>
			</div>
		</div>
	</div>
</div>

<div class="tec-events-category-colors__field">
	<label for="tec-events-category-colors-priority">
		Category Priority	</label>
	<input
		type="number"
		id="tec-events-category-colors-priority"
		name="tec_events_category-color[priority]"
		value=""
		min="0"
		class="tec-events-category-colors__input"
	>
	<p class="tec-events-category-colors__description">
		This is used to determine which category color is assigned to an event if the event has more than one category.	</p>
</div>

<div class="tec-events-category-colors__field">
	<label class="tec-events-category-colors__checkbox-label">
		<input
			type="checkbox"
			id="tec-events-category-colors-hide-legend"
			name="tec_events_category-color[hide_from_legend]"
			value="1"
						class="tec-events-category-colors__hide-legend"
		>
		Hide category from legend	</label>
	<p class="tec-events-category-colors__description">
		Do not show this category if legend shows on event listing views.	</p>
</div>
';
