jQuery(document).ready(function($) {
	if(typeof(TEC) != 'undefined'){
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
		
		if( $('#allDayCheckbox').attr("checked") === true || $('#allDayCheckbox').attr("checked") === "checked" ) {
			$(".timeofdayoptions").addClass("tec_hide");
			$("#EventTimeFormatDiv").addClass("tec_hide");
		}

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

		// Form validation
		$("form[name='post']").submit(function() {
			if( $("#isEventNo").attr('checked') === true || $("#isEventNo").attr('checked') === "checked" ) {
				// do not validate since this is not an event
				return true;
			}
			return true;
		});

		// hide unnecessary fields
		var venueFields = $(".venue");

		var savedVenue = $("#saved_venue");
		
		if ( savedVenue.val() != '0' && !$('.nosaved').get(0) ) {
			venueFields.hide();
			$('input',venueFields).val('');
		}
		
		savedVenue.change(function() {
			if ( $(this).val() == '0' ) {
				venueFields.fadeIn()
					//.find("input, select").val('').removeAttr('checked');
			}
			else {
				venueFields.fadeOut();
			}
		});
		// hide unnecessary fields
		var organizerFields = $(".organizer");

		var savedorganizer = $("#saved_organizer");
		
		if ( savedorganizer.val() != '0' && !$('.nosaved_organizer').get(0) ) {
			organizerFields.hide();
			$('input',organizerFields).val('');
		}
		
		savedorganizer.change(function() {
			if ( $(this).val() == '0' ) {
				organizerFields.fadeIn()
					//.find("input, select").val('').removeAttr('checked');
			}
			else {
				organizerFields.fadeOut();
			}
		});
	}

	//show state/province input based on first option in countries list, or based on user input of country
	function spShowHideCorrectStateProvinceInput(country) {
		if (country == 'US') {
			$("#StateProvinceSelect").removeClass("tec_hide");
			$("#StateProvinceText").addClass("tec_hide");
		} else if ( country != '' ) {
			$("#StateProvinceText").removeClass("tec_hide");
			$("#StateProvinceSelect").addClass("tec_hide");
		} else {
			$("#StateProvinceText").addClass("tec_hide");
			$("#StateProvinceSelect").addClass("tec_hide");
		}
	}
	
	spShowHideCorrectStateProvinceInput( $("#EventCountry > option:selected").attr('label') );

	$("#EventCountry").change(function() {
		var countryLabel = $(this).find('option:selected').attr('label');
		spShowHideCorrectStateProvinceInput( countryLabel );
	});
	
});
