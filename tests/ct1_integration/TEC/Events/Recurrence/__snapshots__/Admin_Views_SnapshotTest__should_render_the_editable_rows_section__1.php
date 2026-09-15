<?php return '<tr class="tec-events-recurrence-dates">
	<td class="label">Event Dates</td>
	<td class="tribe-datetime-block">
		<input type="hidden" id="tec_events_recurrence_dates_save_nonce" name="tec_events_recurrence_dates_save_nonce" value="2ab7cc6b39" /><input type="hidden" name="_wp_http_referer" value="/wp-admin/post.php" />
		<p>
			<input
				type="checkbox"
				id="tec-events-recurrence-dates-toggle"
				 checked=\'checked\'			/>
			<label for="tec-events-recurrence-dates-toggle">
				Schedule this event on more dates			</label>
		</p>

		<div id="tec-events-recurrence-dates-rows" >
			<p class="description">
				Each date becomes its own entry on the calendar, with its own link. The event date above is always included. All dates share the event’s All Day setting.			</p>

			<div class="tec-events-recurrence-dates-list" id="tec-events-recurrence-dates-list">
										<div class="tec-events-recurrence-dates-row">
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			name="tec_events_recurrence_dates[0][date]"
			value="2050-01-10"
		/>
		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
						data-step="30"
			name="tec_events_recurrence_dates[0][start]"
			value="9:00am"
		/>
		<span class="tribe-datetime-separator"> to </span>
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			aria-label="End date"
			name="tec_events_recurrence_dates[0][end_date]"
			value="2050-01-10"
		/>

		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
						data-step="30"
			name="tec_events_recurrence_dates[0][end]"
			value="10:00am"
		/>

		<button type="button" class="button tec-events-recurrence-dates-remove" aria-label="Remove this date">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M4 10h12"/></svg>
		</button>
		<button type="button" class="button tec-events-recurrence-dates-add" aria-label="Add another date">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M10 4v12M4 10h12"/></svg>
		</button>
	</div>
											<div class="tec-events-recurrence-dates-row">
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			name="tec_events_recurrence_dates[1][date]"
			value="2050-01-17"
		/>
		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
						data-step="30"
			name="tec_events_recurrence_dates[1][start]"
			value="2:30pm"
		/>
		<span class="tribe-datetime-separator"> to </span>
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			aria-label="End date"
			name="tec_events_recurrence_dates[1][end_date]"
			value="2050-01-17"
		/>

		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
						data-step="30"
			name="tec_events_recurrence_dates[1][end]"
			value="4:00pm"
		/>

		<button type="button" class="button tec-events-recurrence-dates-remove" aria-label="Remove this date">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M4 10h12"/></svg>
		</button>
		<button type="button" class="button tec-events-recurrence-dates-add" aria-label="Add another date">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M10 4v12M4 10h12"/></svg>
		</button>
	</div>
								</div>
		</div>

		<script type="text/template" id="tec-events-recurrence-dates-row">
				<div class="tec-events-recurrence-dates-row">
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			name="tec_events_recurrence_dates[__index__][date]"
			value=""
		/>
		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
						data-step="30"
			name="tec_events_recurrence_dates[__index__][start]"
			value="8:00am"
		/>
		<span class="tribe-datetime-separator"> to </span>
		<input
			autocomplete="off"
			type="text"
			class="tribe-datepicker"
			aria-label="End date"
			name="tec_events_recurrence_dates[__index__][end_date]"
			value=""
		/>

		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker"
						data-step="30"
			name="tec_events_recurrence_dates[__index__][end]"
			value="5:00pm"
		/>

		<button type="button" class="button tec-events-recurrence-dates-remove" aria-label="Remove this date">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M4 10h12"/></svg>
		</button>
		<button type="button" class="button tec-events-recurrence-dates-add" aria-label="Add another date">
			<svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M10 4v12M4 10h12"/></svg>
		</button>
	</div>
			</script>

		<style>
			/* Every control in a row shares one fixed height: the buttons stay square. */
			.tec-events-recurrence-dates-row { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
			.tec-events-recurrence-dates-row .tribe-datepicker { box-sizing: border-box; height: 42px; width: 8em; }
			.tec-events-recurrence-dates-row .tribe-timepicker { box-sizing: border-box; height: 42px; width: 6.5em; }
			.tec-events-recurrence-dates-row .tribe-datetime-separator { align-self: center; }
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-remove,
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-add { align-items: center; display: inline-flex; height: 42px; justify-content: center; padding: 0; width: 42px; }
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-remove svg,
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-add svg { display: block; }
			.tec-events-recurrence-dates-row:not(:last-child) .tec-events-recurrence-dates-add { display: none; }
			.tec-events-recurrence-dates-row .tec-events-recurrence-dates-allday { align-items: center; align-self: center; display: inline-flex; gap: 4px; }
			.tec-events-recurrence-dates-row--allday .tribe-timepicker,
			.tec-events-recurrence-dates-row--allday .tribe-datetime-separator { display: none; }
			#tec-events-recurrence-dates-rows > .description { margin: 12px 0 16px; }
		</style>

		<script>
			( function () {
				var toggle = document.getElementById( \'tec-events-recurrence-dates-toggle\' );
				var wrapper = document.getElementById( \'tec-events-recurrence-dates-rows\' );
				var list = document.getElementById( \'tec-events-recurrence-dates-list\' );
				var rowTemplate = document.getElementById( \'tec-events-recurrence-dates-row\' );
				var nextIndex = 2;

				if ( ! toggle || ! wrapper || ! list || ! rowTemplate ) {
					return;
				}

				function initPickers( row ) {
					if ( ! window.jQuery ) {
						return;
					}

					var $ = window.jQuery;

					if ( $.fn.datepicker && window.tribe_datepicker_opts ) {
						$( row ).find( \'.tribe-datepicker\' ).datepicker( window.tribe_datepicker_opts );
					}

					if ( window.tribe_timepickers && window.tribe_timepickers.setup_timepickers ) {
						window.tribe_timepickers.setup_timepickers( $( row ).find( \'.tribe-timepicker\' ) );
					}
				}

				function nextDateValue() {
					if ( ! window.jQuery || ! window.jQuery.datepicker || ! window.tribe_datepicker_opts ) {
						return \'\';
					}

					// The previous row\'s date, or the event\'s own date, plus one day.
					var rows = list.querySelectorAll( \'.tec-events-recurrence-dates-row .tribe-datepicker\' );
					var base = rows.length ? rows[ rows.length - 1 ].value : \'\';

					if ( ! base ) {
						var eventStart = document.getElementById( \'EventStartDate\' );
						base = eventStart ? eventStart.value : \'\';
					}

					if ( ! base ) {
						return \'\';
					}

					try {
						var format = window.tribe_datepicker_opts.dateFormat;
						var parsed = window.jQuery.datepicker.parseDate( format, base );
						parsed.setDate( parsed.getDate() + 1 );

						return window.jQuery.datepicker.formatDate( format, parsed );
					} catch ( error ) {
						return \'\';
					}
				}

				function addRow() {
					var prefill = nextDateValue();
					var container = document.createElement( \'div\' );
					container.innerHTML = rowTemplate.innerHTML.replace( /__index__/g, String( nextIndex ) );
					nextIndex++;

					var row = container.querySelector( \'.tec-events-recurrence-dates-row\' );
					row.querySelectorAll( \'.tribe-datepicker\' ).forEach( function ( input ) { input.value = prefill; } );
					list.appendChild( row );
					initPickers( row );
				}

				function syncDisabled() {
					var active = toggle.checked;
					wrapper.style.display = active ? \'\' : \'none\';
					list.querySelectorAll( \'input\' ).forEach( function ( input ) {
						// Disabled inputs are not posted: toggling off and saving removes the dates.
						input.disabled = ! active;
					} );
				}

				toggle.addEventListener( \'change\', function () {
					if ( toggle.checked && ! list.querySelector( \'.tec-events-recurrence-dates-row\' ) ) {
						addRow();
					}

					syncDisabled();
				} );

				wrapper.addEventListener( \'click\', function ( event ) {
					// closest(): the click may land on the SVG inside the button.
					if ( event.target.closest( \'.tec-events-recurrence-dates-add\' ) ) {
						addRow();
						return;
					}

					var removeButton = event.target.closest( \'.tec-events-recurrence-dates-remove\' );

					if ( ! removeButton ) {
						return;
					}

					var row = removeButton.closest( \'.tec-events-recurrence-dates-row\' );

					if ( row ) {
						row.parentNode.removeChild( row );
					}

					if ( ! list.querySelector( \'.tec-events-recurrence-dates-row\' ) ) {
						// The last row is gone: the event is back to a single date.
						toggle.checked = false;
						syncDisabled();
					}
				} );


				syncDisabled();
			}() );
		</script>
	</td>
</tr>
';
