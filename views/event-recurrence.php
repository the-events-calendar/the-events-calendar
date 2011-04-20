<script>
	// temporary spot for js during dev.  Move to main js file before release
	jQuery(document).ready(function($) {
		$('[name="recurrence[type]"]').change(function() {
			var curOption =  $(this).find("option:selected").val();
			$('.custom-recurrence-row').hide();

			if (curOption == "Custom" ) {
				$('#recurrence-end').show();
				$('#custom-recurrence-frequency').show();
				$('[name="recurrence[custom-type]"]').change();
			} else if (curOption == "None") {
				$('#recurrence-end').hide();
			} else {
				$('#recurrence-end').show();
				$('#custom-recurrence-frequency').hide();
			}
		});

		$('[name="recurrence[custom-type]"]').change(function() {
			$('.custom-recurrence-row').hide();
			var customSelector = $(this).find('option:selected').data('tablerow');
			$(customSelector).show()
		});
	});
</script>
		<tr>
			<td><?php _e('Recurrence:',$this->pluginDomain); ?></td>
			<td>
				<select name="recurrence[type]">
					<option value="None">None</option>
					<option value="Every Day">Every Day</option>
					<option value="Every Week">Every Week</option>
					<option value="Every Month">Every Month</option>
					<option value="Every Year">Every Year</option>
					<option value="Custom">Custom</option>
				</select>
				End
				<span id="recurrence-end">
					<select name="recurrence[end-type]">
						<option value="On">On</option>
						<option value="Never">Never</option>
						<option value="After">After</option>
					</select>
					<input autocomplete="off" type="text" class="datepicker" name="recurrence[end]" id=""  value="" />
				</span>
			</td>
		</tr>
		<tr id="custom-recurrence-frequency" style="display: none;">
			<td></td>
			<td>
				Frequency
				<select name="recurrence[custom-type]">
					<option value="Daily" data-plural="Day(s)" data-tablerow="">Daily</option>
					<option value="Weekly" data-plural="Week(s) on:" data-tablerow="#custom-recurrence-weeks">Weekly</option>
					<option value="Monthly" data-plural="Month(s) on:" data-tablerow="#custom-recurrence-months">Monthly</option>
					<option value="Yearly" data-plural="Year(s) on:" data-tablerow="#custom-recurrence-years">Yearly</option>
				</select>
				Every <input name="recurrence[custom-interval]" value="1"/> <span id="recurrence-interval-type">Days</span>
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-weeks" style="display: none;">
			<td></td>
			<td>
				<input type="checkbox" name="recurrence[custom-week-day]" value="M"/> M
				<input type="checkbox" name="recurrence[custom-week-day]" value="Tu"/> Tu
				<input type="checkbox" name="recurrence[custom-week-day]" value="W"/> W
				<input type="checkbox" name="recurrence[custom-week-day]" value="Th"/> Th
				<input type="checkbox" name="recurrence[custom-week-day]" value="F"/> F
				<input type="checkbox" name="recurrence[custom-week-day]" value="Sa"/> Sa
				<input type="checkbox" name="recurrence[custom-week-day]" value="Su"/> Su
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-months" style="display: none;">
			<td></td>
			<td>
				<input type="radio" name="recurrence[custom-months-type]" value="Each"/> Each
				<input type="radio" name="recurrence[custom-months-type]" value="On The"/> On The
				<div>
					<select name="recurrence[custom-month-day-of-month]">
						<?php for($i = 1; $i<=31; $i++): ?>
							<option value="<?php echo $i ?>"><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select name="recurrence[custom-month-number]">
						<option value="First">First</option>
						<option value="Second">Second</option>
						<option value="Third">Third</option>
						<option value="Fourth">Fourth</option>
						<option value="Last">Last</option>
					</select>
					<select name="recurrence[custom-month-day]">
						<option value="Monday">Monday</option>
						<option value="Tuesday">Tuesday</option>
						<option value="Wednesday">Wednesday</option>
						<option value="Thursday">Thursday</option>
						<option value="Friday">Friday</option>
						<option value="Saturday">Saturday</option>
						<option value="Sunday">Sunday</option>
						<option value="-">-</option>
						<option value="Day">Day</option>
						<option value="Weekday">Weekday</option>
						<option value="Weekend Day">Weekend Day</option>
					</select>
				</div>
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-years" style="display: none;">
			<td></td>
			<td>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month]" value="Jan"/> Jan
					<input type="checkbox" name="recurrence[custom-year-month]" value="Feb"/> Feb
					<input type="checkbox" name="recurrence[custom-year-month]" value="Mar"/> Mar
					<input type="checkbox" name="recurrence[custom-year-month]" value="Apr"/> Apr
					<input type="checkbox" name="recurrence[custom-year-month]" value="May"/> May
					<input type="checkbox" name="recurrence[custom-year-month]" value="Jun"/> Jun
				</div>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month]" value="Jul"/> Jul
					<input type="checkbox" name="recurrence[custom-year-month]" value="Aug"/> Aug
					<input type="checkbox" name="recurrence[custom-year-month]" value="Sep"/> Sep
					<input type="checkbox" name="recurrence[custom-year-month]" value="Oct"/> Oct
					<input type="checkbox" name="recurrence[custom-year-month]" value="Nov"/> Nov
					<input type="checkbox" name="recurrence[custom-year-month]" value="Dec"/> Dec
				</div>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month]" value="Custom"/> On the:
					<select name="recurrence[custom-year-month-number]">
						<option value="First">First</option>
						<option value="Second">Second</option>
						<option value="Third">Third</option>
						<option value="Fourth">Fourth</option>
						<option value="Last">Last</option>
					</select>
					<select name="recurrence[custom-year-month-day]">
						<option value="Monday">Monday</option>
						<option value="Tuesday">Tuesday</option>
						<option value="Wednesday">Wednesday</option>
						<option value="Thursday">Thursday</option>
						<option value="Friday">Friday</option>
						<option value="Saturday">Saturday</option>
						<option value="Sunday">Sunday</option>
						<option value="-">-</option>
						<option value="Day">Day</option>
						<option value="Weekday">Weekday</option>
						<option value="Weekend Day">Weekend Day</option>
					</select>
				</div>
			</td>
		</tr>