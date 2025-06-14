<?php return '<div class="tec-events-category-color-filter"
	role="button"
	tabindex="0"
	aria-haspopup="listbox"
	aria-expanded="false"
	aria-label="Select categories to highlight">

	<div class="tec-events-category-color-filter__colors">
					<span
				 class="tec-events-category-color-filter__color-circle tribe_events_cat-test-category-1" 			>
			</span>
					<span
				 class="tec-events-category-color-filter__color-circle tribe_events_cat-test-category-2" 			>
			</span>
			</div>

	<span class="tec-events-category-color-filter__dropdown-icon">
		<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--caret-down tec-events-category-color-filter__dropdown-icon-svg"  viewBox="0 0 10 7" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.008.609L5 4.6 8.992.61l.958.958L5 6.517.05 1.566l.958-.958z" class="tribe-common-c-svgicon__svg-fill"/></svg>
	</span>
	<div class="tec-events-category-color-filter__dropdown" role="listbox" aria-label="Category selection">
		<div class="tec-events-category-color-filter__dropdown-header">
			<span>Highlight a category</span>
			<button class="tec-events-category-color-filter__dropdown-close" aria-label="Close category selection">✕</button>
		</div>
		<ul class="tec-events-category-color-filter__dropdown-list">
							<li class="tec-events-category-color-filter__dropdown-item" role="option">
					<label>
													<input type="checkbox"
								class="tec-events-category-color-filter__checkbox"
								data-category="test-category-1"
								aria-label="
								Highlight events in Test Category 1">
												<span class="tec-events-category-color-filter__label">Test Category 1</span>
						<span
							 class="tec-events-category-color-filter__color-dot tribe_events_cat-test-category-1" 						></span>
					</label>
				</li>
							<li class="tec-events-category-color-filter__dropdown-item" role="option">
					<label>
													<input type="checkbox"
								class="tec-events-category-color-filter__checkbox"
								data-category="test-category-2"
								aria-label="
								Highlight events in Test Category 2">
												<span class="tec-events-category-color-filter__label">Test Category 2</span>
						<span
							 class="tec-events-category-color-filter__color-dot tribe_events_cat-test-category-2" 						></span>
					</label>
				</li>
					</ul>

					<div class="tec-events-category-color-filter__reset-wrapper">
				<button type="button" class="tec-events-category-color-filter__reset tribe-common-c-btn-border-small"
					aria-label="Reset category selection">
					Reset				</button>
			</div>
			</div>
</div>
';
