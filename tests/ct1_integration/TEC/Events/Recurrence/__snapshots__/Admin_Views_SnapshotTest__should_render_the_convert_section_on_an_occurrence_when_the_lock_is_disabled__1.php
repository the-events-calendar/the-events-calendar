<?php return '	<tr class="tec-events-recurrence-dates tec-events-recurrence-dates--locked tec-events-recurrence-dates--convertible tec-events-recurrence-dates--occurrence">
		<td class="label">Event Dates</td>
		<td>
						<div class="notice notice-warning inline tec-events-recurrence-dates__lock-notice">
									<p id="tec-events-recurrence-dates-lock-reason">
													This is one date of an event that uses recurrence rules created with Events Calendar Pro. Its start and end dates stay locked until the event is converted into individual dates.											</p>
													<p>
						<a href="http://wordpress.test/wp-admin/post.php?post={{EVENT_ID}}&#038;action=edit">Edit event details.</a>
					</p>
							</div>
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
						Activating Events Calendar Pro later does not restore the rules. Save any other changes to this event first: unsaved changes are discarded when converting.													Converting sends you to the event editor.											</p>
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
