jQuery( document ).ready( function ( $ ) {
	
	var tribe_is_paged = tribe_ev.fn.get_url_param('tribe_paged');	
	var tribe_display = tribe_ev.fn.get_url_param('tribe_event_display');	
	
	if( tribe_is_paged ) {
		tribe_ev.state.paged = tribe_is_paged;
	} 

	if( tribe_display ) {
		tribe_ev.state.view = tribe_display;
	} else {
		tribe_ev.state.view = 'list';
	}

	if( tribe_ev.tests.pushstate && !tribe_ev.tests.map_view() ) {	
		
		var params = 'action=tribe_list&tribe_paged=' + tribe_ev.state.paged;

		if( tribe_ev.data.params.length ) 
			params = params + '&' + tribe_ev.data.params;		
		
		history.replaceState({									
			"tribe_params": params,
			"tribe_url_params": tribe_ev.data.params
		}, '', location.href);		

		$(window).on('popstate', function(event) {			

			var state = event.originalEvent.state;

			if( state ) {				
				tribe_ev.state.do_string = false;
				tribe_ev.state.pushstate = false;	
				tribe_ev.state.popping = true;
				tribe_ev.state.params = state.tribe_params;
				tribe_ev.state.url_params = state.tribe_url_params;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_list_ajax_post();	
				});
				
				tribe_ev.fn.set_form( tribe_ev.state.params );				
			} 
		} );		
	}

	$( '#tribe-events-list-view' ).on( 'click', 'li.tribe-nav-next a', function ( e ) {
		e.preventDefault();
		$('#tribe-bar-date').val('');
		if( $(this).parent().is('.tribe-past') )
			tribe_ev.state.view = 'past';			
		else
			tribe_ev.state.view = 'list';		
		
		tribe_ev.data.cur_url = tribe_ev.fn.url_path($(this).attr('href'));
		
		tribe_ev.state.paged++;	
		
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() { 
			tribe_events_list_ajax_post();
		});
	} ).on( 'click', 'li.tribe-nav-previous a', function ( e ) {
		e.preventDefault();
		$('#tribe-bar-date').val('');
		if( $(this).parent().is('.tribe-past') )
			tribe_ev.state.view = 'past';			
		else
			tribe_ev.state.view = 'list';		
		
		tribe_ev.data.cur_url = tribe_ev.fn.url_path($(this).attr('href'));
		
		if ( tribe_ev.state.paged > 1 ) {
			tribe_ev.state.paged--;
		}
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() {
			tribe_events_list_ajax_post();
		});
	} );

	tribe_ev.fn.snap( '#tribe-events-list-view', '#tribe-events-list-view', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );		

	function tribe_events_bar_listajax_actions(e) {
		if ( tribe_events_bar_action != 'change_view' ) {
			e.preventDefault();
			tribe_ev.state.paged = 1;
			tribe_ev.state.view = 'list';
			tribe_ev.state.popping = false;
			tribe_ev.fn.pre_ajax( function() {
				tribe_events_list_ajax_post();
			});
		}
	}

	if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {
		$('#tribe-bar-date').on( 'change', function (e) {	
			if( !tribe_ev.tests.reset_on() ) {
				tribe_ev.state.popping = false;
				tribe_events_bar_listajax_actions(e);
			}
		} );
	}

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {
		tribe_ev.state.popping = false;
		tribe_events_bar_listajax_actions(e);
	} );

	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {	
		tribe_ev.state.popping = false;
		tribe_events_bar_listajax_actions(e);			
		tribe_ev.fn.hide_settings();
	} );
	
	$(tribe_ev.events).on("tribe_ev_runAjax", function() {
		tribe_events_list_ajax_post();		
	});

	function tribe_events_list_ajax_post() {			

		tribe_ev.fn.spin_show();

		if( !tribe_ev.state.popping ) {
			
			if( tribe_ev.state.filter_cats )
				tribe_ev.data.cur_url = $('#tribe-events-header').attr( 'data-baseurl' );

			var tribe_hash_string = $( '#tribe-events-list-hash' ).val();
			
			tribe_ev.state.params = {
				action     :'tribe_list',
				tribe_paged:tribe_ev.state.paged,
				tribe_event_display: tribe_ev.state.view
			};

			tribe_ev.state.url_params = {
				action     :'tribe_list',
				tribe_paged:tribe_ev.state.paged				
			};							

			if( tribe_hash_string.length ) {
				tribe_ev.state.params['hash'] = tribe_hash_string;
			}
			
			if( tribe_ev.state.category ) {
				tribe_ev.state.params['tribe_event_category'] = tribe_ev.state.category;
			}			

			$(tribe_ev.events).trigger('tribe_ev_serializeBar');

			tribe_ev.state.params = $.param(tribe_ev.state.params);
			tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);
			
			$(tribe_ev.events).trigger('tribe_ev_collectParams');

			tribe_ev.state.pushstate = false;
			tribe_ev.state.do_string = true;				

		}

		if( tribe_ev.tests.pushstate && !tribe_ev.state.filter_cats ) {
			
			$(tribe_ev.events).triggerAll('tribe_ev_ajaxStart tribe_ev_listView_AjaxStart');					

			$.post(
				TribeList.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					
					tribe_ev.fn.spin_hide();
					tribe_ev.state.initial_load = false;
					tribe_ev.fn.enable_inputs( '#tribe_events_filters_form', 'input, select' );

					if ( response.success ) {						
						
						$(tribe_ev.events).triggerAll('tribe_ev_ajaxSuccess tribe_ev_listView_AjaxSuccess');

						tribe_ev.data.ajax_response = {
							'type':'tribe_events_ajax',
							'post_count':parseInt(response.total_count),
							'view':'list',
							'max_pages':response.max_pages,
							'page':tribe_ev.state.paged,
							'timestamp':new Date().getTime()
						};							

						$( '#tribe-events-list-hash' ).val( response.hash );
						$( '#tribe-events-list-view' ).html( response.html );
						
						if( tribe_ev.state.do_string ) {															
							history.pushState({									
								"tribe_params": tribe_ev.state.params,
								"tribe_url_params": tribe_ev.state.url_params
							}, '', tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params);															
						}						

						if( tribe_ev.state.pushstate ) {																
							history.pushState({									
								"tribe_params": tribe_ev.state.params,
								"tribe_url_params": tribe_ev.state.url_params
							}, '', tribe_ev.data.cur_url);
						}							
					}
				}
			);
		} else {
			if( tribe_ev.state.do_string ) 
				window.location = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;													
			else
				window.location = tribe_ev.data.cur_url;			
		}
	} 		
} );