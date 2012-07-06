jQuery(document).ready(function($) {
	
	// filters & columns box. Move into place. Show/hide.
	var theFilters = $("#the-filters"),
	tribeFilters = $("#tribe-filters"),
	tribeFiltersHeader = tribeFilters.find("h3");

	tribeFilters
		.insertAfter(".wrap > h2:first")
		.removeClass("wrap");
	tribeFiltersHeader.click(function() {
		if ( theFilters.is(':visible') )
			$.cookie("hideFiltersAndColumns", "true"); // cookies only store strings
		else
			$.cookie("hideFiltersAndColumns", "false");
		
		theFilters.toggle();
		$("#filters-wrap").toggleClass("closed");
	});
	// hide it if it was hidden
	if ( $.cookie("hideFiltersAndColumns") === "true" )
		tribeFiltersHeader.click();
	
	// Also the arrow
	tribeFilters.find(".handlediv").click(function() { tribeFiltersHeader.click()	})
	
	// so we preserve our state when clicking on all/published/drafts
	/*
	$(".subsubsub a").click(function(event) {
		event.preventDefault();
		var url = $(this).attr("href"),
		form = $("#the-filters");
		form.attr("action", url).submit();
	});
	*/
	
	// un-fixed width columns
	$("#posts-filter .fixed").removeClass("fixed");

	// Save/Cancel Filters
	$("#the-filters .save.button-secondary").click(function(ev) {
		$(this).parent().hide().find("input").attr("disabled", "disabled");
		$("#the-filters .save-options").show();
		$("#filter_name").focus();
		ev.preventDefault();
	});
	$("#cancel-save").click(function(ev) {
		$(this).parent().hide();
		$("#the-filters .actions").show().find("input").removeAttr("disabled");
		ev.preventDefault();
	});
	
	// Save that Filter
	$("#filter_name").keypress(function(ev){
		if ( ev.keyCode == 13 ) {
			ev.preventDefault()
			$(this).next().click()
		}
	})
	
	// Maintain sorting
	$(".tribe-filters-active .wp-list-table .sortable a").click(function(ev) {
		theFilters.attr("action", this.href).submit()
		ev.preventDefault()
	})

});

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * @url http://plugins.jquery.com/files/jquery.cookie.js.txt
 */
jQuery.cookie=function(B,I,L){if (typeof I!="undefined"){L=L||{};if (I===null){I="";L.expires=-1;}var E="";if (L.expires&&(typeof L.expires=="number"||L.expires.toUTCString)){var F;if (typeof L.expires=="number"){F=new Date();F.setTime(F.getTime()+(L.expires*24*60*60*1000));}
else {F=L.expires;}E="; expires="+F.toUTCString();}var K=L.path?"; path="+(L.path):"";var G=L.domain?"; domain="+(L.domain):"";var A=L.secure?"; secure":"";document.cookie=[B,"=",encodeURIComponent(I),E,K,G,A].join("");}
else {var D=null;if (document.cookie&&document.cookie!=""){var J=document.cookie.split(";");for(var H=0;H<J.length;H++){var C=jQuery.trim(J[H]);if (C.substring(0,B.length+1)==(B+"=")){D=decodeURIComponent(C.substring(B.length+1));break;}}}return D;}};