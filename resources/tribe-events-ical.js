$( tribe_ev.events ).on( "tribe_ev_ajaxSuccess", function () {
	var params = tribe_ev.state.params;
	var url_params = tribe_ev.state.url_params;

	console.log( params );
	console.log( url_params );

} );