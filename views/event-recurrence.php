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
			var option = $(this).find('option:selected'), customSelector = option.data('tablerow');
			$(customSelector).show()
			$('#recurrence-interval-type').text(option.data('plural'));
			$('[name="recurrence[custom-type-text]"]').val(option.data('plural'));
		});

		$('[name="recurrence[custom-months-type]"]').click(function() {
			if($(this).val() == "Each") {
				$('#recurrence-month-on-the').hide();
				$('#recurrence-month-each').show();
			} else if($(this).val() == "On The") {
				$('#recurrence-month-on-the').show();
				$('#recurrence-month-each').hide();
			}
		});
	});
</script>
		<tr>
			<td><?php _e('Recurrence:',$this->pluginDomain); ?></td>
			<td>
				<select name="recurrence[type]">
					<option value="None" <?php selected($recType, "None") ?>>None</option>
					<option value="Every Day" <?php selected($recType, "Every Day") ?>>Every Day</option>
					<option value="Every Week" <?php selected($recType, "Every Week") ?>>Every Week</option>
					<option value="Every Month" <?php selected($recType, "Every Month") ?>>Every Month</option>
					<option value="Every Year" <?php selected($recType, "Every Year") ?>>Every Year</option>
					<option value="Custom" <?php selected($recType, "Custom") ?>>Custom</option>
				</select>
				End
				<span id="recurrence-end">
					<select name="recurrence[end-type]">
						<option value="On" <?php selected($recEndType, "None") ?>>On</option>
						<option value="After" <?php selected($recEndType, "After") ?>>After</option>
					</select>
					<input autocomplete="off" type="text" class="datepicker" name="recurrence[end]" id=""  value="<?php echo $recEnd ?>" />
				</span>
			</td>
		</tr>
		<tr id="custom-recurrence-frequency" style="display: <?php echo $recType == "Custom" ? "table-row" : "none" ?>;">
			<td></td>
			<td>
				Frequency
				<select name="recurrence[custom-type]">
					<option value="Daily" data-plural="Day(s)" data-tablerow="" <?php selected($recCustomType, "None") ?>>Daily</option>
					<option value="Weekly" data-plural="Week(s) on:" data-tablerow="#custom-recurrence-weeks" <?php selected($recCustomType, "Weekly") ?>>Weekly</option>
					<option value="Monthly" data-plural="Month(s) on:" data-tablerow="#custom-recurrence-months" <?php selected($recCustomType, "Monthly") ?>>Monthly</option>
					<option value="Yearly" data-plural="Year(s) on:" data-tablerow="#custom-recurrence-years" <?php selected($recCustomType, "Yearly") ?>>Yearly</option>
				</select>
				Every <input name="recurrence[custom-interval]" value="<?php echo $recCustomInterval ?>"/> <span id="recurrence-interval-type"><?php echo $recCustomTypeText ?></span>
				<input type="hidden" name="recurrence[custom-type-text]" value="<?php echo $recCustomTypeText ?>"/>
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-weeks" style="display: <?php echo $recCustomType == "Weekly" ? "table-row" : "none" ?>;">
			<td></td>
			<td>
				<input type="checkbox" name="recurrence[custom-week-day][]" value="M" <?php checked(in_array("M", $recCustomWeekDay)) ?>/> M
				<input type="checkbox" name="recurrence[custom-week-day][]" value="Tu" <?php checked(in_array("Tu", $recCustomWeekDay)) ?>/> Tu
				<input type="checkbox" name="recurrence[custom-week-day][]" value="W" <?php checked(in_array("W", $recCustomWeekDay)) ?>/> W
				<input type="checkbox" name="recurrence[custom-week-day][]" value="Th" <?php checked(in_array("Th", $recCustomWeekDay)) ?>/> Th
				<input type="checkbox" name="recurrence[custom-week-day][]" value="F" <?php checked(in_array("F", $recCustomWeekDay)) ?>/> F
				<input type="checkbox" name="recurrence[custom-week-day][]" value="Sa" <?php checked(in_array("Sa", $recCustomWeekDay)) ?>/> Sa
				<input type="checkbox" name="recurrence[custom-week-day][]" value="Su" <?php checked(in_array("Su", $recCustomWeekDay)) ?>/> Su
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-months" style="display: <?php echo $recCustomType == "Monthly" ? "table-row" : "none" ?>;">
			<td></td>
			<td>
				<input type="radio" name="recurrence[custom-months-type]" value="Each" <?php checked(!$recCustomMonthType || $recCustomMonthType == "Each") ?>/> Each
				<input type="radio" name="recurrence[custom-months-type]" value="On The" <?php checked($recCustomMonthType, "On The") ?>/> On The
				<div id="recurrence-month-each" style="display: <?php echo $recCustomMonthType == "Each" ? "block" : "none" ?>;">
					<select name="recurrence[custom-month-day-of-month]">
						<?php for($i = 1; $i<=31; $i++): ?>
							<option value="<?php echo $i ?>" <?php selected($recCustomMonthDayOfMonth, $i) ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>
				<div id="recurrence-month-on-the" style="display: <?php echo $recCustomMonthType == "On The" ? "block" : "none" ?>;">
					<select name="recurrence[custom-month-number]">
						<option value="First" <?php selected($recCustomMonthNumber, "First") ?>>First</option>
						<option value="Second" <?php selected($recCustomMonthNumber, "Second") ?>>Second</option>
						<option value="Third" <?php selected($recCustomMonthNumber, "Third") ?>>Third</option>
						<option value="Fourth" <?php selected($recCustomMonthNumber, "Fourth") ?>>Fourth</option>
						<option value="Last" <?php selected($recCustomMonthNumber, "Last") ?>>Last</option>
					</select>
					<select name="recurrence[custom-month-day]">
						<option value="Monday"  <?php selected($recCustomMonthDay, "Monday") ?>>Monday</option>
						<option value="Tuesday" <?php selected($recCustomMonthDay, "Tuesday") ?>>Tuesday</option>
						<option value="Wednesday" <?php selected($recCustomMonthDay, "Wednesday") ?>>Wednesday</option>
						<option value="Thursday" <?php selected($recCustomMonthDay, "Thursday") ?>>Thursday</option>
						<option value="Friday" <?php selected($recCustomMonthDay, "Friday") ?>>Friday</option>
						<option value="Saturday" <?php selected($recCustomMonthDay, "Saturday") ?>>Saturday</option>
						<option value="Sunday" <?php selected($recCustomMonthDay, "Sunday") ?>>Sunday</option>
						<option value="-" <?php selected($recCustomMonthDay, "-") ?>>-</option>
						<option value="Day" <?php selected($recCustomMonthDay, "Day") ?>>Day</option>
						<option value="Weekday" <?php selected($recCustomMonthDay, "Weekday") ?>>Weekday</option>
						<option value="Weekend Day" <?php selected($recCustomMonthDay, "Weekend Day") ?>>Weekend Day</option>
					</select>
				</div>
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-years"  style="display: <?php echo $recCustomType == "Yearly" ? "table-row" : "none" ?>;">
			<td></td>
			<td>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Jan" <?php checked(in_array("Jan", $recCustomYearMonth)) ?>/> Jan
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Feb" <?php checked(in_array("Feb", $recCustomYearMonth)) ?>/> Feb
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Mar" <?php checked(in_array("Mar", $recCustomYearMonth)) ?>/> Mar
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Apr" <?php checked(in_array("Apr", $recCustomYearMonth)) ?>/> Apr
					<input type="checkbox" name="recurrence[custom-year-month][]" value="May" <?php checked(in_array("May", $recCustomYearMonth)) ?>/> May
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Jun" <?php checked(in_array("Jun", $recCustomYearMonth)) ?>/> Jun
				</div>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Jul" <?php checked(in_array("Jul", $recCustomYearMonth)) ?>/> Jul
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Aug" <?php checked(in_array("Aug", $recCustomYearMonth)) ?>/> Aug
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Sep" <?php checked(in_array("Sep", $recCustomYearMonth)) ?>/> Sep
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Oct" <?php checked(in_array("Oct", $recCustomYearMonth)) ?>/> Oct
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Nov" <?php checked(in_array("Nov", $recCustomYearMonth)) ?>/> Nov
					<input type="checkbox" name="recurrence[custom-year-month][]" value="Dec" <?php checked(in_array("Dec", $recCustomYearMonth)) ?>/> Dec
				</div>
				<div>
					<input type="checkbox" name="recurrence[custom-year-filter]" value="1" <?php checked($recCustomYearFilter, "1") ?>/> On the:
					<select name="recurrence[custom-year-month-number]">
						<option value="First" <?php selected($recCustomYearMonthNumber, "First") ?>>First</option>
						<option value="Second" <?php selected($recCustomYearMonthNumber, "Second") ?>>Second</option>
						<option value="Third" <?php selected($recCustomYearMonthNumber, "Third") ?>>Third</option>
						<option value="Fourth" <?php selected($recCustomYearMonthNumber, "Fourth") ?>>Fourth</option>
						<option value="Last" <?php selected($recCustomYearMonthNumber, "Last") ?>>Last</option>
					</select>
					<select name="recurrence[custom-year-month-day]">
						<option value="Monday"  <?php selected($recCustomYearMonthDay, "Monday") ?>>Monday</option>
						<option value="Tuesday" <?php selected($recCustomYearMonthDay, "Tuesday") ?>>Tuesday</option>
						<option value="Wednesday" <?php selected($recCustomYearMonthDay, "Wednesday") ?>>Wednesday</option>
						<option value="Thursday" <?php selected($recCustomYearMonthDay, "Thursday") ?>>Thursday</option>
						<option value="Friday" <?php selected($recCustomYearMonthDay, "Friday") ?>>Friday</option>
						<option value="Saturday" <?php selected($recCustomYearMonthDay, "Saturday") ?>>Saturday</option>
						<option value="Sunday" <?php selected($recCustomYearMonthDay, "Sunday") ?>>Sunday</option>
						<option value="-" <?php selected($recCustomYearMonthDay, "-") ?>>-</option>
						<option value="Day" <?php selected($recCustomYearMonthDay, "Day") ?>>Day</option>
						<option value="Weekday" <?php selected($recCustomYearMonthDay, "Weekday") ?>>Weekday</option>
						<option value="Weekend Day" <?php selected($recCustomYearMonthDay, "Weekend Day") ?>>Weekend Day</option>
					</select>
				</div>
			</td>
		</tr>