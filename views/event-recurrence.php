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
					<input autocomplete="off" type="text" class="datepicker" name="recurrence[end]" id="recurrence_end"  value="<?php echo $recEnd ?>" />
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
				<input type="checkbox" name="recurrence[custom-week-day][]" value="1" <?php checked(in_array("1", $recCustomWeekDay)) ?>/> M
				<input type="checkbox" name="recurrence[custom-week-day][]" value="2" <?php checked(in_array("2", $recCustomWeekDay)) ?>/> Tu
				<input type="checkbox" name="recurrence[custom-week-day][]" value="3" <?php checked(in_array("3", $recCustomWeekDay)) ?>/> W
				<input type="checkbox" name="recurrence[custom-week-day][]" value="4" <?php checked(in_array("4", $recCustomWeekDay)) ?>/> Th
				<input type="checkbox" name="recurrence[custom-week-day][]" value="5" <?php checked(in_array("5", $recCustomWeekDay)) ?>/> F
				<input type="checkbox" name="recurrence[custom-week-day][]" value="6" <?php checked(in_array("6", $recCustomWeekDay)) ?>/> Sa
				<input type="checkbox" name="recurrence[custom-week-day][]" value="7" <?php checked(in_array("7", $recCustomWeekDay)) ?>/> Su
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
						<option value="1" <?php selected($recCustomMonthNumber, "1") ?>>First</option>
						<option value="2" <?php selected($recCustomMonthNumber, "2") ?>>Second</option>
						<option value="3" <?php selected($recCustomMonthNumber, "3") ?>>Third</option>
						<option value="4" <?php selected($recCustomMonthNumber, "4") ?>>Fourth</option>
						<option value="-1" <?php selected($recCustomMonthNumber, "5") ?>>Last</option>
					</select>
					<select name="recurrence[custom-month-day]">
						<option value="1"  <?php selected($recCustomMonthDay, "1") ?>>Monday</option>
						<option value="2" <?php selected($recCustomMonthDay, "2") ?>>Tuesday</option>
						<option value="3" <?php selected($recCustomMonthDay, "3") ?>>Wednesday</option>
						<option value="4" <?php selected($recCustomMonthDay, "4") ?>>Thursday</option>
						<option value="5" <?php selected($recCustomMonthDay, "5") ?>>Friday</option>
						<option value="6" <?php selected($recCustomMonthDay, "6") ?>>Saturday</option>
						<option value="7" <?php selected($recCustomMonthDay, "7") ?>>Sunday</option>
						<option value="-" <?php selected($recCustomMonthDay, "-") ?>>-</option>
						<option value="-1" <?php selected($recCustomMonthDay, "-1") ?>>Day</option>
						<option value="-2" <?php selected($recCustomMonthDay, "-2") ?>>Weekday</option>
						<option value="-3" <?php selected($recCustomMonthDay, "-3") ?>>Weekend Day</option>
					</select>
				</div>
			</td>
		</tr>
		<tr class="custom-recurrence-row" id="custom-recurrence-years"  style="display: <?php echo $recCustomType == "Yearly" ? "table-row" : "none" ?>;">
			<td></td>
			<td>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month][]" value="1" <?php checked(in_array("1", $recCustomYearMonth)) ?>/> Jan
					<input type="checkbox" name="recurrence[custom-year-month][]" value="2" <?php checked(in_array("2", $recCustomYearMonth)) ?>/> Feb
					<input type="checkbox" name="recurrence[custom-year-month][]" value="3" <?php checked(in_array("3", $recCustomYearMonth)) ?>/> Mar
					<input type="checkbox" name="recurrence[custom-year-month][]" value="4" <?php checked(in_array("4", $recCustomYearMonth)) ?>/> Apr
					<input type="checkbox" name="recurrence[custom-year-month][]" value="5" <?php checked(in_array("5", $recCustomYearMonth)) ?>/> May
					<input type="checkbox" name="recurrence[custom-year-month][]" value="6" <?php checked(in_array("6", $recCustomYearMonth)) ?>/> Jun
				</div>
				<div>
					<input type="checkbox" name="recurrence[custom-year-month][]" value="7" <?php checked(in_array("7", $recCustomYearMonth)) ?>/> Jul
					<input type="checkbox" name="recurrence[custom-year-month][]" value="8" <?php checked(in_array("8", $recCustomYearMonth)) ?>/> Aug
					<input type="checkbox" name="recurrence[custom-year-month][]" value="9" <?php checked(in_array("9", $recCustomYearMonth)) ?>/> Sep
					<input type="checkbox" name="recurrence[custom-year-month][]" value="10" <?php checked(in_array("10", $recCustomYearMonth)) ?>/> Oct
					<input type="checkbox" name="recurrence[custom-year-month][]" value="11" <?php checked(in_array("11", $recCustomYearMonth)) ?>/> Nov
					<input type="checkbox" name="recurrence[custom-year-month][]" value="12" <?php checked(in_array("12", $recCustomYearMonth)) ?>/> Dec
				</div>
				<div>
					<input type="checkbox" name="recurrence[custom-year-filter]" value="1" <?php checked($recCustomYearFilter, "1") ?>/> On the:
					<select name="recurrence[custom-year-month-number]">
						<option value="1" <?php selected($recCustomYearMonthNumber, "1") ?>>First</option>
						<option value="2" <?php selected($recCustomYearMonthNumber, "2") ?>>Second</option>
						<option value="3" <?php selected($recCustomYearMonthNumber, "3") ?>>Third</option>
						<option value="4" <?php selected($recCustomYearMonthNumber, "4") ?>>Fourth</option>
						<option value="-1" <?php selected($recCustomYearMonthNumber, "-1") ?>>Last</option>
					</select>
					<select name="recurrence[custom-year-month-day]">
						<option value="1"  <?php selected($recCustomYearMonthDay, "1") ?>>Monday</option>
						<option value="2" <?php selected($recCustomYearMonthDay, "2") ?>>Tuesday</option>
						<option value="3" <?php selected($recCustomYearMonthDay, "3") ?>>Wednesday</option>
						<option value="4" <?php selected($recCustomYearMonthDay, "4") ?>>Thursday</option>
						<option value="5" <?php selected($recCustomYearMonthDay, "5") ?>>Friday</option>
						<option value="6" <?php selected($recCustomYearMonthDay, "6") ?>>Saturday</option>
						<option value="7" <?php selected($recCustomYearMonthDay, "7") ?>>Sunday</option>
						<option value="-" <?php selected($recCustomYearMonthDay, "-") ?>>-</option>
						<option value="-1" <?php selected($recCustomYearMonthDay, "-1") ?>>Day</option>
						<option value="-2" <?php selected($recCustomYearMonthDay, "-2") ?>>Weekday</option>
						<option value="-3" <?php selected($recCustomYearMonthDay, "-3") ?>>Weekend Day</option>
					</select>
				</div>
			</td>
		</tr>