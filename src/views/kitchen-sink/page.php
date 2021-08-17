<?php
/**
 * The Events Calendar Styles kitchen sink.
 *
 * Displays all the styles applied to all UI components.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

tribe_asset_enqueue( 'tribe-common-style' );
?>
<div class="tribe-common tribe-events">
	<h1>Kitchen Sink</h1>
	<br />
	<h2>Typography</h2>
	<br />
	<h3>Headings</h3>
	<br />
	<h1 class="tribe-common-h1">Heading 1</h1>
	<br />
	<h2 class="tribe-common-h2">Heading 2</h2>
	<br />
	<h3 class="tribe-common-h3">Heading 3</h3>
	<br />
	<h4 class="tribe-common-h4">Heading 4</h4>
	<br />
	<h5 class="tribe-common-h5">Heading 5</h5>
	<br />
	<h6 class="tribe-common-h6">Heading 6</h6>
	<br />
	<h6 class="tribe-common-h7">Heading 7</h6>
	<br />
	<h6 class="tribe-common-h8">Heading 8</h6>
	<br />
	<h3>Body</h3>
	<br />
	<p class="tribe-common-b1">Body 1, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<p class="tribe-common-b2">Body 2, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<p class="tribe-common-b3">Body 3, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<h3>Base</h3>
	<br />
	<button>I'm a button</button>
	<br />
	<a href="#">I'm a link</a>
	<br />
	<input type="text" />
	<br />
	<h3>Buttons</h3>
	<br />
	<button class="tribe-common-c-btn">Primary Button</button>
	<br />
	<button class="tribe-common-c-btn-border">Border Button</button>
	<br />
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left"></button>
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right"></button>
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--filters"></button>
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--search"></button>
	<br />
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--caret-left"></button>
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--caret-right"></button>
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--filters"></button>
	<button class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--search"></button>
	<br />
	<a href="#" class="tribe-common-c-btn">Primary Button</a>
	<br />
	<a href="#" class="tribe-common-c-btn-border">Border Button</a>
	<br />
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left"></a>
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right"></a>
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--filters"></a>
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--search"></a>
	<br />
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--caret-left"></a>
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--caret-right"></a>
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--filters"></a>
	<a href="#" class="tribe-common-c-btn-icon tribe-common-c-btn-icon--border tribe-common-c-btn-icon--search"></a>
	<h3>CTAs</h3>
	<br />
	<a href="#" class="tribe-common-anchor">Anchor</a>
	<br />
	<a href="#" class="tribe-common-anchor-alt">Anchor</a>
	<br />
	<a href="#" class="tribe-common-anchor-thin">Anchor</a>
	<br />
	<a href="#" class="tribe-common-cta">Call to Action</a>
	<br />
	<a href="#" class="tribe-common-cta tribe-common-cta--alt">Call to Action</a>
	<br />
	<h3>Checkboxes & Radios</h3>
	<br />
	<fieldset>
		<legend>Legend for Checkboxes</legend>
		<div class="tribe-common-form-control-checkbox-radio-group">
			<div class="tribe-common-form-control-checkbox">
				<input class="tribe-common-form-control-checkbox__input" id="checkboxOne" name="checkboxGroup" type="checkbox" value="checkboxOne" />
				<label class="tribe-common-form-control-checkbox__label" for="checkboxOne">Checkbox One</label>
			</div>
			<div class="tribe-common-form-control-checkbox">
				<input class="tribe-common-form-control-checkbox__input" id="checkboxTwo" name="checkboxGroup" type="checkbox" value="checkboxTwo" />
				<label class="tribe-common-form-control-checkbox__label" for="checkboxTwo">Checkbox Two</label>
			</div>
		</div>
	</fieldset>
	<br />
	<fieldset>
		<legend>Legend for Radios</legend>
		<div class="tribe-common-form-control-checkbox-radio-group">
			<div class="tribe-common-form-control-radio">
				<input class="tribe-common-form-control-radio__input" id="radioOne" name="radioGroup" type="radio" value="radioOne" />
				<label class="tribe-common-form-control-radio__label" for="radioOne">Radio One</label>
			</div>
			<div class="tribe-common-form-control-radio">
				<input class="tribe-common-form-control-radio__input" id="radioTwo" name="radioGroup" type="radio" value="radioTwo" />
				<label class="tribe-common-form-control-radio__label" for="radioTwo">Radio Two</label>
			</div>
		</div>
	</fieldset>
	<br />
	<h3>Toggles & Sliders</h3>
	<br />
	<div class="tribe-common-form-control-toggle">
		<input class="tribe-common-form-control-toggle__input" id="toggleHorizontal" name="toggleGroup" type="checkbox" value="toggleHorizontal" />
		<label class="tribe-common-form-control-toggle__label" for="toggleHorizontal">Toggle Horizontal</label>
	</div>
	<br />
	<div class="tribe-common-form-control-toggle tribe-common-form-control-toggle--vertical">
		<label class="tribe-common-form-control-toggle__label" for="toggleVertical">Toggle Vertical</label>
		<input class="tribe-common-form-control-toggle__input" id="toggleVertical" name="toggleGroup" type="checkbox" value="toggleVertical" />
	</div>
	<br />
	<div class="tribe-common-form-control-slider">
		<input class="tribe-common-form-control-slider__input" id="sliderOne" type="range" min="0" max="100" value="50" />
		<label class="tribe-common-form-control-slider__label" for="sliderOne">Slider One</label>
	</div>
	<br />
	<div class="tribe-common-form-control-slider tribe-common-form-control-slider--vertical">
		<label class="tribe-common-form-control-slider__label" for="sliderTwo">Slider Two</label>
		<input class="tribe-common-form-control-slider__input" id="sliderTwo" type="range" min="0" max="100" value="50" />
	</div>
	<br />
	<h3>Tabs & Selects</h3>
	<br />
	<div class="tribe-events-c-view-selector" style="margin-left: 200px">
		<button
			class="tribe-events-c-view-selector__button"
			aria-controls="container-id-1"
			aria-expanded="false"
			aria-selected="false"
			data-js="tribe-events-accordion-trigger"
		>
			<span class="tribe-events-c-view-selector__button-icon tribe-common-svgicon tribe-common-svgicon--month"></span>
			<span class="tribe-events-c-view-selector__button-text">
				Month
			</span>
		</button>
		<div
			class="tribe-events-c-view-selector__content"
			id="container-id-1"
			aria-hidden="true"
		>
			<ul class="tribe-events-c-view-selector__list">
				<li class="tribe-events-c-view-selector__list-item">
					<a href="#" class="tribe-events-c-view-selector__list-item-link">
						<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--month"></span>
						<span class="tribe-events-c-view-selector__list-item-text">
							Month
						</span>
					</a>
				</li>
				<li class="tribe-events-c-view-selector__list-item tribe-events-c-view-selector__list-item--active">
					<a href="#" class="tribe-events-c-view-selector__list-item-link">
						<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--list"></span>
						<span class="tribe-events-c-view-selector__list-item-text">
							List
						</span>
					</a>
				</li>
				<li class="tribe-events-c-view-selector__list-item">
					<a href="#" class="tribe-events-c-view-selector__list-item-link">
						<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--day"></span>
						<span class="tribe-events-c-view-selector__list-item-text">
							Day
						</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<br />
	<div class="tribe-events-c-view-selector tribe-events-c-view-selector--tabs" style="margin-top: 150px; margin-left: 200px">
		<button
			class="tribe-events-c-view-selector__button"
			aria-controls="container-id-2"
			aria-expanded="false"
			aria-selected="false"
			data-js="tribe-events-accordion-trigger"
		>
			<span class="tribe-events-c-view-selector__button-icon tribe-common-svgicon tribe-common-svgicon--month"></span>
			<span class="tribe-events-c-view-selector__button-text">
				Month
			</span>
		</button>
		<div
			class="tribe-events-c-view-selector__content"
			id="container-id-2"
			aria-hidden="true"
		>
			<ul class="tribe-events-c-view-selector__list">
				<li class="tribe-events-c-view-selector__list-item">
					<a href="#" class="tribe-events-c-view-selector__list-item-link">
						<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--month"></span>
						<span class="tribe-events-c-view-selector__list-item-text">
							Month
						</span>
					</a>
				</li>
				<li class="tribe-events-c-view-selector__list-item tribe-events-c-view-selector__list-item--active">
					<a href="#" class="tribe-events-c-view-selector__list-item-link">
						<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--list"></span>
						<span class="tribe-events-c-view-selector__list-item-text">
							List
						</span>
					</a>
				</li>
				<li class="tribe-events-c-view-selector__list-item">
					<a href="#" class="tribe-events-c-view-selector__list-item-link">
						<span class="tribe-events-c-view-selector__list-item-icon tribe-common-svgicon tribe-common-svgicon--day"></span>
						<span class="tribe-events-c-view-selector__list-item-text">
							Day
						</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<br />
	<h3>Search</h3>
	<br />
	<form
		action="#"
		method="post"
		class="tribe-events-c-search"
		style="margin-top: 100px"
	>
		<div class="tribe-events-c-search__input-group">
		<div
				class="tribe-common-form-control-text tribe-events-c-search__input-control tribe-events-c-search__input-control--keyword"
				data-js="tribe-events-events-bar-input-control"
			>
				<em
					class="tribe-events-c-search__input-control-icon"
					aria-label="Search"
					title="Search"
				>
					<?php $this->template( 'components/icons/search', [ 'classes' => [ 'tribe-events-c-search__input-control-icon-svg' ] ] ); ?>
				</em>
				<label class="tribe-common-form-control-text__label" for="tribe-events-events-bar-keyword">
					Keyword
				</label>
				<input
					class="tribe-common-form-control-text__input tribe-events-c-search__input"
					data-js="tribe-events-events-bar-input-control-input"
					type="text"
					id="tribe-events-events-bar-keyword"
					name="tribe-events-views[tribe-bar-search]"
					value=""
					placeholder="<?php esc_attr_e( 'Search for events', 'the-events-calendar' ); ?>"
				/>
			</div>
			<div
				class="tribe-common-form-control-text tribe-events-c-search__input-control tribe-events-c-search__input-control--location"
				data-js="tribe-events-events-bar-input-control"
			>
				<em
					class="tribe-events-c-search__input-control-icon"
					aria-label="Search"
					title="Search"
				>
					<?php $this->template( 'components/icons/search', [ 'classes' => [ 'tribe-events-c-search__input-control-icon-svg' ] ] ); ?>
				</em>
				<label class="tribe-common-form-control-text__label" for="tribe-events-events-bar-location">
					Location
				</label>
				<input
					class="tribe-common-form-control-text__input tribe-events-c-search__input"
					data-js="tribe-events-events-bar-input-control-input"
					type="text"
					id="tribe-events-events-bar-location"
					name="tribe-events-views[tribe-bar-search]"
					value=""
					placeholder="<?php esc_attr_e( 'In a location', 'the-events-calendar' ); ?>"
				/>
			</div>
		</div>
		<button
			class="tribe-common-c-btn tribe-events-c-search__button"
			type="submit"
			name="submit-bar"
		>
			Find Events
		</button>
	</form>
</div>
