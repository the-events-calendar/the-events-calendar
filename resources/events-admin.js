jQuery(document).ready(function($) {
	
	var datepickerOpts = { 
		dateFormat: 'yy-mm-dd',
		showAnim: 'fadeIn',
		changeMonth: true,
		changeYear: true,
		numberOfMonths: 3,
		showButtonPanel: true,
		onSelect: function(selectedDate) {
			var option = this.id == "EventStartDate" ? "minDate" : "maxDate";
			var instance = $(this).data("datepicker");
			var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
			dates.not(this).datepicker("option", option, date);
		}
	};
	$.extend(datepickerOpts, TEC);
	var dates = $("#EventStartDate, #EventEndDate").datepicker(datepickerOpts);

	// toggle time input
	$('#allDayCheckbox').click(function(){
		$(".timeofdayoptions").toggle();
		$("#EventTimeFormatDiv").toggle();
	});
	if( $('#allDayCheckbox').attr('checked') == true ) {
		$(".timeofdayoptions").addClass("tec_hide");
		$("#EventTimeFormatDiv").addClass("tec_hide");
	}

	//show state/province input based on first option in countries list, or based on user input of country
	function spShowHideCorrectStateProvinceInput(country) {
		if (country == 'US') {
			$("#USA").removeClass("tec_hide");
			$("#International").addClass("tec_hide");
			$('input[name="EventStateExists"]').val(1);
		} else if ( country != '' ) {
			$("#International").removeClass("tec_hide");
			$("#USA").addClass("tec_hide");
			$('input[name="EventStateExists"]').val(0);			
		} else {
			$("#International").addClass("tec_hide");
			$("#USA").addClass("tec_hide");
			$('input[name="EventStateExists"]').val(0);
		}
	}
	
	spShowHideCorrectStateProvinceInput( $("#EventCountry > option:selected").attr('label') );
	
	$("#EventCountry").change(function() {
		var countryLabel = $(this).find('option:selected').attr('label');
		$('input[name="EventCountryLabel"]').val(countryLabel);
		spShowHideCorrectStateProvinceInput( countryLabel );
	});
	
	var spDaysPerMonth = [29,31,28,31,30,31,30,31,31,30,31,30,31];
	
	// start and end date select sections
	var spStartDays = [ $('#28StartDays'), $('#29StartDays'), $('#30StartDays'), $('#31StartDays') ];
	var spEndDays = [ $('#28EndDays'), $('#29EndDays'), $('#30EndDays'), $('#31EndDays') ];
			
	$("select[name='EventStartMonth'], select[name='EventEndMonth']").change(function() {
		var t = $(this);
		var startEnd = t.attr("name");
		// get changed select field
		if( startEnd == 'EventStartMonth' ) startEnd = 'Start';
		else startEnd = 'End';
		// show/hide date lists according to month
		var chosenMonth = t.attr("value");
		if( chosenMonth.charAt(0) == '0' ) chosenMonth = chosenMonth.replace('0', '');
		// leap year
		var remainder = $("select[name='Event" + startEnd + "Year']").attr("value") % 4;
		if( chosenMonth == 2 && remainder == 0 ) chosenMonth = 0;
		// preserve selected option
		var currentDateField = $("select[name='Event" + startEnd + "Day']");

		$('.event' + startEnd + 'DateField').remove();
		if( startEnd == "Start") {
			var selectObject = spStartDays[ spDaysPerMonth[ chosenMonth ] - 28 ];
			selectObject.val( currentDateField.val() );
			$("select[name='EventStartMonth']").after( selectObject );
		} else {
			var selectObject = spEndDays[ spDaysPerMonth[ chosenMonth ] - 28 ];
			selectObject.val( currentDateField.val() );
			$('select[name="EventEndMonth"]').after( selectObject );
		}
	});
	
	$("select[name='EventStartMonth'], select[name='EventEndMonth']").change();
	
	$("select[name='EventStartYear']").change(function() {
		$("select[name='EventStartMonth']").change();
	});
	
	$("select[name='EventEndYear']").change(function() {
		$("select[name='EventEndMonth']").change();
	});
	// hide / show google map toggles
	var tecAddressExists = false;
	var tecAddressInputs = ["EventAddress","EventCity","EventZip"];
	function tecShowHideGoogleMapToggles() {
		var selectValExists = false;
		var inputValExists = false;
			if($('input[name="EventCountryLabel"]').val()) selectValExists = true;
			$.each( tecAddressInputs, function(key, val) {
				if( $('input[name="' + val + '"]').val() ) {
					inputValExists = true;
					return false;
				}
			});
		if( selectValExists || inputValExists ) $('tr#google_map_link_toggle,tr#google_map_toggle').removeClass('tec_hide');
		else $('tr#google_map_link_toggle,tr#google_map_toggle').addClass('tec_hide');
	}
	$.each( tecAddressInputs, function(key, val) {
		$('input[name="' + val + '"]').bind('keyup', function(event) {
			var textLength = event.currentTarget.textLength;
			if(textLength == 0) tecShowHideGoogleMapToggles();
			else if(textLength == 1) tecShowHideGoogleMapToggles();
		});
	});
	$('select[name="EventCountry"]').bind('change', function(event) {
		if(event.currentTarget.selectedIndex) tecShowHideGoogleMapToggles();
		else tecShowHideGoogleMapToggles();
	});
	tecShowHideGoogleMapToggles();
	// Form validation
	$("form[name='post']").submit(function() {
		if( $("#isEventNo").attr('checked') == true ) {
			// do not validate since this is not an event
			return true;
		}
		return true;
	});
	
});
