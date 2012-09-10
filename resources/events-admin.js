jQuery(document).ready(function($) {

	// Load the Chosen JQuery plugin for all select elements with the class 'chosen'.
	$('.chosen, .tribe-field-dropdown_chosen select').chosen();

	//not done by default on front end
	$('.hide-if-js').hide();

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
				dates.not(this).not('#recurrence_end').datepicker("option", option, date);
			}
		};
		$.extend(datepickerOpts, TEC);
		var dates = $("#EventStartDate, #EventEndDate, .datepicker").datepicker(datepickerOpts);
		
		// toggle time input
		function toggleDayTimeDisplay(){
			if( $('#allDayCheckbox').attr("checked") === true || $('#allDayCheckbox').attr("checked") === "checked" ) {
				$(".timeofdayoptions").hide();
				$("#EventTimeFormatDiv").hide();
			} else {
				$(".timeofdayoptions").show();
				$("#EventTimeFormatDiv").show();				
			}
		}
		// check on click
		$('#allDayCheckbox').click(function(){
			toggleDayTimeDisplay();
		});
		// check on load
		toggleDayTimeDisplay();
		
		var tribeDaysPerMonth = [29,31,28,31,30,31,30,31,31,30,31,30,31];
		
		// start and end date select sections
		var tribeStartDays = [ $('#28StartDays'), $('#29StartDays'), $('#30StartDays'), $('#31StartDays') ];
		var tribeEndDays = [ $('#28EndDays'), $('#29EndDays'), $('#30EndDays'), $('#31EndDays') ];
				
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
				var selectObject = tribeStartDays[ tribeDaysPerMonth[ chosenMonth ] - 28 ];
				selectObject.val( currentDateField.val() );
				$("select[name='EventStartMonth']").after( selectObject );
			} else {
				var selectObject = tribeEndDays[ tribeDaysPerMonth[ chosenMonth ] - 28 ];
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
		
		if ( savedVenue.size() > 0 && savedVenue.val() != '0' && !$('.nosaved').get(0) ) {
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
		
		if ( savedorganizer.size() > 0 && savedorganizer.val() != '0' && !$('.nosaved_organizer').get(0) ) {
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
	function tribeShowHideCorrectStateProvinceInput(country) {
		if (country == 'US' || country == 'United States') {
			$("#StateProvinceSelect_chzn").show();
			$("#StateProvinceText").hide();
		} else if ( country != '' ) {
			$("#StateProvinceText").show();
			$("#StateProvinceSelect_chzn").hide();
		} else {
			$("#StateProvinceText").hide();
			$("#StateProvinceSelect_chzn").hide();
		}
	}
	
	tribeShowHideCorrectStateProvinceInput( $("#EventCountry > option:selected").val() );

	$("#EventCountry").change(function() {
		var countryLabel = $(this).find('option:selected').val();
		tribeShowHideCorrectStateProvinceInput( countryLabel );
	});

	// If recurrence changes on a recurring event, then show warning, and automatically change whole recurrence
	if($('[name="is_recurring"]').val() == "true" && !$('[name="recurrence_action"]').val() ) {	
		function recurrenceChanged() {
			$('#recurrence-changed-row').show();
			$('[name="recurrence_action"]').val(2);
		}

		$('.recurrence-row input, .custom-recurrence-row input,.recurrence-row select, .custom-recurrence-row select').change(recurrenceChanged)
		$( '[name="recurrence[end]"]' ).datepicker('option', 'onSelect', recurrenceChanged);
	}
	
	$( '[name="recurrence[end]"]' ).datepicker('option', 'onSelect', function() {
		$('[name="recurrence[end]"]').removeClass('placeholder');
	});	
	
	/* Fix for deleting multiple events */
	$('.wp-admin.events-cal.edit-php #doaction').click(function(e) {
		if($("[name='action'] option:selected").val() == "trash") {
			if(confirm("Are you sure you want to trash all occurrences of these events? All recurrence data will be lost.")) {
				var ids = new Array();

				$('[name="post[]"]:checked').each(function() {
					var curval = $(this).val();
					if(ids[curval]) {
						$(this).prop('checked', false);
					}

					ids[curval] = true;
				});
			} else {
				e.preventDefault();
			}
		}
	});
	
	function isExistingRecurringEvent() {
		return $('[name="is_recurring"]').val() == "true" && !$('[name="recurrence_action"]').val() && !$('[name="recurrence_action"]').val()
	}
	
	function resetSubmitButton() {
		$('#publishing-action .button-primary-disabled').removeClass('button-primary-disabled');
		$('#publishing-action #ajax-loading').css('visibility', 'hidden');
		
	}
	
	$('#EventInfo input, #EventInfo select').change(function() {
		$('.rec-error').hide();
	})
	
	var eventSubmitButton = $('.wp-admin.events-cal #post #publishing-action input[type="submit"]');
	eventSubmitButton.click(function(e) {
		$(this).data('clicked', true);
	});
	
	/* Recurring Events Dialog */
	$('.wp-admin.events-cal #post').submit(function(e) {
		var self = $(this);
		
		if( isExistingRecurringEvent() ) { // not a new event
			e.preventDefault();
			$('#recurring-dialog').dialog({
				modal: true,
				buttons: [{
						text:"Only This Event",
						click: function() { 
							$('[name="recurrence_action"]').val(3);
							
							if (eventSubmitButton.data('clicked') )
								$('<input type="hidden" name="' + eventSubmitButton.attr('name') + '" value="' + eventSubmitButton.val() + '"/>').appendTo(self);
							
							$(this).dialog("close"); 							
							self.submit();
						}
				}, {
						text:"All Events",
						click: function() { 
							$('[name="recurrence_action"]').val(2);

							if (eventSubmitButton.data('clicked') )
								$('<input type="hidden" name="' + eventSubmitButton.attr('name') + '" value="' + eventSubmitButton.val() + '"/>').appendTo(self);							

							$(this).dialog("close"); 
							self.submit();
						}				
				}],
				close: function() {
					eventSubmitButton.data('clicked', null);
				}
			});
		}
	});	
	
	function setupSubmitButton() {
		//publishing-action		
	}

	$('.wp-admin.events-cal .submitdelete').click(function(e) {

		var link = $(this);
		var isRecurringLink = $(this).attr('href').split('&eventDate');

		if(isRecurringLink[1]) {
			e.preventDefault();

			$('#deletion-dialog').dialog({
				//submitdelete
				modal: true,
				buttons: [{
					text: "Only This Event",
					click: function() {
						document.location = link.attr('href') + '&event_start=' + $(this).data('start');
					}
				},
				{
					text: "All Events",
					click: function() {
						document.location = link.attr('href') + '&deleteAll';
					}
				}]
			});
		}

	});

	// recurrence ui
	$('[name="recurrence[type]"]').change(function() {
		var curOption =  $(this).find("option:selected").val();
		$('.custom-recurrence-row').hide();

		if (curOption == "Custom" ) {
			$('#recurrence-end').show();
			$('#custom-recurrence-frequency').show();
			$('[name="recurrence[custom-type]"]').change();
		} else if (curOption == "None") {
			$('#recurrence-end').hide();
			$('#custom-recurrence-frequency').hide();				
		} else {
			$('#recurrence-end').show();
			$('#custom-recurrence-frequency').hide();
		}
	});
	
	$('[name="recurrence[end-type]"]').change(function() {
		var val = $(this).find('option:selected').val();
		
		if (val == "On") {
			$('#rec-count').hide();
			$('#recurrence_end').show();
		} else {
			$('#recurrence_end').hide();
			$('#rec-count').show();
		}
	});
	
	$('[name="recurrence[custom-type]"]').change(function() {
		$('.custom-recurrence-row').hide();
		var option = $(this).find('option:selected'), customSelector = option.data('tablerow');
		$(customSelector).show()
		$('#recurrence-interval-type').text(option.data('plural'));
		$('[name="recurrence[custom-type-text]"]').val(option.data('plural'));
	});
	
	$('#recurrence_end_count').change(function() {
		$('[name="recurrence[type]"]').change();
	});	
	
	$('[name="recurrence[type]"]').change(function() {
		var option = $(this).find('option:selected'), numOccurrences = $('#recurrence_end_count').val();
		$('#occurence-count-text').text(numOccurrences == 1 ? option.data('single') : option.data('plural'));
		$('[name="recurrence[occurrence-count-text]"]').val($('#occurence-count-text').text());
	});
	
	$('[name="recurrence[custom-month-number]"]').change(function() {
		var option = $(this).find('option:selected'), dayselect = $('[name="recurrence[custom-month-day]"]');
		
		if(isNaN(option.val())) {
			dayselect.show();
		} else {
			dayselect.hide();
		}
	});

	function maybeDisplayPressTrendsDialogue() {
		return $('[name="maybeDisplayPressTrendsDialogue"]').val() == "1"
	}

	if( maybeDisplayPressTrendsDialogue() ) {
			$('#presstrends-dialog').dialog({
				modal: true,
				buttons: [{
						text:"Send data",
						click: function() { 
							$('[name="presstrends_action"]').val(1);
							$(this).dialog("close"); 							
							$('[name="sendPressTrendsData"]').prop("checked", true);
							$('#tribeSaveSettings').click();
						}
				}, {
						text:"Do not send data",
						click: function() { 
							$('[name="presstrends_action"]').val(0);
							$(this).dialog("close"); 
							$('[name="sendPressTrendsData"]').prop("checked", false);
						}
				}]
			});
			
		}
	
});
