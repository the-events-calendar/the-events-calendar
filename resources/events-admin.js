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
});
