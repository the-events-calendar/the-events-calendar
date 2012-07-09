jQuery(document).ready(function($) {
	
	var form = $("#the-filters"),
	inactive = $("#tribe-filters-inactive"),
	active = $("#tribe-filters-active");
	
	inactive.change(function() {
		var picked = $(this).find(":selected"),
		val = picked.val(),
		html = Tribe_Filters.template[val];
		if ( val == "0" ) // ignore the pseudo-header
			return false;
		
		$(html).css({opacity:0}).appendTo(active).animate({opacity:1});
		picked.remove();
	});
	
	active.delegate(".close", "click", function(){
		var clicked = $(this),
			row = clicked.parent().parent("tr"),
			val = row.find(":input:last").attr("name")
		val = cleanUpKey(val)
		inactive.append(Tribe_Filters.option[val])
		row.fadeRemove()
	});
	
	function cleanUpKey(val) {
		return val
			.replace(Tribe_Filters.valPrefix, "")
			.replace(Tribe_Filters.prefix, "")
			.replace(/\[\]$/, "");
	}
	
	// toggle single- or multi-selct
	active.delegate('.multi-toggle', 'click', function(event) {
		var me = $(this),
		select = me.prev(),
		name = select.attr("name");
		
		if ( me.hasClass("on") ) {
			select.attr("multiple", "multiple").attr("name", name+"[]");
			me.text("-");
		}
		else {
			select.removeAttr("multiple").attr("name", name.replace(/\[\]$/, ""));
			me.text("+");
		}
		select.toggleClass("multi-active");
		me.toggleClass("on");
		
	});
	
	// view a saved filter
	$("#tribe-saved-filters").change(function(){
		var me = $(this),
		id = me.val(),
		url = me.attr("data:submit_url") + id;

		if ( id > 0 )
			window.location = url;
	});
	
	// clicking on page numbers - need to save the goodness
	$("#posts-filter .tablenav-pages").delegate('a', 'click', function(ev) {
		ev.preventDefault();
		var base_url = $(this).attr("href");

		form.attr("action", base_url).submit();
	});
	
	// Update Filters
	form.find("input[name=tribe-update-saved-filter]").click(function() {
		// change to current URL to keep the saved_filter bit.
		form.attr("action", window.location.href);
	});
	
	// add the # of posts found, if applicable
	if ( 0 == $(".tablenav-pages").size() ) {
		$("<div class='tablenav-pages'><span class='displaying-num'>"+ Tribe_Filters.displaying +"</span></div>").prependTo(".tablenav");
	}
	
	// Datepicker
	active.delegate('.tribe-datepicker', 'focusin', function(event) {
		$(this).datepicker({
			dateFormat: 'yy-mm-dd',
			changeYear: true,
			changeMonth: true,
			numberOfMonths: 2
		});
	});
	
});


(function($){
	$.fn.fadeRemove = function(speed) {
		return $(this).animate({opacity: 0}, speed, function() {
		  $(this).remove();
		});
	};
})(jQuery);