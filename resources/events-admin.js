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
			codeAddress(selectedOption.data('address'));
		}
		
	});
});
