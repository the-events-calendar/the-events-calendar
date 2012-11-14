jQuery(document).ready(function($){
		
	// a function to find overlapping events. Manipulate css to adjust display. Extend selectors to do more fancy stuff with multiples.
					
	function tribe_find_overlapped_events($week_events) {			    

		$week_events.each(function() {
				
			var $this = $(this);
			var $target = $this.next();
				
			if($target.length){
					
				var tAxis = $target.offset();
				var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
				var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];			    
				var thisPos = $this.offset();
				var i_x = [thisPos.left, thisPos.left + $this.outerWidth()]
				var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];

				if ( t_x[0] < i_x[1] && t_x[1] > i_x[0] && t_y[0] < i_y[1] && t_y[1] > i_y[0]) {
						
					// we've got an overlap
						
					$this.css({
						"left":"0",
						"width":"60%"
					});
					$target.css({
						"right":"0",
						"width":"60%"
					});
				}
			}
		});			
	}
					
	var $week_events = $(".tribe-grid-body .tribe-grid-content-wrap .column > div[id*='tribe-events-event-']");
	var grid_height = $(".tribe-week-grid-inner-wrap").height();
		
	$week_events.hide();
		
	$week_events.each(function() {
			
		// let's iterate through each event in the main grid and set their length plus position in time.
			
		var $this = $(this);			
		var event_hour = $this.attr("data-hour");			
		var event_length = $this.attr("duration") - 14;	
		var event_min = $this.attr("data-min");
			
		// $event_target is our grid block with the same data-hour value as our event.
			
		var $event_target = $('.tribe-week-grid-block[data-hour="' + event_hour + '"]');
			
		// let's find it's offset from top of main grid container
			
		var event_position_top = 
		$event_target.offset().top -
		$event_target.parent().offset().top - 
		$event_target.parent().scrollTop();
			
		// now let's add the events minutes to the offset (relies on grid block being 60px, 1px per minute, nice)
			
		event_position_top = parseInt(Math.round(event_position_top)) + parseInt(event_min);
			
		// now let's see if we've exceeding space because this event runs into next day
			
		var free_space = grid_height - event_length - event_position_top;
			
		if(free_space < 0) {
			event_length = event_length + free_space - 14;
		}
			
		// ok we have all our values, let's set length and position from top for our event and show it.

		$this.css({
			"height":event_length + "px",
			"top":event_position_top + "px"
			}).show();			
	});
		
	// now that we have set our events up correctly let's deal with our overlaps
		
	tribe_find_overlapped_events($week_events);
		
	// let's set the height of the allday columns to the height of the tallest
		
	var all_day_height = $(".tribe-grid-allday .tribe-grid-content-wrap").height();
		
	$(".tribe-grid-allday .column").height(all_day_height);
		
	// let's set the height of the other columns for week days to be as tall as the main container
		
	var week_day_height = $(".tribe-grid-body").height();
		
	$(".tribe-grid-body .tribe-grid-content-wrap .column").height(week_day_height);
		
});