jQuery(document).ready(function($) {

	// Admin Google Maps Preview
	$('#event_venue input, #event_venue select').change(function() {
		if($('#EventShowMap').prop('checked')) {
			$('#googlemaps').parent().show();
		} else {
			$('#googlemaps').parent().hide();
		}

		var selectedOption = $('#saved_venue option:selected');
		if(selectedOption.val() == 0) {
			var form = $(this).closest('form'), street = form.find('[name="venue[Address]"]').val(),
				city = form.find('[name="venue[City]"]').val(),
				country = form.find('[name="venue[Country]"]').val(),
				state = form.find('[name="venue[Country]"] option:selected').val() == "US"
					?form.find('[name="venue[State]"]').val() : form.find('[name="venue[Province]"]').val(),
				zip = form.find('[name="venue[Zip]"]').val(),
				address = street + "," + city + "," + state + "," + country + "," + zip;

         if ( typeof codeAddress == 'function' )
            codeAddress(address);
		} else {
         if ( typeof codeAddress == 'function' )
            codeAddress(selectedOption.data('address'));
		}
		
	});

  $('#doaction, #doaction2').click(function(e){
      var n = $(this).attr('id').substr(2);
      if ( $('select[name="'+n+'"]').val() == 'edit' && $('.post_type_page').val() == 'tribe_events' ) {
         e.preventDefault();

         var ids = new Array();

         $('#bulk-titles div').each(function() {
            var id = $(this).attr('id'), postId = id.replace('ttle', ''), 
               title = $('#post-' + postId + ' .row-title').first().text(), 
               tempHolder = $('<div/>').append($(this).find('a'));
         $(this).html('').append(tempHolder).append(title);

            if(ids[id])
               $(this).remove();

            ids[id] = true;
         });
      }
   });


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
	
	function resetSubmitButton() {
		$('#publishing-action .button-primary-disabled').removeClass('button-primary-disabled');
		$('#publishing-action #ajax-loading').css('visibility', 'hidden');
		
	}
	
	function validRecDays() {
		if( $('[name="recurrence[custom-interval]"]').val() != parseInt($('[name="recurrence[custom-interval]"]').val()) && 
			$('[name="recurrence[type]"] option:selected"').val() == "Custom")
		{
			return false;
		}
		
		return true;
	}
	
	$('.wp-admin.events-cal #post').submit(function(e) {
		if(!validRecDays()) {
			e.preventDefault();
			alert($('#rec-days-error').text());
			$('#rec-days-error').show();
			resetSubmitButton();
		}
	});
	
	function validRecEnd() {
		if($('[name="recurrence[type]"]').val() != "None" && 
			$('[name="recurrence[end-type]"] option:selected"').val() == "On")
		{
			return $('[name="recurrence[end]"]').val() && 
			!$('[name="recurrence[end]"]').hasClass('placeholder');
		}
		
		return true;
	} 
	
	$('.wp-admin.events-cal #post').submit(function(e) {
		if(!validRecEnd()) {
			e.preventDefault();
			alert($('#rec-end-error').text());
			$('#rec-end-error').show();
			resetSubmitButton();
		}
	});

});