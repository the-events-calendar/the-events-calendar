jQuery(document).ready(function($) {

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
		if($('[name="eventsDefaultVenueID"]').find('option:selected').val() != "0") {
			$('.venue-default-info').hide();
		} else {
			$('.venue-default-info').show();
		}
	}

	// toggle view of the google maps size fields
	function updateMapsFields() {
		if($('.google-embed-size input').attr("checked")) {
			$('.google-embed-field').show();
		} else {
			$('.google-embed-field').hide();
		}
	}

});