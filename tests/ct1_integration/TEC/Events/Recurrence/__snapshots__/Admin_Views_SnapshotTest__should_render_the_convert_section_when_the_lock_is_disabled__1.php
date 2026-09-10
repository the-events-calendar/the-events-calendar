<?php return '	<tr class="tec-events-recurrence-dates tec-events-recurrence-dates--locked tec-events-recurrence-dates--convertible">
		<td class="label">Event Dates</td>
		<td>
						<div class="notice notice-warning inline tec-events-recurrence-dates__lock-notice">
									<p id="tec-events-recurrence-dates-lock-reason">
													This event uses recurrence rules created with Events Calendar Pro. Its start and end dates stay locked until you convert it into individual dates.											</p>
											</div>
							<p class="description tec-events-recurrence-dates__count">
					2 dates are scheduled.				</p>
									<ul class="tec-events-recurrence-dates__chips" aria-label="Upcoming dates">
														<li class="tec-events-recurrence-dates__chip-wrap">
		<span class="tec-events-recurrence-dates__chip-group">
							<a
					class="tec-events-recurrence-dates__chip tec-events-recurrence-dates__chip--next"
					href="http://wordpress.test/?tribe_events=admin-snapshot-convertible-event&#038;eventDate=2050-01-03"
					target="_blank"
					rel="noreferrer noopener"
					aria-describedby="tec-events-recurrence-dates-tip-upcoming-0"
				>January 3, 2050</a>
						<a
				class="tec-events-recurrence-dates__chip-edit"
				href="http://wordpress.test/wp-admin/post.php?post={{OCCURRENCE_ID_1}}&#038;action=edit"
				target="_blank"
				rel="noreferrer noopener"
				aria-label="Edit the occurrence on January 3, 2050 (opens in a new tab)"
			><svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.5l3 3-9 9h-3v-3l9-9zM11.5 5.5l3 3"/></svg></a>
		</span>
		<span role="tooltip" id="tec-events-recurrence-dates-tip-upcoming-0" class="tec-events-recurrence-dates__chip-tooltip">
							<span class="tec-events-recurrence-dates__chip-tooltip-line">Monday, January 3, 2050 @ 9:00 am – 10:00 am</span>
							<span class="tec-events-recurrence-dates__chip-tooltip-line">Next occurrence</span>
							<span class="tec-events-recurrence-dates__chip-tooltip-line">Opens the occurrence in a new tab.</span>
					</span>
	</li>
																<li class="tec-events-recurrence-dates__chip-wrap">
		<span class="tec-events-recurrence-dates__chip-group">
							<a
					class="tec-events-recurrence-dates__chip tec-events-recurrence-dates__chip--upcoming"
					href="http://wordpress.test/?tribe_events=admin-snapshot-convertible-event&#038;eventDate=2050-01-10"
					target="_blank"
					rel="noreferrer noopener"
					aria-describedby="tec-events-recurrence-dates-tip-upcoming-1"
				>January 10, 2050</a>
						<a
				class="tec-events-recurrence-dates__chip-edit"
				href="http://wordpress.test/wp-admin/post.php?post={{OCCURRENCE_ID_2}}&#038;action=edit"
				target="_blank"
				rel="noreferrer noopener"
				aria-label="Edit the occurrence on January 10, 2050 (opens in a new tab)"
			><svg height="20" width="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.5l3 3-9 9h-3v-3l9-9zM11.5 5.5l3 3"/></svg></a>
		</span>
		<span role="tooltip" id="tec-events-recurrence-dates-tip-upcoming-1" class="tec-events-recurrence-dates__chip-tooltip">
							<span class="tec-events-recurrence-dates__chip-tooltip-line">Monday, January 10, 2050 @ 9:00 am – 10:00 am</span>
							<span class="tec-events-recurrence-dates__chip-tooltip-line">Upcoming</span>
							<span class="tec-events-recurrence-dates__chip-tooltip-line">Opens the occurrence in a new tab.</span>
					</span>
	</li>
													</ul>
																		<div class="tec-events-recurrence-dates__convert">
					<p><strong>Converting this event:</strong></p>
					<ul id="tec-events-recurrence-convert-effects" class="tec-events-recurrence-dates__convert-effects">
						<li>removes the Events Calendar Pro recurrence rules;</li>
						<li>
							keeps the 2 dates currently scheduled as individual dates you can edit one by one;						</li>
						<li>stops generating further dates;</li>
						<li>removes the event from its Series.</li>
					</ul>
					<p class="description">
						Activating Events Calendar Pro later does not restore the rules. Save any other changes to this event first: unsaved changes are discarded when converting.											</p>
											<p class="tec-events-recurrence-dates__convert-actions">
														<input
								type="checkbox"
								id="tec-events-recurrence-convert-ack"
								class="tec-events-recurrence-dates__convert-ack"
								name="tec_events_recurrence_convert_ack"
								value="1"
								form="tec-events-recurrence-convert"
							/>
							<label for="tec-events-recurrence-convert-ack">I understand that the recurrence rules will be removed.</label>
							<button
								type="submit"
								form="tec-events-recurrence-convert"
								class="button button-secondary tec-events-recurrence-dates__convert-button"
								disabled
								aria-describedby="tec-events-recurrence-convert-effects"
							>Convert to individual dates</button>
						</p>
						<script>
							( function () {
								var ack    = document.getElementById( \'tec-events-recurrence-convert-ack\' );
								var button = document.querySelector( \'.tec-events-recurrence-dates__convert-button\' );

								if ( ! ack || ! button ) {
									return;
								}

								ack.addEventListener( \'change\', function () {
									button.disabled = ! ack.checked;
								} );
							} )();
						</script>
									</div>
					</td>
	</tr>
	';
