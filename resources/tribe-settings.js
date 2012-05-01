jQuery(document).ready(function($) {

	// init chosen
	$('.tribe-field-dropdown_chosen select').chosen();

	updateVenueFields();
	updateMapsFields();

	$('[name="eventsDefaultVenueID"]').change(function() {
		updateVenueFields();
	})

	// toggle view of the google maps size fields
	$('.google-embed-size input').change(function() {
		updateMapsFields();
	})

	// toggle view of the venue defaults fields
	function updateVenueFields() {
		if($('#tribe-field-eventsDefaultVenueID select').find('option:selected').val() != '0') {
			$('.venue-default-info').fadeOut();
		} else {
			$('.venue-default-info').fadeIn();
		}
	}

	// toggle view of the google maps size fields
	function updateMapsFields() {
		if($('.google-embed-size input').attr("checked")) {
			$('.google-embed-field').slideDown();
		} else {
			$('.google-embed-field').slideUp();
		}
	}

});