jQuery(document).ready(function($) {
	
	var dropdown = $("#tribe-cols-drop"),
	activeList = $("#tribe-cols-active");
	
	activeList.sortable({
		placeholder: 'ui-sortable-placeholder',
		stop: normalizeActiveInputs
	});
	
	dropdown.change(function() {
		var active = $(this).find(":selected"),
		item = Tribe_Columns.item.replace('%name%', active.text() ),
		input = Tribe_Columns.input.replace('%value%', active.val() );

		if ( active.val() == 0 )
			return false;
		
		$(item).prepend(input).appendTo(activeList);
		active.remove();
		normalizeActiveInputs();
	});
	
	activeList.delegate(".close", "click", function() {
		var active = $(this).parent();
		$(this).remove();
		var value = active.find("input").val();
		var name = active.text();

		if ( value == 'comments' ) {
			name = 'Comments';
		}

		option = $('<option></option>').val(value).text(name);
		
		option.appendTo(dropdown);
	
		active.remove();
		normalizeActiveInputs();
	});
	
	function normalizeActiveInputs() {
		activeList.children().each(function(idx) {
			var i = idx + 1;
			$(this).find("input").attr("name", Tribe_Columns.prefix + i );
		});
	}
});
