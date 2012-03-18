jQuery(document).ready(function($) {

	// init chosen
	$('.tribe-field-dropdown_chosen select').chosen();

	// init tooltips
	$(".tribe-settings-form fieldset").tooltip({
    position: "center right",
    offset: [-4, 10],
    effect: "fade",
    opacity: 1,
    layout: '<div><div class="wp-pointer-content"><p class="tribe-tooltip-inner"></p></div><div class="wp-pointer-arrow"><div class="wp-pointer-arrow-inner"></div></div></div>',
    tipInner: 'tribe-tooltip-inner',
    tipClass: "wp-pointer-left tribe-tooltip"
  });

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