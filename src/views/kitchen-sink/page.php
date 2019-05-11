<?php
tribe_asset_enqueue( 'tribe-common-style' );
?>
<div class="tribe-events">
	<h1>Kitchen Sink</h1>
	<br />
	<h2>Typography</h2>
	<br />
	<h3>Headings</h3>
	<br />
	<p class="tribe-common-h1">Heading 1</p>
	<br />
	<p class="tribe-common-h2">Heading 2</p>
	<br />
	<p class="tribe-common-h3">Heading 3</p>
	<br />
	<p class="tribe-common-h4">Heading 4</p>
	<br />
	<p class="tribe-common-h5">Heading 5</p>
	<br />
	<p class="tribe-common-h6">Heading 6</p>
	<br />
	<h3>Body</h3>
	<br />
	<p class="tribe-common-b1">Body 1, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<p class="tribe-common-b2">Body 2, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<p class="tribe-common-b3">Body 3, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<p class="tribe-common-b4">Body 4, lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
	<br />
	<h3>Buttons</h3>
	<br />
	<button class="tribe-common-c-btn">Primary Button</button>
	<br />
	<button class="tribe-common-c-btn tribe-common-c-btn--secondary">Secondary Button</button>
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
	<h3>CTAs</h3>
	<br />
	<a href="#" class="tribe-common-cta">Call to Action</a>
	<br />
	<h3>Checkboxes & Radios</h3>
	<br />
	<fieldset>
		<legend>Legend for Checkboxes</legend>
		<div class="tribe-common-form-control-checkbox">
			<input id="checkboxOne" name="checkboxGroup" type="checkbox" value="checkboxOne" />
			<label for="checkboxOne">Checkbox One</label>
		</div>
		<div class="tribe-common-form-control-checkbox">
			<input id="checkboxTwo" name="checkboxGroup" type="checkbox" value="checkboxTwo" />
			<label for="checkboxTwo">Checkbox Two</label>
		</div>
	</fieldset>
	<br />
	<fieldset>
		<legend>Legend for Radios</legend>
		<div class="tribe-common-form-control-radio">
			<input id="radioOne" name="radioGroup" type="radio" value="radioOne" />
			<label for="radioOne">Radio One</label>
		</div>
		<div class="tribe-common-form-control-radio">
			<input id="radioTwo" name="radioGroup" type="radio" value="radioTwo" />
			<label for="radioTwo">Radio Two</label>
		</div>
	</fieldset>
	<br />
	<h3>Toggles & Sliders</h3>
	<br />
	<div class="tribe-common-form-control-toggle">
		<input id="toggleHorizontal" name="toggleGroup" type="checkbox" value="toggleHorizontal" />
		<label for="toggleHorizontal">Toggle Horizontal</label>
	</div>
	<br />
	<div class="tribe-common-form-control-toggle tribe-common-form-control-toggle--vertical">
		<label for="toggleVertical">Toggle Vertical</label>
		<input id="toggleVertical" name="toggleGroup" type="checkbox" value="toggleVertical" />
	</div>
	<br />
	<div class="tribe-common-form-control-slider">
		<input id="sliderOne" type="range" min="0" max="100" value="50" />
		<label for="sliderOne">Slider One</label>
	</div>
	<br />
	<div class="tribe-common-form-control-slider tribe-common-form-control-slider--vertical">
		<label for="sliderTwo">Slider Two</label>
		<input id="sliderTwo" type="range" min="0" max="100" value="50" />
	</div>
	<br />
	<h3>Tabs & Selects</h3>
	<br />
	<div class="tribe-common-form-control-tabs">
		<button id="tabButton" aria-haspopup="listbox" aria-labelledby="tabButton" aria-expanded="true">Tab One</button>
		<ul tabindex="-1" role="listbox" aria-activedescendant="tabOneLabel">
			<li role="presentation">
				<input id="tabOne" name="tabGroup" type="radio" value="tabOne" checked="checked" />
				<label id="tabOneLabel" for="tabOne" role="option" aria-selected="true">Tab One</label>
			</li>
			<li role="presentation">
				<input id="tabTwo" name="tabGroup" type="radio" value="tabTwo" />
				<label id="tabTwoLabel" for="tabTwo" role="option">Tab Two</label>
			</li>
		</ul>
	</div>
	<br />
	<div class="tribe-common-form-control-select" style="margin-top: 100px;">
		<button id="selectButton" aria-haspopup="listbox" aria-labelledby="selectButton" aria-expanded="true">Month</button>
		<ul tabindex="-1" role="listbox" aria-activedescendant="selectItemMonth">
			<li id="selectItemMonth" role="option" aria-selected="true">Month</li>
			<li id="selectItemWeek" role="option">Week</li>
			<li id="selectItemDay" role="option">Day</li>
		</ul>
	</div>
</div>
